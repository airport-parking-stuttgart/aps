<?php

// get events
add_action('rest_api_init', 'getEvents');
function getEvents()
{
    register_rest_route('api', 'events', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => function () {
            global $wpdb;
            $product_id = isset($_GET['productId']) ? (int)$_GET['productId'] : 0;

            $sql = "
            select 
                events.id, 
                events.datefrom as start,
                events.dateto as end,
                prices.name as title
            from 
                " . $wpdb->prefix . "itweb_events events, 
                " . $wpdb->prefix . "itweb_prices prices 
            where
                prices.id = events.price_id and
                events.product_id = " . $product_id;

            return $wpdb->get_results($sql);
        }
    ]);
}

// save event
add_action('rest_api_init', 'saveEvent');
function saveEvent()
{
    register_rest_route('api', 'save-event', [
        'methods' => [WP_REST_Server::READABLE, WP_REST_Server::CREATABLE],
        'callback' => function () {
            global $wpdb;
            $price_id = (int)$_POST['price_id'];
            $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
			$name = $_POST['name'];
            $res = $wpdb->query("SELECT * FROM `{$wpdb->prefix}itweb_events` WHERE ('" . $_POST['datefrom'] . "' BETWEEN datefrom AND dateto) and product_id = " . $product_id);
            if ($res > 0) {
                return false;
            }
            $sql = "insert into " . $wpdb->prefix . "itweb_events(`datefrom`, `dateto`, `price_id`, `product_id`) values('" . $_POST['datefrom'] . "', '" . $_POST['dateto'] . "', " . $price_id . ", " . $product_id . ")";
            $wpdb->query($sql);
			
			$name = str_replace(" ", "%20", $name);
			
			$data1 = array(
			
			);
			
			$query1 = http_build_query($data1);
			$query2 = http_build_query($data1);
			
			$ch1 = curl_init();
			$ch2 = curl_init();
			
			curl_setopt($ch1, CURLOPT_URL, "https://airport-parking-germany.de/curl/?request=apm_save_cal&pw=apmcal_req57159428&price_id=".(int)$_POST['price_id']."&p_name=".$name."&product_id=".$product_id."&datefrom=".$_POST['datefrom']."&dateto=".$_POST['dateto']);
			curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch1, CURLOPT_POST, true);
			curl_setopt($ch1, CURLOPT_POSTFIELDS, $query1);

			curl_setopt($ch2, CURLOPT_URL, "https://parken-zum-fliegen.de/curl/?request=apm_save_cal&pw=apmcal_req57159428&price_id=".(int)$_POST['price_id']."&p_name=".$name."&product_id=".$product_id."&datefrom=".$_POST['datefrom']."&dateto=".$_POST['dateto']);
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
			
            return $wpdb->get_results("select * from " . $wpdb->prefix . "itweb_events");
        }
    ]);
}

// delete event
add_action('rest_api_init', 'deleteEvent');
function deleteEvent()
{
    register_rest_route('api', 'delete-event', [
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => function () {
            global $wpdb;
            $id = (int)$_GET['id'];
			
			$event = $wpdb->get_row("SELECT date(e.datefrom) as datefrom, date(e.dateto) as dateto, e.product_id, p.name FROM " . $wpdb->prefix . "itweb_events e
			inner join " . $wpdb->prefix . "itweb_prices p on p.id = e.price_id
			WHERE e.id = " . $id);
			
			$event->name = str_replace(" ", "%20", $event->name);
			
			$data1 = array(
			
			);
			
			$query1 = http_build_query($data1);
			$query2 = http_build_query($data1);
			
			$ch1 = curl_init();
			$ch2 = curl_init();
			
			curl_setopt($ch1, CURLOPT_URL, "https://airport-parking-germany.de/curl/?request=apm_del_cal&pw=apmcal_req57159428&p_name=".$event->name."&product_id=".$event->product_id."&datefrom=".$event->datefrom."&dateto=".$event->dateto);
			curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch1, CURLOPT_POST, true);
			curl_setopt($ch1, CURLOPT_POSTFIELDS, $query1);

			curl_setopt($ch2, CURLOPT_URL, "https://parken-zum-fliegen.de/curl/?request=apm_del_cal&pw=apmcal_req57159428&p_name=".$event->name."&product_id=".$event->product_id."&datefrom=".$event->datefrom."&dateto=".$event->dateto);
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
						
			$wpdb->query("delete from " . $wpdb->prefix . "itweb_events where id = " . $id);
            return ['status' => 200];
        }
    ]);
}

// delete table row
add_action('rest_api_init', 'deleteTableRow');
function deleteTableRow()
{
    register_rest_route('api', 'delete-table-row', [
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => function () {
            global $wpdb;
            $id = (int)$_GET['id'];
            $table = $_GET['table'];
            $wpdb->query("delete from " . $wpdb->prefix . "itweb_{$table} where id = " . $id);
            if($table == "brokers"){
				$wpdb->query("delete from " . $wpdb->prefix . "itweb_commissions where broker_id = " . $id);
				$wpdb->query("delete from " . $wpdb->prefix . "itweb_brokers_products where broker_id = " . $id);
			}
			
			if($table == "discounts"){
				$url = "https://airport-parking-germany.de/curl/?request=apm_del_discount&pw=apmds_req57159428&discount_id=".$id;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 0);
				// Receive server response ...
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
				$server_output = curl_exec($ch);
				curl_close($ch);
			}
			
			if($table == "product_groups"){
				$wpdb->query("delete from " . $wpdb->prefix . "itweb_product_groups where perent_id = " . $id);
			}
			
			return ['status' => 200];
        }
    ]);
}

// delete product gallery image
add_action('rest_api_init', 'deleteProductGalleryImage');
function deleteProductGalleryImage()
{
    register_rest_route('api', 'delete-product-gallery-image', [
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => function () {
            global $wpdb;
            $id = (int)$_GET['id'];
            wp_delete_post($id, true);
            $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id = $id");
//            wp_delete_attachment( $id, true );
            return ['status' => 200];
        }
    ]);
}

// delete order valet car image
add_action('rest_api_init', 'deleteValetCarImage');
function deleteValetCarImage()
{
    register_rest_route('api', 'delet-valet-car-image', [
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => function () {
            global $wpdb;
            $id = (int)$_GET['id'];
            wp_delete_post($id, true);
            $wpdb->query("DELETE FROM {$wpdb->prefix}itweb_valet_car_images WHERE id = $id");

			$url = '/www/htdocs/w01bcb66/airport-parking-management.de/wp-content/uploads/valet-car-images/' . $_GET['name'];
			//$path = parse_url($url, PHP_URL_PATH); // Remove "http://localhost"
			//$fullPath = get_home_path() . $path;
			unlink($url);
            return ['status' => 200];
        }
    ]);
}

// delete order valet car image
add_action('rest_api_init', 'deleteValetCarVideo');
function deleteValetCarVideo()
{
    register_rest_route('api', 'delet-valet-car-video', [
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => function () {
            global $wpdb;
            $id = (int)$_GET['id'];
            wp_delete_post($id, true);
            $wpdb->query("DELETE FROM {$wpdb->prefix}itweb_valet_car_videos WHERE id = $id");

			$url = '/www/htdocs/w01bcb66/airport-parking-management.de/wp-content/uploads/valet-car-videos/' . $_GET['name'];
			//$path = parse_url($url, PHP_URL_PATH); // Remove "http://localhost"
			//$fullPath = get_home_path() . $path;
			unlink($url);
            return ['status' => 200];
        }
    ]);
}

// update event
add_action('rest_api_init', 'updateEvent');
function updateEvent()
{
    register_rest_route('api', 'update-event', [
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => function () {
            global $wpdb;
            $id = (int)$_POST['id'];
            $res = $wpdb->query("SELECT * FROM `{$wpdb->prefix}itweb_events` WHERE ('" . $_POST['datefrom'] . "' BETWEEN datefrom AND dateto)");
            if ($res > 0) {
                return false;
            }
//            return "update ".$wpdb->prefix."itweb_events set datefrom = '".$_POST['datefrom']."', dateto = '".$_POST['dateto']."' where id = " . $id;
            $wpdb->query("update " . $wpdb->prefix . "itweb_events set datefrom = '" . $_POST['datefrom'] . "', dateto = '" . $_POST['dateto'] . "' where id = " . $id);
        }
    ]);
}

// delete product
add_action('rest_api_init', 'deleteProduct');
function deleteProduct()
{
    register_rest_route('api', 'delete-product', [
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => function () {
            global $wpdb;
            $db = Database::getInstance();
            $id = (int)$_GET['id'];

            deleteWCProduct($id, true);
            $db->deleteAdditionalServiceProductsByProductId($id);
            $db->deleteDiscountsByProductId($id);
            $db->deleteOrderCancellationsByProductId($id);
            $db->deleteEventsByProductId($id);
            $db->deleteParklotsByProductId($id);
            $db->deleteRestrictionsByProductId($id);
            return ['status' => 200];
        }
    ]);
}

// calculate order price
add_action('rest_api_init', 'calculateOrderPrice');
function calculateOrderPrice()
{
    register_rest_route('api', 'calc-order-price', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => function () {
            $productId = (int)$_GET['productId'];
            $dateFrom = dateFormat($_GET['dateFrom']);
            $dateTo = dateFormat($_GET['dateTo']);
            $price = Pricelist::calculate($productId, $dateFrom, $dateTo);
            $price = Discounts::checkDiscounts($productId, $price, $dateFrom, $dateTo);
            return [
                'price' => $price,
                'price_float' => to_float($price)
            ];
        }
    ]);
}

// date restriction
add_action('rest_api_init', 'dateRestriction');
function dateRestriction()
{
    register_rest_route('api', 'date-restriction', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => function () {
            $productId = (int)$_GET['productId'];
            $date = dateFormat($_GET['date']);

            $returnData = [
                'restriction' => Database::getInstance()->getDateRestriction($productId, $date)
            ];

            if(isset($_GET['timefrom'])){
                $parklot = new Parklot($productId);
                $returnData['order_lead_time'] = $parklot->canOrderLeadTime($_GET['datefrom'], $_GET['timefrom_value']);
            }

            return $returnData;
        }
    ]);
}

// check parklot availability
add_action('rest_api_init', 'parklotAvailability');
function parklotAvailability()
{
    register_rest_route('api', 'check-availibility', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => function () {
            $productId = (int)$_GET['productId'];
            $parklot = new Parklot($productId);
            $date = dateFormat($_GET['date']);
            $orders = $parklot->getUsedTimes($productId, $date);
            $tmpUsedTimes = [];
            $usedTimes = [];
            
            /**
             * 
             * store orders time_from - time_to on a temp array
             * 
             */
            foreach($orders as $order){
                $timefrom = date('H:i', strtotime($order->date_from));
                $timeto = date('H:i', strtotime($order->date_to));
                $tmpUsedTimes[] = getTimesFromRange($timefrom, $timeto);
            }

            /**
             * 
             * Combine all times in one array
             * 
             */
            foreach($tmpUsedTimes as $timeArr){
                foreach($timeArr as $time){
                    if(!in_array($time, $usedTimes)){
                        $usedTimes[] = $time;
                        if(!endsWith($time, '30')){
                            $usedTimes[] = substr($time, 0, strpos($time, ':')) . ':30';
                        }
                    }
                }
            }

            /**
             * 
             * remove last item from array to remove the extra ":30" item
             */
            array_pop($usedTimes);

            return [
                'used_times' => $usedTimes,
                'restriction' => Database::getInstance()->getDateRestriction($productId, $date)
            ];
        }
    ]);
}

// cancel order
add_action('rest_api_init', 'cancelOrder');
function cancelOrder()
{
    register_rest_route('api', 'cancel-order', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => function () {
            if (!isset($_GET['token']) || empty($_GET['token'])) {
                return ['error' => 'Bitte eingeben Buchungsnummer!'];
            }

            $token = $_GET['token'];
            $email = $_GET['email'];
            $reason = $_GET['reason'];
			$price = '';

            $data = array(
                'limit' => -1, // Query all orders
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_value' => $token,
                'meta_compare' => 'EXISTS', // The comparison argument
            );

            $data['meta_key'] ='token';
            $orders = wc_get_orders($data);
			if (count($orders) <= 0) {
                return ['message' => 'Keine Bestellungen gefunden'];
            }

			$order_id = $orders[0]->id;
			$items = $orders[0]->get_items();
			foreach ( $items as $item ) {		
				$product_id = $item->get_product_id();
				if($product_id == 41453 || $product_id == 41466 || $product_id == 41468 || $product_id == 41470 || $product_id == 41472 || $product_id == 41474 || 
				$product_id == 41577 || $product_id == 41581 || $product_id == 41584 || $product_id == 41582 || $product_id == 41585 || $product_id == 41580)
					return ['message' => 'Winter Spezial ist nicht stornierbar.'];
			}
			
			if(get_post_meta($order_id, 'Nicht_stornierbar', true) == 1)
				return ['message' => 'Buchung kann nicht storniert werden.'];
			
			$cancelOptions = Database::getInstance()->getOrderCancellationByProductId($product_id);
			
			date_default_timezone_set('Europe/Berlin');
			$anreise = strtotime($orders[0]->get_meta('Anreisedatum') . " " . $orders[0]->get_meta('Uhrzeit von'));			
			$cancelDate = strtotime(date('Y-m-d H:i'));
			
			if($orders[0]->get_meta('_payment_method_title') != 'Barzahlung'){
				if(count($cancelOptions) > 0){
					foreach ($cancelOptions as $cancelOption ) {		
						if((abs($cancelDate - $anreise)/(60*60)) < $cancelOption->hours_before){
							$order_total = $orders[0]->get_total();
							if($cancelOption->type == "fix")
								$price = number_format($order_total - $cancelOption->value,2,",",".");
							else{
								$price = number_format($order_total / 100 * $cancelOption->value,2,",",".");
							}
							break;
						}
						else{
							continue;
						}
					}
				}
				elseif((abs($cancelDate - $anreise)/(60*60)) < 24)
					return ['message' => 'Kurzfristige Stornierung nicht möglich.'];
			}

            
            foreach ($orders as $order) {
                if(!$order->update_status('cancelled')){					
                    return ['error' => 'Ihre Buchung konnte leider nicht storniert werden. Bitte kontaktieren sie den Support.'];
                }
				else{
					Orders::updateOrder([
						'order_status' => 'wc-cancelled'
					], ['order_id' => $order->id]);
				}
            }
			
			$url = "https://airport-parking-germany.de/search-result/";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,
			http_build_query(array(
				 'request' => 'apm_cancel',
				 'pw' => 'apmc_req57159428',
				 'token' => $token
				 
			)));
			// Receive server response ...
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$server_output = curl_exec($ch);
			curl_close($ch);
			
			if($product_id == 537 || $product_id == 592 || $product_id == 619 || $product_id == 873){
				if(get_post_meta($order_id, '_payment_method_title', true) == 'PayPal')
					add_post_meta($order_id, 'paypal_rerunded', 0, true);
			}

            if($reason != "" && $reason != null)
                $reason_c = "<p>Grund für Ihre Stornierung:<br>".$reason."</p>";
            else
                $reason_c = "";
			if($price != "" && $price != null){
				$toPay = "<p>Aufgrund der kurzfristigen Stornierung entstehen Stornogebühren in Höne von ". $price . "€. Restbetrag wird erstattet.</p>";
				$msg = "Aufgrund der kurzfristigen Stornierung entstehen Stornogebühren in Höne von ". $price . "€. Restbetrag wird erstattet.";
				add_post_meta($order_id, 'cancel_fee', $price, true);
			}				
			else{
				$toPay = "";
				$msg = "";
			}
            $to = $email;
            $subject = 'Ihre Buchung ' . $token . ' wurde storniert';
            $body = "<h3>Ihr Parkplatz wurde storniert.</h3>
						<p>Sehr geehrte Damen und Herren,</p>
						<p>Ihre Buchung mit der Buchungsnummer <strong>".$token."</strong> wurde storniert.<br/></p>"
                .$reason_c.
				$toPay.
                "<p>Sie haben noch Fragen? Schreiben Sie uns einfach eine E-Mail an
							<a href='mailto:info@airport-parking-stuttgart.de'>info@airport-parking-stuttgart.de</a> oder rufen Sie uns
							unter <a href='tel:+49 711 22 051 247'>+49 711 22 051 247</a> an.
						</p>
						<p>Montag bis Freitag von 11:00 bis 19:00 Uhr.
					    Aus dem dt. Festnetz zum Ortstarif. Mobilfunkkosten abweichend.</p>
						<p>Mit freundlichen Grüßen</p>
						<p>APS-Airport-Parking-Stuttgart GmbH<br>
						Raiffeisenstraße 18, 70794 Filderstadt, Deutschland<br></p>
						<p><a href='www.airport-parking-stuttgart.de'>www.airport-parking-stuttgart.de</a></p>			
					";

            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail([$to, 'noreply@airport-parking-stuttgart.de'], $subject, $body, $headers);
            return ['message' => 'Ihre Buchung wurde so eben erfolgreich Storniert. ' . $msg];
        }
    ]);
}