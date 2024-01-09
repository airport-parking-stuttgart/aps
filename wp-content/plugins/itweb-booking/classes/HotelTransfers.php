<?php

class HotelTransfers
{
	static function getHotelProdukt($user_id)
    {
		global $wpdb;		
		return $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'itweb_parklots WHERE user_id = ' . $user_id);
	}
    static function newHotelTransfer($pID, $type, $datefrom, $dateto, $vorname, $nachname, $phone, $email, $product_id, $transfer_vom_hotel, $ankunftszeit_ruckflug, $hinflugnummer, $ruckflugnummer, $sonstiges)
    {
        global $wpdb;
		unset($_SESSION['errors']);
		$user_id = $pID;
		$product_perent = self::getHotelProdukt($user_id);
        $product = wc_get_product($product_id);

        $order = Orders::createWCOrder($product);
        $order->set_billing_first_name($vorname);
        $order->set_billing_last_name($nachname);
        $order->set_billing_phone($phone);
		$order->set_billing_email($email);
        $order->save();
        $order_id = $order->get_id();
		$variation = wc_get_product($product_id);
		if($variation){
			$product_variation_name = $variation->get_variation_attributes();
		}
			
		else{
			$_SESSION['errors']['all'][] = ['Es gibt Probleme mit dem Produkt. Zuweisung fehlerhaft.'];
		}
		if (isset($_SESSION['errors']['all'])) {
            unset($_SESSION['errors']);
			return;
        }
		
		$personen = $product_variation_name['attribute_pa_passagiere'] * 1;
		add_post_meta($order_id, 'Personenanzahl', $personen, true);

        $token = $product_perent->prefix . "-" . generateToken(5);
        update_post_meta($order->get_id(), 'token', $token);
		if($datefrom){
			add_post_meta($order_id, 'Anreisedatum', dateFormat($datefrom), true);
			add_post_meta($order_id, 'first_anreisedatum', dateFormat($datefrom), true);
			add_post_meta($order_id, 'Uhrzeit von', $transfer_vom_hotel, true);
			add_post_meta($order_id, 'Hinflugnummer', $hinflugnummer, true);
        }
		if($dateto){
			add_post_meta($order_id, 'Abreisedatum', dateFormat($dateto), true);
			add_post_meta($order_id, 'first_abreisedatum', dateFormat($dateto), true);
			add_post_meta($order_id, 'Uhrzeit bis', $ankunftszeit_ruckflug, true);       
			add_post_meta($order_id, 'Rückflugnummer', $ruckflugnummer, true);
        }
		if($sonstiges)
			add_post_meta($order_id, 'Sonstige 1', $sonstiges, true);
		//Database::getInstance()->saveBookingMeta($order_id, 'provision', '0.00');
//        $wpdb->insert($wpdb->prefix . 'itweb_orders', [
//            'date_from' => $datefrom ? date('Y-m-d H:i', strtotime($datefrom . ' ' . $transfer_vom_hotel)) : date('Y-m-d H:i', strtotime('2000-01-01 00:00:00')),
//            'date_to' => $dateto ? date('Y-m-d H:i', strtotime($dateto . ' ' . $ankunftszeit_ruckflug)) : date('Y-m-d H:i', strtotime('2000-01-01 00:00:00')),
//            'product_id' => $product_id,
//            'order_id' => $order_id,
//            'out_flight_number' => $hinflugnummer ? $hinflugnummer : '',
//            'return_flight_number' => $ruckflugnummer ? $ruckflugnummer : '',
//            'nr_people' => 0
//        ]);
        $data = [
            'post_date' => date('Y-m-d'),
			'datefrom' => $datefrom ? date('Y-m-d', strtotime($datefrom)) : null,
            'dateto' => $dateto ? date('Y-m-d', strtotime($dateto)) : null,
            'product_id' => $product_perent->product_id,
            'variation_id' => $product_id,
            'user_id' => $user_id,
            'order_id' => $order->get_id(),
            'transfer_vom_hotel' => $transfer_vom_hotel,
            'ankunftszeit_ruckflug' => $ankunftszeit_ruckflug,
            'hinflug_nummer' => $hinflugnummer,
            'ruckflug_nummer' => $ruckflugnummer,
            'token' => $token,
			'first_name' => $vorname,
			'last_name' => $nachname,
			'email' => $email,
			'phone' => $phone,
			'nr_people' => $personen,
			'order_status' => 'wc-processing'
        ];

        if ($type == 'rucktransfer') {
            unset($data['datefrom']);
            unset($data['transfer_vom_hotel']);
            unset($data['hinflug_nummer']);
        } else if ($type == 'hintransfer') {
            unset($data['dateto']);
            unset($data['ankunftszeit_ruckflug']);
            unset($data['ruckflug_nummer']);
        }
		if($datefrom != null && $dateto != null){
			$priceD = $order->get_total() * 2;
			$order_items = $order->get_items();
			foreach ( $order_items as $key => $value ) {
				$order_item_id = $key;
				   $product_value = $value->get_data();
				   $product_id    = $product_value['product_id']; 
			}
			$product = wc_get_product( $product_id );
			$price = number_format((float)$priceD, 4, '.', '');
			$order_items[ $order_item_id ]->set_total( $price );
			$order->calculate_taxes();
			$order->calculate_totals();
			$order->save();
		}
		$data['order_price'] = $order->get_total();
        return $wpdb->insert($wpdb->prefix . 'itweb_hotel_transfers', $data);
    }
	
	static function newHotelTransfer_backend($pID, $type, $datefrom, $dateto, $vorname, $nachname, $phone, $email, $product_id, $transfer_vom_hotel, $ankunftszeit_ruckflug, $hinflugnummer, $ruckflugnummer, $sonstiges)
    {
        global $wpdb;
		unset($_SESSION['errors']);
		$user_id = $pID;
		$product_perent = self::getHotelProdukt($user_id);
        $product = wc_get_product($product_id);

        $order = Orders::createWCOrder($product);
        $order->set_billing_first_name($vorname);
        $order->set_billing_last_name($nachname);
        $order->set_billing_phone($phone);
		$order->set_billing_email($email);
        $order->save();
        $order_id = $order->get_id();
		$variation = wc_get_product($product_id);
		if($variation){
			$product_variation_name = $variation->get_variation_attributes();
		}
			
		else{
			$_SESSION['errors']['all'][] = ['Es gibt Probleme mit dem Produkt. Zuweisung fehlerhaft.'];
		}
		if (isset($_SESSION['errors']['all'])) {
            unset($_SESSION['errors']);
			return;
        }
		
		$personen = $product_variation_name['attribute_pa_passagiere'] * 1;
		add_post_meta($order_id, 'Personenanzahl', $personen, true);

        $token = $product_perent->prefix . "-" . generateToken(5);
        update_post_meta($order->get_id(), 'token', $token);
		if($datefrom){
			add_post_meta($order_id, 'Anreisedatum', dateFormat($datefrom), true);
			add_post_meta($order_id, 'first_anreisedatum', dateFormat($datefrom), true);
			add_post_meta($order_id, 'Uhrzeit von', $transfer_vom_hotel, true);
			add_post_meta($order_id, 'Hinflugnummer', $hinflugnummer, true);
        }
		if($dateto){
			add_post_meta($order_id, 'Abreisedatum', dateFormat($dateto), true);
			add_post_meta($order_id, 'first_abreisedatum', dateFormat($dateto), true);
			add_post_meta($order_id, 'Uhrzeit bis', $ankunftszeit_ruckflug, true);       
			add_post_meta($order_id, 'Rückflugnummer', $ruckflugnummer, true);
        }
		if($sonstiges)
			add_post_meta($order_id, 'Sonstige 1', $sonstiges, true);
		Database::getInstance()->saveBookingMeta($order_id, 'provision', '0.00');

        $data = [
            'post_date' => date('Y-m-d'),
			'datefrom' => $datefrom ? date('Y-m-d', strtotime($datefrom)) : null,
            'dateto' => $dateto ? date('Y-m-d', strtotime($dateto)) : null,
            'product_id' => $product_perent->product_id,
            'variation_id' => $product_id,
            'user_id' => $user_id,
            'order_id' => $order->get_id(),
            'transfer_vom_hotel' => $transfer_vom_hotel,
            'ankunftszeit_ruckflug' => $ankunftszeit_ruckflug,
            'hinflug_nummer' => $hinflugnummer,
            'ruckflug_nummer' => $ruckflugnummer,
            'token' => $token,
			'first_name' => $vorname,
			'last_name' => $nachname,
			'email' => $email,
			'phone' => $phone,
			'nr_people' => $personen,
			'order_status' => 'wc-processing'
        ];

        if ($type == 'rucktransfer') {
            unset($data['datefrom']);
            unset($data['transfer_vom_hotel']);
            unset($data['hinflug_nummer']);
        } else if ($type == 'hintransfer') {
            unset($data['dateto']);
            unset($data['ankunftszeit_ruckflug']);
            unset($data['ruckflug_nummer']);
        }
		if($datefrom != null && $dateto != null){
			$priceD = $order->get_total() * 2;
			$order_items = $order->get_items();
			foreach ( $order_items as $key => $value ) {
				$order_item_id = $key;
				   $product_value = $value->get_data();
				   $product_id    = $product_value['product_id']; 
			}
			$product = wc_get_product( $product_id );
			$price = number_format((float)$priceD, 4, '.', '');
			$order_items[ $order_item_id ]->set_total( $price );
			$order->calculate_taxes();
			$order->calculate_totals();
			$order->save();
		}
		$data['order_price'] = $order->get_total();
		$wpdb->insert($wpdb->prefix . 'itweb_hotel_transfers', $data);
		
		
		ob_start();
	
		$t_tem = str_replace("/classes", "/templates", __DIR__);
		 if (file_exists($t_tem . '/buchung/transfer-template.php')) {
			include($t_tem . '/buchung/transfer-template.php');
		}		
        return true;
    }

    static function getHotelTransfers($date1, $date2)
    {
        global $wpdb;
        return $wpdb->get_results("select t.* from {$wpdb->prefix}itweb_hotel_transfers t 
						where t.user_id = " . get_current_user_id() . " and t.datefrom between date('".$date1."') and date('".$date2."') 
						union all 
						select t.* from {$wpdb->prefix}itweb_hotel_transfers t 
						where t.user_id = " . get_current_user_id() . " and (t.dateto between date('".$date1."') and date('".$date2."')) and t.datefrom is null 
						order by dateto, datefrom");
    }
	
    static function getHotelTransfers_ForBackend($user_id, $date1, $date2)
    {
        global $wpdb;
        return $wpdb->get_results("select t.* from {$wpdb->prefix}itweb_hotel_transfers t 
						where t.user_id = " . $user_id . " and t.datefrom between date('".$date1."') and date('".$date2."') 
						union all 
						select t.* from {$wpdb->prefix}itweb_hotel_transfers t 
						where t.user_id = " . $user_id . " and (t.dateto between date('".$date1."') and date('".$date2."')) and t.datefrom is null 
						order by dateto, datefrom");
    }
	
	static function getHotelTransfersForInvioce_Hin($month, $year, $user_id)
    {
		global $wpdb;
        return $wpdb->get_results("
			SELECT t.order_id AS Buchung, pm.meta_value AS Betrag, t.variation_id, t.user_id
			FROM {$wpdb->prefix}itweb_hotel_transfers t
			LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = t.order_id AND pm.meta_key = '_order_total'
			LEFT JOIN {$wpdb->prefix}wc_order_stats s ON s.order_id = t.order_id
			WHERE s.status != 'wc-cancelled' AND t.user_id = " . $user_id . " AND YEAR(t.datefrom) = '".$year."' AND MONTH(t.datefrom) = '".$month."' AND 
			t.dateto IS NULL
		");
	}
	static function getHotelTransfersForInvioce_Zurück($month, $year, $user_id)
    {
		global $wpdb;
        return $wpdb->get_results("
			SELECT t.order_id AS Buchungen, pm.meta_value AS Betrag, t.variation_id, t.user_id
			FROM {$wpdb->prefix}itweb_hotel_transfers t
			LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = t.order_id AND pm.meta_key = '_order_total'
			LEFT JOIN {$wpdb->prefix}wc_order_stats s ON s.order_id = t.order_id
			WHERE s.status != 'wc-cancelled' AND t.user_id = " . $user_id . " AND YEAR(t.dateto) = '".$year."' AND MONTH(t.dateto) = '".$month."' AND 
			t.datefrom IS NULL
		");
	}
	static function getHotelTransfersForInvioce_Beide($month, $year, $user_id)
    {
		global $wpdb;
        return $wpdb->get_results("
			SELECT t.order_id AS Buchungen, pm.meta_value AS Betrag, t.variation_id, t.user_id
			FROM {$wpdb->prefix}itweb_hotel_transfers t
			LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = t.order_id AND pm.meta_key = '_order_total'
			LEFT JOIN {$wpdb->prefix}wc_order_stats s ON s.order_id = t.order_id
			WHERE s.status != 'wc-cancelled' AND t.user_id = " . $user_id . " AND YEAR(t.datefrom) = '".$year."' AND MONTH(t.datefrom) = '".$month."' AND 
			t.dateto IS NOT NULL AND t.datefrom IS NOT NULL
		");
	}
	
	static function getHotelTransfersForInvioce($month, $year)
    {
        global $wpdb;
        return $wpdb->get_results("
			SELECT 'Hin', COUNT(t.order_id) AS Buchungen, ROUND(SUM(pm.meta_value),2) AS Betrag, t.user_id
			FROM {$wpdb->prefix}itweb_hotel_transfers t
			LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = t.order_id AND pm.meta_key = '_order_total'
			LEFT JOIN {$wpdb->prefix}wc_order_stats s ON s.order_id = t.order_id
			WHERE s.status != 'wc-cancelled' AND t.user_id = " . get_current_user_id() . " AND YEAR(t.datefrom) = '".$year."' AND MONTH(t.datefrom) = '".$month."' AND 
			t.dateto IS NULL

			UNION ALL

			SELECT 'Zurück', COUNT(t.order_id) AS Buchungen, ROUND(SUM(pm.meta_value),2) AS Betrag, t.user_id
			FROM {$wpdb->prefix}itweb_hotel_transfers t
			LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = t.order_id AND pm.meta_key = '_order_total'
			LEFT JOIN {$wpdb->prefix}wc_order_stats s ON s.order_id = t.order_id
			WHERE s.status != 'wc-cancelled' AND t.user_id = " . get_current_user_id() . " AND YEAR(t.dateto) = '".$year."' AND MONTH(t.dateto) = '".$month."' AND 
			t.datefrom IS NULL
			GROUP BY t.user_id

			UNION ALL

			SELECT 'Beide', COUNT(t.order_id) AS Buchungen, ROUND(SUM(pm.meta_value),2) AS Betrag, t.user_id
			FROM {$wpdb->prefix}itweb_hotel_transfers t
			LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = t.order_id AND pm.meta_key = '_order_total'
			LEFT JOIN {$wpdb->prefix}wc_order_stats s ON s.order_id = t.order_id
			WHERE s.status != 'wc-cancelled' AND t.user_id = " . get_current_user_id() . " AND YEAR(t.datefrom) = '".$year."' AND MONTH(t.datefrom) = '".$month."' AND 
			t.dateto IS NOT NULL AND t.datefrom IS NOT NULL
			GROUP BY t.user_id
		");
    }

    static function updateHotelTransfer($data, $condition){
        global $wpdb;
        $wpdb->update($wpdb->prefix.'itweb_hotel_transfers', $data, $condition);
    }

    static function getHotelTransferByOrderId($order_id){
        global $wpdb;
        return $wpdb->get_row("select * from {$wpdb->prefix}itweb_hotel_transfers where order_id = $order_id");
    }

    static function getHotelTransfer($id){
        global $wpdb;
        return $wpdb->get_row("select * from {$wpdb->prefix}itweb_hotel_transfers where id = $id");
    }

    static function deleteHotelTransfer($condition)
    {
        global $wpdb;
        return $wpdb->delete($wpdb->prefix . 'itweb_hotel_transfers', $condition);
    }
	
	static function getTransferProduct($user_id)
    {
        global $wpdb;
        return $wpdb->get_row("select * from {$wpdb->prefix}itweb_parklots where user_id = $user_id");
    }
	
	static function getTransferLastInvice($user_id, $month, $year)
    {
        global $wpdb;
        return $wpdb->get_row("select id from {$wpdb->prefix}itweb_partner_rechnungen where user_id = $user_id and month = $month and year = $year");
    }
	static function setTransferLastInvice($user_id, $month, $year)
    {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . "itweb_partner_rechnungen", [
                    'user_id' => $user_id,
                    'month' => $month,
                    'year' => $year
                ]);
    }
}