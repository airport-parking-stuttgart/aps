<?php

class ReviewXPro_Import_Review {

    private static $file;
    private static $external_product_id;
    private static $csv_header;
    private static $csv_data;
    private $success_count;
    private $error_count;

    function __construct() {
        add_action( 'wp_ajax_rx_import_review', array( $this, 'rx_import_review' ) );
        add_action( 'wp_ajax_create_array_group_by_product', array( $this, 'create_array_group_by_product' ) );
        add_action( 'wp_ajax_rx_revert_review', array( $this, 'rx_revert_review' ) );
    }

    /**
     * Import review ajax call
     */
    function rx_import_review() {
        if ( ! isset( $_POST['security'] ) ) {
            return;
        }
        $security = check_ajax_referer( 'special-string', 'security' );
        if ( false == $security ) {
            return;
        }
        $form_state = isset( $_POST['form_state'] ) ? $_POST['form_state'] : 0;

        if ( isset( $_FILES['csv_file'] ) ) {
            $file_type = $_FILES['csv_file']['type'];
            //var_dump($file_type);//application/vnd.ms-excel// text/csv
            if ( $form_state == 0 &&  $file_type === 'text/csv' ) {
                $status = $this->handle_csv_file();
                if ( empty($status) ) {
                    wp_send_json_error( __( 'Error while importing file', 'reviewx-pro' ), 404 );
                    wp_die();
                }

                require_once REVIEWX_PRO_ADMIN_DIR_PATH . 'partials/external_review/import/import-column-list.php';
                wp_die();
            }
        }
        
        if ( $form_state == 2 && isset( $_POST['rv_wc_product'] ) ) {
            $product_map_list = $this->group_product($_POST['rv_wc_product'], $_POST['rv_external_product']);

            require_once REVIEWX_PRO_ADMIN_DIR_PATH . 'partials/external_review/import/import-review-column-map.php';
            wp_die();
        }

        if ( ! isset( $_POST['rx_file_name'] ) && $form_state == 3 && isset( $_POST['rv_review_fields'] ) ) {
            $product_id = isset( $_POST['rv_external_product_identifire'] ) ?  $_POST['rv_external_product_identifire'] : false;
            $review_fields = $_POST['rv_review_fields'];

            wp_send_json( json_encode(
                [
                    'total_file_count' => $this->handle_csv_file(),
                    'message' => $this->import_message(),
                    'product_id'    => $product_id,
                    'product_map_list' => $product_map_list,
                    'review_fields' => $review_fields
                ]
            ) );
        }

        if ( isset( $_POST['rx_file_name'] ) ) {
            $product_id = isset( $_POST['rv_external_product_identifire'] ) ?  $_POST['rv_external_product_identifire'] : false;
            $product_map_list = $this->group_product($_POST['rv_wc_product'], $_POST['rv_external_product']);
            $review_fields = $_POST['rv_review_fields'];
            $import_status = $this->database_query_with_file_name( $product_map_list, $product_id, $review_fields, $_POST['rx_file_name']);

            if($import_status) {
                $this->import_history_table();
            }

            wp_send_json( json_encode(
                [
                    'post' => $_POST,
                    'file_name' => $_POST['rx_file_name'],
                    'product_map_list' => $product_map_list,
                    'message' => $this->import_message(),
                    'import_status' => $import_status,
                    'product_id'    => $product_id,
                    'product_map_list' => $product_map_list,
                    'review_fields' => $review_fields
                ]
            ) );
        }

        wp_die();
    }

    public function handle_csv_file() {

        //make a folder in upload folder
        $upload = wp_upload_dir();
        $upload_dir_url = $upload['path'];
        $file_name = time();
        $upload_dir = $upload_dir_url . '/'. $file_name;
        if (! file_exists($upload_dir)) {
            mkdir( $upload_dir, 0777);
        }

        //store directory path name
        update_option( "rvx_temp_dir_path", $upload_dir );

        $csv_file = $_FILES['csv_file'];

        //store original original file name
        update_option( "rx_csv_file_name", $csv_file['name'] );

        //rename uploded csv file
        function my_custom_filename($dir, $name, $ext){
            $file_name = time();
            //csv file new name insert as batch name
            update_option("rx_import_batch", $file_name );
            $new_file_name =  $file_name . $ext;
            return $new_file_name;
        }

        $overrides = array(
            'test_form' => false,
            'mimes'     => array(
                'csv' => 'text/csv',
                'txt' => 'text/plain',
            ),
            'unique_filename_callback' => 'my_custom_filename'
        );

        $upload = wp_handle_upload( $csv_file, $overrides );
        if ( isset( $upload['error'] ) ) return false;

        static::$file = $upload['file'];
        update_option( "rvx_temp_import_path", $upload['file'] );


        $file_path = get_option( "rvx_temp_import_path" );

        $file = fopen( $file_path,"r" );
        $csv_data = array();

        while(!feof($file)) {      
            $fpTotal = fgetcsv($file);
            array_push( $csv_data, $fpTotal );
        }

        //put data in chunked file in upload files
        $j = 1;
        $temp_array = [];
        $columns = [];
        $k = 1;

        for($i = 0; $i < count( $csv_data ); $i++){
            if( ! $columns ) {
                $columns[] = $csv_data[$i];
                continue;
            }

            if( $j < 50 ) {                   
                $temp_array[] = $csv_data[$i];
                $j++;
            } else {         
                $uploaded_csv_files = $k.'.csv';
                if( ! file_exists( $upload_dir .'/'.$file_name [0] ) ) {
                    mkdir( $upload_dir .'/'.$file_name [0] );
                }

                $new_chunk_file = fopen( $upload_dir.'/'.$file_name [0].'/'.$uploaded_csv_files, "w" );

                for( $ll = 0; $ll<count( $columns ); $ll++ ) {
                    fputcsv( $new_chunk_file, $columns[$ll] );   
                }

                for( $ll=0; $ll<count( $temp_array ); $ll++ ){
                    fputcsv( $new_chunk_file, $temp_array[$ll] ); 
                }                        
                        
                $temp_array = [];
                $j = 1;
                $k++;
            }
        }

        fclose($file);
        return $k;
    }

    /**
     * gorup data with Woocommerce product
     */
    public function group_product($woo_product, $external_product) {
        if ( ! $external_product || count( $external_product ) == 0 ) {
            return;
        }

        $products = [];
        foreach ( $external_product as $key => $ex_product ) {
            if ( $woo_product[$key] == 'none' || $ex_product == 'none' ) continue;

            $woo_p = $woo_product[$key];
            if ( isset( $products[$woo_p] ) ) {
                array_push( $products[$woo_p], $ex_product );
            } else {
                $products[$woo_p] = [$ex_product];
            }
        }
        return $products;
    }

    function create_array_group_by_product() {
        $security = check_ajax_referer( 'special-string', 'security' );
        if ( false == $security ) {
            return;
        }
        $product_id = isset( $_POST['productID'] ) ?  $_POST['productID'] : false;
        static::$external_product_id = $product_id;
        $file_path = get_option( "rvx_temp_import_path" );
        
        if ( ! file_exists( $file_path ) ) {
            wp_send_json_error( false, 404 );
            wp_die();
        }
        
        $csv_stream = fopen( $file_path, "r" );
        if ( $csv_stream === false ) {
            wp_send_json_error( false, 404 );
            wp_die();
        }

        fgetcsv( $csv_stream );
        $review_group_product = [];
        while ( $line = fgetcsv( $csv_stream ) ) {
            $review_group_product[] = $line;
        }
        fclose( $csv_stream );
        
        $external_product_list = $this->external_product_list($review_group_product, $product_id);
        $wc_product_list = $this->get_wc_products();
        
        require_once REVIEWX_PRO_ROOT_DIR_PATH . 'admin/partials/external_review/import/product-map.php';
        wp_die();
    }

    /** 
     * get csv file product list
     * 
     * @return array
    */
    public function external_product_list($review_group_product, $key) {
        $product_list = [];
        foreach ( $review_group_product as $product ) {
            if( ! in_array( $product[$key], $product_list, true ) ) {
                array_push( $product_list, $product[$key] );
            }
        }

        return $product_list;
    }

    /**
    * get column list of csv
    *
    * @return array
    */
    public static function column_list() {
        $file_path = get_option( "rvx_temp_import_path" );
        if ( ! $file_path || ! file_exists( $file_path ) ) return false;

        $csv_stream = fopen( $file_path, "r" );
        if ( $csv_stream === false ) return false;

        $column = fgetcsv( $csv_stream );
        fclose( $csv_stream );
        
        static::$csv_header = $column;
        return $column;
    }

    public static function get_wc_products() {
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        );
        $loop = new \WP_Query( $args );
    
        $products = [];
        while ( $loop->have_posts() ) : $loop->the_post();
            $id = get_the_ID();
            $title = get_the_title();
            $products[$id] = $title;
        endwhile;
        wp_reset_query();

        return $products;
    }

    public static function all_review_column($fields = []) {
        $review_column = array(
            "reviewx_title"         => __( "Review Title", "reviewx-pro" ),
            "rating"                => __( "Product Rating", "reviewx-pro" ),
            "comment_content"       => __( "Review Description", "reviewx-pro" ),
            "comment_date"          => __( "Date", "reviewx-pro" ),
            "comment_author"        => __( "Customer Name", "reviewx-pro" ),
            "comment_author_email"  => __( "Customer Email", "reviewx-pro" ),
        );
        return array_merge( $review_column, $fields );
    }

    /**
     * handle chunked file data
     * 
     * @return array 
     */
    private function review_field_map_split($review_fields, $product_id, $file_name) {

        $files_dir_path = get_option( "rvx_temp_dir_path" ); 
        $dir_file_path = $files_dir_path . '/' . $file_name . '.csv';                
        $reviewx_db_column = array_keys( static::all_review_column() );

        if( ! file_exists ( $dir_file_path ) ) {
            $file_path = get_option( "rvx_temp_import_path" );
        } else {
            $file_path = $files_dir_path . '/' . $file_name . '.csv';      
        }

        $csv_stream = fopen( $file_path, "r" );

        if ( $csv_stream === false ) return false;

        $review_field_map = [];
        $review_col_map = [];
        $header = fgetcsv( $csv_stream );
        while ( $line = fgetcsv( $csv_stream ) ) {
            // mapping review fields
            foreach ( $review_fields as $key => $field ) {
                $review_col_map[$reviewx_db_column[$key]] = $line[$field];
            }
            if( ! isset( $review_field_map[ $line[$product_id] ] ) ) {
                $review_field_map[ $line[$product_id] ] = [];
            }
            $review_field_map[ $line[$product_id] ][] = $review_col_map;
        }
        fclose( $csv_stream ); 

        return $review_field_map;
    }

    /**
    * map all files data with products
    *
    * @return array 
    */
    public function product_review_map($product_map_list, $product_id, $review_fields, $file_name) {
        $review_field_map = $this->review_field_map_split( $review_fields, $product_id, $file_name );        
        if ( $review_field_map === false ) return false;

        $product_reviews = array();
        foreach ( $product_map_list as $id => $products ) {
            if ( ! isset( $product_reviews[$id] ) ) {
                $product_reviews[$id] = [];
            }
            foreach ( $products as $product ) {
                $product_reviews[$id] = $review_field_map[$product];
            }
        }
        return $product_reviews;
    }
    
    /**
     * store mapping data in database
     * 
     * @return boolen
     */
    public function database_query_with_file_name($product_map_list, $product_id, $review_fields, $file_name) {
        $product_reviews = $this->product_review_map( $product_map_list, $product_id, $review_fields, $file_name );
        $fields = static::all_review_column(
            [
                'comment_type'         => 'review',
                'user_id'              => 1,
                'comment_author_url'   => '',
            ]
        );

        foreach ( $product_reviews as $product_id => $reviews ) {
            if ( ! $product_reviews[$product_id] ) continue;

            $import_batch_name = get_option('rx_import_batch');
            // var_dump($import_batch_name);

            $fields['comment_post_ID'] = $product_id;
            
            foreach ( $reviews as $review ) {
                $fields['comment_date'] = date( "Y-m-d h:i:s", strtotime( esc_html( $review['comment_date'] ) ) );
                $fields['comment_date_gmt'] = date( "Y-m-d h:i:s", strtotime( esc_html( $review['comment_date'] ) ) );
                $fields['comment_content'] = esc_html( $review['comment_content'] );
                $fields['comment_author'] = esc_html( $review['comment_author'] );
                $fields['comment_author_email'] = esc_html( $review['comment_author_email'] );
                
                $review_id = wp_new_comment( $fields, true );
                if ( is_wp_error( $review_id ) ) {
                    $this->error_count++;
                } else {
                    $this->success_count++;
                    $criteria = [];
                    foreach(wpFluent()->table('reviewx_criterias')->get() as $criterium){
                        if( $review['rating'] > 0 ) {
                            $criteria[$criterium->criteria_id] = $review['rating'];
                        }
                    }
                    add_comment_meta( $review_id, 'rating', (float)$review['rating'], false );
                    add_comment_meta( $review_id, 'reviewx_title', $review['reviewx_title'], false );
                    add_comment_meta( $review_id, 'reviewx_review_type', 'imported', false );
                    add_comment_meta( $review_id, 'reviewx_import_batch', $import_batch_name, false );
                    add_comment_meta( $review_id, 'reviewx_rating', $criteria, false );
                }
            }
        }
        return true;
    }

    /**
     * store file name, batch name and date in databse
     * 
     * @return void
     */
    public function import_history_table() {
        $import_batch_name = get_option('rx_import_batch');
        $import_file_name = get_option('rx_csv_file_name');
        $import_date = date("Y-m-d");

        wpFluent()->table('reviewx_import_history')->insert([
            'batch_id' => $import_batch_name,
            'file_name' => $import_file_name,
            'import_date' => $import_date,
            'status'    => 'Imported'
        ]);
    }

    /**
     * popup import massage bar counter
     */
    private function import_message() {
        $messages = [];
        if ( $this->success_count > 0 ) {
            $success_msg = __( $this->success_count . " review successfully imported.", "reviewx-pro" );
            array_push( $messages, [ 'success' => $success_msg ] );
        }
        if ( $this->error_count > 0 ) {
            $error_msg = __( $this->error_count . " Duplicate comment not imported.", "reviewx-pro" );
            array_push( $messages, [ 'error' => $error_msg ] );
        }

        return $messages;
    }

    public static function is_required($value) {
        $is_required = '';
        $columns = self::all_review_column(
            [
                "reviewx_title"         => [ __( "Review Title", "reviewx-pro" ), 'required' => false ],
                "rating"                => [ __( "Product Rating", "reviewx-pro" ), 'required' => true ],
                "comment_content"       => [ __( "Review Description", "reviewx-pro" ), 'required' => true ],
                "comment_date"          => [ __( "Date", "reviewx-pro" ), 'required' => false ],
                "comment_author"        => [ __( "Customer Name", "reviewx-pro" ), 'required' => true ],
                "comment_author_email"  => [ __( "Customer Email", "reviewx-pro" ), 'required' => true ],
            ]
        );

        foreach( $columns as $key => $colunm ) {
            if( $key === $value && $colunm['required'] == true ) {
                $is_required = 'required';
            }
        }
                
        return $is_required;
    }
    
    /**
     * Imported review reverted function 
     */ 

    function rx_revert_review() {
        $batch_id = $_POST['batchId'];

        $comments = get_comments (array ('meta_key'=> 'reviewx_import_batch', 'meta_value'=> $batch_id ));

        // wp_send_json(json_encode($comments));

        $comments_id = array();
        foreach( $comments as $comment ){
            $id = intval( $comment -> comment_ID );
            array_push($comments_id, $id);

            wp_delete_comment($id, true);

            delete_comment_meta( $id, 'reviewx_title' );
            delete_comment_meta( $id, 'verified' );
            delete_comment_meta( $id, 'rating' );
            delete_comment_meta( $id, 'reviewx_review_type' );
            delete_comment_meta( $id, 'reviewx_import_batch' );
            delete_comment_meta( $id, 'reviewx_rating' );
        }

        wpFluent()->table('reviewx_import_history')->where('batch_id', '=', intval($batch_id))->update([
            'status'    => 'Reverted'
        ]);

        wp_send_json(true);
    }
}
