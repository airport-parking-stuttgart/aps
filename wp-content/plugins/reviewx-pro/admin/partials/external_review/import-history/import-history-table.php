<?php

use ReviewX\Extended\WPCore\WordpressListTable;

class ReviewHistory extends WordpressListTable {

	//get data from import history table
	public function imported_table_data() {
		$tableData =  wpFluent()->table( 'reviewx_import_history' )->get();
		$testData = json_decode( json_encode( $tableData), true );
		return $testData;
	}
    
    public function prepare_items() {
    	$table_header = $this->get_columns();
    	$this-> _column_headers = array( $table_header );
    	$this->items = $this->imported_table_data();
		$data = $this->imported_table_data();
    }

    public function get_columns() {
    	$table_header = array(
    		'batch_id' => 'Batch No',
    		'file_name' => 'File Name',
    		'import_date' => 'Import Date',
    		'status'	=> 'Status',
    		'action'	=> 'Action'
    	);

    	return $table_header;
    }

    public function column_default( $item, $column_name ) {
		$is_disable = '';
		if ( $item['status'] == 'Reverted' ) {
			$is_disable = 'disabled';
		}
    	switch( $column_name ) {
    		case 'batch_id':
    		case 'file_name':
    		case 'import_date':
			case 'status':
    			return $item[ $column_name ];
			case 'action':
				return '<input type="button" id="revert-btn" data-batch-id="' . $item['batch_id'] . '" class="rvx-import-revert-btn" value="Revert" ' . $is_disable . ' />';
    	}
    }
}

function rvx_show_import_history() {
	$rvx_review_history = new ReviewHistory();
	$rvx_review_history->prepare_items();
	$rvx_review_history->display(); 
} 

rvx_show_import_history();