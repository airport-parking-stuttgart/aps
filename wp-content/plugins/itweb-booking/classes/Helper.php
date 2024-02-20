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
		$orderSQL = Orders::getOrderByOrderId($_POST['order-nr']);
		$user = wp_get_current_user();
		if($_POST['order-betrag'] == "-")
			$betrag = $orderSQL->order_price;
		else
			$betrag = $_POST['order-betrag'];
		
		if(($orderSQL->Anreisedatum < date('Y-m-d')) || ($orderSQL->Anreisedatum == date('Y-m-d') && date('H:i', strtotime($orderSQL->Uhrzeit_von)) < date('H:i'))){
			$d1 = strtotime($orderSQL->first_abreisedatum);
			$d2 = strtotime($orderSQL->Anreisedatum);
			$orig_days = round(($d1 - $d2) / 86400);
			
			$d3 = strtotime($_POST['order-dateto']);
			$d4 = strtotime($orderSQL->Anreisedatum);				
			$update_days = round(($d3 - $d4) / 86400);
			
			if($update_days > $orig_days){
				$comp_days = $update_days - $orig_days;
				$sonstige_2 = "+". $comp_days . " TAG " . ($comp_days * 15) . " €";				
			}
			else
				$sonstige_2 = '';			
		}
		else
			$sonstige_2 = '';
		
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
            'Sonstige 2' => $sonstige_2,
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
			if($_POST['order-fahrer'])
				$fahrerAb = $_POST['order-fahrer'];
			else
				$fahrerAb = null;
			if($_POST['order-sonstige1'])
				$sonstiges1 = $_POST['order-sonstige1'];
			else
				$sonstiges1 = null;
            HotelTransfers::updateHotelTransfer([
                'dateto' => $arrDateto,
                'ankunftszeit_ruckflug' => $arrTimeto,
                'ruckflug_nummer' => $arrFlNr,
				'RuckflugnummerEdit' => $arrFlNr,
				'Sonstige_1' => $sonstiges1,
				'FahrerAb' => $fahrerAb,
				'editByRet' => $user->user_login
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
			
			if($ruckflug != ""){
				Orders::updateOrder([
					'return_flight_number' => $ruckflug,
					'RuckflugnummerEdit' => $ruckflug
				], ['order_id' => $_POST['order-nr']]);
			}
			
			if($_POST['order-parkplatz'] != ""){
				Orders::updateOrder([
					'Parkplatz' => $_POST['order-parkplatz']
				], ['order_id' => $_POST['order-nr']]);
			}
			
			if($_POST['order-sonstige1'] != ""){
				Orders::updateOrder([
					'Sonstige_1' => $_POST['order-sonstige1']
				], ['order_id' => $_POST['order-nr']]);
			}
			
			if($_POST['order-fahrer'] != ""){
				Orders::updateOrder([
					'FahrerAb' => $_POST['order-fahrer']
				], ['order_id' => $_POST['order-nr']]);
			}
			
			if($_POST['order-fahrer'] != ""){
				Orders::updateOrder([
					'FahrerAb' => $_POST['order-fahrer']
				], ['order_id' => $_POST['order-nr']]);
			}
			
            Orders::updateOrder([
				'date_to' => $dateTo,
				'Abreisedatum' => date('Y-m-d', strtotime($_POST['order-dateto'])),
				'AbreisedatumEdit' => date('Y-m-d', strtotime($_POST['order-dateto'])),
				'Uhrzeit_bis' => date('H:i', strtotime($_POST['order-timeto'])),
				'Uhrzeit_bisEdit' => date('H:i', strtotime($_POST['order-timeto'])),
				'nr_people' => $_POST['order-persons'],
				'Sonstige_2' => $sonstige_2,
				'editByRet' => $user->user_login,
				'order_status' => $_POST['order-status']
			], ['order_id' => $_POST['order-nr']]);
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
	
	public function update_abreiseliste_valet()
    {
		$orderSQL = Orders::getOrderByOrderId($_POST['order-nr']);
		$user = wp_get_current_user();
		if($_POST['order-betrag'] == "-")
			$betrag = $orderSQL->order_price;
		else
			$betrag = $_POST['order-betrag'];
		
		if(($orderSQL->Anreisedatum < date('Y-m-d')) || ($orderSQL->Anreisedatum == date('Y-m-d') && date('H:i', strtotime($orderSQL->Uhrzeit_von)) < date('H:i'))){
			$d1 = strtotime($orderSQL->first_abreisedatum);
			$d2 = strtotime($orderSQL->Anreisedatum);
			$orig_days = round(($d1 - $d2) / 86400);
			
			$d3 = strtotime($_POST['order-dateto']);
			$d4 = strtotime($orderSQL->Anreisedatum);				
			$update_days = round(($d3 - $d4) / 86400);
			
			if($update_days > $orig_days){
				$comp_days = $update_days - $orig_days;
				$sonstige_2 = "+". $comp_days . " TAG " . ($comp_days * 10) . " €";				
			}
			else
				$sonstige_2 = '';			
		}
		else
			$sonstige_2 = '';
		
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
            'Parkplatz' => $_POST['order-parkplatz'],
			'Kennzeichen' => $_POST['order-kennzeichen'],
			'Fahrzeugmodell' => $_POST['order-fahrzeug'],
			'Fahrzeugfarbe' => $_POST['order-farbe'],
            'Sonstige 1' => $_POST['order-sonstige1'],
            'Sonstige 2' => $sonstige_2,
            'P.-Code.' => $_POST['order-pcode'],
            'FahrerAb' => $_POST['order-fahrer'],
            'Status' => $_POST['order-status'],
            'Betrag' => $betrag
        ];
        $order = Orders::getByOrderId($_POST['order-nr']);
        $dateTo = date('Y-m-d H:i', strtotime(date('Y-m-d', strtotime($order->date_to)) . $_POST['order-timeto']));
			
		if($ruckflug != ""){
			Orders::updateOrder([
				'return_flight_number' => $ruckflug,
				'RuckflugnummerEdit' => $ruckflug
			], ['order_id' => $_POST['order-nr']]);
		}
		
		if($_POST['order-parkplatz'] != ""){
			Orders::updateOrder([
				'Parkplatz' => $_POST['order-parkplatz']
			], ['order_id' => $_POST['order-nr']]);
		}
		
		if($_POST['order-kennzeichen'] != ""){
			Orders::updateOrder([
				'Kennzeichen' => $_POST['order-kennzeichen']
			], ['order_id' => $_POST['order-nr']]);
		}
		
		if($_POST['order-sonstige1'] != ""){
			Orders::updateOrder([
				'Sonstige_1' => $_POST['order-sonstige1']
			], ['order_id' => $_POST['order-nr']]);
		}
		
		if($_POST['order-fahrer'] != ""){
			Orders::updateOrder([
				'FahrerAb' => $_POST['order-fahrer']
			], ['order_id' => $_POST['order-nr']]);
		}
		
		if($_POST['order-fahrer'] != ""){
			Orders::updateOrder([
				'FahrerAb' => $_POST['order-fahrer']
			], ['order_id' => $_POST['order-nr']]);
		}
		
		Orders::updateOrder([
			'date_to' => $dateTo,
			'Abreisedatum' => date('Y-m-d', strtotime($_POST['order-dateto'])),
			'AbreisedatumEdit' => date('Y-m-d', strtotime($_POST['order-dateto'])),
			'Uhrzeit_bis' => date('H:i', strtotime($_POST['order-timeto'])),
			'Uhrzeit_bisEdit' => date('H:i', strtotime($_POST['order-timeto'])),
			'Sonstige_2' => $sonstige_2,
			'editByRet' => $user->user_login,
			'order_status' => $_POST['order-status']
		], ['order_id' => $_POST['order-nr']]);

		update_post_meta($_POST['order-nr'], 'editByRet', $user->user_login);
        $this->db->updateList($_POST['order-nr'], $data);
    }

    public function update_anreiseliste()
    {
		$user = wp_get_current_user();
		$orderSQL = Orders::getOrderByOrderId($_POST['order-nr']);
		
		if($_POST['order-betrag'] == "-")
			$betrag = $orderSQL->order_price;
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
		
		if($_POST['order-spgp'] == "1")
			$spgp = "1";
		else
			$spgp = "0";
		
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
			'Sperrgepack' => $spgp,
            'P.-Code.' => $_POST['order-pcode'],
            'Status' => $_POST['order-status'],
            'Betrag' => $betrag
        ];

        $dateTo = date('Y-m-d H:i', strtotime($_POST['order-dateto'] . ' ' . $_POST['order-landung']));
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
			if($_POST['order-fahrer'])
				$fahrerAn = $_POST['order-fahrer'];
			else
				$fahrerAn = null;
			if($_POST['order-sonstiges'])
				$sonstiges1 = $_POST['order-sonstiges'];
			else
				$sonstiges1 = null;
			
            HotelTransfers::updateHotelTransfer([
                'datefrom' => $arrDatefrom,
                'dateto' => $arrDateto,
				'AbreisedatumEdit' => $arrDateto,
                'transfer_vom_hotel' => $arrTimefrom,
                'ankunftszeit_ruckflug' => $arrTimeto,
				'Uhrzeit_bisEdit' => $arrTimeto,
                'ruckflug_nummer' => $arrFlNr,
				'RuckflugnummerEdit' => $arrFlNr,
				'order_price' => number_format($price, 2, ".", "."),
				'Sonstige_1' => $sonstiges1,
				'Sperrgepack' => $spgp,
				'FahrerAn' => $fahrerAn,
				'editByArr' => $user->user_login,
				'order_status' => $_POST['order-status']
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
			if($_POST['order-dateto'] != ""){
				Orders::updateOrder([
					'date_to' => $dateTo,
					'Abreisedatum' => date('Y-m-d', strtotime($_POST['order-dateto']))
				], ['order_id' => $_POST['order-nr']]);
			}
			if($_POST['order-landung'] != ""){
				Orders::updateOrder([
					'Uhrzeit_bis' => $_POST['order-landung']
				], ['order_id' => $_POST['order-nr']]);
			}
			if($_POST['order-ruckflug'] != ""){
				Orders::updateOrder([
					'return_flight_number' => $ruckflug
				], ['order_id' => $_POST['order-nr']]);
			}
			if($_POST['order-parkplatz'] != ""){
				Orders::updateOrder([
					'Parkplatz' => $_POST['order-parkplatz']
				], ['order_id' => $_POST['order-nr']]);
			}
			if($dateToEdit != ""){
				Orders::updateOrder([
					'AbreisedatumEdit' => $dateToEdit
				], ['order_id' => $_POST['order-nr']]);
			}
			if($ruckflug != ""){
				Orders::updateOrder([
					'RuckflugnummerEdit' => $ruckflug
				], ['order_id' => $_POST['order-nr']]);
			}
			if($timeToEdit != ""){
				Orders::updateOrder([
					'Uhrzeit_bisEdit' => $timeToEdit
				], ['order_id' => $_POST['order-nr']]);
			}
			if($_POST['order-fahrer'] != ""){
				Orders::updateOrder([
					'FahrerAn' => $_POST['order-fahrer']
				], ['order_id' => $_POST['order-nr']]);
			}
			
			Orders::updateOrder([
				'Sonstige_1' => $_POST['order-sonstiges']
			], ['order_id' => $_POST['order-nr']]);
			

			Orders::updateOrder([
				'editByArr' => $user->user_login
			], ['order_id' => $_POST['order-nr']]);
			
            Orders::updateOrder([
                'date_from' => $dateFrom,
				'Anreisedatum' => date('Y-m-d', strtotime($_POST['order-datefrom'])),
				'Uhrzeit_von' => date('H:i', strtotime($_POST['order-timefrom'])),
                'last_name' => $_POST['order-lname'],
				'nr_people' => $_POST['order-persons'],
				'Sperrgepack' => $spgp,
				'order_status' => $_POST['order-status']
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
	
	public function update_anreiseliste_valet()
    {
		$user = wp_get_current_user();
		$orderSQL = Orders::getOrderByOrderId($_POST['order-nr']);
		
		if($_POST['order-betrag'] == "-")
			$betrag = $orderSQL->order_price;
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
            'Uhrzeit bis Edit' => $timeToEdit,
            'Parkplatz' => $_POST['order-parkplatz'],
			'Kennzeichen' => $_POST['order-kennzeichen'],
			'Fahrzeugmodell' => $_POST['order-fahrzeug'],
			'Fahrzeugfarbe' => $_POST['order-farbe'],
            'FahrerAn' => $_POST['order-fahrer'],
            'Sonstige 1' => $_POST['order-sonstiges'],
			'Sperrgepack' => $spgp,
            'P.-Code.' => $_POST['order-pcode'],
            'Status' => $_POST['order-status'],
            'Betrag' => $betrag
        ];

        $dateTo = date('Y-m-d H:i', strtotime($_POST['order-dateto'] . ' ' . $_POST['order-landung']));
        $dateFrom = date('Y-m-d H:i', strtotime($_POST['order-datefrom'] . ' ' . $_POST['order-timefrom']));

		if($_POST['order-dateto'] != ""){
			Orders::updateOrder([
				'date_to' => $dateTo,
				'Abreisedatum' => date('Y-m-d', strtotime($_POST['order-dateto']))
			], ['order_id' => $_POST['order-nr']]);
		}
		if($_POST['order-landung'] != ""){
			Orders::updateOrder([
				'Uhrzeit_bis' => $_POST['order-landung']
			], ['order_id' => $_POST['order-nr']]);
		}
		if($_POST['order-ruckflug'] != ""){
			Orders::updateOrder([
				'return_flight_number' => $ruckflug
			], ['order_id' => $_POST['order-nr']]);
		}
		if($_POST['order-parkplatz'] != ""){
			Orders::updateOrder([
				'Parkplatz' => $_POST['order-parkplatz']
			], ['order_id' => $_POST['order-nr']]);
		}
		if($_POST['order-kennzeichen'] != ""){
			Orders::updateOrder([
				'Kennzeichen' => $_POST['order-kennzeichen']
			], ['order_id' => $_POST['order-nr']]);
		}
		if($dateToEdit != ""){
			Orders::updateOrder([
				'AbreisedatumEdit' => $dateToEdit
			], ['order_id' => $_POST['order-nr']]);
		}
		if($ruckflug != ""){
			Orders::updateOrder([
				'RuckflugnummerEdit' => $ruckflug
			], ['order_id' => $_POST['order-nr']]);
		}
		if($timeToEdit != ""){
			Orders::updateOrder([
				'Uhrzeit_bisEdit' => $timeToEdit
			], ['order_id' => $_POST['order-nr']]);
		}
		if($_POST['order-fahrer'] != ""){
			Orders::updateOrder([
				'FahrerAn' => $_POST['order-fahrer']
			], ['order_id' => $_POST['order-nr']]);
		}
		
		Orders::updateOrder([
			'Sonstige_1' => $_POST['order-sonstiges']
		], ['order_id' => $_POST['order-nr']]);
		

		Orders::updateOrder([
			'editByArr' => $user->user_login
		], ['order_id' => $_POST['order-nr']]);
		
		Orders::updateOrder([
			'date_from' => $dateFrom,
			'Anreisedatum' => date('Y-m-d', strtotime($_POST['order-datefrom'])),
			'Uhrzeit_von' => date('H:i', strtotime($_POST['order-timefrom'])),
			'last_name' => $_POST['order-lname'],
			'order_status' => $_POST['order-status']
		], ['order_id' => $_POST['order-nr']]);
        
		update_post_meta($_POST['order-nr'], 'editByArr', $user->user_login);
        $this->db->updateList($_POST['order-nr'], $data);
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
		$base_url = $_SERVER['HTTP_HOST'];
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
		
		
		$this->db->updateParklot($parkhaus, $_POST['group_id'], $parklot, $type, $datefrom, $dateto, $_POST['booking_lead_time'], $_POST['contigent'], 		
		$product->get_id(), $_POST['product_isfor'], $_POST['parklot_adress'], $_POST['parklot_phone'], 
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
                    $this->db->updateDiscount($_POST['discount_name'][$i], $interval_from, $interval_to, $_POST['discount_type'][$i], $_POST['discount_value'][$i], $_POST['discount_days_before'][$i], $_POST['discount_contigent_limit'][$i], $_POST['discount_min_days'][$i], $_POST['discount_cancel'][$i], $_POST['discount_message'][$i], $product->get_id(), ['id' => $_POST['discount_id'][$i]]);
					
					if($base_url == "airport-parking-stuttgart.de"){
						$url = "https://airport-parking-germany.de/curl/?request=apm_update_discount&pw=apmprd_req57159428";
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS,
						http_build_query(array(
							 'discount_name' => $_POST['discount_name'][$i],
							 'interval_from' => $interval_from,
							 'interval_to' => $interval_to,
							 'discount_type' => $_POST['discount_type'][$i],
							 'discount_value' => $_POST['discount_value'][$i],
							 'discount_days_before' => $_POST['discount_days_before'][$i],
							 'discount_min_days' => $_POST['discount_min_days'][$i],
							 'discount_contigent_limit' => $_POST['discount_contigent_limit'][$i],
							 'discount_cancel' => $_POST['discount_cancel'][$i],
							 'discount_message' => $_POST['discount_message'][$i],
							 'product_id' => $product->get_id(),
							 'discount_id' => $_POST['discount_id'][$i],
						)));
						// Receive server response ...
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
						$server_output = curl_exec($ch);
						curl_close($ch);
					}
				
				} else {
					$this->db->saveDiscount($_POST['discount_name'][$i], $interval_from, $interval_to, $_POST['discount_type'][$i], $_POST['discount_value'][$i], $_POST['discount_days_before'][$i], $_POST['discount_contigent_limit'][$i], $_POST['discount_min_days'][$i], $_POST['discount_cancel'][$i], $_POST['discount_message'][$i], $product->get_id());
					
					global $wpdb;					
					$discount_id = $wpdb->get_row("SELECT id FROM " . $wpdb->prefix . "itweb_discounts WHERE name = '" . $_POST['discount_name'][$i] . "' AND interval_from = '" . $interval_from . "' AND interval_to = '" . $interval_to . "' 
					AND type = '" . $_POST['discount_type'][$i] . "' AND value = '" . $_POST['discount_value'][$i] . "' AND days_before = '" . $_POST['discount_days_before'][$i] . "' AND discount_contigent = '" . $_POST['discount_contigent_limit'][$i] . "' AND 
					product_id = " . $product->get_id());
					
					if($base_url == "airport-parking-stuttgart.de"){
						$url = "https://airport-parking-germany.de/curl/?request=apm_add_discount&pw=apmprd_req57159428";
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS,
						http_build_query(array(
							 'discount_name' => $_POST['discount_name'][$i],
							 'interval_from' => $interval_from,
							 'interval_to' => $interval_to,
							 'discount_type' => $_POST['discount_type'][$i],
							 'discount_value' => $_POST['discount_value'][$i],
							 'discount_days_before' => $_POST['discount_days_before'][$i],
							 'discount_min_days' => $_POST['discount_min_days'][$i],
							 'discount_contigent_limit' => $_POST['discount_contigent_limit'][$i],
							 'discount_cancel' => $_POST['discount_cancel'][$i],
							 'discount_message' => $_POST['discount_message'][$i],
							 'product_id' => $product->get_id(),
							 'discount_id' => $discount_id->id
						)));
						// Receive server response ...
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
						$server_output = curl_exec($ch);
						curl_close($ch);
					}
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
		$_POST['parkhaus'], $_POST['group_id'], $_POST['parklot'], $_POST['type'], $datefrom, $dateto, $_POST['booking_lead_time'], $_POST['contigent'], 
		$product->get_id(), $_POST['product_isfor'], $_POST[$_POST['product_isfor']], $_POST['parklot_adress'], $_POST['parklot_phone'], 
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
					$this->db->saveDiscount($_POST['discount_name'][$i], $interval_from, $interval_to, $_POST['discount_type'][$i], $_POST['discount_value'][$i], $_POST['discount_days_before'][$i], $_POST['discount_contigent_limit'][$i], $_POST['discount_min_days'][$i], $product->get_id());
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
	
	public function delete_price()
    {
        $id = $_POST['id'];
		$name = urlencode($_POST['name']);
		$base_url = $_SERVER['HTTP_HOST'];
		
		if($base_url == "airport-parking-stuttgart.de"){
			$data1 = array(
				
			);
			$query1 = http_build_query($data1);
			$query2 = http_build_query($data1);
			
			$ch1 = curl_init();
			$ch2 = curl_init();
			
			curl_setopt($ch1, CURLOPT_URL, "https://airport-parking-germany.de/search-result/?request=apm_price_del&pw=apmprd_req57159428&p_name=".$name);
			curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch1, CURLOPT_POST, true);
			curl_setopt($ch1, CURLOPT_POSTFIELDS, $query1);

			curl_setopt($ch2, CURLOPT_URL, "https://parken-zum-fliegen.de/curl/?request=apm_price_del&pw=apmprd_req57159428&p_name=".$name);
			curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch2, CURLOPT_POST, true);
			curl_setopt($ch2, CURLOPT_POSTFIELDS, $query2);
			
			$mh = curl_multi_init();
			
			curl_multi_add_handle($mh, $ch1);
			curl_multi_add_handle($mh, $ch2);
			
			do {
				curl_multi_exec($mh, $running);
			} while ($running > 0);
			
			$response1 = curl_multi_getcontent($ch1);
			$response2 = curl_multi_getcontent($ch2);
			
			curl_multi_remove_handle($mh, $ch1);
			curl_multi_remove_handle($mh, $ch2);
			curl_multi_close($mh);
			
			curl_close($ch1);
			curl_close($ch2);
		}
		
        return $this->db->deletePrice($id);
    }
	
	public function price_delete_from_calendar(){
        $lot_id = $_POST['lotid'];
		$base_url = $_SERVER['HTTP_HOST'];
		
		if($base_url == "airport-parking-stuttgart.de"){
			$data1 = array(
				
			);
			
			$query1 = http_build_query($data1);
			$query2 = http_build_query($data1);
			
			$ch1 = curl_init();
			$ch2 = curl_init();
			
			curl_setopt($ch1, CURLOPT_URL, "https://airport-parking-germany.de/curl/?request=apm_delAll_cal&pw=apmcal_req57159428&product_id=".$lot_id);
			curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch1, CURLOPT_POST, true);
			curl_setopt($ch1, CURLOPT_POSTFIELDS, $query1);

			curl_setopt($ch2, CURLOPT_URL, "https://parken-zum-fliegen.de/curl/?request=apm_delAll_cal&pw=apmcal_req57159428&product_id=".$lot_id);
			curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch2, CURLOPT_POST, true);
			curl_setopt($ch2, CURLOPT_POSTFIELDS, $query2);
			
			$mh = curl_multi_init();
			
			curl_multi_add_handle($mh, $ch1);
			curl_multi_add_handle($mh, $ch2);
			
			do {
				curl_multi_exec($mh, $running);
			} while ($running > 0);
			
			$response1 = curl_multi_getcontent($ch1);
			$response2 = curl_multi_getcontent($ch2);
			
			curl_multi_remove_handle($mh, $ch1);
			curl_multi_remove_handle($mh, $ch2);
			curl_multi_close($mh);
			
			curl_close($ch1);
			curl_close($ch2);
		}
		
        echo $this->db->allprice_delete_from_calendar($lot_id);
    }
	
	public function addPrice_calendarFast(){
        global $wpdb;
		$product = $_POST['product'];
        $price = $_POST['price'];
		$date = $_POST['date'];
		$base_url = $_SERVER['HTTP_HOST'];
		
		if(isset($date)){
			$date = (explode(" - ",$date));
			$date[0] = date('Y-m-d', strtotime($date[0]));
			$date[1] = date('Y-m-d', strtotime($date[1]));
			
			$name = $wpdb->get_row("SELECT name FROM " . $wpdb->prefix . "itweb_prices WHERE id = " . $price);
			$name->name = str_replace(" ", "%20", $name->name);
			
			while($date[0] <= $date[1]){
				$this->db->addEventsFast($date[0], $product, $price);				
				
				if($base_url == "airport-parking-stuttgart.de"){
					$data1 = array(
						
					);
					
					$query1 = http_build_query($data1);
					$query2 = http_build_query($data1);
					
					$ch1 = curl_init();
					$ch2 = curl_init();
					
					curl_setopt($ch1, CURLOPT_URL, "https://airport-parking-germany.de/curl/?request=apm_save_cal&pw=apmcal_req57159428&p_name=".$name->name."&product_id=".$product."&datefrom=".$date[0]."&dateto=".$date[0]);
					curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch1, CURLOPT_POST, true);
					curl_setopt($ch1, CURLOPT_POSTFIELDS, $query1);

					curl_setopt($ch2, CURLOPT_URL, "https://parken-zum-fliegen.de/curl/?request=apm_save_cal&pw=apmcal_req57159428&p_name=".$name->name."&product_id=".$product."&datefrom=".$date[0]."&dateto=".$date[0]);
					curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch2, CURLOPT_POST, true);
					curl_setopt($ch2, CURLOPT_POSTFIELDS, $query2);
					
					$mh = curl_multi_init();
					
					curl_multi_add_handle($mh, $ch1);
					curl_multi_add_handle($mh, $ch2);
					
					do {
						curl_multi_exec($mh, $running);
					} while ($running > 0);
					
					$response1 = curl_multi_getcontent($ch1);
					$response2 = curl_multi_getcontent($ch2);
					
					curl_multi_remove_handle($mh, $ch1);
					curl_multi_remove_handle($mh, $ch2);
					curl_multi_close($mh);
					
					curl_close($ch1);
					curl_close($ch2);
				}
				
				$date[0] = date('Y-m-d', strtotime($date[0]. ' + 1 days'));
			}			
		}
		
		return true;
    }
	
	public function delPrice_calendarFast(){
        global $wpdb;
		$product = $_POST['product'];
		$date = $_POST['date'];
		$base_url = $_SERVER['HTTP_HOST'];
		
		if(isset($date)){
			$date = (explode(" - ",$date));
			$date[0] = date('Y-m-d', strtotime($date[0]));
			$date[1] = date('Y-m-d', strtotime($date[1]));
			
			$name = $wpdb->get_row("SELECT name FROM " . $wpdb->prefix . "itweb_prices WHERE id = " . $price);
			$name->name = str_replace(" ", "%20", $name->name);
			
			while($date[0] <= $date[1]){
				$this->db->delEventsFast($date[0], $product);				
				
				if($base_url == "airport-parking-stuttgart.de"){
					$data1 = array(
						
					);
					
					$query1 = http_build_query($data1);
					$query2 = http_build_query($data1);
					
					$ch1 = curl_init();
					$ch2 = curl_init();
					
					curl_setopt($ch1, CURLOPT_URL, "https://airport-parking-germany.de/curl/?request=apm_del_cal&pw=apmcal_req57159428&p_name=".$name->name."&product_id=".$product."&datefrom=".$date[0]."&dateto=".$date[0]);
					curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch1, CURLOPT_POST, true);
					curl_setopt($ch1, CURLOPT_POSTFIELDS, $query1);

					curl_setopt($ch2, CURLOPT_URL, "https://parken-zum-fliegen.de/curl/?request=apm_del_cal&pw=apmcal_req57159428&p_name=".$name->name."&product_id=".$product."&datefrom=".$date[0]."&dateto=".$date[0]);
					curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch2, CURLOPT_POST, true);
					curl_setopt($ch2, CURLOPT_POSTFIELDS, $query2);
					
					$mh = curl_multi_init();
					
					curl_multi_add_handle($mh, $ch1);
					curl_multi_add_handle($mh, $ch2);
					
					do {
						curl_multi_exec($mh, $running);
					} while ($running > 0);
					
					$response1 = curl_multi_getcontent($ch1);
					$response2 = curl_multi_getcontent($ch2);
					
					curl_multi_remove_handle($mh, $ch1);
					curl_multi_remove_handle($mh, $ch2);
					curl_multi_close($mh);
					
					curl_close($ch1);
					curl_close($ch2);
				}
				
				$date[0] = date('Y-m-d', strtotime($date[0]. ' + 1 days'));
			}			
		}
		
		return true;
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
        $_SESSION["parklots"][$proid]['discount_id'] = $_POST['discount'] == 'true' ? (int)$_POST['discount_id'] : null;

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
			$_POST['email'],
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

		HotelTransfers::newHotelTransfer_backend(
            $_POST['pID'],
			$_POST['type'],
            $_POST['datefrom'],
            $_POST['dateto'],
            $_POST['vorname'],
            $_POST['nachname'],
            $_POST['phone'],
			$_POST['email'],
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
		
		HotelTransfers::updateHotelTransfer([
            'order_status' => 'wc-cancelled'
        ], ['id' => $_POST['order_id']]);
		
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
	
	public function editBooking_cancel(){
		$order_id = $_POST['order_id'];
		$order = new WC_Order($order_id);
		$base_url = $_SERVER['HTTP_HOST'];
		$web_company = Database::getInstance()->getSiteCompany();
		if (!empty($order)) {
			$booking_date = date('Y-m-d', strtotime($order->get_date_created()));
			$order->update_status( 'cancelled' );
			
			Orders::updateOrder([
					'order_status' => 'wc-cancelled'
				], ['order_id' => $order_id]);
				
			HotelTransfers::updateHotelTransfer([
				'order_status' => 'wc-cancelled'
			], ['id' => $order_id]);
			
			$bookingRef = $order->get_meta('token');
			$items = $order->get_items();
			foreach ( $items as $item ) {				
				$product_id = $item->get_product_id();
			}
			if($product_id == 537 || $product_id == 592 || $product_id == 619 || $product_id == 873){
				if(get_post_meta($order_id, '_payment_method_title', true) != 'Barzahlung' && get_post_meta($order_id, '_payment_method_title', true) != 'MasterCard')
					add_post_meta($order_id, 'paypal_rerunded', 0, true);
			}
			
			$apiUrl = "https://www.parkplatzvergleich.de/pricechecker/public/api/getReservations?date=".$booking_date."&api_token=8235e4aa19f360f2a5263256aa915d65";
			$jsonData = file_get_contents($apiUrl);
			$data = json_decode($jsonData, true);
			
			foreach($data['bookings'] as $val){
				if($val['external_id'] == $bookingRef){
					$cancelUrl = 'https://parkplatzvergleich.de/pricechecker/public/api/cancelReservation?booking_id='.$val['id'].'&api_token=8235e4aa19f360f2a5263256aa915d65&name=xxx&mail=xxx&phone=xxx&reason=xxx';
					$ch = curl_init($cancelUrl);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$response = curl_exec($ch);
					curl_close($ch);
				}
			}
			
			$billing_email = $order->get_billing_email();			
			$subject = 'Ihre Buchung ' . $bookingRef . ' wurde storniert';
			if($web_company->name && $web_company->email && $web_company->phone){
				$body = "<h3>Ihr Parkplatz wurde storniert.</h3>
						<p>Sehr geehrte Damen und Herren,</p>
						<p>Ihre Buchung mit der Buchungsnummer <strong>".$bookingRef."</strong> wurde storniert.<br/></p>
						<p>Sie haben noch Fragen? Schreiben Sie uns einfach eine E-Mail an
							<a href='mailto:".$web_company->email."'>".$web_company->email."</a> oder rufen Sie uns
							unter <a href='tel:".$web_company->phone."'>".$web_company->phone."</a> an.
						</p>
						<p>Montag bis Freitag von 11:00 bis 19:00 Uhr.
						   Aus dem dt. Festnetz zum Ortstarif. Mobilfunkkosten abweichend.</p>
						<p>Mit freundlichen Grüßen</p>
						<p>".$web_company->name."<br>
						".$web_company->street.", ".$web_company->zip." ".$web_company->location.", Deutschland<br>
						<a href='www.".$_SERVER['HTTP_HOST']."'>www.".$_SERVER['HTTP_HOST']."</a></p>			
					";
			}
			else{
				$body = "<h3>Ihr Parkplatz wurde storniert.</h3>
							<p>Sehr geehrte Damen und Herren,</p>
							<p>Ihre Buchung mit der Buchungsnummer <strong>".$bookingRef."</strong> wurde storniert.<br/></p>
						<p>Mit freundlichen Grüßen</p>";
			}
			$headers = array('Content-Type: text/html; charset=UTF-8');
			wp_mail( $billing_email, $subject, $body, $headers );
			wp_mail("noreply@".$_SERVER['HTTP_HOST'], $subject, $body, $headers );
			
			if($base_url == "airport-parking-stuttgart.de"){
				$data1 = array(
					'request' => 'apm_cancel',
					 'pw' => 'apmc_req57159428',
					 'token' => $bookingRef	
				);
				
				$query1 = http_build_query($data1);
				$query2 = http_build_query($data1);
				
				$ch1 = curl_init();
				$ch2 = curl_init();
				
				curl_setopt($ch1, CURLOPT_URL, "https://airport-parking-germany.de/search-result/");
				curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch1, CURLOPT_POST, true);
				curl_setopt($ch1, CURLOPT_POSTFIELDS, $query1);

				curl_setopt($ch2, CURLOPT_URL, "https://parken-zum-fliegen.de/curl/");
				curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch2, CURLOPT_POST, true);
				curl_setopt($ch2, CURLOPT_POSTFIELDS, $query2);
				
				$mh = curl_multi_init();
				
				curl_multi_add_handle($mh, $ch1);
				curl_multi_add_handle($mh, $ch2);
				
				do {
					curl_multi_exec($mh, $running);
				} while ($running > 0);
				
				$response1 = curl_multi_getcontent($ch1);
				$response2 = curl_multi_getcontent($ch2);
				
				curl_multi_remove_handle($mh, $ch1);
				curl_multi_remove_handle($mh, $ch2);
				curl_multi_close($mh);
				
				curl_close($ch1);
				curl_close($ch2);
			}
					
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
			
			Orders::updateOrder([
					'order_status' => 'wc-processing'
				], ['order_id' => $order_id]);
			
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
			elseif($parklot->product_id == 82029)
				$tmp_product = 80566;
			elseif($parklot->product_id == 82031)
				$tmp_product = 80567;
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
			$price = $priceList;
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
	
	public function produktPreisAbfrage(){
		$order_id = $_POST['order_id'];
		$product_id = $_POST['product_id'];
		$discount_id = $_POST['discount_id'];
		$from = date_format(date_create($_POST['dateFrom']), 'Y-m-d');
		$to = date_format(date_create($_POST['dateTo']), 'Y-m-d');
		if($discount_id != null)
			$price = number_format(Pricelist::calculateAndDiscount($product_id, $from, $to), 2, '.', '.');
		else			
			$price = number_format(Pricelist::calculate($product_id, $from, $to), 2, ".", ".");
		
		echo $price;
	}
}

$helper = new Helper;
if (method_exists($helper, $task)) {
    $helper->$task();
}
