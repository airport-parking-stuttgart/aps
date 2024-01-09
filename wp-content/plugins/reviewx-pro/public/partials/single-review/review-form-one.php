<?php 
    // This condition for [rvx-woo-reviews] shortcode
    if( shortcode_divi_review_form(get_the_ID(), $reviewx_shortcode) ) {
    ?>
    <!--Default comment form-->
    <?php if( ! empty( $allow_multiple_review ) && $allow_multiple_review == 1 ) {  ?>
        <div class="bg-light rx-review-form-area-style-1 rx-review-form-area rx-margin-bottom-30">
            <div class="rx-flex-grid-container">
                <div class="rx-flex-grid-100 rx_padding_left_right_0"> 
                    <div id="review_form_wrapper">
                        <div id="review_form">
                        <?php
                            $commenter    = wp_get_current_commenter();
                            $comment_form = array(
                                'title_reply'         => have_comments() ? ( ! empty(get_theme_mod('reviewx_form_title_label') ) ? get_theme_mod('reviewx_form_title_label') : esc_html__( 'Leave feedback about this', 'reviewx-pro' ) ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'reviewx-pro' ), get_the_title() ),
                                'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'reviewx-pro' ),
                                'title_reply_before'  => '<h3 id="reply-title" class="comment-reply-title">',
                                'title_reply_after'   => '</h3>',
                                'label_submit'        => !empty(get_theme_mod('reviewx_submit_button_label') ) ? get_theme_mod('reviewx_submit_button_label') : esc_html__( 'Submit Review', 'reviewx-pro' )
                            );

                            $name_email_required = (bool) get_option( 'require_name_email', 1 );
                            $fields              = array(
                                'author' => array(
                                    'label'    => __( 'Name', 'reviewx-pro' ),
                                    'type'     => 'text',
                                    'value'    => $commenter['comment_author'],
                                    'required' => $name_email_required,
                                    'placeholder' => __( 'Name', 'reviewx-pro' ),
                                ),
                                'email' => array(
                                    'label'    => __( 'Email', 'reviewx-pro' ),
                                    'type'     => 'email',
                                    'value'    => $commenter['comment_author_email'],
                                    'required' => $name_email_required,
                                    'placeholder' => __( 'Email', 'reviewx-pro' ),
                                ),
                            );

                            $comment_form['fields'] = array();
                            foreach ( $fields as $key => $field ) {
                                $field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
                                $field_html .= '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '"  placeholder="' . esc_attr( $field['placeholder'] ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';
                                $comment_form['fields'][ $key ] = $field_html;
                            }

                            $account_page_url = class_exists('WooCommerce') ? wc_get_page_permalink( 'myaccount' ): '';
                            if ( $account_page_url ) {
                                $comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in %2$s to post a review.', 'reviewx-pro' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
                            }
                            comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
                        ?>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <?php } else {
            $status = \ReviewX\Modules\Gatekeeper::hasPermit(get_the_ID());
            if ( $status['success'] ) : ?>
            <div class="bg-light rx-review-form-area-style-1 rx-review-form-area rx-margin-bottom-30">
                <div class="rx-flex-grid-container">
                    <div class="rx-flex-grid-100 rx_padding_left_right_0">                
                        <div id="review_form_wrapper">
                            <div id="review_form">
                            <?php
                                $commenter    = wp_get_current_commenter();
                                $comment_form = array(
                                    'title_reply'         => have_comments() ? ( ! empty(get_theme_mod('reviewx_form_title_label') ) ? get_theme_mod('reviewx_form_title_label') : esc_html__( 'Leave feedback about this', 'reviewx-pro' ) ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'reviewx-pro' ), get_the_title() ),
                                    'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'reviewx-pro' ),
                                    'title_reply_before'  => '<h3 id="reply-title" class="comment-reply-title">',
                                    'title_reply_after'   => '</h3>',
                                    'label_submit'        => !empty(get_theme_mod('reviewx_submit_button_label') ) ? get_theme_mod('reviewx_submit_button_label') : esc_html__( 'Submit Review', 'reviewx-pro' )
                                );

                                $name_email_required = (bool) get_option( 'require_name_email', 1 );
                                $fields              = array(
                                    'author' => array(
                                        'label'    => __( 'Name', 'reviewx-pro' ),
                                        'type'     => 'text',
                                        'value'    => $commenter['comment_author'],
                                        'required' => $name_email_required,
                                        'placeholder' => __( 'Name', 'reviewx-pro' ),
                                    ),
                                    'email' => array(
                                        'label'    => __( 'Email', 'reviewx-pro' ),
                                        'type'     => 'email',
                                        'value'    => $commenter['comment_author_email'],
                                        'required' => $name_email_required,
                                        'placeholder' => __( 'Email', 'reviewx-pro' ),
                                    ),
                                );

                                $comment_form['fields'] = array();
                                foreach ( $fields as $key => $field ) {
                                    $field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
                                    $field_html .= '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '"  placeholder="' . esc_attr( $field['placeholder'] ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';
                                    $comment_form['fields'][ $key ] = $field_html;
                                }

                                $account_page_url = class_exists('WooCommerce') ? wc_get_page_permalink( 'myaccount' ): '';
                                if ( $account_page_url ) {
                                    $comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'reviewx-pro' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
                                }
                                comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
                            ?>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>                
        <?php else : ?>
            <?php
                if( get_post_type( $post_id ) == 'product' || get_post_type( $post_id ) == 'download') {
                    ?>
                <div class="bg-light rx-review-form-area-style-1 rx-review-form-area rx-margin-bottom-30">
                    <div class="rx-flex-grid-container">
                        <div class="rx-flex-grid-100 rx_padding_left_right_0">                           
                            <p class="woocommerce-verification-required">
                                <?php echo $status['msg']; ?>
                            </p>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>                    
                    <?php
                } 
            ?>
        <?php endif; ?>
    <?php } ?>
<?php } ?> 