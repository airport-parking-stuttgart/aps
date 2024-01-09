<?php

if (!defined('ABSPATH'))
    define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/../');
require_once(ABSPATH . 'wp-config.php');
global $wpdb;

require_once plugin_dir_path(__FILE__) . '/Database.php';

$task = $_POST["task"];

class Helper
{
    private $wpdb;
    private $prefix;
    private $db;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->prefix = $wpdb->prefix;
        $this->db = Database::getInstance();
    }

    public function update_abreiseliste()
    {
		$user = wp_get_current_user();
		if($_POST['order-betrag'] == "-")
			$betrag = get_post_meta($_POST['order-nr'], '_order_total')[0];
		else
			$betrag = $_POST['order-betrag'];
		
		if((get_post_meta($_POST['order-nr'], 'Anreisedatum', true) < date('Y-m-d')) || (get_post_meta($_POST['order-nr'], 'Anreisedatum', true) == date('Y-m-d') && date('H:i', strtotime(get_post_meta($_POST['order-nr'], 'Uhrzeit von', true))) < date('H:i'))){
			$d1 = strtotime(get_post_meta($_POST['order-nr'], 'first_abreisedatum', true));
			$d2 = strtotime(get_post_meta($_POST['order-nr'], 'Anreisedatum', true));
			$orig_days = round(($d1 - $d2) / 86400);
			
			$d3 = strtotime($_POST['order-dateto']);
			$d4 = strtotime(get_post_meta($_POST['order-nr'], 'Anreisedatum', true));				
			$update_days = round(($d3 - $d4) / 86400);
			
			if($update_days > $orig_days){
				$comp_days = $update_days - $orig_days;
				$field = "+". $comp_days . " TAG " . ($comp_days * 10) . " €";				
			}
			else
				$field = '';			
		}
		else
			$field = '';
		
		if($_POST['order-ruckflug'] != ""){
			$ruckflug = $_POST['order-ruckflug'];
			$str1 = preg_replace('/[^A-Z]/', '', $ruckflug);
			$str2 = preg_replace('/[^0-9]/', '', $ruckflug);
			if($str2 != null)
				$str2 *= 1;
			$ruckflug = $str1 . $str2;
			update_post_meta($_POST['order-nr'], 'Rückflugnummer', $ruckflug);
		}
		else
			$ruckflug = "";
		
        $data = [
            //'_billing_first_name' => $_POST['order-fname'],
            '_billing_last_name' => $_POST['order-lname'],
            'Uhrzeit bis' => $_POST['order-timeto'],
            'Abreisedatum' => dateFormat($_POST['order-dateto']),
            'RückflugnummerEdit' => $ruckflug,
            'Personenanzahl' => $_POST['order-persons'],
            'Parkplatz' => $_POST['order-parkplatz'],
            'Sonstige 1' => $_POST['order-sonstige1'],
            'Sonstige 2' => $field,
            'P.-Code.' => $_POST['order-pcode'],
            'FahrerAb' => $_POST['order-fahrer'],
            'Status' => $_POST['order-status'],
            'Betrag' => $betrag
        ];
        $order = Orders::getByOrderId($_POST['order-nr']);
        $dateTo = date('Y-m-d H:i', strtotime(date('Y-m-d', strtotime($order->date_to)) . $_POST['order-timeto']));
        
		if(HotelTransfers::getHotelTransferByOrderId($_POST['order-nr'])){

			$order_product = HotelTransfers::getHotelTransferByOrderId($_POST['order-nr']);				
			$product = wc_get_product($order_product->variation_id);
			$price = number_format((float)$product->get_price() / 119 * 100,4, '.', '');
			
			if($_POST['order-dateto'])
				$arrDateto = dateFormat( $_POST['order-dateto']);
			else
				$arrDateto = null;
			if($_POST['order-timeto'])
				$arrTimeto = $_POST['order-timeto'];
			else
				$arrTimeto = null;
			if($_POST['order-ruckflug'])
				$arrFlNr = $_POST['order-ruckflug'];
			else
				$arrFlNr = null;			
            HotelTransfers::updateHotelTransfer([
                'dateto' => $arrDateto,
                'ankunftszeit_ruckflug' => $arrTimeto,
                'ruckflug_nummer' => $arrFlNr,
            ], ['order_id' => $_POST['order-nr']]);
			
			if($arrDateto != null && get_post_meta($_POST['order-nr'], 'Anreisedatum', true) != null){
				$price = number_format((float)$price * 2, 4, '.', '.');				
			}
			
			if($arrDateto == null)
				delete_post_meta($_POST['order-nr'], 'Abreisedatum' );
							
			$order = wc_get_order($_POST['order-nr']);
			// Set new price
			$order_items = $order->get_items();
			foreach ( $order_items as $key => $value ) {
				$order_item_id = $key;
				$product_value = $value->get_data();
				$product_id    = $product_value['product_id'];
			}
			$product = wc_get_product( $product_id );
			$order_items[ $order_item_id ]->set_total( $price );
			$order->calculate_taxes();
			$order->calculate_totals();
			$order->save();
			
        }else{
            Orders::updateOrder(['date_to' => $dateTo, 'nr_persons' => $_POST['order-persons']], ['order_id' => $_POST['order-nr']]);
			
			/*
			$datefromO = get_post_meta($_POST['order-nr'], 'Anreisedatum', true);
			$dateToO = get_post_meta($_POST['order-nr'], 'Abreisedatum', true);
			$daysO = getDaysBetween2Dates(new DateTime($datefromO), new DateTime($dateToO));
			$dateToN = date('Y-m-d', strtotime$_POST['order-dateto']));
			$daysN = getDaysBetween2Dates(new DateTime($datefromO), new DateTime($dateToN));
			if($daysN > $daysO){
				$s = get_post_meta($_POST['order-nr'], 'Sonstige 1', true);
				$s != "" ? update_post_meta($_POST['order-nr'], 'Sonstige 1', $s . " + " . ($daysn - $dayso) . "€ Extratage") : update_post_meta($_POST['order-nr'], 'Sonstige 1', "+ " . ($daysn - $dayso) . "€ Extratage");
			}
			*/
        }
		update_post_meta($_POST['order-nr'], 'editByRet', $user->user_login);
        $this->db->updateList($_POST['order-nr'], $data);
		
		
		if(HotelTransfers::getHotelTransferByOrderId($_POST['order-nr'])){
			$order = wc_get_order($_POST['order-nr']);
			$order->calculate_taxes();
			$order->calculate_totals();
			$order->save();
		}
    }

    public function update_anreiseliste()
    {
		$user = wp_get_current_user();
		
		if($_POST['order-betrag'] == "-")
			$betrag = get_post_meta($_POST['order-nr'], '_order_total')[0];
		else
			$betrag = $_POST['order-betrag'];
		
		if($_POST['order-dateto'] != ""){
			$dateToEdit = dateFormat($_POST['order-dateto']);
			update_post_meta($_POST['order-nr'], 'Abreisedatum', dateFormat($_POST['order-dateto']));
		}
		else
			$dateToEdit = "";
		
		if($_POST['order-landung'] != ""){
			$timeToEdit = $_POST['order-landung'];
			update_post_meta($_POST['order-nr'], 'Uhrzeit bis', $_POST['order-landung']);
		}
		else
			$timeToEdit = "";
		
		if($_POST['order-ruckflug'] != ""){
			$ruckflug = $_POST['order-ruckflug'];
			$str1 = preg_replace('/[^A-Z]/', '', $ruckflug);
			$str2 = preg_replace('/[^0-9]/', '', $ruckflug);
			if($str2 != null)
				$str2 *= 1;
			$ruckflug = $str1 . $str2;
			update_post_meta($_POST['order-nr'], 'Rückflugnummer', $ruckflug);
		}
		else
			$ruckflug = "";
		
		
        $data = [
            //'_billing_first_name' => $_POST['order-fname'],
            '_billing_last_name' => $_POST['order-lname'],
            'AbreisedatumEdit' => $dateToEdit,
            'Anreisedatum' => dateFormat($_POST['order-datefrom']),
            'Uhrzeit von' => $_POST['order-timefrom'],
            'RückflugnummerEdit' => $ruckflug,
            'Personenanzahl' => $_POST['order-persons'],
            'Uhrzeit bis Edit' => $timeToEdit,
            'Parkplatz' => $_POST['order-parkplatz'],
            'FahrerAn' => $_POST['order-fahrer'],
            'Sonstige 1' => $_POST['order-sonstiges'],
            'P.-Code.' => $_POST['order-pcode'],
            'Status' => $_POST['order-status'],
            'Betrag' => $betrag
        ];

        $dateTo = date('Y-m-d H:i', strtotime($_POST['order-dateto'] . ' ' . $_POST['order-landung']));
//        Orders::updateOrder(['date_to' => $dateTo], ['order_id' => $_POST['order-nr']]);
        $dateFrom = date('Y-m-d H:i', strtotime($_POST['order-datefrom'] . ' ' . $_POST['order-timefrom']));
        if(HotelTransfers::getHotelTransferByOrderId($_POST['order-nr'])){
			
			$order_product = HotelTransfers::getHotelTransferByOrderId($_POST['order-nr']);			
			$product = wc_get_product($order_product->variation_id);
			$price = number_format((float)$product->get_price() / 119 * 100,4, '.', '');
			
			if($_POST['order-datefrom'])
				$arrDatefrom = dateFormat($_POST['order-datefrom']);
			else
				$arrDtefefrom = null;
			if($_POST['order-dateto'])
				$arrDateto = dateFormat($_POST['order-dateto']);
			else
				$arrDateto = null;
			if($_POST['order-timefrom'])
				$arrTimefrom = $_POST['order-timefrom'];
			else
				$arrTimefrom = null;
			if($_POST['order-landung'])
				$arrTimeto = $_POST['order-landung'];
			else
				$arrTimeto = null;
			if($_POST['order-ruckflug'])
				$arrFlNr = $_POST['order-ruckflug'];
			else
				$arrFlNr = null;
				
            HotelTransfers::updateHotelTransfer([
                'datefrom' => $arrDatefrom,
                'dateto' => $arrDateto,
                'transfer_vom_hotel' => $arrTimefrom,
                'ankunftszeit_ruckflug' => $arrTimeto,
                'ruckflug_nummer' => $arrFlNr,
            ], ['order_id' => $_POST['order-nr']]);
			
			if($arrDatefrom != null && $arrDateto != null){
				$price = number_format((float)$price * 2, 4, '.', '.');
			}
			if($arrDateto == null)
				delete_post_meta($_POST['order-nr'], 'Abreisedatum' );
			if($arrDatefrom == null)
				delete_post_meta($_POST['order-nr'], 'Anreisedatum' );
			
			$order = wc_get_order($_POST['order-nr']);
			// Set new price
			$order_items = $order->get_items();
			foreach ( $order_items as $key => $value ) {
				$order_item_id = $key;
				$product_value = $value->get_data();
				$product_id    = $product_value['product_id'];
			}
			$product = wc_get_product( $product_id );
			$order_items[ $order_item_id ]->set_total( $price );
			$order->calculate_taxes();
			$order->calculate_totals();
			$order->save();
			
        }else{
            Orders::updateOrder([
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'nr_people' => $_POST['order-persons'],
            ], ['order_id' => $_POST['order-nr']]);
        }
		update_post_meta($_POST['order-nr'], 'editByArr', $user->user_login);
        $this->db->updateList($_POST['order-nr'], $data);
		
		if(HotelTransfers::getHotelTransferByOrderId($_POST['order-nr'])){
			$order = wc_get_order($_POST['order-nr']);
			$order->calculate_taxes();
			$order->calculate_totals();
			$order->save();
		}
    }

    public function mark_as_done_anreise_item()
    {
        $id = $_POST['id'];
        update_post_meta($id, 'mark_as_done', 'done');
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

    public function add_one_day()
    {
        $id = $_POST['id'];
        $order = wc_get_order($id);
        $list = $_POST['list'];
        $plusOneDate = addDay($order->get_meta($list), 1);
        update_post_meta($id, 'prev_date', $order->get_meta($list));
        update_post_meta($id, $list, $plusOneDate);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

    public function delete_price()
    {
        $id = $_POST['id'];
		$name = $_POST['name'];
		
		$url = "https://airport-parking-germany.de/search-result/?request=apm_price_del&pw=apmprd_req57159428&p_name=".$name;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
		http_build_query(array(
			 //'request' => 'apm_price_del',
			 //'pw' => 'apmprd_req57159428',
			 //'p_name' => $_POST['name']			 
		)));
		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close($ch);
        return $this->db->deletePrice($id);
    }
	
    public function add_location()
    {
        $location = $_POST['newLocation'];
		$loc = $this->db->getLocationByName($location);
        
		// if entery not already exist
		if(!$loc){
			$this->db->addLocation($location);
		}

		echo json_encode($this->db->getLocations());
    }

    function update_product()
    {
        $product = wc_get_product($_POST['product_id']);
        $product->set_name($_POST['parklot']);
		$product->set_description($_POST['dsc']);
        $product->set_price(0);
        $product->set_regular_price(0);
		if($_POST['for_hotel']){
			$variations = $product->get_children();
			foreach ($variations as $variation){
				$product_variation = new WC_Product_Variation($variation);
				if($_POST[$product_variation->get_id()])
					$v_price = $_POST[$product_variation->get_id()];					
				else
					$v_price = 0;				
				$product_variation->set_regular_price($v_price);
				$product_variation->save();
			}
		}
        $product->save();
		$clean_permalink = sanitize_title( $_POST['parklot'], $_POST['product_id'] );
		wp_update_post( array(
			'ID' => $_POST['product_id'],
			'post_name' => $clean_permalink
		));
		$images = $_FILES['images'];
        $image_ids = [];

        // update parklot
        
        
        // $leadTime = date('Y-m-d H:i', strtotime($_POST['booking_lead_time']));
        
		$origData = $this->db->getParklotByProductId($_POST['product_id']);
		
		if(!empty($_POST['parkhaus']))
			$parkhaus = $_POST['parkhaus'];
		else
			$parkhaus = $origData->parkhaus;
		
		if(!empty($_POST['parklot']))
			$parklot = $_POST['parklot'];
		else
			$parklot = $origData->parklot;
		
		if(!empty($_POST['type']))
			$type = $_POST['type'];
		else
			$type = $origData->type;
		
		if(!empty($_POST['date_from']))
			$datefrom = date('Y-m-d', strtotime($_POST['date_from']));
		else
			$datefrom = date('Y-m-d', strtotime($origData->datefrom));
		
		if(!empty($_POST['date_to']))
			$dateto = date('Y-m-d', strtotime($_POST['date_to']));
		else
			$dateto = date('Y-m-d', strtotime($origData->date_to));
		
		
		$this->db->updateParklot($parkhaus, $parklot, $type, $datefrom, $dateto, $_POST['booking_lead_time'], $_POST['contigent'], 		
		$product->get_id(), $_POST['location'], $_POST['product_isfor'], $_POST['parklot_adress'], $_POST['parklot_phone'], 
		$_POST['parklot_prefix'], $_POST['parklot_color'], $_POST['parklot_short'], $_POST['parklot_distance'], $_POST['parklot_extraPrice_perDay'], $_POST['commision'], $_POST['commision_ws'], 
		$_POST['confirmation_byArrival'], $_POST['confirmation_byDeparture'], $_POST['confirmation_note'], ['product_id' => $_POST['product_id']]);

        // update product additional services
        if (isset($_POST['add_ser_id']) && count($_POST['add_ser_id']) > 0) {
            $this->db->updateProductAdditionalServices($_POST['add_ser_id'], $product->get_id());
        }

		// update order cancellation
        if (isset($_POST['cancellation_hours']) && $_POST['cancellation_hours'] != "") {
			for ($i = 0; $i < count($_POST['cancellation_hours']); $i++) {
				if (empty($_POST['cancellation_hours'][$i])) {
                    continue;
                }
				if (isset($_POST['order_cancellation_id'][$i]) && !empty($_POST['order_cancellation_id'][$i])) {
					$this->db->updateOrderCancellation($_POST['cancellation_hours'][$i], $_POST['cancellation_type'][$i], $_POST['cancellation_value'][$i], $product->get_id(), ['id' => $_POST['order_cancellation_id'][$i]]);
				} else {
					$this->db->saveOrderCancellation($_POST['cancellation_hours'][$i], $_POST['cancellation_type'][$i], $_POST['cancellation_value'][$i], $product->get_id());
				}
            }
        }

        // save discounts
        if (isset($_POST['discount_name']) && !empty($_POST['discount_name'])) {
            for ($i = 0; $i < count($_POST['discount_name']); $i++) {
                if (empty($_POST['discount_name'][$i])) {
                    continue;
                }
                $dates = explode(' - ', $_POST['discount_interval'][$i]);
                $interval_from = date('Y-m-d', strtotime($dates[0]));
                $interval_to = date('Y-m-d', strtotime($dates[1]));
                if (isset($_POST['discount_id'][$i]) && !empty($_POST['discount_id'][$i])) {
                    $this->db->updateDiscount($_POST['discount_name'][$i], $interval_from, $interval_to, $_POST['discount_type'][$i], $_POST['discount_value'][$i], $_POST['discount_days_before'][$i], $_POST['discount_contigent_limit'][$i], $_POST['discount_cancel'][$i], $_POST['discount_message'][$i], $product->get_id(), ['id' => $_POST['discount_id'][$i]]);
                } else {
                    $this->db->saveDiscount($_POST['discount_name'][$i], $interval_from, $interval_to, $_POST['discount_type'][$i], $_POST['discount_value'][$i], $_POST['discount_days_before'][$i], $_POST['discount_contigent_limit'][$i], $_POST['discount_cancel'][$i], $_POST['discount_message'][$i], $product->get_id());
                }
            }
        }

        // save restrictions
        if (isset($_POST['restriction_darum']) && !empty($_POST['restriction_darum'])) {
            for ($i = 0; $i < count($_POST['restriction_darum']); $i++) {
                if (empty($_POST['restriction_darum'][$i])) {
                    continue;
                }
                $date = date('Y-m-d', strtotime($_POST['restriction_date'][$i]));
                if (isset($_POST['restriction_id'][$i]) && !empty($_POST['restriction_id'][$i])) {
                    $this->db->updateRestriction($_POST['restriction_darum'][$i], $date, $_POST['restriction_time'][$i], $product->get_id(), ['id' => $_POST['restriction_id'][$i]]);
                } else {
                    $this->db->saveRestriction($_POST['restriction_darum'][$i], $date, $_POST['restriction_time'][$i], $product->get_id());
                }
            }
        }

        // Get the path to the upload directory.
        $wp_upload_dir = wp_upload_dir();
        if ($images['name'] && !empty($images['name'][0])) {
            for ($i = 0; $i < count($images['name']); $i++) {
                $image = wp_upload_bits($images['name'][$i], null, file_get_contents($images['tmp_name'][$i]))['url'];
                $attachment = array(
                    'guid' => $wp_upload_dir['url'] . '/' . basename($image),
                    'post_mime_type' => $images['type'][$i],
                    'post_title' => 'my description',
                    'post_content' => 'my description',
                    'post_status' => 'inherit'
                );

                $image_ids[] = wp_insert_attachment($attachment, $image, $product->get_id());
            }
        }
        update_post_meta($product->get_id(), '_product_image_gallery', implode(',', array_merge($image_ids, $product->get_gallery_image_ids())));
		update_post_meta($product->get_id(), '_thumbnail_id', implode(',', array_merge($image_ids, $product->get_gallery_image_ids())));
        
		//echo "<pre>";
		//print_r($_POST);
		//echo "</pre>";
		header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

    public function save_product()
    {
        $product = new WC_Product();
        $product->set_name($_POST['parklot']);
        $product->set_price(0);
		$product->set_description($_POST['dsc']);
        $product->set_regular_price(0);
        $product->save();
        $images = $_FILES['images'];
        $image_ids = [];

        // update unsaved events
        $this->db->updateEventsProductId($product->get_id());

        // save parklot
		//$datefrom = date_create($_POST['date_from']);
		//$datefrom = date_format($datefrom, 'Y-m-d');
        $datefrom = date('Y-m-d', strtotime($_POST['date_from']));
        //$dateto = date_create($_POST['date_to']);
		//$dateto = date_format($dateto, 'Y-m-d');
		$dateto = date('Y-m-d', strtotime($_POST['date_to']));
        // $leadTime = date('Y-m-d H:i', strtotime($_POST['booking_lead_time']));
        $this->db->saveParklot(
		$_POST['parkhaus'], $_POST['parklot'], $_POST['type'], $datefrom, $dateto, $_POST['booking_lead_time'], $_POST['contigent'], 
		$product->get_id(), $_POST['location'], $_POST['product_isfor'], $_POST['parklot_adress'], $_POST['parklot_phone'], 
		$_POST['parklot_prefix'], $_POST['parklot_color'], $_POST['parklot_short'], $_POST['parklot_distance'], $_POST['parklot_extraPrice_perDay'], $_POST['commision'], $_POST['commision_ws'], 
		$_POST['confirmation_byArrival'], $_POST['confirmation_byDeparture'], $_POST['confirmation_note']);

        // save restrictions
        if (isset($_POST['restriction_darum']) && !empty($_POST['restriction_darum']) && $_POST['restriction_darum'] != "") {
            for ($i = 0; $i < count($_POST['restriction_darum']); $i++) {
                if($_POST['restriction_darum'][$i] != null){
					$date = date('Y-m-d', strtotime($_POST['restriction_date'][$i]));
					if(($date != null || $date != "") && $date != "1970-01-01")
					$this->db->saveRestriction($_POST['restriction_darum'][$i], $date, $_POST['restriction_time'][$i], $product->get_id());
				}
				else
					continue;
            }
        }

        // save discounts
        if (isset($_POST['discount_name']) && !empty($_POST['discount_name'])) {
            for ($i = 0; $i < count($_POST['discount_name']); $i++) {
                if($_POST['discount_interval'][$i] != null){
					$dates = explode(' - ', $_POST['discount_interval'][$i]);
					$interval_from = date('Y-m-d', strtotime($dates[0]));
					$interval_to = date('Y-m-d', strtotime($dates[1]));
					$this->db->saveDiscount($_POST['discount_name'][$i], $interval_from, $interval_to, $_POST['discount_type'][$i], $_POST['discount_value'][$i], $_POST['discount_days_before'][$i], $_POST['discount_contigent_limit'][$i], $product->get_id());
				}
				else
					continue;
            }
        }

        // save order cancellation
        if (isset($_POST['cancellation_hours']) && $_POST['cancellation_hours'] != "") {
            for ($i = 0; $i < count($_POST['cancellation_hours']); $i++) {
				if (empty($_POST['cancellation_hours'][$i])) {
                    continue;
                }
				$this->db->saveOrderCancellation($_POST['cancellation_hours'][$i], $_POST['cancellation_type'][$i], $_POST['cancellation_value'][$i], $product->get_id());
			}
        }

        // Get the path to the upload directory.
        $wp_upload_dir = wp_upload_dir();
        if ($images['name'] && !empty($images['name'][0])) {
            for ($i = 0; $i < count($images['name']); $i++) {
                $image = wp_upload_bits($images['name'][$i], null, file_get_contents($images['tmp_name'][$i]))['url'];
                $attachment = array(
                    'guid' => $wp_upload_dir['url'] . '/' . basename($image),
                    'post_mime_type' => $images['type'][$i],
                    'post_title' => 'my description',
                    'post_content' => 'my description',
                    'post_status' => 'inherit'
                );

                $image_ids[] = wp_insert_attachment($attachment, $image, $product->get_id());
            }
        }

        update_post_meta($product->get_id(), '_product_image_gallery', implode(',', $image_ids));
		update_post_meta($product->get_id(), '_thumbnail_id', implode(',', array_merge($image_ids, $product->get_gallery_image_ids())));
        foreach ($_POST['add_ser_id'] as $id) {
            if ((int)$id > 0) {
                $this->db->saveAddSerProductId($id, $product->get_id());
            }
        }

        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

    public function bulk_delete(){
        $table = $_POST['table'];
        $ids = $_POST['checkedIds'];
        echo $this->db->bulkDelete($table, $_POST['attribute'], $ids);

        if($table === 'orders'){
            Orders::deleteWCOrders($ids);
        }
    }
	
	public function price_delete_from_calendar(){
        $lot_id = $_POST['lotid'];
        echo $this->db->allprice_delete_from_calendar($lot_id);
    }

    public function add_to_cart(){
        $datefrom = $_POST["datefrom"];
        $dateto = $_POST["dateto"];
        $proid = $_POST["proid"];
        $shuttle = $_POST["shuttle"];
        $_SESSION["parklots"][$proid]["datefrom"] = $datefrom;
        $_SESSION["parklots"][$proid]["dateto"] = $dateto;
        $_SESSION["parklot_shuttle"] = $shuttle;
        $_SESSION["product_id"] = $proid;
		$_SESSION["discount"] = $_POST["discount"];
        $_SESSION["parklots"][$proid]['discount'] = $_POST['discount'];

        // remove & add product once
        WC()->cart->empty_cart();
        WC()->cart->remove_cart_item($proid);
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] == $proid) {
                //remove single product
                WC()->cart->remove_cart_item($cart_item_key);
            }
        }

        WC()->session->set_customer_session_cookie(true);
        WC()->cart->add_to_cart($proid, 1, null);
    }

    public function new_hotel_transfer()
    {
        HotelTransfers::newHotelTransfer(
            $_POST['pID'],
			$_POST['type'],
            $_POST['datefrom'],
            $_POST['dateto'],
            $_POST['vorname'],
            $_POST['nachname'],
            $_POST['phone'],
            $_POST['product'],
            $_POST['transfer_vom_hotel'],
            $_POST['ankunftszeit_ruckflug'],
            $_POST['hinflugnummer'],
            $_POST['ruckflugnummer'],
			$_POST['sonstiges']
        );
		$user = wp_get_current_user();
		$roles = ( array ) $user->roles;
		if($roles[0] == 'administrator')
			header('Location: /partner-dashboard');
		else
			header('Location: /transferliste');
    }
	
    public function new_hotel_transfer_backend()
    {

		HotelTransfers::newHotelTransfer(
            $_POST['pID'],
			$_POST['type'],
            $_POST['datefrom'],
            $_POST['dateto'],
            $_POST['vorname'],
            $_POST['nachname'],
            $_POST['phone'],
            $_POST['product'],
            $_POST['transfer_vom_hotel'],
            $_POST['ankunftszeit_ruckflug'],
            $_POST['hinflugnummer'],
            $_POST['ruckflugnummer'],
			$_POST['sonstiges']
        );
		header('Location: /wp-admin/admin.php?page=buchung-erstellen');
    }

    public function delete_hotel_transfer()
    {
        $order = wc_get_order($_POST['order_id']);
        $order->update_status('wc-cancelled');
//        HotelTransfers::deleteHotelTransfer(['order_id' => $_POST['order_id']]);
//        Orders::deleteWCOrders([$_POST['order_id']]);
    }

    public function update_hotel_transfer()
    {
        $hotelTransfer = HotelTransfers::getHotelTransfer($_POST['id']);
        $order = wc_get_order($hotelTransfer->order_id);
        $order->set_billing_first_name($_POST['vorname']);
        $order->set_billing_last_name($_POST['nachname']);
        $order->set_billing_phone($_POST['phone']);
        //$order->save();

		$user_id = get_current_user_id();
		$product_perent = HotelTransfers::getHotelProdukt($user_id);
        $product = wc_get_product($_POST['product']);
		$price = number_format((float)$product->get_price() / 119 * 100,4, '.', '');
		
		if($_POST['datefrom'] != null && $_POST['dateto'] != null)
			$price = $price * 2;
		
		$product_variation_name = $product->get_variation_attributes();
		$personen = $product_variation_name['attribute_pa_passagiere'] * 1;
		
		$order_items = $order->get_items();
		foreach ( $order_items as $key => $value ) {
			$order_item_id = $key;
			   $product_value = $value->get_data();
			   $product_id    = $product_value['product_id']; 
		}
		$order_items[ $order_item_id ]->set_total( $price );
		$order->calculate_taxes();
		$order->calculate_totals();
		$order->save();
		
		update_post_meta($hotelTransfer->order_id, 'Personenanzahl', $personen);
		if($_POST['sonstiges'])
			update_post_meta($hotelTransfer->order_id, 'Sonstige 1', $_POST['sonstiges']);
        
		HotelTransfers::updateHotelTransfer([
            'datefrom' => !empty($_POST['datefrom']) ? dateFormat($_POST['datefrom']) : null,
            'dateto' => !empty($_POST['dateto']) ? dateFormat($_POST['dateto']) : null,
            'variation_id' => !empty($_POST['product']) ? $_POST['product'] : null,
            'transfer_vom_hotel' => !empty($_POST['transfer_vom_hotel']) ? $_POST['transfer_vom_hotel'] : null,
            'ankunftszeit_ruckflug' => !empty($_POST['ankunftszeit_ruckflug']) ? $_POST['ankunftszeit_ruckflug'] : null,
            'hinflug_nummer' => !empty($_POST['hinflugnummer']) ? $_POST['hinflugnummer'] : null,
            'ruckflug_nummer' => !empty($_POST['ruckflugnummer']) ? $_POST['ruckflugnummer'] : null,
        ], ['id' => $_POST['id']]);
		
		update_post_meta($hotelTransfer->order_id, '_billing_first_name', $_POST['vorname']);
		update_post_meta($hotelTransfer->order_id, '_billing_last_name', $_POST['nachname']);
		update_post_meta($hotelTransfer->order_id, '_billing_phone', $_POST['phone']);
		if(!empty($_POST['datefrom']))
			update_post_meta($hotelTransfer->order_id, 'Anreisedatum', dateFormat($_POST['datefrom']));
		if(!empty($_POST['dateto']))
			update_post_meta($hotelTransfer->order_id, 'Abreisedatum', dateFormat($_POST['dateto']));
		if(!empty($_POST['transfer_vom_hotel']))
			update_post_meta($hotelTransfer->order_id, 'Uhrzeit von', $_POST['transfer_vom_hotel']);
		if(!empty($_POST['ankunftszeit_ruckflug']))
			update_post_meta($hotelTransfer->order_id, 'Uhrzeit bis', $_POST['ankunftszeit_ruckflug']);
		if(!empty($_POST['hinflugnummer']))
			update_post_meta($hotelTransfer->order_id, 'Hinflugnummer', $_POST['hinflugnummer']);
		if(!empty($_POST['ruckflugnummer']))
			update_post_meta($hotelTransfer->order_id, 'Rückflugnummer', $_POST['ruckflugnummer']);
		//update_post_meta($hotelTransfer->order_id, '_transaction_id', 'barzahlung');
		//update_post_meta($hotelTransfer->order_id, '_payment_method_title', 'Barzahlung');
        
		header('Location: /transferliste');
    }
	
	public function addPrice_calendarFast(){
        $product = $_POST['product'];
        $price = $_POST['price'];
		$date = $_POST['date'];
		
		if(isset($date)){
			$date = (explode(" - ",$date));
			$date[0] = date('Y-m-d', strtotime($date[0]));
			$date[1] = date('Y-m-d', strtotime($date[1]));
			while($date[0] <= $date[1]){
				$this->db->addEventsFast($date[0], $product, $price);				
				$date[0] = date('Y-m-d', strtotime($date[0]. ' + 1 days'));
			}			
		}
		
		return true;
    }
	
	public function editBooking_cancel(){
		$order_id = $_POST['order_id'];
		$order = new WC_Order($order_id);
		if (!empty($order)) {
			$order->update_status( 'cancelled' );
			$items = $order->get_items();
			foreach ( $items as $item ) {				
				$product_id = $item->get_product_id();
			}
			if($product_id == 537 || $product_id == 592 || $product_id == 619 || $product_id == 873){
				if(get_post_meta($order_id, '_payment_method_title', true) != 'Barzahlung' && get_post_meta($order_id, '_payment_method_title', true) != 'MasterCard')
					add_post_meta($order_id, 'paypal_rerunded', 0, true);
			}
			
			$billing_email = $order->get_billing_email();
			$bookingRef = $order->get_meta('token');
			$subject = 'Ihre Buchung ' . $bookingRef . ' wurde storniert';
			$body = "<h3>Ihr Parkplatz wurde storniert.</h3>
					<p>Sehr geehrte Damen und Herren,</p>
					<p>Ihre Buchung mit der Buchungsnummer <strong>".$bookingRef."</strong> wurde storniert.<br/></p>
					<p>Sie haben noch Fragen? Schreiben Sie uns einfach eine E-Mail an
						<a href='mailto:info@airport-parking-stuttgart.de'>info@airport-parking-stuttgart.de</a> oder rufen Sie uns
						unter <a href='tel:+49 711 22 051 245'>+49 711 22 051 245</a> an.
					</p>
					<p>Montag bis Freitag von 11:00 bis 19:00 Uhr.
					   Aus dem dt. Festnetz zum Ortstarif. Mobilfunkkosten abweichend.</p>
					<p>Mit freundlichen Grüßen</p>
					<p>APS-Airport-Parking-Stuttgart GmbH<br>
					Raiffeisenstraße 18, 70794 Filderstadt, Deutschland<br>
					<a href='www.airport-parking-stuttgart.de'>www.airport-parking-stuttgart.de</a></p>			
				";
			$headers = array('Content-Type: text/html; charset=UTF-8');
			wp_mail( $billing_email, $subject, $body, $headers );
			wp_mail( 'noreply@airport-parking-stuttgart.de', $subject, $body, $headers );
			
			$url = "https://airport-parking-germany.de/search-result/";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,
			http_build_query(array(
				 'request' => 'apm_cancel',
				 'pw' => 'apmc_req57159428',
				 'token' => $bookingRef
				 
			)));
			// Receive server response ...
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$server_output = curl_exec($ch);
			curl_close($ch);
					
			return Database::getInstance()->addEditBookingLog($order_id, 'cancel', 1);
		}
	}
	
	public function set_refund(){
		update_post_meta($_POST['order_id'], 'paypal_rerunded', $_POST['status']);
	}
	
	public function set_processing(){
		$order_id = $_POST['order_id'];
		$order = new WC_Order($order_id);
		if (!empty($order)) {
			$order->update_status( 'processing' );
			$parklot = Database::getInstance()->getParklotByOrderId($order_id);
			$dateFrom = get_post_meta($order_id, 'Anreisedatum', true);
			$dateTo = get_post_meta($order_id, 'Abreisedatum', true);
			
			if($parklot->product_id == 595)
				$tmp_product = 537;
			elseif($parklot->product_id == 3080)
				$tmp_product = 592;
			elseif($parklot->product_id == 3081)
				$tmp_product = 619;
			elseif($parklot->product_id == 3082)
				$tmp_product = 873;
			elseif($parklot->product_id == 24224)
				$tmp_product = 24222;
			elseif($parklot->product_id == 24228)
				$tmp_product = 24226;
			else
				$tmp_product = $parklot->product_id;
			
			$priceList = Pricelist::calculateAndDiscount($tmp_product, dateFormat($dateFrom), dateFormat($dateTo));
			
			// Set new price
			$order_items = $order->get_items();
			foreach ( $order_items as $key => $value ) {
				$order_item_id = $key;
				$product_value = $value->get_data();
				$product_id    = $product_value['product_id'];
			}
			$product = wc_get_product( $product_id );
			$price = $priceList / 119 * 100;
			$order_items[ $order_item_id ]->set_total( $price );
			$order->calculate_taxes();
			$order->calculate_totals();
			$order->save();
		}
	}
	
	public function del_urlaubsplanung(){
		return $this->db->deleteUrlubsplanung($_POST);
	}
	
	public function urlaubsplan_eintrag(){
		
		if($_POST['eintrag'] == 'X'){
			$this->db->add_urlaubsplanung_sperre($_POST);
		}
		
		return $this->db->add_urlaubsplanung($_POST);
	}
	
	public function einsatzplan_ordnen(){
		unset($_POST['task']);
		$this->db->order_einsatzplan($_POST);
		return 'ok';
	}
}

$helper = new Helper;
if (method_exists($helper, $task)) {
    $helper->$task();
}
