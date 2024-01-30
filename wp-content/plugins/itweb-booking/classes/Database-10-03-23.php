<?php

class Database
{
    private static $db;
    private static $prefix;
	private static $prefix_DB;
    private static $instance;

    public function __construct()
    {
        global $wpdb;
        self::$db = $wpdb;
        self::$prefix = $wpdb->prefix . 'itweb_';
		self::$prefix_DB = $wpdb->prefix;
    }
    
    function mydd($array, $name = 'data')
	{
		echo "<div style='border: 2px solid black; position: relative; margin: 50px; padding: 50px; background-color: #ddd;'";
		highlight_string("<?php\n$$name =\n" . var_export($array, true) . ";\n?>");
		echo "</div>";
	}


    static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    static function getProducts($categories = false)
    {
        $args = array(
            'orderby' => 'name',
			'order' => 'ASC',
			'limit' => -1
        );
        if ($categories) {
            $args['category'] = $categories;
        }
        return wc_get_products($args);
    }
	
    static function saveOrder($data)
    {
        $parklot = new Parklot($data['product']);
        if (!$parklot->canOrderLeadTime($data['date_from'], $data['time_from'])) {
            $_SESSION['errors']['all'][] = [
                'Cannot create order for this product!'
            ];
        }

        // check for order contigent
        $dateTimeFrom = date('Y-m-d H:i', strtotime($data['date_from'] . ' ' . $data['time_from']));
        $dateTimeTo = date('Y-m-d H:i', strtotime($data['date_to'] . ' ' . $data['time_to']));
        $orders = Orders::getOrdersByProductId($data['product'], $dateTimeFrom, $dateTimeTo);
        if (count($orders) >= $parklot->getContigent()) {
            $_SESSION['errors']['all'][] = ['This date is not free!'];
        }
        if (isset($_SESSION['errors']['all'])) {
            return;
        }


        $product = wc_get_product($data['product']);
        // get price from pricelist
        $priceList = Pricelist::calculateAndDiscount($product->get_id(), dateFormat($data["date_from"]), dateFormat($data["date_to"]));

//        $discountPrice = Discounts::checkDiscounts($product->get_id(), $priceList, dateFormat($data['date_from']), dateFormat($data['date_to']));

        // get price from product additional services
        $additionalServicesPrice = AdditionalServices::calculateProductPriceFromIds($data['add_ser_id']);
        // set new product price
        //$product->set_price($priceList + $additionalServicesPrice);
		$product->set_price($priceList);


        $order = Orders::createWCOrder($product);
        $order_id = $order->get_id();
		$productDate = self::getParklotByProductId($data['product']);
		
        // add a bunch of meta data
        add_post_meta($order_id, '_transaction_id', 'barzahlung', true);
        add_post_meta($order_id, '_payment_method_title', 'Barzahlung', true);
        add_post_meta($order_id, '_order_total', '', true);
        add_post_meta($order_id, '_customer_user', get_current_user_id(), true);
        add_post_meta($order_id, '_completed_date', '', true);
        add_post_meta($order_id, '_order_currency', '', true);
        add_post_meta($order_id, '_paid_date', '', true);

        add_post_meta($order_id, '_billing_address_1', $data["anschrift"], true);
        add_post_meta($order_id, '_billing_city', $data["ort"], true);
        add_post_meta($order_id, '_billing_state', $data["customer_state"], true);
        add_post_meta($order_id, '_billing_postcode', $data["postleitzahl"], true);
        add_post_meta($order_id, '_billing_company', $data["firmenname"], true);
        add_post_meta($order_id, '_billing_country', $data["customer_country"], true);
        add_post_meta($order_id, '_billing_email', $data["email"], true);
        add_post_meta($order_id, '_billing_first_name', $data["vorname"], true);
        add_post_meta($order_id, '_billing_last_name', $data["nachname"], true);
        add_post_meta($order_id, '_billing_phone', $data["telefonnummer"], true);

        add_post_meta($order_id, 'Anreisedatum', dateFormat($data["date_from"]), true);
        add_post_meta($order_id, 'first_anreisedatum', dateFormat($data["date_from"]), true);
        add_post_meta($order_id, 'Abreisedatum', dateFormat($data["date_to"]), true);
        add_post_meta($order_id, 'first_abreisedatum', dateFormat($data["date_to"]), true);
        add_post_meta($order_id, 'Uhrzeit von', $data["time_from"], true);
        add_post_meta($order_id, 'Uhrzeit bis', $data["time_to"], true);
        add_post_meta($order_id, 'Hinflugnummer', $data["hinflugnummer"], true);
        add_post_meta($order_id, 'Rückflugnummer', $data["ruckflugnummer"], true);
        add_post_meta($order_id, 'Personenanzahl', $data["personenanzahl"] ? $data["personenanzahl"] : 0, true);
        if($product->type == 'valet'){		
			add_post_meta($order_id, 'Fahrzeughersteller', $data["model"], true);
			add_post_meta($order_id, 'Fahrzeugmodell', $data["type"], true);
			add_post_meta($order_id, 'Fahrzeugfarbe', $data["color"], true);
			add_post_meta($order_id, 'Kilometerstand', '', true);
			add_post_meta($order_id, 'Tankstand', '', true);
			add_post_meta($order_id, 'Merkmale', '', true);
			add_post_meta($order_id, 'Annahmedatum MA', '', true);
			add_post_meta($order_id, 'Annahmezeit MA', '', true);
			add_post_meta($order_id, 'Annahme MA', '', true);
			add_post_meta($order_id, 'Übergabedatum K', '', true);
			add_post_meta($order_id, 'Übergabezeit K', '', true);
			add_post_meta($order_id, 'Übergabe K', '', true);
			add_post_meta($order_id, 'Übergabedatum Ende', '', true);
			add_post_meta($order_id, 'Unterschrift MA', '', true);
			add_post_meta($order_id, 'Unterschrift K', '', true);
        }
        add_post_meta($order_id, 'Kennzeichen', $data["kennzeichen"], true);
		add_post_meta($order_id, 'Anrede', $data["anrede"], true);


        update_post_meta($order_id, 'token', $productDate->prefix . '-' . generateToken(5));

        self::$db->insert(self::$prefix . 'orders', [
            'date_from' => date('Y-m-d H:i', strtotime($data["date_from"] . ' ' . $data["time_from"])),
            'date_to' => date('Y-m-d H:i', strtotime($data["date_to"] . ' ' . $data["time_to"])),
            'product_id' => $data['product'],
            'order_id' => $order_id,
			'b_total' => ($priceList + $additionalServicesPrice),
            'out_flight_number' => $data['hinflugnummer'],
            'return_flight_number' => $data['ruckflugnummer'],
            'nr_people' => $data["personenanzahl"] ? $data["personenanzahl"] : 0
        ]);
		
		// Save productAdditionalServices to booking metaphone
		foreach($data['add_ser_id'] as $service){
			if($service != ""){
				self::saveBookingMeta($order_id, 'additional_services', $service);
			}
		}
		// Save commission to booking metaphone
		$provision = self::getBrokerCommissionForBooking($data['product'], date('Y-m-d', strtotime($data["date_from"])));
		if($provision->commission_type != "" || $provision->commission_type != null){
			if($provision->commission_type == 'netto'){
				$netto = number_format((float)(($priceList + $additionalServicesPrice) / 119 * 100), 2, '.', '');
				$amount = number_format((float)($netto / 100 * $provision->commission_value), 2, '.', '');
				self::saveBookingMeta($order_id, 'provision', $amount);
			}
			elseif($provision->commission_type == 'brutto'){
				$brutto = number_format((float)($priceList + $additionalServicesPrice), 2, '.', '');
				$amount = number_format((float)($brutto / 100 * $provision->commission_value), 2, '.', '');
				self::saveBookingMeta($order_id, 'provision', $amount);
			}
		}
		else{
			self::saveBookingMeta($order_id, 'provision', '0.00');
		}
		
		if($data["send_mail"] == 'mail'){
			$mailer = WC()->mailer();
			$mails = $mailer->get_emails();
			if ( ! empty( $mails ) ) {                
				foreach ( $mails as $mail ) {
					if ( $mail->id == 'customer_processing_order' /*|| $mail->id == 'customer_on_hold_order' */ ){
						$mail->trigger( $order_id ); 
						break;
					}
				}            
			}
		}
    }

    static function updateOrder($order, $data)
    {
        $order_id = $order;
        $order = wc_get_order($order_id);
        if($data['price'] == "" || $data['price'] == null)
            return false;

        $product = Database::getInstance()->getParklotByProductId(count($order->get_items()) > 0 ? array_values($order->get_items())[0]->get_product_id() : '');

        // Set new price
        $order_items = $order->get_items();
        foreach ( $order_items as $key => $value ) {
            $order_item_id = $key;
            $product_value = $value->get_data();
            $product_id    = $product_value['product_id'];
        }
        $product = wc_get_product( $product_id );
        $price = $data['price'] / 119 * 100;
        $order_items[ $order_item_id ]->set_total( $price );
        $order->calculate_taxes();
        $order->calculate_totals();
        $order->save();
		
		if(get_post_meta($order_id, '_payment_method_title')[0] == 'Barzahlung'){
			$barzahlung = $data['price'];
			$mv = 0;
		}			
		else{
			$barzahlung = 0;
			$mv = $data['price'];
		}
		
		if(get_post_meta($order_id->post_id, 'Anreisedatum', true) < date('Y-m-d')){
			$d1 = strtotime(get_post_meta($order_id->post_id, 'Abreisedatum', true));
			$d2 = strtotime(get_post_meta($order_id->post_id, 'Anreisedatum', true));
			$orig_days = round(($d1 - $d2) / 86400);
			
			$d3 = strtotime($data["dateTo"]);
			$d4 = strtotime($data["dateFrom"]);				
			$update_days = round(($d3 - $d4) / 86400);
			
			if($update_days > $orig_days){
				$comp_days = $update_days - $orig_days;
				$field = "+". $comp_days . "TAG " . ($comp_days * 10) . " €";				
				update_post_meta($order_id->post_id, 'Sonstige 2', $field);
			}
			else
				update_post_meta($order_id->post_id, 'Sonstige 2', '');			
		}

        // add a bunch of meta data
		if($data["first_name"])
			update_post_meta($order_id, '_billing_first_name', $data["first_name"]);
        if($data["last_name"])
			update_post_meta($order_id, '_billing_last_name', $data["last_name"]);
		if($data["phone_number"])
			update_post_meta($order_id, '_billing_phone', $data["phone_number"]);
        if($data["mail_adress"])
			update_post_meta($order_id, '_billing_email', $data["mail_adress"]);
        if($data["dateFrom"])
			update_post_meta($order_id, 'Anreisedatum', dateFormat($data["dateFrom"]));
        //update_post_meta($order_id, 'first_anreisedatum', dateFormat($data["dateFrom"]));
        if($data["dateTo"])
			update_post_meta($order_id, 'Abreisedatum', dateFormat($data["dateTo"]));
        //update_post_meta($order_id, 'first_abreisedatum', dateFormat($data["dateTo"]));
        if($data["timeFrom"])
			update_post_meta($order_id, 'Uhrzeit von', $data["timeFrom"]);
        if($data["timeTo"])
			update_post_meta($order_id, 'Uhrzeit bis', $data["timeTo"]);
        if($data["flightTo"])
			update_post_meta($order_id, 'Hinflugnummer', $data["flightTo"]);
        if($data["flightFrom"])
			update_post_meta($order_id, 'Rückflugnummer', $data["flightFrom"]);
        if($data["person"])
            update_post_meta($order_id, 'Personenanzahl', $data["person"]);
        if($data["plate"])
			update_post_meta($order_id, 'Kennzeichen', $data["plate"]);
        if($data["anrede"])
			update_post_meta($order_id, 'Anrede', $data["anrede"]);
		if($data["model"])
			update_post_meta($order_id, 'Fahrzeughersteller', $data["model"]);
		if($data["type"])
			update_post_meta($order_id, 'Fahrzeugmodell', $data["type"]);
		if($data["color"])
			update_post_meta($order_id, 'Fahrzeugfarbe', $data["color"]);
		if($data["kilometerstand"])
			update_post_meta($order_id, 'Kilometerstand', $data["kilometerstand"]);
		if($data["tankstand"])
			update_post_meta($order_id, 'Tankstand', $data["tankstand"]);
		if($data["merkmale"])
			update_post_meta($order_id, 'Merkmale', $data["merkmale"]);
		if($data["a-m-date"])
			update_post_meta($order_id, 'Annahmedatum MA', $data["a-m-date"]);
		if($data["a-m-time"])
			update_post_meta($order_id, 'Annahmezeit MA', $data["a-m-time"]);
		if($data["a-m-user"])
			update_post_meta($order_id, 'Annahme MA', $data["a-m-user"]);
		if($data["u-kunde-date"])
			update_post_meta($order_id, 'Übergabedatum K', $data["u-kunde-date"]);
		if($data["u-kunde-time"])
			update_post_meta($order_id, 'Übergabezeit K', $data["u-kunde-time"]);
		if($data["u-kunde"])
			update_post_meta($order_id, 'Übergabe K', $data["u-kunde"]);
		if($data["u-date"])
			update_post_meta($order_id, 'Übergabedatum Ende', $data["u-date"]);
		if($data["u-unterschrift-ma"])
			update_post_meta($order_id, 'Unterschrift MA', $data["u-unterschrift-ma"]);
		if($data["u-unterschrift-k"])
			update_post_meta($order_id, 'Unterschrift K', $data["u-unterschrift-k"]);

        if(self::$db->get_row("select * from " . self::$prefix . "hotel_transfers where order_id = $order_id")){
            self::$db->update(self::$prefix . 'hotel_transfers', [
                'datefrom' => dateFormat($data["dateFrom"]),
                'dateto' => dateFormat($data["dateTo"]),
                'transfer_vom_hotel' => $data["timeFrom"],
                'ankunftszeit_ruckflug' => $data["timeTo"],
                'hinflug_nummer' => $data['flightTo'],
                'ruckflug_nummer' => $data['flightFrom'],
            ], ['order_id' => $order_id]);
        }else{
            self::$db->update(self::$prefix . 'orders', [
                'date_from' => date('Y-m-d H:i', strtotime($data["dateFrom"] . ' ' . $data["timeFrom"])),
                'date_to' => date('Y-m-d H:i', strtotime($data["dateTo"] . ' ' . $data["timeTo"])),
				'b_total' => $barzahlung,
				'm_v_total' => $mv,
                'out_flight_number' => $data['flightTo'],
                'return_flight_number' => $data['flightFrom'],
                'nr_people' => $data['person']
            ], ['order_id' => $order_id]);			
        }
		
		self::$db->update(self::$prefix_DB . 'wc_order_product_lookup', [
                'product_net_revenue' => $price,
                'product_gross_revenue' => $data['price'],
                'tax_amount' => $data['price'] / 119 * 19
            ], ['order_id' => $order_id]);

        // Send Mail if selected
        if(isset($data["send_mail"])){
            $mailer = WC()->mailer();
            $mails = $mailer->get_emails();
            if ( ! empty( $mails ) ) {
                foreach ( $mails as $mail ) {
                    if ( $mail->id == 'customer_processing_order' ){
                        $mail->trigger( $order_id );
                    }
                }
            }
            WC()->mailer()->get_emails()['WC_Email_New_Order']->trigger( $order_id );
        }
    }
	
	// Save valet car images names
	static function saveValetCarImage($order_id, $data)
    {
		//self::$db->delete(self::$prefix . 'valet_car_images', ['order_id' => $order_id]);
		foreach($data as $image_name){
			if($image_name != null){
				self::$db->insert(self::$prefix . 'valet_car_images', [
					'order_id' => $order_id,
					'image_file' => $image_name
				]);
			}
		}
	}
	
	// Save valet car video names
	static function saveValetCarVideo($order_id, $data)
    {
		//self::$db->delete(self::$prefix . 'valet_car_images', ['order_id' => $order_id]);
		foreach($data as $video_name){
			if($video_name != null){
				self::$db->insert(self::$prefix . 'valet_car_videos', [
					'order_id' => $order_id,
					'video_file' => $video_name
				]);
			}
		}
	}
	
	// Get valet car images names
    static function getValetCarImage($order_id)
    {
        return self::$db->get_results("select * 
            from " . self::$prefix . "valet_car_images
            where order_id = $order_id");
    }
	// Get valet car videos names
    static function getValetCarVideos($order_id)
    {
        return self::$db->get_results("select * 
            from " . self::$prefix . "valet_car_videos
            where order_id = $order_id");
    }

    static function updateList($order_id, $data)
    {	
	
        foreach ($data as $key => $value) {
            $orig_val = get_post_meta($order_id, $key, true) != null ? get_post_meta($order_id, $key, true) : "";
            if($orig_val != $value){
				self::addEditBookingLog($order_id, 'Liste: ' . $key, get_post_meta($order_id, $key, true) . " -> " . $value);
			}
			
			update_post_meta($order_id, $key, $value, false);
        }
		
        $order = wc_get_order($order_id);
        $order->update_status($data['Status']);
        if (isset($data['Betrag'])) {
            $order->set_total((double)$data['Betrag']);
			update_post_meta($order_id, '_order_tax', (double)$data['Betrag']/119 * 19, true);
        }
        $order->save();

        //$product = array_values($order->get_items())[0];
        //$product->set_name($data['Parkplatz']);
        //$product->save();
		self::checkBookingDate($order_id);
		
		if (isset($data['Betrag'])) {
			self::$db->update(self::$prefix_DB . 'wc_order_product_lookup', [
				'product_net_revenue' => $data['Betrag'] / 119 * 100,
				'product_gross_revenue' => $data['Betrag'],
				'tax_amount' => $data['Betrag'] / 119 * 19
			], ['order_id' => $order_id]);
			
			if(get_post_meta($order_id, '_payment_method_title')[0] == 'Barzahlung'){
				$barzahlung = $data['Betrag'];
				$mv = 0;
			}			
			else{
				$barzahlung = 0;
				$mv = $data['Betrag'];
			}

			self::$db->update(self::$prefix . 'orders', [
				'b_total' => $barzahlung,
				'm_v_total' => $mv,
            ], ['order_id' => $order_id]);	
			
		}
		
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

    static function saveClient($client, $location, $tax_number, $contact, $tel, $email, $address, $inv_date)
    {
        self::$db->insert(self::$prefix . 'clients', [
            'client' => $client,
            'location_id' => $location,
            'tax_number' => $tax_number,
            'contact' => $contact,
            'tel' => $tel,
            'address' => $address,
            'inv_date' => $inv_date,
            'email' => $email
        ]);
		return self::$db->insert_id;
    }

    static function updateClient($client, $location, $tax_number, $contact, $tel, $email, $address, $inv_date, $condition)
    {
        return self::$db->update(self::$prefix . 'clients', [
            'client' => $client,
            'location_id' => $location,
            'tax_number' => $tax_number,
            'contact' => $contact,
            'tel' => $tel,
            'address' => $address,
            'inv_date' => $inv_date,
            'email' => $email
        ], $condition);
    }
	
    // save client products
    static function saveClientProducts($clientId, $product_ids)
    {
        foreach ($product_ids as $product_id) {
            self::$db->insert(self::$prefix . 'clients_products', [
                'client_id' => $clientId,
                'product_id' => $product_id
            ]);
        }
    }

	static function getClient($id){
        return self::$db->get_row("select c.*, l.location from " . self::$prefix . "clients c inner join " . self::$prefix . "locations l on c.location_id = l.id where c.id = $id");
    }

    // delete client
    static function deleteClient($id){
        return self::$db->delete(self::$prefix . 'clients', ['id' => $id]);
    }
	
    // delete client product link
    static function deleteClientPrudictLink($id){
        return self::$db->delete(self::$prefix . 'clients_products', ['client_id' => $id]);
    }

    static function getLocations()
    {
        return self::$db->get_results("select * from " . self::$prefix . "locations");
    }
	
	// get locations by name
    static function getLocationByName($location)
    {
		return self::$db->get_row("select * from " . self::$prefix . "locations", ['location' => $location]);
    }
	
	// add locations
    static function addLocation($location)
    {
		return self::$db->insert(self::$prefix . 'locations', [
            'location' => $location
        ]);
    }

    static function getAllClients()
    {
        return self::$db->get_results("select c.*, l.location from " . self::$prefix . "clients c inner join " . self::$prefix . "locations l on c.location_id = l.id order by c.id desc");
    }
	
	// get products for clients
	static function getClientLots()
    {
        return self::$db->get_results("select * from " . self::$prefix . "parklots where is_for = 'betreiber'");
    }
	
	// get products for clients
	static function getAllLots()
    {
        return self::$db->get_results("select * from " . self::$prefix . "parklots order by order_lot");
    }
	
    // get client products
    static function getClientProducts($clientId)
    {
        return self::$db->get_results("select * from " . self::$prefix . "clients_products where client_id = $clientId");
    }
	
    // update client products
    static function updateClientProducts($clientId, $product_ids)
    {
        self::$db->delete(self::$prefix . 'clients_products', ['client_id' => $clientId]);
        self::saveClientProducts($clientId, $product_ids);
    }

    static function deletePrice($id)
    {
        return self::$db->delete(self::$prefix . 'prices', ['id' => $id]);
    }

    static function getPrices()
    {
        return self::$db->get_results("select * from " . self::$prefix . "prices order by name");
    }

    static function deleteUnsavedEvents()
    {
        return self::$db->delete(self::$prefix . 'events', ['product_id' => 0]);
    }

    static function updateEventsProductId($productId)
    {
        return self::$db->update(self::$prefix . 'events', [
            'product_id' => $productId
        ], ['product_id' => 0]);
    }
	
    static function addEventsFast($date, $productId, $eventId)
    {
		$data = self::$db->get_row("select datefrom 
            from " . self::$prefix . "events
            where date(datefrom) = '$date' and product_id = $productId");
			
		if($data->datefrom == null){
			return self::$db->insert(self::$prefix . 'events', [
				'datefrom' => $date,
				'dateto' => $date,
				'price_id' => $eventId,
				'product_id' => $productId
			]);
		}
		else{
			return self::$db->update(self::$prefix . 'events', [
				'datefrom' => $date,
				'dateto' => $date,
				'price_id' => $eventId,
				'product_id' => $productId
			], ['datefrom' => $date, 'product_id' => $productId]);
		}
    }

    // save additional service
    static function saveAdditionalService($name, $description, $price)
    {
        return self::$db->insert(self::$prefix . 'additional_services', [
            'name' => $name,
			'description' => $description,
            'price' => (double)$price
        ]);
    }

    // update product additional services
    static function updateProductAdditionalServices($add_ser_ids, $product_id)
    {
        self::deleteAdditionalServiceProductsByProductId($product_id);
        foreach ($add_ser_ids as $id) {
            if ((int)$id > 0) {
                self::saveAddSerProductId($id, $product_id);
            }
        }
    }

    // get product additional services
    static function getProductAdditionalServices($product_id)
    {
        return self::$db->get_results("select ad.* 
            from " . self::$prefix . "additional_services_products adp, " . self::$prefix . "additional_services ad
            where adp.add_ser_id = ad.id and adp.product_id = $product_id");
    }

    // get product_additional_services by product id
    static function getProductAdditionalServicesByProductId($id)
    {
        return self::$db->get_results("select * from " . self::$prefix . "additional_services_products where product_id = $id");
    }

    // get additional service
    static function getAdditionalService($id)
    {
        return self::$db->get_row("select * from " . self::$prefix . "additional_services where id = $id");
    }

    // get all additional services
    static function getAdditionalServices()
    {
        return self::$db->get_results("select * from " . self::$prefix . "additional_services order by id desc");
    }

    // update additional service
    static function updateAdditionalService($id, $name, $description, $price)
    {
        return self::$db->update(self::$prefix . "additional_services", [
            'name' => $name,
			'description' => $description,
            'price' => $price
        ], ['id' => $id]);
    }

    static function deleteAdditionalService($id)
    {
        return self::$db->delete(self::$prefix . 'additional_services', ['id' => $id]);
    }

    static function deleteAdditionalServiceProductsByProductId($productId)
    {
        return self::$db->delete(self::$prefix . 'additional_services_products', ['product_id' => $productId]);
    }

    static function saveAddSerProductId($addSerId, $productId)
    {
        return self::$db->insert(self::$prefix . "additional_services_products",
            [
                'add_ser_id' => $addSerId,
                'product_id' => $productId
            ]);
    }

    static function saveParklot($parkhaus, $parklot, $type, $datefrom, $dateto, $lead_time, $contigent, $product_id, $location, $isFor, $adress, $phone, $prefix, $color, $short, $distance, $extraPrice_perDay, $commision, $commision_ws, $confirmation_byArrival, $confirmation_byDeparture, $confirmation_note)
    {
        return self::$db->insert(self::$prefix . 'parklots', [
            'parkhaus' => $parkhaus,
            'parklot' => $parklot,
			'parklot_short' => $short,
            'type' => $type,
            'datefrom' => $datefrom,
            'dateto' => $dateto,
            'booking_lead_time' => $lead_time,
			'is_for' => $isFor,
            'contigent' => $contigent,
            'product_id' => $product_id,
            'location_id' => $location,
			'adress' => $adress,
			'phone' => $phone,
			'prefix' => $prefix,
			'color' => $color,
			'distance' => $distance,
			'extraPrice_perDay' => $extraPrice_perDay,
			'commision' => $commision,
			'commision_ws' => $commision_ws,
			'confirmation_byArrival' => $confirmation_byArrival,
			'confirmation_byDeparture' => $confirmation_byDeparture,
			'confirmation_note' => $confirmation_note
			
        ]);
    }

    static function updateParklot($parkhaus, $parklot, $type, $datefrom, $dateto, $lead_time, $contigent, $product_id, $location, $isFor, $adress, $phone, $prefix, $color, $short, $distance, $extraPrice_perDay, $commision, $commision_ws, $confirmation_byArrival, $confirmation_byDeparture, $confirmation_note, $condition)
    {
		if(!empty($datefrom))
			$datefrom = date('Y-m-d H:i', strtotime($datefrom . "00:00"));
		else
			$datefrom = '2021-07-01 00:00';
		
		if(!empty($dateto))
			$dateto = date('Y-m-d H:i', strtotime($dateto . "00:00"));
		else
			$dateto = '2022-12-30 00:00';
		
		if($product_id == 595 || $product_id == 3080 || $product_id == 3081 || $product_id == 3082 || $product_id == 24224 || $product_id == 24228){
			$url = "https://airport-parking-germany.de/search-result/";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,
			http_build_query(array(
				 'request' => 'apm_con',
				 'pw' => 'apmc_req57159428',
				 'plot' => $product_id,
				 'con' => $contigent
				 
			)));
			// Receive server response ...
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$server_output = curl_exec($ch);
			curl_close($ch);
		}
		
        return self::$db->update(self::$prefix . 'parklots', [
            'parkhaus' => $parkhaus,
            'parklot' => $parklot,
			'parklot_short' => $short,
            'type' => $type,
            //'datefrom' => $datefrom,
            //'dateto' => $dateto,
            'booking_lead_time' => $lead_time,
			'is_for' => $isFor,
            'contigent' => $contigent,
            'product_id' => $product_id,
            'location_id' => $location,
			'adress' => $adress,
			'phone' => $phone,
			'prefix' => $prefix,
			'color' => $color,
			'distance' => $distance,
			'extraPrice_perDay' => $extraPrice_perDay,
			'commision' => $commision,
			'commision_ws' => $commision_ws,
			'confirmation_byArrival' => $confirmation_byArrival,
			'confirmation_byDeparture' => $confirmation_byDeparture,
			'confirmation_note' => $confirmation_note
        ], $condition);
    }

    // save restriction
    static function saveRestriction($darum, $date, $time, $product_id)
    {
        return self::$db->insert(self::$prefix . 'restrictions', [
            'darum' => $darum,
            'date' => $date,
            'time' => $time,
            'product_id' => $product_id
        ]);
    }

    // update restriction
    static function updateRestriction($darum, $date, $time, $product_id, $condition)
    {
        return self::$db->update(self::$prefix . 'restrictions', [
            'darum' => $darum,
            'date' => $date,
            'time' => $time,
            'product_id' => $product_id
        ], $condition);
    }

    // save discount
    static function saveDiscount($name, $interval_from, $interval_to, $type, $value, $days_before, $discount_contigent, $cancel, $message, $product_id)
    {
        return self::$db->insert(self::$prefix . 'discounts', [
            'name' => $name,
            'interval_from' => $interval_from,
            'interval_to' => $interval_to,
            'type' => $type,
            'value' => $value,
            'days_before' => $days_before,
            'discount_contigent' => $discount_contigent,
			'cancel' => $cancel,
            'message' => $message,
            'product_id' => $product_id
        ]);
    }

    // update discount
    static function updateDiscount($name, $interval_from, $interval_to, $type, $value, $days_before, $discount_contigent, $cancel, $message, $product_id, $condition)
    {
        return self::$db->update(self::$prefix . 'discounts', [
            'name' => $name,
            'interval_from' => $interval_from,
            'interval_to' => $interval_to,
            'type' => $type,
            'value' => $value,
            'days_before' => $days_before,
            'discount_contigent' => $discount_contigent,
			'cancel' => $cancel,
            'message' => $message,
            'product_id' => $product_id
        ], $condition);
    }

    // get order cancellation by product id
    static function getOrderCancellationByProductId($id)
    {
        return self::$db->get_results("select * from " . self::$prefix . "order_cancellations where product_id = $id");
    }

    // save new order cancellation
    static function saveOrderCancellation($hours, $type, $value, $product_id)
    {
        return self::$db->insert(self::$prefix . 'order_cancellations', [
            'hours_before' => $hours,
            'type' => $type,
            'value' => $value,
            'product_id' => $product_id
        ]);
    }

    // update order cancellation
    static function updateOrderCancellation($hours, $type, $value, $product_id, $condition)
    {
        return self::$db->update(self::$prefix . 'order_cancellations', [
            'hours_before' => $hours,
            'type' => $type,
            'value' => $value,
            'product_id' => $product_id
        ], $condition);
    }

    // get discounts by product id
    static function getDiscountsByProductId($id)
    {
        return self::$db->get_results("select * from " . self::$prefix . "discounts where product_id = $id");
    }

    // delete discounts by product id
    static function deleteDiscountsByProductId($id)
    {
        return self::$db->delete(self::$prefix . 'discounts', ['product_id' => $id]);
    }

    // get order cancellations by product id
    static function getOrderCancellationsByProductId($id)
    {
        return self::$db->get_row("select * from " . self::$prefix . "order_cancellations where product_id = $id");
    }

    // delete order cancellations by product id
    static function deleteOrderCancellationsByProductId($id)
    {
        return self::$db->delete(self::$prefix . 'order_cancellations', ['product_id' => $id]);
    }

    static function deleteEventsByProductId($id)
    {
        return self::$db->delete(self::$prefix . 'events', ['product_id' => $id]);
    }

	// get parklot by product id
    static function getParklotByProductId($id)
    {
        return self::$db->get_row("select p.*, l.location from " . self::$prefix . "parklots p inner join " . self::$prefix . "locations l on p.location_id = l.id where product_id = $id");
    }
	
	// get parklot by order id
    static function getParklotByOrderId($id)
    {
        return self::$db->get_row("select p.* from " . self::$prefix . "parklots p inner join " . self::$prefix . "orders o on p.product_id = o.product_id where o.order_id = $id");
    }

    // delete parklot by product id
    static function deleteParklotsByProductId($id)
    {
        return self::$db->delete(self::$prefix . 'parklots', ['product_id' => $id]);
    }

    // get product restrictions by product id
    static function getRestrictionsByProductId($id)
    {
        return self::$db->get_results("select * from " . self::$prefix . "restrictions where product_id = $id");
    }

    // delete product restrictions by product id
    static function deleteRestrictionsByProductId($id)
    {
        return self::$db->delete(self::$prefix . 'restrictions', ['product_id' => $id]);
    }

    // get date restriction
    static function getDateRestriction($id, $date)
    {
        return self::$db->get_row("select * from " . self::$prefix . "restrictions
        where product_id = $id and date(date) = date('$date')");
    }

    // save broker
    static function saveBroker($company, $title, $firstname, $lastname, $street, $zip, $location)
    {
        self::$db->insert(self::$prefix . 'brokers', [
            'company' => $company,
            'title' => $title,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'street' => $street,
            'zip' => $zip,
            'location_id' => $location
        ]);

        return self::$db->insert_id;
    }

    // save broker products
    static function saveBrokerProducts($brokerId, $product_ids)
    {
        foreach ($product_ids as $product_id) {
            self::$db->insert(self::$prefix . 'brokers_products', [
                'broker_id' => $brokerId,
                'product_id' => $product_id
            ]);
        }
    }

    // update broker
    static function updateBroker($company, $title, $firstname, $lastname, $street, $zip, $location, $condition)
    {
        return self::$db->update(self::$prefix . 'brokers', [
            'company' => $company,
            'title' => $title,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'street' => $street,
            'zip' => $zip,
            'location_id' => $location
        ], $condition);
    }

    // update broker products
    static function updateBrokerProducts($brokerId, $product_ids)
    {
        self::$db->delete(self::$prefix . 'brokers_products', ['broker_id' => $brokerId]);
        self::saveBrokerProducts($brokerId, $product_ids);
    }

    // get broker products
    static function getBrokerProducts($brokerId)
    {
        return self::$db->get_results("select * from " . self::$prefix . "brokers_products where broker_id = $brokerId");
    }
	
	// get products for broker
	static function getBrokerLots()
    {
        return self::$db->get_results("select * from " . self::$prefix . "parklots where is_for = 'vermittler'");
    }
	
	// get products for broker by id
	static function getBrokerLotsById($brokerId)
    {
        return self::$db->get_results("SELECT * FROM " . self::$prefix . "parklots pl 
			LEFT JOIN " . self::$prefix . "brokers_products bp ON bp.product_id = pl.product_id
			WHERE bp.broker_id = $brokerId");
    }

    // get broker by id
    static function getBroker($id)
    {
        return self::$db->get_row("select b.*, l.location from " . self::$prefix . "brokers b inner join " . self::$prefix . "locations l on b.location_id = l.id where b.id = $id");
    }

    // get all brokers
    static function getBrokers()
    {
        return self::$db->get_results("select b.*, l.location from " . self::$prefix . "brokers b inner join " . self::$prefix . "locations l on b.location_id = l.id");
    }
	
	static function getClientByProductID($product_id){
        return self::$db->get_row("select c.* from " . self::$prefix . "clients c inner join " . self::$prefix . "clients_products cp on c.id = cp.client_id where cp.product_id = $product_id");
    }
	
	static function getBrokerByProductID($product_id){
        return self::$db->get_row("select b.*, l.location from " . self::$prefix . "brokers b 
				inner join " . self::$prefix . "brokers_products bp on b.id = bp.broker_id 
				inner join " . self::$prefix . "locations l on l.id = bp.location_id 
				where bp.product_id = $product_id");
    }
	
    // save broker commissions
    static function saveBrokerCommission($brokerId, $date_from, $date_to, $type, $value)
    {
           return self::$db->insert(self::$prefix . 'commissions', [
                'commission_date_from' => $date_from,
                'commission_date_to' => $date_to,
				'commission_type' => $type,
				'commission_value' => $value,
				'broker_id' => $brokerId
            ]);
    }
	
	// get commissions
    static function getBrokerCommissions($id)
    {
        return self::$db->get_results("select * from " . self::$prefix . "commissions where broker_id = $id");
    }
	
	// get commission for booking
    static function getBrokerCommissionForBooking($id, $date)
    {
        return self::$db->get_row("select c.* from " . self::$prefix . "commissions c inner join " . self::$prefix . "brokers_products bp on bp.broker_id = c.broker_id where bp.product_id = $id and '$date' between c.commission_date_from and c.commission_date_to");
    }
	
    // delete commissions
    static function deleteBrokerCommissions($id)
    {
        return self::$db->delete(self::$prefix . 'commissions', ['broker_id' => $id]);
    }
	
    // update commission
    static function updateBrokerCommission($brokerId, $date_from, $date_to, $type, $value, $condition)
    {
        return self::$db->update(self::$prefix . 'commissions', [
            'commission_date_from' => $date_from,
			'commission_date_to' => $date_to,
			'commission_type' => $type,
			'commission_value' => $value,
			'broker_id' => $brokerId
        ], $condition);
    }
		
		
	// get sales product by id
    static function getSalesProducts($month, $year, $product_id, $selected, $period, $status)
    {
		if($selected == "betreiber"){
			$joinTable = "LEFT JOIN " . self::$prefix . "clients_products cp ON cp.product_id = parklots.product_id";
			$andFor = "AND parklots.is_for = 'betreiber' AND cp.client_id = " . $product_id;
		}
		else{
			$joinTable = "INNER JOIN " . self::$prefix . "brokers_products bp ON bp.product_id = parklots.product_id";
			$andFor = "AND parklots.is_for = 'vermittler' AND bp.broker_id = " . $product_id;
		}
		if($period = "month")
			$andMonth = "AND MONTH(opl.post_date) ='" . $month . "'";
		else
			$andMonth = "";
		if($status == 'processing')
			$bStatus = "po.deleted = 0 AND opl.post_status = 'wc-processing'";
		elseif($status == 'cancelled')
			$bStatus = "(po.deleted = 0 OR po.deleted = 1) AND opl.post_status = 'wc-cancelled'";
		else
			$bStatus = "po.deleted = 0 AND opl.post_status = 'wc-processing'";
		
        return self::$db->get_results("
			SELECT DATE_FORMAT(opl.post_date,'%Y-%m-%d') AS Datum, DATE_FORMAT(opl.post_date,'%Y') AS Jahr, DATE_FORMAT(opl.post_date,'%c') AS Monat, 
			DATE_FORMAT(opl.post_date,'%e') AS Tag, 
			COUNT(parklots.parklot) AS Buchungen,
			SUM(ROUND(po.b_total, 2) ) AS Brutto_b, 
			SUM(ROUND(po.b_total / 119 * 100, 4) ) AS Netto_b,
			SUM(ROUND(po.m_v_total, 2) ) AS Brutto_k, 
			SUM(ROUND(po.m_v_total / 119 * 100, 4) ) AS Netto_k, 
			ROUND(SUM(om.meta_value ), 2) AS Provision,
			SUM(DATEDIFF(po.date_to, po.date_from)) AS Tage,
			parklots.parklot
			FROM " . self::$prefix_DB . "posts opl
			LEFT JOIN " . self::$prefix . "orders po ON opl.ID = po.order_id
			LEFT JOIN " . self::$prefix . "parklots parklots ON po.product_id = parklots.product_id
			" . $joinTable . "
			LEFT JOIN " . self::$prefix . "orders_meta om on om.order_id = po.order_id and om.meta_key = 'provision'
			WHERE ".$bStatus." AND YEAR(opl.post_date) ='" . $year . "' 
				" . $andMonth . " 
				" . $andFor . "
			GROUP BY Datum ORDER BY Datum ASC");
    }
	
	// get sales arrivals product by id
    static function getSalesArrivalsProducts($month, $year, $product_id, $selected, $period, $status)
    {
		if($selected == "betreiber"){
			$joinTable = "LEFT JOIN " . self::$prefix . "clients_products cp ON cp.product_id = parklots.product_id";
			$andFor = "AND parklots.is_for = 'betreiber' AND cp.client_id = " . $product_id;
		}
		else{
			$joinTable = "INNER JOIN " . self::$prefix . "brokers_products bp ON bp.product_id = parklots.product_id";
			$andFor = "AND parklots.is_for = 'vermittler' AND bp.broker_id = " . $product_id;
		}
		if($period = "month")
			$andMonth = "AND MONTH(po.date_from) ='" . $month . "'";
		else
			$andMonth = "";
		if($status == 'processing')
			$bStatus = "po.deleted = 0 AND opl.post_status = 'wc-processing'";
		elseif($status == 'cancelled')
			$bStatus = "(po.deleted = 0 OR po.deleted = 1) AND opl.post_status = 'wc-cancelled'";
		else
			$bStatus = "po.deleted = 0 AND opl.post_status = 'wc-processing'";
		
        return self::$db->get_results("
			SELECT DATE_FORMAT(po.date_from,'%Y-%m-%d') AS Datum, DATE_FORMAT(po.date_from,'%Y') AS Jahr, DATE_FORMAT(po.date_from,'%c') AS Monat, 
			DATE_FORMAT(po.date_from,'%e') AS Tag, 
			COUNT(parklots.parklot) AS Buchungen,
			SUM(ROUND(po.b_total, 2) ) AS Brutto_b, 
			SUM(ROUND(po.b_total / 119 * 100, 4) ) AS Netto_b,
			SUM(ROUND(po.m_v_total, 2) ) AS Brutto_k, 
			SUM(ROUND(po.m_v_total / 119 * 100, 4) ) AS Netto_k, 
			ROUND(SUM(om.meta_value ), 2) AS Provision,
			SUM(DATEDIFF(po.date_to, po.date_from)) AS Tage,
			parklots.parklot
			FROM " . self::$prefix_DB . "posts opl
			LEFT JOIN " . self::$prefix . "orders po ON opl.ID = po.order_id
			LEFT JOIN " . self::$prefix . "parklots parklots ON po.product_id = parklots.product_id
			" . $joinTable . "
			LEFT JOIN " . self::$prefix . "orders_meta om on om.order_id = po.order_id and om.meta_key = 'provision'
			WHERE ".$bStatus." AND YEAR(po.date_from) ='" . $year . "' 
				" . $andMonth . " 
				" . $andFor . "
			GROUP BY Datum ORDER BY Datum ASC");
    }
	
	
	// get all sales 
    static function getAllSales($month, $year, $selected, $period, $status)
    {	
		if($selected == "betreiber" || $selected == "vermittler")
			$andFor = "AND parklots.is_for = '" . $selected . "'";		
		
		elseif($selected == "ph")
			$andFor = "AND (parklots.product_id = '537' OR parklots.product_id = '595' OR parklots.product_id = '621' 
						OR parklots.product_id = '683' OR parklots.product_id = '592' OR parklots.product_id = '3080' OR parklots.product_id = '624'
						OR parklots.product_id = '41453' OR parklots.product_id = '41577' OR parklots.product_id = '41466' OR parklots.product_id = '41581')";
		elseif($selected == "php")
			$andFor = "AND (parklots.product_id = '537' OR parklots.product_id = '595' OR parklots.product_id = '621' OR parklots.product_id = '683' 
						OR parklots.product_id = '41453' OR parklots.product_id = '41577')";
		elseif($selected == "pho")
			$andFor = "AND (parklots.product_id = '592' OR parklots.product_id = '3080' OR parklots.product_id = '624' 
						OR parklots.product_id = '41466' OR parklots.product_id = '41581')";
		elseif($selected == "ppb")
			$andFor = "AND (parklots.product_id = '619' OR parklots.product_id = '3081' OR parklots.product_id = '24609'
						OR parklots.product_id = '41468' OR parklots.product_id = '41584')";
		elseif($selected == "pps")
			$andFor = "AND (parklots.product_id = '873' OR parklots.product_id = '3082' OR parklots.product_id = '901'
						OR parklots.product_id = '41470' OR parklots.product_id = '41582' OR parklots.product_id = '45856')";
		elseif($selected == "ost")
			$andFor = "AND (parklots.product_id = '24222' OR parklots.product_id = '24224' OR parklots.product_id = '24261' 
						OR parklots.product_id = '28881' OR parklots.product_id = '28878' OR parklots.product_id = '24226' 
						OR parklots.product_id = '24228' OR parklots.product_id = '24263' OR parklots.product_id = '41472' 
						OR parklots.product_id = '41580' OR parklots.product_id = '41474' OR parklots.product_id = '41585'
						OR parklots.product_id = '41402' OR parklots.product_id = '41403')";
		elseif($selected == "ostph")
			$andFor = "AND (parklots.product_id = '24222' OR parklots.product_id = '24224' OR parklots.product_id = '24261' OR parklots.product_id = '28881' OR parklots.product_id = '28878'
						OR parklots.product_id = '41472' OR parklots.product_id = '41580' OR parklots.product_id = '41402')";
		elseif($selected == "ostnu")
			$andFor = "AND (parklots.product_id = '24226' OR parklots.product_id = '24228' OR parklots.product_id = '24263'
						OR parklots.product_id = '41474' OR parklots.product_id = '41585' OR parklots.product_id = '41403')";
		else
			$andFor = "";
		if($period = "month")
			$andMonth = "AND MONTH(opl.post_date) ='" . $month . "'";
		else
			$andMonth = "";
		if($status == 'processing')
			$bStatus = "po.deleted = 0 AND (opl.post_status = 'wc-processing' OR opl.post_status = 'wc-on-hold')";
		elseif($status == 'cancelled')
			$bStatus = "(po.deleted = 0 OR po.deleted = 1) AND opl.post_status = 'wc-cancelled'";
		else
			$bStatus = "po.deleted = 0 AND opl.post_status = 'wc-processing' OR opl.post_status = 'wc-on-hold'";
		
        return self::$db->get_results("
			SELECT DATE_FORMAT(opl.post_date,'%Y-%m-%d') AS Datum, DATE_FORMAT(opl.post_date,'%Y') AS Jahr, DATE_FORMAT(opl.post_date,'%c') AS Monat, 
			DATE_FORMAT(opl.post_date,'%e') AS Tag, 
			COUNT(parklots.parklot) AS Buchungen,
			SUM(ROUND(po.b_total, 2) ) AS Brutto_b, 
			SUM(ROUND(po.b_total / 119 * 100, 4) ) AS Netto_b,
			SUM(ROUND(po.m_v_total, 2) ) AS Brutto_k, 
			SUM(ROUND(po.m_v_total / 119 * 100, 4) ) AS Netto_k, 
			SUM(ROUND(om.meta_value, 2) ) AS Provision,
			SUM(DATEDIFF(po.date_to, po.date_from)) AS Tage
			FROM " . self::$prefix_DB . "posts opl
			LEFT JOIN " . self::$prefix . "orders po ON opl.ID = po.order_id
			LEFT JOIN " . self::$prefix . "parklots parklots ON po.product_id = parklots.product_id
			LEFT JOIN " . self::$prefix . "orders_meta om on om.order_id = po.order_id and om.meta_key = 'provision'
			WHERE ".$bStatus." AND YEAR(opl.post_date) = '" . $year . "' 
			" . $andMonth . "
			" . $andFor . "
			GROUP BY Datum ORDER BY Datum ASC");
    }
	
	// get all sales arrivals
    static function getAllSalesArrivals($month, $year, $selected, $period, $status)
    {	
		if($selected == "betreiber" || $selected == "vermittler")
			$andFor = "AND parklots.is_for = '" . $selected . "'";		
		
		elseif($selected == "ph")
			$andFor = "AND (parklots.product_id = '537' OR parklots.product_id = '595' OR parklots.product_id = '621' 
						OR parklots.product_id = '683' OR parklots.product_id = '592' OR parklots.product_id = '3080' OR parklots.product_id = '624'
						OR parklots.product_id = '41453' OR parklots.product_id = '41577' OR parklots.product_id = '41466' OR parklots.product_id = '41581')";
		elseif($selected == "php")
			$andFor = "AND (parklots.product_id = '537' OR parklots.product_id = '595' OR parklots.product_id = '621' OR parklots.product_id = '683'
						OR parklots.product_id = '41453' OR parklots.product_id = '41577')";
		elseif($selected == "pho")
			$andFor = "AND (parklots.product_id = '592' OR parklots.product_id = '3080' OR parklots.product_id = '624'
						OR parklots.product_id = '41466' OR parklots.product_id = '41581')";
		elseif($selected == "ppb")
			$andFor = "AND (parklots.product_id = '619' OR parklots.product_id = '3081' OR parklots.product_id = '24609'
						OR parklots.product_id = '41468' OR parklots.product_id = '41584')";
		elseif($selected == "pps")
			$andFor = "AND (parklots.product_id = '873' OR parklots.product_id = '3082' OR parklots.product_id = '901'
						OR parklots.product_id = '41470' OR parklots.product_id = '41582' OR parklots.product_id = '45856')";
		elseif($selected == "ost")
			$andFor = "AND (parklots.product_id = '24222' OR parklots.product_id = '24224' OR parklots.product_id = '24261' 
						OR parklots.product_id = '28881' OR parklots.product_id = '28878' OR parklots.product_id = '24226' 
						OR parklots.product_id = '24228' OR parklots.product_id = '24263'
						OR parklots.product_id = '41472' OR parklots.product_id = '41580' OR parklots.product_id = '41474' OR parklots.product_id = '41585'
						OR parklots.product_id = '41402' OR parklots.product_id = '41403')";
		elseif($selected == "ostph")
			$andFor = "AND (parklots.product_id = '24222' OR parklots.product_id = '24224' OR parklots.product_id = '24261' OR parklots.product_id = '28881' OR parklots.product_id = '28878'
						OR parklots.product_id = '41472' OR parklots.product_id = '41580' OR parklots.product_id = '41402')";
		elseif($selected == "ostnu")
			$andFor = "AND (parklots.product_id = '24226' OR parklots.product_id = '24228' OR parklots.product_id = '24263'
						OR parklots.product_id = '41474' OR parklots.product_id = '41585' OR parklots.product_id = '41403')";
		else
			$andFor = "";
		if($period = "month")
			$andMonth = "AND MONTH(po.date_from) ='" . $month . "' ";
		else
			$andMonth = "";
		if($status == 'processing')
			$bStatus = "po.deleted = 0 AND (opl.post_status = 'wc-processing' OR opl.post_status = 'wc-on-hold')";
		elseif($status == 'cancelled')
			$bStatus = "(po.deleted = 0 OR po.deleted = 1) AND opl.post_status = 'wc-cancelled'";
		else
			$bStatus = "po.deleted = 0 AND opl.post_status = 'wc-processing' OR opl.post_status = 'wc-on-hold'";
		
        return self::$db->get_results("
			SELECT DATE_FORMAT(po.date_from,'%Y-%m-%d') AS Datum, DATE_FORMAT(po.date_from,'%Y') AS Jahr, DATE_FORMAT(po.date_from,'%c') AS Monat, 
			DATE_FORMAT(po.date_from,'%e') AS Tag, 
			COUNT(parklots.parklot) AS Buchungen,
			SUM(ROUND(po.b_total, 2) ) AS Brutto_b, 
			SUM(ROUND(po.b_total / 119 * 100, 4) ) AS Netto_b,
			SUM(ROUND(po.m_v_total, 2) ) AS Brutto_k, 
			SUM(ROUND(po.m_v_total / 119 * 100, 4) ) AS Netto_k, 
			SUM(ROUND(om.meta_value, 2) ) AS Provision,
			SUM(DATEDIFF(po.date_to, po.date_from)) AS Tage
			FROM " . self::$prefix_DB . "posts opl
			LEFT JOIN " . self::$prefix . "orders po ON opl.ID = po.order_id
			LEFT JOIN " . self::$prefix . "parklots parklots ON po.product_id = parklots.product_id
			LEFT JOIN " . self::$prefix . "orders_meta om on om.order_id = po.order_id and om.meta_key = 'provision'
			WHERE ".$bStatus." AND YEAR(po.date_from) = '" . $year . "' 
			" . $andMonth . "
			" . $andFor . "
			GROUP BY Datum ORDER BY Datum ASC");
    }
	
    static function getAllSalesBookings($date1, $date2, $selected)
    {	
		if($selected == true && ($selected != 'ph' && $selected != 'php' && $selected != 'pho' && $selected != 'ppb' && $selected != 'pps' && $selected != 'ost' && $selected != 'ostph' && $selected != 'ostnu' && $selected != 'phv' && $selected != 'aps' && $selected != 'apg' && $selected != 'hex' && $selected != 'parkos'))
			$andFor = "AND po.product_id = '" . $selected . "'";		
		elseif($selected == true && $selected == 'ph')
			$andFor = "AND (po.product_id = 537 OR po.product_id = 595 OR po.product_id = 621 
						OR po.product_id = 683 OR po.product_id = 592 OR po.product_id = 3080 OR po.product_id = 624
						OR po.product_id = 41453 OR po.product_id = 41577 OR po.product_id = 41466 OR po.product_id = 41581)";
		elseif($selected == true && $selected == 'php')
			$andFor = "AND (po.product_id = 537 OR po.product_id = 595 OR po.product_id = 621 OR po.product_id = 683
						OR po.product_id = 41453 OR po.product_id = 41577)";
		elseif($selected == true && $selected == 'pho')
			$andFor = "AND (po.product_id = 592 OR po.product_id = 3080 OR po.product_id = 624
						OR po.product_id = 41466 OR po.product_id = 41581)";
		elseif($selected == true && $selected == 'ppb')
			$andFor = "AND (po.product_id = 619 OR po.product_id = 3081 OR po.product_id = 24609
						OR po.product_id = 41468 OR po.product_id = 41584)";
		elseif($selected == true && $selected == 'pps')
			$andFor = "AND (po.product_id = 873 OR po.product_id = 901 OR po.product_id = 3082
						OR po.product_id = 41470 OR po.product_id = 41582 OR po.product_id = 45856)";
		elseif($selected == true && $selected == 'ost')
			$andFor = "AND (po.product_id = 24222 OR po.product_id = 24224 OR po.product_id = 24261 
						OR po.product_id = 24226 OR po.product_id = 24228 OR po.product_id = 24263 
						OR po.product_id = 28881 OR po.product_id = 28878 OR po.product_id = 41472 
						OR po.product_id = 41580 OR po.product_id = 41474 OR po.product_id = 41585
						OR po.product_id = 41402 OR po.product_id = 41403)";
		elseif($selected == true && $selected == 'ostph')
			$andFor = "AND (po.product_id = 24222 OR po.product_id = 24224 OR po.product_id = 24261
						OR po.product_id = 41472 OR po.product_id = 41580 OR po.product_id = 41402)";
		elseif($selected == true && $selected == 'ostnu')
			$andFor = "AND (po.product_id = 24226 OR po.product_id = 24228 OR po.product_id = 24263
						OR po.product_id = 41474 OR po.product_id = 41585 OR po.product_id = 41403)";
			elseif($selected == true && $selected == 'phv')
			$andFor = "AND (po.product_id = 28881 OR po.product_id = 28878)";
		elseif($selected == true && $selected == 'aps')
			$andFor = "AND (po.product_id = 537 OR po.product_id = 592 OR po.product_id = 619 
						OR po.product_id = 873 OR po.product_id = 24222 OR po.product_id = 24226 
						OR po.product_id = 28881 OR po.product_id = 41453 OR po.product_id = 41466
						OR po.product_id = 41468 OR po.product_id = 41470 OR po.product_id = 41472 OR po.product_id = 41474)";
		elseif($selected == true && $selected == 'apg')
			$andFor = "AND (po.product_id = 595 OR po.product_id = 3080 OR po.product_id = 3081 
						OR po.product_id = 3082 OR po.product_id = 24224 OR po.product_id = 24228
						OR po.product_id = 41577 OR po.product_id = 41581 OR po.product_id = 41584 OR po.product_id = 41582 OR po.product_id = 41580 OR po.product_id = 41585)";
		elseif($selected == true && $selected == 'hex')
			$andFor = "AND (po.product_id = 621 OR po.product_id = 624 OR po.product_id = 683 OR po.product_id = 901 OR po.product_id = 24261 OR po.product_id = 24263 OR po.product_id = 24609 OR po.product_id = 28878)";
		elseif($selected == true && $selected == 'parkos')
			$andFor = "AND (po.product_id = 41402 OR po.product_id = 41403 OR po.product_id = 45856)";
		else
			$andFor = "";
		
        return self::$db->get_results("
			SELECT DATE_FORMAT(po.date_from,'%Y-%m-%d') AS Datum, DATE_FORMAT(po.date_from,'%Y') AS Jahr, DATE_FORMAT(po.date_from,'%c') AS Monat, 
			DATE_FORMAT(po.date_from,'%e') AS Tag, 
			COUNT(parklots.parklot) AS Buchungen,
			SUM(ROUND(po.b_total, 2) ) AS Brutto_b, 
			SUM(ROUND(po.b_total / 119 * 100, 4) ) AS Netto_b,
			SUM(ROUND(po.m_v_total, 2) ) AS Brutto_k, 
			SUM(ROUND(po.m_v_total / 119 * 100, 4) ) AS Netto_k, 
			SUM(ROUND(om.meta_value, 4) ) AS Provision
			FROM " . self::$prefix_DB . "posts opl
			LEFT JOIN " . self::$prefix . "orders po ON opl.ID = po.order_id
			LEFT JOIN " . self::$prefix . "parklots parklots ON po.product_id = parklots.product_id
			LEFT JOIN " . self::$prefix . "orders_meta om on om.order_id = po.order_id and om.meta_key = 'provision'
			WHERE po.deleted = 0 AND (opl.post_status = 'wc-processing' OR opl.post_status = 'wc-on-hold') AND parklots.is_for != 'hotel' AND DATE(po.date_from) BETWEEN '" . $date1 . "' AND '" . $date2 . "' 
			" . $andFor . " 
			GROUP BY Datum ORDER BY Datum ASC");
    }
	
	static function getSalesLots($date, $product_id)
    {
        return self::$db->get_row("
			SELECT DATE_FORMAT(po.date_from,'%Y-%m-%d') AS Datum,
			COUNT(parklots.parklot) AS Buchungen, 
			SUM(ROUND(po.b_total, 2) ) AS Brutto_b, 
			SUM(ROUND(po.b_total / 119 * 100, 2) ) AS Netto_b,
			SUM(ROUND(po.m_v_total, 2) ) AS Brutto_k, 
			SUM(ROUND(po.m_v_total / 119 * 100, 2) ) AS Netto_k, 
			SUM(ROUND(om.meta_value, 2) ) AS Provision, 
			parklots.parklot AS Produkt, parklots.contigent AS Kontingent, parklots.product_id AS product_id, c.client AS Betreiber, b.company AS Vermittler 
			FROM " . self::$prefix_DB . "posts opl
			LEFT JOIN " . self::$prefix . "orders po ON opl.ID = po.order_id
			LEFT JOIN " . self::$prefix . "parklots parklots ON po.product_id = parklots.product_id
			LEFT JOIN " . self::$prefix . "orders_meta om on om.order_id = po.order_id and om.meta_key = 'provision'
			LEFT JOIN " . self::$prefix . "clients_products cp ON cp.product_id = parklots.product_id
			LEFT JOIN " . self::$prefix . "brokers_products bp ON bp.product_id = parklots.product_id
			LEFT JOIN " . self::$prefix . "clients c ON c.id = cp.client_id
			LEFT JOIN " . self::$prefix . "brokers b ON b.id = bp.broker_id
			WHERE po.deleted = 0 AND (opl.post_status = 'wc-processing' OR opl.post_status = 'wc-on-hold') AND po.product_id = " . $product_id . " 
			AND DATE_FORMAT(po.date_from,'%Y-%m-%d') = '" . $date . "' 
			GROUP BY Datum, Produkt
			ORDER BY Produkt ASC, Datum DESC");		
	}

	static function getLotsContingent()
    {
		return self::$db->get_results("SELECT pl.id, pl.parklot, pl.contigent, c.id, c.client, b.id, b.company 
				FROM " . self::$prefix . "parklots pl
				LEFT JOIN " . self::$prefix . "clients_products cp ON cp.product_id = pl.product_id
				LEFT JOIN " . self::$prefix . "brokers_products bp ON bp.product_id = pl.product_id
				LEFT JOIN " . self::$prefix . "clients c ON c.id = cp.client_id
				LEFT JOIN " . self::$prefix . "brokers b ON b.id = bp.broker_id");
	}
	
	static function setLotsContingent($lot_id, $ammount)
    {
		return self::$db->update(self::$prefix . 'parklots', [
				'contigent' => $ammount
			], ['product_id' => $lot_id]);
	}
	
	// save booking meta
    static function saveBookingMeta($order_id, $meta_key, $meta_value)
    {
           return self::$db->insert(self::$prefix . 'orders_meta', [
                'order_id' => $order_id,
                'meta_key' => $meta_key,
				'meta_value' => $meta_value				
            ]);
    }
	
	// update booking meta
    static function updateBookingMeta($order_id, $meta_key, $meta_value)
    {
           return self::$db->update(self::$prefix . 'orders_meta', [                               
				'meta_value' => $meta_value				
			], ['order_id' => $order_id, 'meta_key' => $meta_key]);
    }
	
	// get booking meta
    static function getBookingMeta($order_id, $meta_key)
    {
           $result = self::$db->get_row("SELECT meta_value FROM " . self::$prefix . "orders_meta WHERE meta_key = '".$meta_key."' and order_id = " . $order_id);
			return $result->meta_value;
    }
	static function getBookingMetaAsResults($order_id, $meta_key)
    {
           $result = self::$db->get_results("SELECT meta_value FROM " . self::$prefix . "orders_meta WHERE meta_key = '".$meta_key."' and order_id = " . $order_id);
			return $result;
    }
	
	// delete booking meta
    static function deleteBookingMeta($order_id)
    {
		if($key)
			$key2 = array('meta_key' => $key);
		return self::$db->delete(self::$prefix . 'orders_meta', ['order_id' => $order_id]);
    }
	// delete booking meta by order_id and key
    static function deleteBookingMetaByKey($order_id, $key)
    {
		return self::$db->delete(self::$prefix . 'orders_meta', ['order_id' => $order_id, 'meta_key' => $key]);
    }
	
	
	// insert edit booking log data
    static function addEditBookingLog($order_id, $log_key, $log_value)
    {
		date_default_timezone_set("Europe/Amsterdam");
		self::$db->insert(self::$prefix . 'edit_booking_log', [                               
			'order_id' => $order_id,
			'user_id' => get_current_user_id(),
			'date' => date("Y-m-d") . " " . date("H:i:s"),
			'log_key' => $log_key,
			'log_value' => $log_value
		]);
    }
	
	// get edit booking log data
    static function getEditBookingLog($order_id)
    {
		return self::$db->get_results("select lg.date, u.display_name, lg.log_key, lg.log_value from " . self::$prefix . "edit_booking_log lg 
			inner join " . self::$prefix_DB . "users u on u.ID = lg.user_id
			where lg.order_id = " . $order_id);
    }
	
	// save Urlaubsplanung
    static function saveUrlaubsplanung($data)
    {
           return self::$db->insert(self::$prefix . 'urlaubskalender', [
                'user_id' => $data['user_id'],
                'datum_von' => $data['datum_von'],
				'datum_bis' => $data['datum_bis'],
				'tage' => $data['tage'],
				'typ' => $data['typ'],
				'color' => $data['color']
            ]);
    }
	
	// get Urlaubsplanung
    static function getUrlaubsplanung()
    {
		return self::$db->get_results("select * from " . self::$prefix . "urlaubskalender");
    }
	
	// get Urlaubsplanung Tabe by user_id
    static function getUrlaubsplanungTageByUserId($id)
    {
		return self::$db->get_row("select SUM(tage) as tage from " . self::$prefix . "urlaubskalender
			where user_id = " . $id);
    }
	
	// delete Uralubsplanung
	static function deleteUrlubsplanung($data)
    {
        return self::$db->delete(self::$prefix . 'urlaubskalender', ['datum_von' => $data['from'], 'datum_bis' => $data['to'], 'user_id' => $data['user']]);
    }
    
    
    
//     #####################


static function getBookings2($filter)
	{

		$get_ids = 1;
		$token_ids = 1;

		if ($filter['datum_von'] != null && $filter['datum_bis'] != null && $filter['name_an'] == null && $filter['name_ab'] == null && $filter['token_an'] == null && $filter['token_ab'] == null) {
			$arr_ids = [];
			$datum_von = $filter['datum_von'];
			$datum_bis = $filter['datum_bis'];
			$first_sql = "SELECT * FROM " . self::$prefix_DB . "postmeta
						WHERE meta_key = 'Anreisedatum'
						AND meta_value >= '$datum_von'
						AND meta_value <= '$datum_bis'";

			$this_data = self::$db->get_results($first_sql);

			foreach ($this_data as $d) {
				$arr_ids[] = $d->post_id;
			}
			$final_ids = join("','", $arr_ids);
			$get_ids = "pm.post_id IN ('" . $final_ids . "')";
		} elseif ($filter['datum_von_Ad'] != null && $filter['datum_bis_Ad'] != null && $filter['name_an'] == null && $filter['name_ab'] == null && $filter['token_an'] == null && $filter['token_ab'] == null) {
			$arr_ids = [];
			$datum_von = $filter['datum_von_Ad'];
			$datum_bis = $filter['datum_bis_Ad'];
			$first_sql = "SELECT * FROM " . self::$prefix_DB . "postmeta
							WHERE meta_key = 'Abreisedatum'
							AND meta_value >= '$datum_von'
							AND meta_value <= '$datum_bis'";

			$this_data = self::$db->get_results($first_sql);
			foreach ($this_data as $d) {
				$arr_ids[] = $d->post_id;
			}
			$final_ids = join("','", $arr_ids);
			$get_ids = "pm.post_id IN ('" . $final_ids . "')";
		} elseif ($filter['token'] != null) {

			$arr_ids = [];
			$this_token = $filter['token'];

			$first_sql = "SELECT * FROM " . self::$prefix_DB . "postmeta
						WHERE meta_key = 'token'
						AND meta_value = '$this_token'";

			$this_data = self::$db->get_results($first_sql);

			foreach ($this_data as $d) {
				$arr_ids[] = $d->post_id;
			}
			$final_ids = join("','", $arr_ids);
			$token_ids = "pm.post_id IN ('" . $final_ids . "')";

		} elseif ($filter['token_an'] != null) {

			$arr_ids = [];
			$this_token = $filter['token_an'];
			$datum_von = $filter['datum_von'];
			$datum_bis = $filter['datum_bis'];

			$first_sql = "SELECT pm1.*, pm2.* FROM " . self::$prefix_DB . "postmeta pm1
						JOIN " . self::$prefix_DB . "postmeta pm2 ON pm1.post_id = pm2.post_id
						WHERE pm1.meta_key = 'token'
						AND pm1.meta_value LIKE '%$this_token%'
						AND pm2.meta_key = 'Anreisedatum'
						AND pm2.meta_value >= '$datum_von'
						AND pm2.meta_value <= '$datum_bis'";

			$this_data = self::$db->get_results($first_sql);

			foreach ($this_data as $d) {
				$arr_ids[] = $d->post_id;
			}
			$final_ids = join("','", $arr_ids);
			$token_ids = "pm.post_id IN ('" . $final_ids . "')";

		} elseif ($filter['token_ab'] != null) {

			$arr_ids = [];
			$this_token = $filter['token_ab'];
			$datum_von = $filter['datum_von_Ad'];
			$datum_bis = $filter['datum_bis_Ad'];

			$first_sql = "SELECT pm1.*, pm2.* FROM " . self::$prefix_DB . "postmeta pm1
						JOIN " . self::$prefix_DB . "postmeta pm2 ON pm1.post_id = pm2.post_id
						WHERE pm1.meta_key = 'token'
						AND pm1.meta_value LIKE '%$this_token%'
						AND pm2.meta_key = 'Abreisedatum'
						AND pm2.meta_value >= '$datum_von'
						AND pm2.meta_value <= '$datum_bis'";

			$this_data = self::$db->get_results($first_sql);

			foreach ($this_data as $d) {
				$arr_ids[] = $d->post_id;
			}
			$final_ids = join("','", $arr_ids);
			$token_ids = "pm.post_id IN ('" . $final_ids . "')";

		} elseif ($filter['name_an'] != null) {

			$arr_ids =  [];
			$this_name = $filter['name_an'];
			$datum_von = $filter['datum_von'];
			$datum_bis = $filter['datum_bis'];

			$first_sql1 = "SELECT pm1.*, pm2.* FROM " . self::$prefix_DB . "postmeta pm1
						JOIN " . self::$prefix_DB . "postmeta pm2 ON pm1.post_id = pm2.post_id
						WHERE pm1.meta_key = '_billing_last_name'
						AND pm1.meta_value LIKE '%$this_name%'
						AND pm2.meta_key = 'Anreisedatum'
						AND pm2.meta_value >= '$datum_von'
						AND pm2.meta_value <= '$datum_bis'";						
			$first_sql2 = "SELECT pm1.*, pm2.* FROM " . self::$prefix_DB . "postmeta pm1
						JOIN " . self::$prefix_DB . "postmeta pm2 ON pm1.post_id = pm2.post_id
						WHERE pm1.meta_key = '_billing_first_name'
						AND pm1.meta_value LIKE '%$this_name%'
						AND pm2.meta_key = 'Anreisedatum'
						AND pm2.meta_value >= '$datum_von'
						AND pm2.meta_value <= '$datum_bis'";

			$this_data = self::$db->get_results($first_sql1);
			
			if($this_data == null){
				$this_data = self::$db->get_results($first_sql2);
			}
			
			
			foreach ($this_data as $d) {
				$arr_ids[] = $d->post_id;
			}
			
			$final_ids = join("','", $arr_ids);
			$get_ids = "pm.post_id IN ('" . $final_ids . "')";			

		} elseif ($filter['name_ab'] != null) {

			$arr_ids = [];
			$this_name = $filter['name_ab'];
			$datum_von = $filter['datum_von_Ad'];
			$datum_bis = $filter['datum_bis_Ad'];

			$first_sql1 = "SELECT pm1.*, pm2.* FROM " . self::$prefix_DB . "postmeta pm1
						JOIN " . self::$prefix_DB . "postmeta pm2 ON pm1.post_id = pm2.post_id
						WHERE pm1.meta_key = '_billing_last_name'
						AND pm1.meta_value LIKE '%$this_name%'
						AND pm2.meta_key = 'Abreisedatum'
						AND pm2.meta_value >= '$datum_von'
						AND pm2.meta_value <= '$datum_bis'";
			$first_sql2 = "SELECT pm1.*, pm2.* FROM " . self::$prefix_DB . "postmeta pm1
						JOIN " . self::$prefix_DB . "postmeta pm2 ON pm1.post_id = pm2.post_id
						WHERE pm1.meta_key = '_billing_first_name'
						AND pm1.meta_value LIKE '%$this_name%'
						AND pm2.meta_key = 'Abreisedatum'
						AND pm2.meta_value >= '$datum_von'
						AND pm2.meta_value <= '$datum_bis'";

			$this_data = self::$db->get_results($first_sql1);
			if($this_data == null){
				$this_data = self::$db->get_results($first_sql2);
			}

			foreach ($this_data as $d) {
				$arr_ids[] = $d->post_id;
			}
			$final_ids = join("','", $arr_ids);
			$get_ids = "pm.post_id IN ('" . $final_ids . "')";

		} elseif ($filter['kennzeichen'] != null)
			$having = " HAVING
						MAX(CASE WHEN pm.meta_key = 'Kennzeichen' THEN pm.meta_value END) = '" . $filter['kennzeichen'] . "' ";

		elseif ($filter['lotnr'] != null)
			$having = " HAVING
						MAX(CASE WHEN pm.meta_key = 'Parkplatz' THEN pm.meta_value END) = '" . $filter['lotnr'] . "' ";
		else
			$having = "";

		if ($filter['orderBy'] == "Anreisedatum" && $filter['list'] == null)
			$orderBy = " ORDER BY position ASC";
		elseif ($filter['orderBy'] == "Buchungsdatum")
		$orderBy = " ORDER BY Buchungsdatum ASC ";
		else
			$orderBy = "";

		if ($filter['buchung_von'] != null && $filter['buchung_bis'] != null)
			$booking = " AND p.post_date >= '" . $filter['buchung_von'] . "' AND p.post_date <= '" . $filter['buchung_bis'] . "' ";
		elseif ($filter['pkw_datum_von']) {
			$booking = " AND '" . $filter['pkw_datum_von'] . "' BETWEEN DATE(o.date_from) AND DATE(o.date_to ) ";
		} else
			$booking = "";

		if ($filter['list'] != null) {
			$status = " AND p.post_status != 'wc-pending' ";
			if ($filter['datum_von'] != null && $filter['datum_bis'] != null) {
				$orderBy = " ORDER BY Anreisedatum ASC, Status DESC, Uhrzeit_von ASC ";
			} elseif ($filter['datum_von_Ad'] != null && $filter['datum_bis_Ad'] != null) {
				$orderBy = " ORDER BY Abreisedatum ASC, Status DESC, Uhrzeit_bis ASC ";
			}
		} else
			$status = " AND (p.post_status = 'wc-processing' AND p.post_status != 'wc-pending') ";

		if ($filter['type'] == "shuttle")
		$type = " AND pl.type = 'shuttle' ";
		elseif ($filter['type'] == "valet")
		$type = " AND pl.type = 'valet' ";
		else
			$type = "";
	
		$produkt_query = 1;
		if (isset($filter['filter_product']) && $filter['filter_product'] != null) {
			if ($filter['filter_product'] == 'phg') {
				$produkt_query = '
				  o.product_id = 537 OR
			      o.product_id = 595 OR
			      o.product_id = 621 OR
			      o.product_id = 683 OR
			      o.product_id = 592 OR
			      o.product_id = 3080 OR
			      o.product_id = 624';
			} elseif ($filter['filter_product'] == 'ph') {
				$produkt_query = 'o.product_id = 537 OR
			      o.product_id = 595 OR
			      o.product_id = 621 OR
			      o.product_id = 683';
			} elseif ($filter['filter_product'] == 'pho') {
				$produkt_query = 'o.product_id = 592 OR
			      o.product_id = 3080 OR
			      o.product_id = 624';
			} elseif ($filter['filter_product'] == 'ppb') {
				$produkt_query = 'o.product_id = 619 OR
			      o.product_id = 3081 OR
			      o.product_id = 24609';
			} elseif ($filter['filter_product'] == 'pps') {
				$produkt_query = 'o.product_id = 873 OR
			      o.product_id = 3082 OR
			      o.product_id = 901 OR
			      o.product_id = 45856';
			} elseif ($filter['filter_product'] == 'ost') {
				$produkt_query = 'o.product_id = 24222 OR
			      o.product_id = 24224 OR
			      o.product_id = 24261 OR
				  o.product_id = 28881 OR
				  o.product_id = 28878 OR
				  o.product_id = 41402 OR				  
			      o.product_id = 24226 OR
			      o.product_id = 24228 OR
			      o.product_id = 24263 OR
				  o.product_id = 41403';
			} elseif ($filter['filter_product'] == 'ostph') {
				$produkt_query = 'o.product_id = 24222 OR
			      o.product_id = 24224 OR
			      o.product_id = 24261 OR
				  o.product_id = 28881 OR
				  o.product_id = 28878 OR
				  o.product_id = 41402';
			} elseif ($filter['filter_product'] == 'ostnu') {
				$produkt_query = 'o.product_id = 24226 OR
			      o.product_id = 24228 OR
			      o.product_id = 24263 OR
			      o.product_id = 41403';
			}
		}

		$filter_product = $filter_product_transfer = 1;
		if (isset($filter['product']) && $filter['product'] != null) {
			$filter_product = "o.product_id ='". $filter['product']."'";
			$filter_product_transfer = "ht.product_id ='". $filter['product']."'";
		}

		$filter_payment_method = 1;
		if (isset($filter['payment_method']) && $filter['payment_method'] != null) {
			// Database::getInstance()->mydd($filter['payment_method']);
			// $filter_payment_method = "(pm.meta_key = '_payment_method_title' AND pm . meta_value = '". $filter['payment_method']."')";

			$arr_ids = [];
			$datum_von = $filter['datum_von2'];
			$datum_bis = $filter['datum_bis2'];
			$this_payment_method = $filter['payment_method'];

			$first_sql = "SELECT p.ID
						FROM " . self::$prefix_DB . "posts p
						LEFT JOIN " . self::$prefix_DB . "postmeta pm ON pm.post_id = p.ID
						WHERE pm.meta_key = '_payment_method_title' AND pm.meta_value = '$this_payment_method'
						AND p.post_type = 'shop_order'   
						AND (p.post_status = 'wc-processing' AND p.post_status != 'wc-pending')  
						" . $booking . $type;

			$this_data = self::$db->get_results($first_sql);

			foreach ($this_data as $d) {
				$arr_ids[] = $d->ID;
			}
			$final_ids = join("','", $arr_ids);
// 			$filter_payment_method = "pm.post_id IN ('" . $final_ids . "')";
		}

		$filter_betreiber  = 1;
		if ($filter['betreiber'] == 'aps') {
			$filter_betreiber  = 'o.product_id != 537 AND
			o.product_id != 592 AND
			o.product_id != 619 AND
			o.product_id != 873 AND
			o.product_id != 24222 AND
			o.product_id != 28881 AND
			o.product_id != 24226';
			
		} elseif ($filter['betreiber'] == 'apg') {
			$filter_betreiber  = 'o.product_id != 595 AND
			o.product_id != 3080 AND
			o.product_id != 3081 AND
			o.product_id != 3082 AND
			o.product_id != 24224 AND
			o.product_id != 24228'; 
		} elseif ($filter['betreiber'] == 'hex') {
			$filter_betreiber  = 'o.product_id != 621 AND
			o.product_id != 683 AND
			o.product_id != 624 AND
			o.product_id != 24609 AND
			o.product_id != 901 AND
			o.product_id != 24261 AND
			o.product_id != 28878 AND
			o.product_id != 24263'; 
		} elseif ($filter['betreiber'] == 'parkos') {
			$filter_betreiber  = 'o.product_id != 41402 AND o.product_id != 41403';
		} elseif ($filter['betreiber'] == 'amh') {
			$filter_betreiber  = 'o.product_id != 3851';
		} elseif ($filter['betreiber'] == 'hma') {
			$filter_betreiber  = 'o.product_id != 6762'; 
		} elseif ($filter['betreiber'] == 'iaps') {
			$filter_betreiber  = 'o.product_id != 6772';
		}

		$sql = "SELECT @num := @num + 1 AS position,
			p.ID AS order_id,
			p.post_date AS Buchungsdatum,
			p.post_status AS Status,
			pl.parklot_short AS Code,
			pl.color AS Color,
			pl.is_for AS is_for,
			o.product_id AS Produkt,
			MAX(CASE WHEN pm.meta_key = 'Anreisedatum' THEN pm.meta_value END) AS Anreisedatum,
			MAX(CASE WHEN pm.meta_key = 'Abreisedatum' THEN pm.meta_value END) AS Abreisedatum,
			MAX(CASE WHEN pm.meta_key = 'AbreisedatumEdit' THEN pm.meta_value END) AS AbreisedatumEdit,
			MAX(CASE WHEN pm.meta_key = 'Uhrzeit von' THEN pm.meta_value END) AS Uhrzeit_von,
			MAX(CASE WHEN pm.meta_key = 'Uhrzeit bis' THEN pm.meta_value END) AS Uhrzeit_bis,
			MAX(CASE WHEN pm.meta_key = 'Uhrzeit bis Edit' THEN pm.meta_value END) AS Uhrzeit_bis_Edit, 
			MAX(CASE WHEN pm.meta_key = 'token' THEN pm.meta_value END) AS Token,
			MAX(CASE WHEN pm.meta_key = '_billing_first_name' THEN pm.meta_value END) AS Vorname,
			MAX(CASE WHEN pm.meta_key = '_billing_last_name' THEN pm.meta_value END) AS Nachname,
			MAX(CASE WHEN pm.meta_key = '_billing_email' THEN pm.meta_value END) AS Email,
			MAX(CASE WHEN pm.meta_key = 'Personenanzahl' THEN pm.meta_value END) AS Personenanzahl,
			MAX(CASE WHEN pm.meta_key = 'Kennzeichen' THEN pm.meta_value END) AS Kennzeichen,
			MAX(CASE WHEN pm.meta_key = 'Parkplatz' THEN pm.meta_value END) AS Parkplatz,
			MAX(CASE WHEN pm.meta_key = 'Rückflugnummer' THEN pm.meta_value END) AS Rückflugnummer,
			MAX(CASE WHEN pm.meta_key = 'RückflugnummerEdit' THEN pm.meta_value END) AS RückflugnummerEdit,
			MAX(CASE WHEN pm.meta_key = 'Hinflugnummer' THEN pm.meta_value END) AS Hinflugnummer,
			MAX(CASE WHEN pm.meta_key = 'Fahrzeughersteller' THEN pm.meta_value END) AS Fahrzeughersteller,
			MAX(CASE WHEN pm.meta_key = 'FahrerAn' THEN pm.meta_value END) AS FahrerAn,
			MAX(CASE WHEN pm.meta_key = 'FahrerAb' THEN pm.meta_value END) AS FahrerAb,
			MAX(CASE WHEN pm.meta_key = 'Sonstige 1' THEN pm.meta_value END) AS Sonstige_1,
			MAX(CASE WHEN pm.meta_key = 'Sonstige 2' THEN pm.meta_value END) AS Sonstige_2,
			MAX(CASE WHEN pm.meta_key = 'editByArr' THEN pm.meta_value END) AS editByArr,
			MAX(CASE WHEN pm.meta_key = 'editByRet' THEN pm.meta_value END) AS editByRet,
			MAX(CASE WHEN pm.meta_key = '_order_total' THEN pm.meta_value END) AS Betrag,
			MAX(CASE WHEN pm.meta_key = '_payment_method_title' THEN pm.meta_value END) AS Bezahlmethode
						
		FROM " . self::$prefix_DB . "posts p
		JOIN (SELECT @num := 0 FROM DUAL) AS n ON 1=1
		LEFT JOIN " . self::$prefix_DB . "postmeta pm ON pm.post_id = p.ID
		INNER JOIN " . self::$prefix . "orders o ON o.order_id = p.ID
		INNER JOIN " . self::$prefix . "parklots pl ON pl.product_id = o.product_id
		WHERE p.post_type = 'shop_order'  " . $status . $booking . $type . "	
		AND " . $get_ids . "
		AND " . $token_ids . " 
		AND (" . $produkt_query . ")
		AND (" . $filter_betreiber . ")
		AND " . $filter_payment_method . " 
		AND " . $filter_product . " 

		GROUP BY p.ID 
		" . $having;
		if ($filter['pkw_datum_von'] == null) {
			$sql2 = "UNION ALL
				
			SELECT @num := @num + 1 AS position,
				p.ID AS order_id,
				p.post_date AS Buchungsdatum,
				p.post_status AS Status,
				pl.parklot_short AS Code,
				pl.color AS Color,
				pl.is_for AS is_for,			
				ht.product_id AS Produkt,
				MAX(CASE WHEN pm.meta_key = 'Anreisedatum' THEN pm.meta_value END) AS Anreisedatum,
				MAX(CASE WHEN pm.meta_key = 'Abreisedatum' THEN pm.meta_value END) AS Abreisedatum,
				MAX(CASE WHEN pm.meta_key = 'AbreisedatumEdit' THEN pm.meta_value END) AS AbreisedatumEdit,
				MAX(CASE WHEN pm.meta_key = 'Uhrzeit von' THEN pm.meta_value END) AS Uhrzeit_von,
				MAX(CASE WHEN pm.meta_key = 'Uhrzeit bis' THEN pm.meta_value END) AS Uhrzeit_bis,
				MAX(CASE WHEN pm.meta_key = 'Uhrzeit bis Edit' THEN pm.meta_value END) AS Uhrzeit_bis_Edit, 
				MAX(CASE WHEN pm.meta_key = 'token' THEN pm.meta_value END) AS Token,
				MAX(CASE WHEN pm.meta_key = '_billing_first_name' THEN pm.meta_value END) AS Vorname,
				MAX(CASE WHEN pm.meta_key = '_billing_last_name' THEN pm.meta_value END) AS Nachname,
				MAX(CASE WHEN pm.meta_key = '_billing_email' THEN pm.meta_value END) AS Email,
				MAX(CASE WHEN pm.meta_key = 'Personenanzahl' THEN pm.meta_value END) AS Personenanzahl,
				MAX(CASE WHEN pm.meta_key = 'Kennzeichen' THEN pm.meta_value END) AS Kennzeichen,
				MAX(CASE WHEN pm.meta_key = 'Parkplatz' THEN pm.meta_value END) AS Parkplatz,
				MAX(CASE WHEN pm.meta_key = 'Rückflugnummer' THEN pm.meta_value END) AS Rückflugnummer,
				MAX(CASE WHEN pm.meta_key = 'RückflugnummerEdit' THEN pm.meta_value END) AS RückflugnummerEdit,
				MAX(CASE WHEN pm.meta_key = 'Hinflugnummer' THEN pm.meta_value END) AS Hinflugnummer,
				MAX(CASE WHEN pm.meta_key = 'Fahrzeughersteller' THEN pm.meta_value END) AS Fahrzeughersteller,
				MAX(CASE WHEN pm.meta_key = 'FahrerAn' THEN pm.meta_value END) AS FahrerAn,
				MAX(CASE WHEN pm.meta_key = 'FahrerAb' THEN pm.meta_value END) AS FahrerAb,
				MAX(CASE WHEN pm.meta_key = 'Sonstige 1' THEN pm.meta_value END) AS Sonstige_1,
				MAX(CASE WHEN pm.meta_key = 'Sonstige 2' THEN pm.meta_value END) AS Sonstige_2,
				MAX(CASE WHEN pm.meta_key = 'editByArr' THEN pm.meta_value END) AS editByArr,
				MAX(CASE WHEN pm.meta_key = 'editByRet' THEN pm.meta_value END) AS editByRet,
				MAX(CASE WHEN pm.meta_key = '_order_total' THEN pm.meta_value END) AS Betrag,
				MAX(CASE WHEN pm.meta_key = '_payment_method_title' THEN pm.meta_value END) AS Bezahlmethode
							
			FROM " . self::$prefix_DB . "posts p
			LEFT JOIN " . self::$prefix_DB . "postmeta pm
				ON pm.post_id = p.ID
			INNER JOIN " . self::$prefix . "hotel_transfers ht ON ht.order_id = p.ID
			INNER JOIN " . self::$prefix . "parklots pl ON pl.product_id = ht.product_id
			WHERE p.post_type = 'shop_order' " . $status . $booking . $type . " 
			AND " . $get_ids . "	
			AND " . $token_ids . "
			AND (" . $filter_betreiber . ")
			AND " . $filter_payment_method . " 
			AND " . $filter_product_transfer . " 
			GROUP BY p.ID "
			. $having;
		} else
			$sql2 = "";

		$ret = self::$db->get_results($sql . $sql2 . $orderBy);
		$_SESSION['total_rows'] = count($ret);
		$_SESSION['allorders'] = $ret;

		$results_per_page = 25;

		if (isset($_GET['step']) && !empty($_GET['step'])) {
			$step  = $_GET["step"];
		} else {
			$step = 1;
		};
		$start_from = ($step - 1) * $results_per_page;

		$get_ids = 1;

		return self::$db->get_results($sql . $sql2 . $orderBy . " LIMIT " . $start_from . ", " . $results_per_page);
	}








    
//     #####################
    
    
    
    
    
    
	
	// get bookings with filter
    static function getBookings($filter)
    {
		/*
		if($filter['pkw_datum_von'] != null && $filter['pkw_datum_bis'] != null)
			$having = " HAVING
						MAX(CASE WHEN pm.meta_key = 'Anreisedatum' THEN pm.meta_value END) >= '".$filter['pkw_datum_von']."' AND 
						MAX(CASE WHEN pm.meta_key = 'Anreisedatum' THEN pm.meta_value END) <= '".$filter['pkw_datum_bis']."' "; 
		*/				
		if($filter['datum_von'] != null && $filter['datum_bis'] != null)
			$having = " HAVING
						MAX(CASE WHEN pm.meta_key = 'Anreisedatum' THEN pm.meta_value END) >= '".$filter['datum_von']."' AND 
						MAX(CASE WHEN pm.meta_key = 'Anreisedatum' THEN pm.meta_value END) <= '".$filter['datum_bis']."' "; 
		
		elseif($filter['datum_von_Ad'] != null && $filter['datum_bis_Ad'] != null)
			$having = " HAVING
						MAX(CASE WHEN pm.meta_key = 'Abreisedatum' THEN pm.meta_value END) >= '".$filter['datum_von_Ad']."' AND 
						MAX(CASE WHEN pm.meta_key = 'Abreisedatum' THEN pm.meta_value END) <= '".$filter['datum_bis_Ad']."' "; 

		elseif($filter['token'] != null)
			$having = " HAVING
						MAX(CASE WHEN pm.meta_key = 'token' THEN pm.meta_value END) = '".$filter['token']."' ";
		
		elseif($filter['kennzeichen'] != null)
			$having = " HAVING
						MAX(CASE WHEN pm.meta_key = 'Kennzeichen' THEN pm.meta_value END) = '".$filter['kennzeichen']."' ";
		
		elseif($filter['lotnr'] != null)
			$having = " HAVING
						MAX(CASE WHEN pm.meta_key = 'Parkplatz' THEN pm.meta_value END) = '".$filter['lotnr']."' ";
		else
			$having = "";
		
		if($filter['orderBy'] == "Anreisedatum" && $filter['list'] == null)
			$orderBy = " ORDER BY position DESC ";
		elseif($filter['orderBy'] == "Buchungsdatum")
			$orderBy = " ORDER BY Buchungsdatum DESC ";
		else
			$orderBy = "";
		
		if($filter['buchung_von'] != null && $filter['buchung_bis'] != null)
			$booking = " AND p.post_date >= '".$filter['buchung_von']."' AND p.post_date <= '".$filter['buchung_bis']."' ";
		elseif($filter['pkw_datum_von']){
			$booking = " AND '" . $filter['pkw_datum_von'] . "' BETWEEN DATE(o.date_from) AND DATE(o.date_to ) ";
		}
		else
			$booking = "";
		
		if($filter['list'] != null){
			$status = " AND p.post_status != 'wc-pending' ";
			$orderBy = " ORDER BY Anreisedatum ASC, Uhrzeit_von ASC ";
		}			
		else
			$status = " AND (p.post_status = 'wc-processing' AND p.post_status != 'wc-pending') ";
		
		if($filter['type'] == "shuttle")
			$type = " AND pl.type = 'shuttle' ";
		elseif($filter['type'] == "valet")
			$type = " AND pl.type = 'valet' ";
		else
			$type = "";
		
		
		$sql = "SELECT @num := @num + 1 AS position,
			p.ID AS order_id,
			p.post_date AS Buchungsdatum,
			p.post_status AS Status,
			pl.parklot_short AS Code,
			pl.color AS Color,
			pl.is_for AS is_for,
			o.product_id AS Produkt,
			MAX(CASE WHEN pm.meta_key = 'Anreisedatum' THEN pm.meta_value END) AS Anreisedatum,
			MAX(CASE WHEN pm.meta_key = 'Abreisedatum' THEN pm.meta_value END) AS Abreisedatum,
			MAX(CASE WHEN pm.meta_key = 'AbreisedatumEdit' THEN pm.meta_value END) AS AbreisedatumEdit,
			MAX(CASE WHEN pm.meta_key = 'Uhrzeit von' THEN pm.meta_value END) AS Uhrzeit_von,
			MAX(CASE WHEN pm.meta_key = 'Uhrzeit bis' THEN pm.meta_value END) AS Uhrzeit_bis,
			MAX(CASE WHEN pm.meta_key = 'Uhrzeit bis Edit' THEN pm.meta_value END) AS Uhrzeit_bis_Edit, 
			MAX(CASE WHEN pm.meta_key = 'token' THEN pm.meta_value END) AS Token,
			MAX(CASE WHEN pm.meta_key = '_billing_first_name' THEN pm.meta_value END) AS Vorname,
			MAX(CASE WHEN pm.meta_key = '_billing_last_name' THEN pm.meta_value END) AS Nachname,
			MAX(CASE WHEN pm.meta_key = '_billing_email' THEN pm.meta_value END) AS Email,
			MAX(CASE WHEN pm.meta_key = 'Personenanzahl' THEN pm.meta_value END) AS Personenanzahl,
			MAX(CASE WHEN pm.meta_key = 'Kennzeichen' THEN pm.meta_value END) AS Kennzeichen,
			MAX(CASE WHEN pm.meta_key = 'Parkplatz' THEN pm.meta_value END) AS Parkplatz,
			MAX(CASE WHEN pm.meta_key = 'Rückflugnummer' THEN pm.meta_value END) AS Rückflugnummer,
			MAX(CASE WHEN pm.meta_key = 'RückflugnummerEdit' THEN pm.meta_value END) AS RückflugnummerEdit,
			MAX(CASE WHEN pm.meta_key = 'Hinflugnummer' THEN pm.meta_value END) AS Hinflugnummer,
			MAX(CASE WHEN pm.meta_key = 'Fahrzeughersteller' THEN pm.meta_value END) AS Fahrzeughersteller,
			MAX(CASE WHEN pm.meta_key = 'FahrerAn' THEN pm.meta_value END) AS FahrerAn,
			MAX(CASE WHEN pm.meta_key = 'FahrerAb' THEN pm.meta_value END) AS FahrerAb,
			MAX(CASE WHEN pm.meta_key = 'Sonstige 1' THEN pm.meta_value END) AS Sonstige_1,
			MAX(CASE WHEN pm.meta_key = 'Sonstige 2' THEN pm.meta_value END) AS Sonstige_2,
			MAX(CASE WHEN pm.meta_key = 'editByArr' THEN pm.meta_value END) AS editByArr,
			MAX(CASE WHEN pm.meta_key = 'editByRet' THEN pm.meta_value END) AS editByRet,
			MAX(CASE WHEN pm.meta_key = '_order_total' THEN pm.meta_value END) AS Betrag,
			MAX(CASE WHEN pm.meta_key = '_payment_method_title' THEN pm.meta_value END) AS Bezahlmethode
						
		FROM " . self::$prefix_DB . "posts p
		JOIN (SELECT @num := 0 FROM DUAL) AS n ON 1=1
		LEFT JOIN " . self::$prefix_DB . "postmeta pm
			ON pm.post_id = p.ID
		INNER JOIN " . self::$prefix . "orders o ON o.order_id = p.ID
		INNER JOIN " . self::$prefix . "parklots pl ON pl.product_id = o.product_id
		WHERE
			p.post_type = 'shop_order' 
			".$status.$booking.$type."		
		GROUP BY p.ID 
		" . $having;
		if($filter['pkw_datum_von'] == null){
			$sql2 = "UNION ALL
				
			SELECT @num := @num + 1 AS position,
				p.ID AS order_id,
				p.post_date AS Buchungsdatum,
				p.post_status AS Status,
				pl.parklot_short AS Code,
				pl.color AS Color,
				pl.is_for AS is_for,			
				ht.product_id AS Produkt,
				MAX(CASE WHEN pm.meta_key = 'Anreisedatum' THEN pm.meta_value END) AS Anreisedatum,
				MAX(CASE WHEN pm.meta_key = 'Abreisedatum' THEN pm.meta_value END) AS Abreisedatum,
				MAX(CASE WHEN pm.meta_key = 'AbreisedatumEdit' THEN pm.meta_value END) AS AbreisedatumEdit,
				MAX(CASE WHEN pm.meta_key = 'Uhrzeit von' THEN pm.meta_value END) AS Uhrzeit_von,
				MAX(CASE WHEN pm.meta_key = 'Uhrzeit bis' THEN pm.meta_value END) AS Uhrzeit_bis,
				MAX(CASE WHEN pm.meta_key = 'Uhrzeit bis Edit' THEN pm.meta_value END) AS Uhrzeit_bis_Edit, 
				MAX(CASE WHEN pm.meta_key = 'token' THEN pm.meta_value END) AS Token,
				MAX(CASE WHEN pm.meta_key = '_billing_first_name' THEN pm.meta_value END) AS Vorname,
				MAX(CASE WHEN pm.meta_key = '_billing_last_name' THEN pm.meta_value END) AS Nachname,
				MAX(CASE WHEN pm.meta_key = '_billing_email' THEN pm.meta_value END) AS Email,
				MAX(CASE WHEN pm.meta_key = 'Personenanzahl' THEN pm.meta_value END) AS Personenanzahl,
				MAX(CASE WHEN pm.meta_key = 'Kennzeichen' THEN pm.meta_value END) AS Kennzeichen,
				MAX(CASE WHEN pm.meta_key = 'Parkplatz' THEN pm.meta_value END) AS Parkplatz,
				MAX(CASE WHEN pm.meta_key = 'Rückflugnummer' THEN pm.meta_value END) AS Rückflugnummer,
				MAX(CASE WHEN pm.meta_key = 'RückflugnummerEdit' THEN pm.meta_value END) AS RückflugnummerEdit,
				MAX(CASE WHEN pm.meta_key = 'Hinflugnummer' THEN pm.meta_value END) AS Hinflugnummer,
				MAX(CASE WHEN pm.meta_key = 'Fahrzeughersteller' THEN pm.meta_value END) AS Fahrzeughersteller,
				MAX(CASE WHEN pm.meta_key = 'FahrerAn' THEN pm.meta_value END) AS FahrerAn,
				MAX(CASE WHEN pm.meta_key = 'FahrerAb' THEN pm.meta_value END) AS FahrerAb,
				MAX(CASE WHEN pm.meta_key = 'Sonstige 1' THEN pm.meta_value END) AS Sonstige_1,
				MAX(CASE WHEN pm.meta_key = 'Sonstige 2' THEN pm.meta_value END) AS Sonstige_2,
				MAX(CASE WHEN pm.meta_key = 'editByArr' THEN pm.meta_value END) AS editByArr,
				MAX(CASE WHEN pm.meta_key = 'editByRet' THEN pm.meta_value END) AS editByRet,
				MAX(CASE WHEN pm.meta_key = '_order_total' THEN pm.meta_value END) AS Betrag,
				MAX(CASE WHEN pm.meta_key = '_payment_method_title' THEN pm.meta_value END) AS Bezahlmethode
							
			FROM " . self::$prefix_DB . "posts p
			LEFT JOIN " . self::$prefix_DB . "postmeta pm
				ON pm.post_id = p.ID
			INNER JOIN " . self::$prefix . "hotel_transfers ht ON ht.order_id = p.ID
			INNER JOIN " . self::$prefix . "parklots pl ON pl.product_id = ht.product_id
			WHERE
				p.post_type = 'shop_order' 
				".$status.$booking.$type."		
			GROUP BY p.ID 	
				
				" . $having;
		}
		else
			$sql2 = "";
		return self::$db->get_results($sql . $sql2 . $orderBy);
    }	
	
	static function getTime_Bookings($date, $val)
    {
		if($val == 'kf')
			$days = "DATEDIFF((SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Anreisedatum'), p.post_date) <= 14 ";
		elseif($val == 'mf')
			$days = "(DATEDIFF((SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Anreisedatum'), p.post_date) > 14 AND DATEDIFF((SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Anreisedatum'), p.post_date) < 90 ) ";
		elseif($val == 'lf')
			$days = "DATEDIFF((SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Anreisedatum'), p.post_date) > 90 "; 
		
		return self::$db->get_results("
		SELECT p.ID AS order_id, p.post_date AS Buchungsdatum, p.post_status AS Status, pl.parklot_short AS Code, pl.color AS Color, pl.is_for AS is_for, o.product_id AS Produkt,
		(SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Anreisedatum') AS Anreisedatum,
		(SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Uhrzeit von') AS Uhrzeit_von,
		(SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Abreisedatum') AS Abreisedatum,
		(SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Uhrzeit bis') AS Uhrzeit_bis,
		(SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'token') AS Token,
		(SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = '_billing_first_name') AS Vorname,
		(SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = '_billing_last_name') AS Nachname,
		(SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Personenanzahl') AS Personenanzahl,
		(SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = '_billing_email') AS Email,
		(SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = '_payment_method_title') AS Bezahlmethode,
		(SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = '_order_total') AS Betrag,
		DATEDIFF((SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Anreisedatum'), p.post_date) AS Tage

		FROM " . self::$prefix_DB . "posts p

		INNER JOIN " . self::$prefix . "orders o
		ON o.order_id = p.ID
		INNER JOIN " . self::$prefix . "parklots pl
		ON pl.product_id = o.product_id
		WHERE p.post_type = 'shop_order'
		AND p.post_status = 'wc-processing'
		AND DATE(p.post_date) = '".$date."'
		AND ".$days." 
		GROUP BY p.ID ORDER BY Tage DESC");
	}
	
	static function statistic_getTime_Bookings($year, $val)
    {
		if($val == 'kf')
			$days = "DATEDIFF((SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Anreisedatum'), p.post_date) <= 14 ";
		elseif($val == 'mf')
			$days = "(DATEDIFF((SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Anreisedatum'), p.post_date) > 14 AND DATEDIFF((SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Anreisedatum'), p.post_date) < 90 ) ";
		elseif($val == 'lf')
			$days = "DATEDIFF((SELECT pm.meta_value FROM " . self::$prefix_DB . "postmeta pm WHERE pm.post_id = p.ID AND pm.meta_key = 'Anreisedatum'), p.post_date) > 90 "; 
		
		return self::$db->get_results("
		SELECT COUNT(MONTH(p.post_date)) AS Anzahl, YEAR(p.post_date) AS Jahr, MONTH(p.post_date) AS Monat

		FROM " . self::$prefix_DB . "posts p
		INNER JOIN " . self::$prefix . "orders o
		ON o.order_id = p.ID
		INNER JOIN " . self::$prefix . "parklots pl
		ON pl.product_id = o.product_id
		WHERE p.post_type = 'shop_order'
		AND p.post_status = 'wc-processing'
		AND YEAR(p.post_date)= ".$year." AND MONTH(p.post_date) BETWEEN 1 AND 12
		AND ".$days."
		GROUP BY MONTH(p.post_date)
		ORDER BY MONTH(p.post_date)");
	}

	static function statistic_getBookings($year, $status)
    {	
		if($status == 'processing')
			$bStatus = "AND  o.deleted = 0 AND p.post_status = 'wc-processing'";
		elseif($status == 'cancelled')
			$bStatus = "AND (o.deleted = 0 OR o.deleted = 1) AND p.post_status = 'wc-cancelled'";
		else
			$bStatus = "AND o.deleted = 0 AND p.post_status = 'wc-processing'";
	
		return self::$db->get_results("
		SELECT COUNT(MONTH(p.post_date)) AS Anzahl, YEAR(p.post_date) AS Jahr, MONTH(p.post_date) AS Monat, SUM(o.b_total) AS Barzahlung, SUM(o.m_v_total) AS Kreditkarte

		FROM " . self::$prefix_DB . "posts p
		INNER JOIN " . self::$prefix . "orders o
		ON o.order_id = p.ID
		INNER JOIN " . self::$prefix . "parklots pl
		ON pl.product_id = o.product_id
		WHERE p.post_type = 'shop_order'
		".$bStatus."
		AND YEAR(p.post_date)= ".$year." AND MONTH(p.post_date) BETWEEN 1 AND 12
		GROUP BY MONTH(p.post_date)
		ORDER BY MONTH(p.post_date)");
	}	

	static function getRatings($product_id)
    {
		return self::$db->get_results("
			SELECT cm.meta_value AS Rating, p.parklot AS Product, p.product_id, cmc.meta_value AS Kat FROM " . self::$prefix_DB . "commentmeta cm
			LEFT JOIN " . self::$prefix_DB . "comments c ON c.comment_ID = cm.comment_id
			LEFT JOIN " . self::$prefix . "parklots p ON p.product_id = c.comment_post_ID 
            LEFT JOIN " . self::$prefix_DB . "commentmeta cmc ON cmc.comment_id = c.comment_ID 
			WHERE c.comment_post_ID = " . $product_id . " AND cm.meta_key = 'rating' AND cmc.meta_key = 'reviewx_rating'
		");
	}
	static function getRatingsCountMails($product_id)
    {
		return self::$db->get_row("
			SELECT COUNT(o.id) AS Anzahl FROM " . self::$prefix . "orders o 
			WHERE o.sent_reviewmail = 1 AND o.product_id = " . $product_id . "
		");
	}
	
	// Benutzer abrufen
	static function getUser_einsatzplan(){
		return self::$db->get_results("SELECT * FROM " . self::$prefix . "einsatzplan_positions order by position");
    }
	// Aktive Benutzer abrufen
	static function getActivUser_einsatzplan(){
		return self::$db->get_results("SELECT * FROM " . self::$prefix . "einsatzplan_positions where status = 0 order by position");
    }
	
	// Einsatzplan Benutzer eintragen
    static function addUser_einsatzplan($user, $data){
		$last_position = self::$db->get_row("SELECT count(id) as zahl FROM " . self::$prefix . "einsatzplan_positions;");
		return self::$db->insert(self::$prefix . 'einsatzplan_positions', [
		'position' => $last_position->zahl,
		'user_id' => $user,
		'role' => $data['role'],
		'status' => 0
		]);
    }
	
	// Einsatzplan Benutzer deaktivieren
	static function disableUser_einsatzplan($id){
		$role = self::$db->get_row("SELECT role FROM " . self::$prefix . "einsatzplan_positions where user_id = $id;");
        $u = new WP_User( $id );
		$u->remove_role( $role->role );
		$u->add_role( 'disabled' );
		return self::$db->update(self::$prefix . 'einsatzplan_positions', [
		'status' => 1
		], ['user_id' => $id]);
    }
	
	// Einsatzplan Benutzer aktivieren
	static function activateUser_einsatzplan($id){
		$role = self::$db->get_row("SELECT role FROM " . self::$prefix . "einsatzplan_positions where user_id = $id;");
        $u = new WP_User( $id );
		$u->remove_role( 'disabled' );
		$u->add_role( $role->role );
		return self::$db->update(self::$prefix . 'einsatzplan_positions', [
		'status' => 0
		], ['user_id' => $id]);
    }

	// Einsatzplan löschen
    static function delete_einsatzplan($kw, $year){
        return self::$db->delete(self::$prefix . 'einsatzplan', ['kw' => $kw, 'year' => $year]);
    }
	// Einsatzplan eintragen
    static function add_einsatzplan($kw, $year, $data){
        return self::$db->insert(self::$prefix . 'einsatzplan', [
		'kw' => $kw,
		'user_id' => $data->fahrer_id,
		'year' => $year,
		'bus_mo' => $data->bus_mo,
		'mo' => $data->mo,
		'mo_pause' => $data->mo_pause,
		'bus_di' => $data->bus_di,
		'di' => $data->di,
		'di_pause' => $data->di_pause,
		'bus_mi' => $data->bus_mi,
		'mi' => $data->mi,
		'mi_pause' => $data->mi_pause,
		'bus_do' => $data->bus_do,
		'do' => $data->do,
		'do_pause' => $data->do_pause,
		'bus_fr' => $data->bus_fr,
		'fr' => $data->fr,
		'fr_pause' => $data->fr_pause,
		'bus_sa' => $data->bus_sa,
		'sa' => $data->sa,
		'sa_pause' => $data->sa_pause,
		'bus_so' => $data->bus_so,
		'so' => $data->so,
		'so_pause' => $data->so_pause
		
		]);
    }
	
	// get Einsatzplan
    static function getEinsatzplanByUserID($kw, $year, $user_id){
        return self::$db->get_row("SELECT * FROM " . self::$prefix . "einsatzplan WHERE kw = $kw AND year = $year AND user_id = $user_id;");
    }
	
	// get Einsatzplan am Tag Urlaubsplan
    static function getEinsatzplanByUserIDandDay($kw, $year, $wt, $user_id){
        return self::$db->get_row("SELECT $wt FROM " . self::$prefix . "einsatzplan WHERE kw = $kw AND year = $year AND user_id = $user_id;");
    }
	
	// Einsatzplan ordnen
	static function order_einsatzplan($data){		
		foreach($data['position'] as $position => $user_id){
			self::$db->update(self::$prefix . 'einsatzplan_positions', [
				'position' => $position
				], ['user_id' => $user_id]);
		}	
    }
	
	// Urlaubsplanung eintragen
	static function add_urlaubsplanung($data){
		// check of exist
		$row = self::$db->get_row("SELECT * FROM " . self::$prefix . "einsatzplan WHERE kw = ".$data['kw']." AND year = ".$data['year']." AND user_id = ".$data['user_id']);
		
		if($row){
			return self::$db->update(self::$prefix . 'einsatzplan', [
				$data['wochentag'] => $data['eintrag']
				], ['user_id' => $data['user_id'], 'kw' => $data['kw'], 'year' => $data['year']]);
		}
		else{
			return self::$db->insert(self::$prefix . 'einsatzplan', [
				'kw' => $data['kw'],
				'user_id' => $data['user_id'],
				'year' => $data['year'],
				$data['wochentag'] => $data['eintrag']		
				]);
		}
	}
	
	// get Urlaubstage
    static function getUrlaubstage($user_id, $year){
        $u1 = self::$db->get_row("
		SELECT SUM(U1) AS U1 FROM (    
			SELECT count(mo) as 'U1' FROM " . self::$prefix . "einsatzplan WHERE user_id = $user_id and year = $year and (mo = 'U' OR mo = 'U1')
			UNION ALL
			SELECT count(di) as 'U1' FROM " . self::$prefix . "einsatzplan WHERE user_id = $user_id and year = $year and (di = 'U' OR di = 'U1')
			UNION ALL
			SELECT count(mi) as 'U1' FROM " . self::$prefix . "einsatzplan WHERE user_id = $user_id and year = $year and (mi = 'U' OR mi = 'U1')
			UNION ALL
			SELECT count(do) as 'U1' FROM " . self::$prefix . "einsatzplan WHERE user_id = $user_id and year = $year and (do = 'U' OR do = 'U1')
			UNION ALL
			SELECT count(fr) as 'U1' FROM " . self::$prefix . "einsatzplan WHERE user_id = $user_id and year = $year and (fr = 'U' OR fr = 'U1')
		) U1
		");
		
		$u05 = self::$db->get_row("
		SELECT SUM(U05) AS U05 FROM (    
			SELECT count(mo) as 'U05' FROM " . self::$prefix . "einsatzplan WHERE user_id = $user_id and year = $year and mo = 'U05'
			UNION ALL
			SELECT count(di) as 'U05' FROM " . self::$prefix . "einsatzplan WHERE user_id = $user_id and year = $year and di = 'U05'
			UNION ALL
			SELECT count(mi) as 'U05' FROM " . self::$prefix . "einsatzplan WHERE user_id = $user_id and year = $year and mi = 'U05'
			UNION ALL
			SELECT count(do) as 'U05' FROM " . self::$prefix . "einsatzplan WHERE user_id = $user_id and year = $year and do = 'U05'
			UNION ALL
			SELECT count(fr) as 'U05' FROM " . self::$prefix . "einsatzplan WHERE user_id = $user_id and year = $year and fr = 'U05'
		) U1
		");
		
		if($u1->U1 == null)
			$u1->U1 = 0;
		if($u05->U05 == null)
			$u05->U05 = 0;
		
		return $u1->U1 + ($u05->U05 / 2);
    }
	
	
	
	static function saveOrderFomAPS($data, $lot)
    {
		if($lot == 'aps'){
			if($data['id'] == 2){
				$data['product'] = 537;
				$commission = '0.00';
			}				
			elseif($data['id'] == 24){
				$data['product'] = 592;
				$commission = '0.00';
			}				
			elseif($data['id'] == 25){
				$data['product'] = 619;
				$commission = '0.00';
			}				
			/*elseif($data['id'] == 12)
				$data['product'] = 621;
			elseif($data['id'] == 11)
				$data['product'] = 624;*/
			elseif($data['id'] == 1){
				$data['product'] = 873;
				$commission = '0.00';
			}				
			else
				return false;
		}
		elseif($lot == 'aps_ext'){

			$order_id = self::$db->get_row("
				SELECT pm.post_id
				FROM 59hkh_postmeta pm 
				WHERE pm.meta_key = 'token' and pm.meta_value = '" . trim($data["bookingCode"]) . "'
				");
			if($order_id->post_id != null){
				wp_mail('it@airport-parking-stuttgart.de', 'HEX Umbuchung', print_r($data, true), $headers);
				self::updateOrderFomHEX($data);
				return false;
			}
			
			if($data['internalADPrefix'] == 'STR4' || $data['internalADPrefix'] == 'STR8' || $data['internalADPrefix'] == 'STR2' || $data['internalADPrefix'] == 'STB2' || $data['internalADPrefix'] == 'STB1'){
				$data['product'] = 621;
				$productSQL = self::getParklotByProductId($data['product']);
				if($data['internalADPrefix'] == 'STR4' || $data['internalADPrefix'] == 'STR8' || $data['internalADPrefix'] == 'STB2' || $data['internalADPrefix'] == 'STB1'){
					$data['totalParkingCosts'] = $data['totalParkingCosts'] / (100 - $productSQL->commision) * 100;
					$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * $productSQL->commision;
				}
				if($data['internalADPrefix'] == 'STR2'){
					$data['totalParkingCosts'] = $data['totalParkingCosts'] / (100 - $productSQL->commision_ws) * 100;
					$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * $productSQL->commision_ws;
				}
			}
			elseif($data['internalADPrefix'] == 'STRH'){
				$data['product'] = 683;
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
			}
			elseif($data['internalADPrefix'] == 'STR6' || $data['internalADPrefix'] == 'STR7' || $data['internalADPrefix'] == 'STRD' || $data['internalADPrefix'] == 'STR0'){
				$data['product'] = 624;
				if($data['internalADPrefix'] == 'STR6' || $data['internalADPrefix'] == 'STR7' || $data['internalADPrefix'] == 'STRD'){
					$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
					$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
				}
				if($data['internalADPrefix'] == 'STR0'){
					$data['totalParkingCosts'] = $data['totalParkingCosts'] / 75 * 100;
					$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 25;
				}
			}
			elseif($data['internalADPrefix'] == 'STR1' || $data['internalADPrefix'] == 'STR9' || $data['internalADPrefix'] == 'STRW'){
				$data['product'] = 901;
				if($data['internalADPrefix'] == 'STR1' || $data['internalADPrefix'] == 'STR9'){
					$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
					$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
				}
				if($data['internalADPrefix'] == 'STRW'){
					$data['totalParkingCosts'] = $data['totalParkingCosts'] / 75 * 100;
					$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 25;
				}
			}
			elseif($data['internalADPrefix'] == 'ST10' || $data['internalADPrefix'] == 'ST11' || $data['internalADPrefix'] == 'ST12'){
				$data['product'] = 24261;
				if($data['internalADPrefix'] == 'ST10' || $data['internalADPrefix'] == 'ST11'){
					$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
					$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
				}
				if($data['internalADPrefix'] == 'ST12'){
					$data['totalParkingCosts'] = $data['totalParkingCosts'] / 75 * 100;
					$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 25;
				}
			}
			elseif($data['internalADPrefix'] == 'ST13' || $data['internalADPrefix'] == 'ST14' || $data['internalADPrefix'] == 'ST15'){
				$data['product'] = 24263;
				if($data['internalADPrefix'] == 'ST13' || $data['internalADPrefix'] == 'ST14'){
					$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
					$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
				}
				if($data['internalADPrefix'] == 'ST15'){
					$data['totalParkingCosts'] = $data['totalParkingCosts'] / 75 * 100;
					$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 25;
				}
			}
			elseif($data['internalADPrefix'] == 'ST16'){
				$data['product'] = 24609;
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
			}
			elseif($data['internalADPrefix'] == 'STRI'){
				$data['product'] = 28878;
				$productSQL = self::getParklotByProductId($data['product']);
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / (100 - $productSQL->commision) * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * $productSQL->commision;
				wp_mail('it@airport-parking-stuttgart.de', 'T1', $data['totalParkingCosts'] . ' ' . $commission, $headers);
			}
			else{
				wp_mail('it@airport-parking-stuttgart.de', 'HEX Fehler Produktzuordnung', print_r($data, true), $headers);
				return false;
			}
			
			if($data['bookingState'] != 'N' && $data['bookingState'] != 'CHANGED'){
				wp_mail('it@airport-parking-stuttgart.de', 'HEX Fehler Status', print_r($data, true), $headers);
				return false;
			}
		}
		else
			return false;
		
		if($data['internalADPrefix'] != null)
			$code = $data['internalADPrefix'];
		else
			$code = "";
        $parklot = new Parklot($data['product']);
		$productSQL = self::getParklotByProductId($data['product']);
        $product = wc_get_product($data['product']);
        // set new product price
        $product->set_price($data['totalParkingCosts']);

        $order = Orders::createWCOrder($product);
        $order_id = $order->get_id();

        // add a bunch of meta data
		
		if($data["paymentOptions"] == "Barzahlung"){
			add_post_meta($order_id, '_transaction_id', 'barzahlung', true);
			add_post_meta($order_id, '_payment_method_title', 'Barzahlung', true);
			$barzahlung = $data['totalParkingCosts'];
			$mv = 0;
		}
		elseif($data["paymentOptions"] == "M"){
			add_post_meta($order_id, '_transaction_id', 'm', true);
			add_post_meta($order_id, '_payment_method_title', 'MasterCard', true);
			$barzahlung = 0;
			$mv = $data['totalParkingCosts'];
		}
		elseif($data["paymentOptions"] == "V"){
			add_post_meta($order_id, '_transaction_id', 'v', true);
			add_post_meta($order_id, '_payment_method_title', 'Visa', true);
			$barzahlung = 0;
			$mv = $data['totalParkingCosts'];
		}

        add_post_meta($order_id, '_order_total', '', true);
        add_post_meta($order_id, '_customer_user', 6, true);
        add_post_meta($order_id, '_completed_date', '', true);
        add_post_meta($order_id, '_order_currency', '', true);
        add_post_meta($order_id, '_paid_date', '', true);

        add_post_meta($order_id, '_billing_address_1', '', true);
        add_post_meta($order_id, '_billing_city', '', true);
        add_post_meta($order_id, '_billing_state', '', true);
        add_post_meta($order_id, '_billing_postcode', '', true);
        add_post_meta($order_id, '_billing_company', '', true);
        add_post_meta($order_id, '_billing_country', '', true);
        add_post_meta($order_id, '_billing_email', $data["eMail"], true);
        add_post_meta($order_id, '_billing_first_name', $data["firstName"], true);
        add_post_meta($order_id, '_billing_last_name', $data["lastName"], true);
        add_post_meta($order_id, '_billing_phone', $data["mobileNumber"], true);

        add_post_meta($order_id, 'Anreisedatum', dateFormat($data["arrivalDate"]), true);
        add_post_meta($order_id, 'first_anreisedatum', dateFormat($data["arrivalDate"]), true);
        add_post_meta($order_id, 'Abreisedatum', dateFormat($data["departureDate"]), true);
        add_post_meta($order_id, 'first_abreisedatum', dateFormat($data["departureDate"]), true);
        add_post_meta($order_id, 'Uhrzeit von', $data["arrivalTime"], true);
        add_post_meta($order_id, 'Uhrzeit bis', $data["departureTime"], true);
        add_post_meta($order_id, 'Hinflugnummer', $data["outboundFlightNumber"], true);
        add_post_meta($order_id, 'Rückflugnummer', $data["returnFlightNumber"], true);
        add_post_meta($order_id, 'Personenanzahl', $data["countTravellers"], true);
        add_post_meta($order_id, 'Kennzeichen', $data["license_plate"], true);		
		add_post_meta($order_id, 'Fahrzeughersteller', $data["vehicleBrand"], true);      
		add_post_meta($order_id, 'Fahrzeugmodell', $data["vehicleModel"], true);
        add_post_meta($order_id, 'Fahrzeugfarbe', $data["vehicleColor"], true);
        
		if($data["salutation"] != null){
			if($data["salutation"] == "Mr")
				add_post_meta($order_id, 'Anrede', 'Herr', true);
			elseif($data["salutation"] == "Ms")
				add_post_meta($order_id, 'Anrede', 'Frau', true);
		}
		
		$date1 = DateTime::createFromFormat('h:i', '00:00');
		$date2 = DateTime::createFromFormat('h:i', '06:00');
		$arr_time = DateTime::createFromFormat('h:i', $data["arrivalTime"]);

		if($productSQL->type == 'valet'){
			if ($arr_time >= $date1 && $arr_time <= $date2)
			{
				add_post_meta($order_id, 'Sonstige 1', '+ 30€', true);
			}
		}

        update_post_meta($order_id, 'token', $data["bookingCode"]);

		if($data['outboundFlightNumber'] == null)
			$data['outboundFlightNumber'] = "";
		if($data['returnFlightNumber'] == null)
			$data['returnFlightNumber'] = "";
				
        self::$db->insert(self::$prefix . 'orders', [
            'date_from' => date('Y-m-d H:i', strtotime($data["arrivalDate"] . ' ' . $data["arrivalTime"])),
            'date_to' => date('Y-m-d H:i', strtotime($data["departureDate"] . ' ' . $data["departureTime"])),
            'product_id' => $data['product'],
            'order_id' => $order_id,
			'b_total' => $barzahlung,
			'm_v_total' => $mv,
            'out_flight_number' => $data['outboundFlightNumber'],
            'return_flight_number' => $data['returnFlightNumber'],
            'nr_people' => $data['countTravellers'],
			'code' => $code
        ]);
		
		
		// Save commission to booking metaphone
		self::saveBookingMeta($order_id, 'provision', $commission);


		/*
		$provision = self::getBrokerCommissionForBooking($data['product'], date('Y-m-d', strtotime($data["arrivalDate"])));
		if($provision->commission_type != "" || $provision->commission_type != null){
			if($provision->commission_type == 'netto'){
				$netto = number_format((float)($data['totalParkingCosts'] / 119 * 100), 2, '.', '');
				$amount = number_format((float)($netto / 100 * $provision->commission_value), 2, '.', '');
				self::saveBookingMeta($order_id, 'provision', $amount);
			}
			elseif($provision->commission_type == 'brutto'){
				$brutto = number_format((float)$data['totalParkingCosts'], 2, '.', '');
				$amount = number_format((float)($brutto / 100 * $provision->commission_value), 2, '.', '');
				self::saveBookingMeta($order_id, 'provision', $amount);
			}
		}
		else{
			self::saveBookingMeta($order_id, 'provision', '0.00');
		}
		*/
		self::checkBookingDate($order_id);
    }
	
	static function updateOrderFomHEX($data)
    {
		
		$order_id = self::$db->get_row("SELECT post_id FROM " . self::$prefix_DB . "postmeta WHERE meta_key = 'token' and meta_value = '" . $data['bookingCode'] . "'");
		$product = self::$db->get_row("SELECT product_id FROM " . self::$prefix . "orders WHERE order_id = '" . $order_id->post_id . "'");
		
		if($data['internalADPrefix'] == 'STR4' || $data['internalADPrefix'] == 'STR8' || $data['internalADPrefix'] == 'STR2' || $data['internalADPrefix'] == 'STB2' || $data['internalADPrefix'] == 'STB1'){
			$data['product'] = 621;
			if($data['internalADPrefix'] == 'STR4' || $data['internalADPrefix'] == 'STR8' || $data['internalADPrefix'] == 'STB2' || $data['internalADPrefix'] == 'STB1'){
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
			}
			if($data['internalADPrefix'] == 'STR2'){
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 75 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 25;
			}
		}
		elseif($data['internalADPrefix'] == 'STRH'){
			$data['product'] = 683;
			$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
			$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
		}
		elseif($data['internalADPrefix'] == 'STR6' || $data['internalADPrefix'] == 'STR7' || $data['internalADPrefix'] == 'STRD' || $data['internalADPrefix'] == 'STR0'){
			$data['product'] = 624;
			if($data['internalADPrefix'] == 'STR6' || $data['internalADPrefix'] == 'STR7' || $data['internalADPrefix'] == 'STRD'){
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
			}
			if($data['internalADPrefix'] == 'STR0'){
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 75 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 25;
			}
		}
		elseif($data['internalADPrefix'] == 'STR1' || $data['internalADPrefix'] == 'STR9' || $data['internalADPrefix'] == 'STRW'){
			$data['product'] = 901;
			if($data['internalADPrefix'] == 'STR1' || $data['internalADPrefix'] == 'STR9'){
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
			}
			if($data['internalADPrefix'] == 'STRW'){
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 75 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 25;
			}
		}
		elseif($data['internalADPrefix'] == 'ST10' || $data['internalADPrefix'] == 'ST11' || $data['internalADPrefix'] == 'ST12'){
			$data['product'] = 24261;
			if($data['internalADPrefix'] == 'ST10' || $data['internalADPrefix'] == 'ST11'){
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
			}
			if($data['internalADPrefix'] == 'ST12'){
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 75 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 25;
			}
		}
		elseif($data['internalADPrefix'] == 'ST13' || $data['internalADPrefix'] == 'ST14' || $data['internalADPrefix'] == 'ST15'){
			$data['product'] = 24263;
			if($data['internalADPrefix'] == 'ST13' || $data['internalADPrefix'] == 'ST14'){
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
			}
			if($data['internalADPrefix'] == 'ST15'){
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 75 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 25;
			}
		}
		elseif($data['internalADPrefix'] == 'ST16'){
			$data['product'] = 24609;
			$data['totalParkingCosts'] = $data['totalParkingCosts'] / 30 * 100;
			$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
		}
		elseif($data['internalADPrefix'] == 'STRI'){
			$data['product'] = 28878;
			$data['totalParkingCosts'] = $data['totalParkingCosts'] / 75 * 100;
			$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 25;
		}
		
		if($order_id->post_id == "" || $order_id->post_id == null)
			return false;
		
		$price = number_format((float)$data['totalParkingCosts'] / 119 * 100, 4, '.', '');
		$woo_order = wc_get_order($order_id->post_id);
		
		//if( 'cancelled' == $woo_order->get_status() || 'wc-cancelled' == $woo_order->get_status() ) {
			$woo_order->update_status( 'wc-processing' );
		//}
		
		$order_items = $woo_order->get_items();
		foreach ( $order_items as $order_item_id => $order_item) {
			wc_delete_order_item($order_item_id);
		}
		
		$woo_order->calculate_taxes();
		$woo_order->calculate_totals();
		$woo_order->save();
		
		$woo_order = wc_get_order($order_id->post_id);
		$woo_order->add_product( wc_get_product($data['product']), 1, [
				//'subtotal'     => $price, // e.g. 32.95
				'total'        => $price, // e.g. 32.95
			] );
		
		$woo_order->calculate_taxes();
		$woo_order->calculate_totals();
		$woo_order->save();
		
		if(get_post_meta($order_id->post_id, '_payment_method_title')[0] == 'Barzahlung'){
			$barzahlung = $data['totalParkingCosts'];
			$mv = 0;
		}			
		else{
			$barzahlung = 0;
			$mv = $data['totalParkingCosts'];
		}
		
		self::$db->update(self::$prefix_DB . 'wc_order_product_lookup', [
			'product_net_revenue' => ($data['totalParkingCosts'] / 119 * 100),
			'product_gross_revenue' => ($data['totalParkingCosts']),
			'tax_amount' => ($data['totalParkingCosts'] / 119 * 19)
		], ['order_id' => $order_id->post_id]);

        // add a bunch of meta data
		update_post_meta($order_id->post_id, '_billing_first_name', $data["firstName"]);
        update_post_meta($order_id->post_id, '_billing_last_name', $data["lastName"]);
		update_post_meta($order_id->post_id, '_billing_phone', $data["mobileNumber"]);
		update_post_meta($order_id->post_id, '_billing_email', $data["eMail"]);
        update_post_meta($order_id->post_id, 'Anreisedatum', dateFormat($data["arrivalDate"]));
        update_post_meta($order_id->post_id, 'first_anreisedatum', dateFormat($data["arrivalDate"]));
        update_post_meta($order_id->post_id, 'Abreisedatum', dateFormat($data["departureDate"]));
        update_post_meta($order_id->post_id, 'first_abreisedatum', dateFormat($data["departureDate"]));
        update_post_meta($order_id->post_id, 'Uhrzeit von', $data["arrivalTime"]);
        update_post_meta($order_id->post_id, 'Uhrzeit bis', $data["departureTime"]);
        update_post_meta($order_id->post_id, 'Hinflugnummer', $data["outboundFlightNumber"]);
        update_post_meta($order_id->post_id, 'Rückflugnummer', $data["returnFlightNumber"]);
        update_post_meta($order_id->post_id, 'Personenanzahl', $data["countTravellers"]);
        update_post_meta($order_id->post_id, 'Kennzeichen', $data["license_plate"]);

		update_post_meta($order_id->post_id, 'Fahrzeughersteller', $data["vehicleBrand"]);      
		update_post_meta($order_id->post_id, 'Fahrzeugmodell', $data["vehicleModel"]);
        update_post_meta($order_id->post_id, 'Fahrzeugfarbe', $data["vehicleColor"]);

		if($data['outboundFlightNumber'] == null)
			$data['outboundFlightNumber'] = "";
		if($data['returnFlightNumber'] == null)
			$data['returnFlightNumber'] = "";
				
        self::$db->update(self::$prefix . 'orders', [
            'date_from' => date('Y-m-d H:i', strtotime($data["arrivalDate"] . ' ' . $data["arrivalTime"])),
            'date_to' => date('Y-m-d H:i', strtotime($data["departureDate"] . ' ' . $data["departureTime"])),
			'product_id' => $data['product'],
            'out_flight_number' => $data['outboundFlightNumber'],
            'return_flight_number' => $data['returnFlightNumber'],
			'b_total' => $barzahlung,
			'm_v_total' => $mv,
            'nr_people' => $data['countTravellers'],
			'code' => $data['internalADPrefix']
        ], ['order_id' => $order_id->post_id]);
		
		$woo_order = wc_get_order($order_id->post_id);
		$woo_order->calculate_taxes();
		$woo_order->calculate_totals();
		$woo_order->save();
				
		// Save commission to booking metaphone		
		self::updateBookingMeta($order_id->post_id, 'provision', $commission);
		self::checkBookingDate($order_id->post_id);
    }
	
    static function saveOrderFomAPG($data, $lot)
    {
		if($data['product_id'] == 812)
			$data['product'] = 595;
		elseif($data['product_id'] == 1054)
			$data['product'] = 3080;
		elseif($data['product_id'] == 1645)
			$data['product'] = 3081;
		elseif($data['product_id'] == 1740)
			$data['product'] = 24224;
		elseif($data['product_id'] == 2199)
			$data['product'] = 3082;			
		elseif($data['product_id'] == 4655)
			$data['product'] = 24228;

		elseif($data['product_id'] == 5949)
			$data['product'] = 41577;
		elseif($data['product_id'] == 5951)
			$data['product'] = 41581;
		elseif($data['product_id'] == 5953)
			$data['product'] = 41584;
		elseif($data['product_id'] == 5955)
			$data['product'] = 41582;
		elseif($data['product_id'] == 5957)
			$data['product'] = 41585;
		elseif($data['product_id'] == 5958)
			$data['product'] = 41580;
			
		else
			return false;
	
		
        $parklot = new Parklot($data['product']);

        $product = wc_get_product($data['product']);
        // set new product price
        $product->set_price($data['price']);


        $order = Orders::createWCOrder($product);
        $order_id = $order->get_id();
		
		if($data['payment_method_title'] == 'Barzahlung'){
			$payment_method = 'Barzahlung';
			$transaction_id = 'barzahlung';
			$barzahlung = $data['price'];
			$mv = 0;
		}			
		else{
			$payment_method = 'PayPal / Kreditkarte';
			$transaction_id = 'paypal';
			$barzahlung = 0;
			$mv = $data['price'];
		}

        // add a bunch of meta data
		add_post_meta($order_id, '_transaction_id', $transaction_id, true);
        add_post_meta($order_id, '_payment_method_title', $payment_method, true);
        add_post_meta($order_id, '_order_total', '', true);
        add_post_meta($order_id, '_customer_user', 6, true);
        add_post_meta($order_id, '_completed_date', '', true);
        add_post_meta($order_id, '_order_currency', '', true);
        add_post_meta($order_id, '_paid_date', '', true);

        add_post_meta($order_id, '_billing_address_1', $data['billing_address_1'], true);
        add_post_meta($order_id, '_billing_city', $data['billing_city'], true);
        add_post_meta($order_id, '_billing_state', '', true);
        add_post_meta($order_id, '_billing_postcode', $data['billing_postcode'], true);
        add_post_meta($order_id, '_billing_company', $data['billing_company'], true);
        add_post_meta($order_id, '_billing_country', $data['billing_country'], true);
        add_post_meta($order_id, '_billing_email', $data["billing_email"], true);
        add_post_meta($order_id, '_billing_first_name', $data["billing_first_name"], true);
        add_post_meta($order_id, '_billing_last_name', $data["billing_last_name"], true);
        add_post_meta($order_id, '_billing_phone', $data["billing_phone"], true);

        add_post_meta($order_id, 'Anreisedatum', dateFormat($data["startDateOnly"]), true);
        add_post_meta($order_id, 'first_anreisedatum', dateFormat($data["startDateOnly"]), true);
        add_post_meta($order_id, 'Abreisedatum', dateFormat($data["endDateOnly"]), true);
        add_post_meta($order_id, 'first_abreisedatum', dateFormat($data["endDateOnly"]), true);
        add_post_meta($order_id, 'Uhrzeit von', $data["order_time_from"], true);
        add_post_meta($order_id, 'Uhrzeit bis', $data["order_time_to"], true);
        add_post_meta($order_id, 'Hinflugnummer', $data["hinflug"], true);
        add_post_meta($order_id, 'Rückflugnummer', $data["ruckflug"], true);
        add_post_meta($order_id, 'Personenanzahl', $data["persons_nr"], true);
        add_post_meta($order_id, 'Fahrzeughersteller', $data["car_model"], true);
        add_post_meta($order_id, 'Fahrzeugmodell', $data["car_typ"], true);
        add_post_meta($order_id, 'Fahrzeugfarbe', $data["car_color"], true);
        add_post_meta($order_id, 'Kennzeichen', $data["kfz_kennzeichen"], true);

        update_post_meta($order_id, 'token', $data["bookingCode"]);

		if($data['hinflug'] == null)
			$data['hinflug'] = "";
		if($data['ruckflug'] == null)
			$data['ruckflug'] = "";
				
        self::$db->insert(self::$prefix . 'orders', [
            'date_from' => date('Y-m-d H:i', strtotime($data["startDateOnly"] . ' ' . $data["order_time_from"])),
            'date_to' => date('Y-m-d H:i', strtotime($data["endDateOnly"] . ' ' . $data["order_time_to"])),
            'product_id' => $data['product'],
            'order_id' => $order_id,
			'b_total' => $barzahlung,
			'm_v_total' => $mv,
            'out_flight_number' => $data['hinflug'],
            'return_flight_number' => $data['ruckflug'],
            'nr_people' => $data['persons_nr']
        ]);
		
		// Save commission to booking metaphone
		$provision = self::getBrokerCommissionForBooking($data['product'], date('Y-m-d', strtotime($data["startDateOnly"])));
		if($provision->commission_type != "" || $provision->commission_type != null){
			if($provision->commission_type == 'netto'){
				$netto = number_format((float)($data['price'] / 119 * 100), 2, '.', '');
				$amount = number_format((float)($netto / 100 * $provision->commission_value), 2, '.', '');
				self::saveBookingMeta($order_id, 'provision', $amount);
			}
			elseif($provision->commission_type == 'brutto'){
				$brutto = number_format((float)$data['price'], 2, '.', '');
				$amount = number_format((float)($brutto / 100 * $provision->commission_value), 2, '.', '');
				self::saveBookingMeta($order_id, 'provision', $amount);
			}
		}
		else{
			self::saveBookingMeta($order_id, 'provision', '0.00');
		}
		self::checkBookingDate($order_id);
    }
	
	static function updateOrderFomAPG($data)
    {
		$order_id = self::$db->get_row("SELECT post_id FROM " . self::$prefix_DB . "postmeta WHERE meta_key = 'token' and meta_value = '" . $data['booking_token'] . "'");
		$product = self::$db->get_row("SELECT product_id FROM " . self::$prefix . "orders WHERE order_id = '" . $order_id->post_id . "'");
				
		if($order_id->post_id == "" || $order_id->post_id == null)
			return false;

		
		
		if($data['change'] == 1){
			$old_price = self::$db->get_row("SELECT meta_value AS oldPrice FROM " . self::$prefix_DB . "postmeta WHERE post_id = ".$order_id->post_id." AND meta_key = '_order_total'");
			$old_timeFrom = self::$db->get_row("SELECT meta_value AS old_timeFrom FROM " . self::$prefix_DB . "postmeta WHERE post_id = ".$order_id->post_id." AND meta_key = 'Uhrzeit von'");
			$old_timeTo = self::$db->get_row("SELECT meta_value AS old_timeTo FROM " . self::$prefix_DB . "postmeta WHERE post_id = ".$order_id->post_id." AND meta_key = 'Uhrzeit bis'");
			$price = number_format(($old_price->oldPrice + $data['betrag']) / 119 * 100,4, '.', '');
			$mv = number_format($old_price->oldPrice + $data['betrag'],2, '.', '');
			if(get_post_meta($order_id->post_id, 'Anreisedatum', true) < date('Y-m-d')){
				$d1 = strtotime(get_post_meta($order_id->post_id, 'first_abreisedatum', true));
				$d2 = strtotime(get_post_meta($order_id->post_id, 'Anreisedatum', true));
				$orig_days = round(($d1 - $d2) / 86400);
				
				$d3 = strtotime($data["dateTo"]);
				$d4 = strtotime($data["dateFrom"]);				
				$update_days = round(($d3 - $d4) / 86400);
				
				if($update_days > $orig_days){
					$comp_days = $update_days - $orig_days;
					$field = "+". $comp_days . "TAG " . ($comp_days * 10) . " €";				
					update_post_meta($order_id->post_id, 'Sonstige 2', $field);
				}
				else
					update_post_meta($order_id->post_id, 'Sonstige 2', '');			
			}
			
			self::$db->update(self::$prefix_DB . 'wc_order_product_lookup', [
				'product_net_revenue' => (float)$mv / 119 * 100,
				'product_gross_revenue' => (float)$mv,
				'tax_amount' => (float)$mv / 119 * 19
			], ['order_id' => $order_id->post_id]);
			
			update_post_meta($order_id->post_id, 'Anreisedatum', dateFormat($data['dateFrom']));
			update_post_meta($order_id->post_id, 'first_anreisedatum', dateFormat($data["dateFrom"]));
			update_post_meta($order_id->post_id, 'Abreisedatum', dateFormat($data["dateTo"]));
			update_post_meta($order_id->post_id, 'first_abreisedatum', dateFormat($data["dateTo"]));
			
			self::$db->update(self::$prefix . 'orders', [
				'date_from' => date('Y-m-d H:i', strtotime($data['dateFrom'] . ' ' . $old_timeFrom->old_timeFrom)),
				'date_to' => date('Y-m-d H:i', strtotime($data["dateTo"] . ' ' . $old_timeTo->old_timeTo)),
				'm_v_total' => $mv
			], ['order_id' => $order_id->post_id]);
			
			// Save commission to booking metaphone
			$provision = self::getBrokerCommissionForBooking($product->product_id, date('Y-m-d', strtotime($data["dateFrom"])));
			if($provision->commission_type != "" || $provision->commission_type != null){
				if($provision->commission_type == 'netto'){
					$netto = number_format((float)($mv / 119 * 100), 2, '.', '');
					$amount = number_format((float)($netto / 100 * $provision->commission_value), 2, '.', '');
					self::updateBookingMeta($order_id->post_id, 'provision', $amount);
				}
				elseif($provision->commission_type == 'brutto'){
					$brutto = number_format((float)$mv, 2, '.', '');
					$amount = number_format((float)($brutto / 100 * $provision->commission_value), 2, '.', '');
					self::updateBookingMeta($order_id->post_id, 'provision', $amount);
				}
			}
			else{
				self::updateBookingMeta($order_id->post_id, 'provision', '0.00');
			}

		}
		else{
			$price = number_format((float)$data['parking_update'] / 119 * 100,4, '.', '');
			$mv = $data['parking_update'];
			
			if(get_post_meta($order_id->post_id, 'Anreisedatum', true) < date('Y-m-d')){
				$d1 = strtotime(get_post_meta($order_id->post_id, 'Abreisedatum', true));
				$d2 = strtotime(get_post_meta($order_id->post_id, 'Anreisedatum', true));
				$orig_days = round(($d1 - $d2) / 86400);
				
				$d3 = strtotime($data["end-date"]);
				$d4 = strtotime($data["start-date"]);				
				$update_days = round(($d3 - $d4) / 86400);				
				
				if($update_days > $orig_days){
					$comp_days = $update_days - $orig_days;
					$field = "+". $comp_days . "TAG " . ($comp_days * 10) . " €";				
					update_post_meta($order_id->post_id, 'Sonstige 2', $field);
				}
				else
					update_post_meta($order_id->post_id, 'Sonstige 2', '');			
			}
			
			self::$db->update(self::$prefix_DB . 'wc_order_product_lookup', [
				'product_net_revenue' => (float)$data['parking_update'] / 119 * 100,
				'product_gross_revenue' => (float)$data['parking_update'],
				'tax_amount' => (float)$data['parking_update'] / 119 * 19
			], ['order_id' => $order_id->post_id]);
			
			update_post_meta($order_id->post_id, '_billing_first_name', $data["first_name"]);
			update_post_meta($order_id->post_id, '_billing_last_name', $data["last_name"]);
			update_post_meta($order_id->post_id, '_billing_phone', $data["phone_number"]);
			update_post_meta($order_id->post_id, '_billing_email', $data["mail_adress"]);
			update_post_meta($order_id->post_id, 'Anreisedatum', dateFormat($data["start-date"]));
			update_post_meta($order_id->post_id, 'first_anreisedatum', dateFormat($data["start-date"]));
			update_post_meta($order_id->post_id, 'Abreisedatum', dateFormat($data["end-date"]));
			update_post_meta($order_id->post_id, 'first_abreisedatum', dateFormat($data["end-date"]));
			update_post_meta($order_id->post_id, 'Uhrzeit von', $data["ar_time"]);
			update_post_meta($order_id->post_id, 'Uhrzeit bis', $data["de_time"]);
			update_post_meta($order_id->post_id, 'Hinflugnummer', $data["flight_departure"]);
			update_post_meta($order_id->post_id, 'Rückflugnummer', $data["flight_outbound"]);
			update_post_meta($order_id->post_id, 'Personenanzahl', $data["count_person"]);
			update_post_meta($order_id->post_id, 'Kennzeichen', $data["license_plate"]);		
			update_post_meta($order_id->post_id, 'Fahrzeughersteller', $data["car_model"]);
			update_post_meta($order_id->post_id, 'Fahrzeugmodell', $data["car_typ"]);
			update_post_meta($order_id->post_id, 'Fahrzeugfarbe', $data["car_color"]);
			
			if($data['flight_departure'] == null)
				$data['flight_departure'] = "";
			if($data['flight_outbound'] == null)
				$data['flight_outbound'] = "";
			
			if(get_post_meta($order_id->post_id, '_payment_method_title')[0] == 'Barzahlung'){
			$barzahlung = $data['parking_update'];
			$mv = 0;
			}			
			else{
				$barzahlung = 0;
			}
			
			self::$db->update(self::$prefix . 'orders', [
				'date_from' => date('Y-m-d H:i', strtotime($data["start-date"] . ' ' . $data["ar_time"])),
				'date_to' => date('Y-m-d H:i', strtotime($data["end-date"] . ' ' . $data["de_time"])),
				'out_flight_number' => $data['flight_departure'],
				'return_flight_number' => $data['flight_outbound'],
				'b_total' => $barzahlung,
				'm_v_total' => $mv,
				'nr_people' => $data['count_person']
			], ['order_id' => $order_id->post_id]);
			
			// Save commission to booking metaphone
			$provision = self::getBrokerCommissionForBooking($product->product_id, date('Y-m-d', strtotime($data["start-date"])));
			if($provision->commission_type != "" || $provision->commission_type != null){
				if($provision->commission_type == 'netto'){
					$netto = number_format((float)($data['parking_update'] / 119 * 100), 2, '.', '');
					$amount = number_format((float)($netto / 100 * $provision->commission_value), 2, '.', '');
					self::updateBookingMeta($order_id->post_id, 'provision', $amount);
				}
				elseif($provision->commission_type == 'brutto'){
					$brutto = number_format((float)$data['parking_update'], 2, '.', '');
					$amount = number_format((float)($brutto / 100 * $provision->commission_value), 2, '.', '');
					self::updateBookingMeta($order_id->post_id, 'provision', $amount);
				}
			}
			else{
				self::updateBookingMeta($order_id->post_id, 'provision', '0.00');
			}
		}
		
		$woo_order = wc_get_order($order_id->post_id);
		$order_items = $woo_order->get_items();
		foreach ( $order_items as $key => $value ) {
			$order_item_id = $key;
			   $product_value = $value->get_data();
			   $product_id    = $product_value['product_id']; 
		}
		$product = wc_get_product( $product_id );		
		$order_items[ $order_item_id ]->set_total( $price );
		$woo_order->calculate_taxes();
		$woo_order->calculate_totals();
		$woo_order->save();
		

		self::checkBookingDate($order_id->post_id);
    }
	
	static function saveOrderFomParkos($dataC)
    {
		$parkos = self::$db->get_row("SELECT order_id FROM " . self::$prefix . "orders WHERE code = '" . $dataC->code . "'");
		
		if($parkos->order_id != null)
			return false;
		
		if($dataC->merchant_id == 569){
			if($dataC->location_type == 'indoor')
				$data['product'] = 41402;
			else
				$data['product'] = 41403;
		}
		elseif($dataC->merchant_id == 1741){
			$data['product'] = 45856;
		}
		else
			return false;
		
		$commission = number_format(($dataC->total_price / 119 * 100) / 100 * 20, 2, ".", ".");
		
        $parklot = new Parklot($data['product']);
		$data['price'] = number_format($dataC->total_price, 2, ".", ".");
		$nameTemp = explode(" ",$dataC->name);
		$firstName = $nameTemp[0];
		$lastName = $nameTemp[1];
        $product = wc_get_product($data['product']);
        // set new product price
        $product->set_price($data['price']);


        $order = Orders::createWCOrder($product);
        $order_id = $order->get_id();
		
		$payment_method = 'MasterCard';
		$transaction_id = 'm';
		$barzahlung = 0;
		$mv = $data['price'];		

        // add a bunch of meta data
		add_post_meta($order_id, '_transaction_id', $transaction_id, true);
        add_post_meta($order_id, '_payment_method_title', $payment_method, true);
        add_post_meta($order_id, '_order_total', '', true);
        add_post_meta($order_id, '_customer_user', 6, true);
        add_post_meta($order_id, '_completed_date', '', true);
        add_post_meta($order_id, '_order_currency', '', true);
        add_post_meta($order_id, '_paid_date', '', true);

        add_post_meta($order_id, '_billing_address_1', '', true);
        add_post_meta($order_id, '_billing_city', '', true);
        add_post_meta($order_id, '_billing_state', '', true);
        add_post_meta($order_id, '_billing_postcode', '', true);
        add_post_meta($order_id, '_billing_company', '', true);
        add_post_meta($order_id, '_billing_country', '', true);
        add_post_meta($order_id, '_billing_email', '', true);
        add_post_meta($order_id, '_billing_first_name', $firstName, true);
        add_post_meta($order_id, '_billing_last_name', $lastName, true);
        add_post_meta($order_id, '_billing_phone', $dataC->phone, true);

        add_post_meta($order_id, 'Anreisedatum', dateFormat($dataC->arrival_date), true);
        add_post_meta($order_id, 'first_anreisedatum', dateFormat($dataC->arrival_date), true);
        add_post_meta($order_id, 'Abreisedatum', dateFormat($dataC->departure_date), true);
        add_post_meta($order_id, 'first_abreisedatum', dateFormat($dataC->departure_date), true);
        add_post_meta($order_id, 'Uhrzeit von', $dataC->arrival_time, true);
        add_post_meta($order_id, 'Uhrzeit bis', $dataC->departure_time, true);
        add_post_meta($order_id, 'Hinflugnummer', $dataC->flight_departure_nr, true);
        add_post_meta($order_id, 'Rückflugnummer', $dataC->flight_return_nr, true);
        add_post_meta($order_id, 'Personenanzahl', $dataC->persons, true);
        add_post_meta($order_id, 'Fahrzeughersteller', '', true);
        add_post_meta($order_id, 'Fahrzeugmodell', $dataC->car_brand_model, true);
        add_post_meta($order_id, 'Fahrzeugfarbe', '', true);
        add_post_meta($order_id, 'Kennzeichen', $dataC->car_license_plate, true);

        update_post_meta($order_id, 'token', $dataC->code);
		
		if($dataC->flight_departure_nr == null)
			$dataC->flight_departure_nr = "";
		if($dataC->flight_return_nr == null)
			$dataC->flight_return_nr = "";
				
        self::$db->insert(self::$prefix . 'orders', [
            'date_from' => date('Y-m-d H:i', strtotime($dataC->arrival_date . ' ' .  $dataC->arrival_time)),
            'date_to' => date('Y-m-d H:i', strtotime($dataC->departure_date . ' ' . $dataC->departure_time)),
            'product_id' => $data['product'],
            'order_id' => $order_id,
			'b_total' => $barzahlung,
			'm_v_total' => $mv,
            'out_flight_number' => $dataC->flight_departure_nr,
            'return_flight_number' => $dataC->flight_return_nr,
            'nr_people' => $dataC->persons,
			'code' => $dataC->code
        ]);
		self::saveBookingMeta($order_id, 'provision', $commission);
		
		/*
		// Save commission to booking metaphone
		$provision = self::getBrokerCommissionForBooking($data['product'], date('Y-m-d', strtotime($data["startDateOnly"])));
		if($provision->commission_type != "" || $provision->commission_type != null){
			if($provision->commission_type == 'netto'){
				$netto = number_format((float)($data['price'] / 119 * 100), 2, '.', '');
				$amount = number_format((float)($netto / 100 * $provision->commission_value), 2, '.', '');
				self::saveBookingMeta($order_id, 'provision', $amount);
			}
			elseif($provision->commission_type == 'brutto'){
				$brutto = number_format((float)$data['price'], 2, '.', '');
				$amount = number_format((float)($brutto / 100 * $provision->commission_value), 2, '.', '');
				self::saveBookingMeta($order_id, 'provision', $amount);
			}
		}
		else{
			self::saveBookingMeta($order_id, 'provision', '0.00');
		}
		*/
		self::checkBookingDate($order_id);
    }
	
	static function updateOrderFomParkos($dataC)
    {
		$order_id = self::$db->get_row("SELECT order_id FROM " . self::$prefix . "orders WHERE code = '" . $dataC->code . "'");
		
		if($order_id->order_id == null)
			return false;
		
		if($dataC->merchant_id == 569){
			if($dataC->location_type == 'indoor')
				$product = 41402;
			else
				$product = 41403;
		}
		elseif($dataC->merchant_id == 1741){
			$product = 45856;
		}
		else
			return false;
				
		$commission = number_format($dataC->total_price / 100 * 20, 2, ".", ".");
		
		$woo_order = wc_get_order($order_id->order_id);
		$order_items = $woo_order->get_items();

		foreach ( $order_items as $order_item_id => $order_item) {
			wc_delete_order_item($order_item_id);
		}
		
		$woo_order->calculate_taxes();
		$woo_order->calculate_totals();
		$woo_order->save();
		
		$data['parking_update'] = number_format($dataC->total_price, 2, ".", ".");
		$price = number_format((float)$data['parking_update'] / 119 * 100,4, '.', '');
		
		$woo_order = wc_get_order($order_id->order_id);
		$woo_order->add_product( wc_get_product($product), 1, [
				//'subtotal'     => $price, // e.g. 32.95
				'total'        => $price, // e.g. 32.95
			] );
		
		$woo_order->calculate_taxes();
		$woo_order->calculate_totals();
		$woo_order->save();
		
		$barzahlung = 0;
		$mv = $data['parking_update'];		

		self::$db->update(self::$prefix_DB . 'wc_order_product_lookup', [
			'product_net_revenue' => (float)$data['parking_update'] / 119 * 100,
			'product_gross_revenue' => (float)$data['parking_update'],
			'tax_amount' => (float)$data['parking_update'] / 119 * 19
		], ['order_id' => $order_id->order_id]);
		
		$nameTemp = explode(" ",$dataC->name);
		$firstName = $nameTemp[0];
		$lastName = $nameTemp[1];

        // add a bunch of meta data
		update_post_meta($order_id->order_id, '_billing_first_name', $firstName);
        update_post_meta($order_id->order_id, '_billing_last_name', $lastName);
		update_post_meta($order_id->order_id, '_billing_phone', $dataC->phone);
        update_post_meta($order_id->order_id, 'Anreisedatum', dateFormat($dataC->arrival_date));
        update_post_meta($order_id->order_id, 'first_anreisedatum', dateFormat($dataC->arrival_date));
        update_post_meta($order_id->order_id, 'Abreisedatum', dateFormat($dataC->departure_date));
        update_post_meta($order_id->order_id, 'first_abreisedatum', dateFormat($dataC->departure_date));
        update_post_meta($order_id->order_id, 'Uhrzeit von', $dataC->arrival_time);
        update_post_meta($order_id->order_id, 'Uhrzeit bis', $dataC->departure_time);
        update_post_meta($order_id->order_id, 'Hinflugnummer', $dataC->flight_departure_nr);
        update_post_meta($order_id->order_id, 'Rückflugnummer', $dataC->flight_return_nr);
        update_post_meta($order_id->order_id, 'Personenanzahl', $dataC->persons);
        update_post_meta($order_id->order_id, 'Kennzeichen', $dataC->car_license_plate);		
        update_post_meta($order_id->order_id, 'Fahrzeugmodell', $dataC->car_brand_model);


		if($dataC->flight_departure_nr == null)
			$dataC->flight_departure_nr = "";
		if($dataC->flight_return_nr == null)
			$dataC->flight_return_nr = "";
				
        self::$db->update(self::$prefix . 'orders', [
            'date_from' => date('Y-m-d H:i', strtotime($dataC->arrival_date . ' ' . $dataC->arrival_time)),
            'date_to' => date('Y-m-d H:i', strtotime($dataC->departure_date . ' ' . $dataC->departure_time)),
            'product_id' => $product,
			'out_flight_number' => $dataC->flight_departure_nr,
            'return_flight_number' => $dataC->flight_return_nr,
			'b_total' => $barzahlung,
			'm_v_total' => $mv,
            'nr_people' => $dataC->persons
        ], ['order_id' => $order_id->order_id]);
		
		self::updateBookingMeta($order_id->order_id, 'provision', $commission);
		/*
		// Save commission to booking metaphone
		$provision = self::getBrokerCommissionForBooking($product->product_id, date('Y-m-d', strtotime($data["start-date"])));
		if($provision->commission_type != "" || $provision->commission_type != null){
			if($provision->commission_type == 'netto'){
				$netto = number_format((float)($data['parking_update'] / 119 * 100), 2, '.', '');
				$amount = number_format((float)($netto / 100 * $provision->commission_value), 2, '.', '');
				self::updateBookingMeta($order_id->order_id, 'provision', $amount);
			}
			elseif($provision->commission_type == 'brutto'){
				$brutto = number_format((float)$data['parking_update'], 2, '.', '');
				$amount = number_format((float)($brutto / 100 * $provision->commission_value), 2, '.', '');
				self::updateBookingMeta($order_id->order_id, 'provision', $amount);
			}
		}
		else{
			self::updateBookingMeta($order_id->order_id, 'provision', '0.00');
		}
		*/
		self::checkBookingDate($order_id->order_id);
    }
	
	static function cancelOrderFomParkos($dataC)
    {
		$order_id = self::$db->get_row("SELECT order_id FROM " . self::$prefix . "orders WHERE code = '" . $dataC->code . "'");
		
		if($order_id->order_id == null)
			return false;
		
		$order = new WC_Order($order_id->order_id);
		if (!empty($order)) {
			$order->update_status( 'cancelled' );
			update_post_meta($order_id->order_id, 'Uhrzeit von', '23:59');
			update_post_meta($order_id->order_id, 'Uhrzeit bis', '23:59');
			
			self::$db->update(self::$prefix . 'orders', [
				'date_from' => date('Y-m-d H:i', strtotime($dataC->arrival_date . ' 23:59')),
				'date_to' => date('Y-m-d H:i', strtotime($dataC->departure_date . ' 23:59'))
			], ['order_id' => $order_id->order_id]);
		}
	}
	
    static function updateOrderFomAPS($data)
    {
		$order_id = self::$db->get_row("SELECT post_id FROM " . self::$prefix_DB . "postmeta WHERE meta_key = 'token' and meta_value = '" . $data['bookingCode'] . "'");
		$product = self::$db->get_row("SELECT product_id FROM " . self::$prefix . "orders WHERE order_id = '" . $order_id->post_id . "'");
				
		if($order_id->post_id == "" || $order_id->post_id == null)
			return false;
		
		$woo_order = wc_get_order($order_id->post_id);
		$order_items = $woo_order->get_items();
		foreach ( $order_items as $key => $value ) {
			$order_item_id = $key;
			   $product_value = $value->get_data();
			   $product_id    = $product_value['product_id']; 
		}
		$product = wc_get_product( $product_id );
		$price = number_format((float)$data['totalParkingCosts'] / 119 * 100, 4, '.', '');
		$order_items[ $order_item_id ]->set_total( $price );
		$woo_order->calculate_taxes();
		$woo_order->calculate_totals();
		$woo_order->save();
		
		if(get_post_meta($order_id->post_id, '_payment_method_title')[0] == 'Barzahlung'){
			$barzahlung = $data['totalParkingCosts'];
			$mv = 0;
		}			
		else{
			$barzahlung = 0;
			$mv = $data['totalParkingCosts'];
		}	
		
		self::$db->update(self::$prefix_DB . 'wc_order_product_lookup', [
			'product_net_revenue' => (float)$data['totalParkingCosts'] / 119 * 100,
			'product_gross_revenue' => (float)$data['totalParkingCosts'],
			'tax_amount' => (float)$data['totalParkingCosts'] / 119 * 19
		], ['order_id' => $order_id->post_id]);


        // add a bunch of meta data
		update_post_meta($order_id->post_id, '_billing_first_name', $data["firstName"]);
        update_post_meta($order_id->post_id, '_billing_last_name', $data["lastName"]);
		update_post_meta($order_id->post_id, '_billing_phone', $data["mobileNumber"]);
		update_post_meta($order_id->post_id, '_billing_email', $data["eMail"]);
        update_post_meta($order_id->post_id, 'Anreisedatum', dateFormat($data["arrivalDate"]));
        update_post_meta($order_id->post_id, 'first_anreisedatum', dateFormat($data["arrivalDate"]));
        update_post_meta($order_id->post_id, 'Abreisedatum', dateFormat($data["departureDate"]));
        update_post_meta($order_id->post_id, 'first_abreisedatum', dateFormat($data["departureDate"]));
        update_post_meta($order_id->post_id, 'Uhrzeit von', $data["arrivalTime"]);
        update_post_meta($order_id->post_id, 'Uhrzeit bis', $data["departureTime"]);
        update_post_meta($order_id->post_id, 'Hinflugnummer', $data["outboundFlightNumber"]);
        update_post_meta($order_id->post_id, 'Rückflugnummer', $data["returnFlightNumber"]);
        update_post_meta($order_id->post_id, 'Personenanzahl', $data["countTravellers"]);
        update_post_meta($order_id->post_id, 'Kennzeichen', $data["license_plate"]);
		update_post_meta($order_id->post_id, 'Fahrzeughersteller', $data["vehicleBrand"]);
        update_post_meta($order_id->post_id, 'Fahrzeugmodell', $data["vehicleModel"]);
        update_post_meta($order_id->post_id, 'Fahrzeugfarbe', $data["vehicleColor"]);
		
		if($data["salutation"] == "Mr"){
			if($data["salutation"] == "Mr")
				update_post_meta($order_id->post_id, 'Anrede', 'Herr');
			elseif($data["salutation"] == "Ms")
				update_post_meta($order_id->post_id, 'Anrede', 'Frau');
		}

		if($data['outboundFlightNumber'] == null)
			$data['outboundFlightNumber'] = "";
		if($data['returnFlightNumber'] == null)
			$data['returnFlightNumber'] = "";
				
        self::$db->update(self::$prefix . 'orders', [
            'date_from' => date('Y-m-d H:i', strtotime($data["arrivalDate"] . ' ' . $data["arrivalTime"])),
            'date_to' => date('Y-m-d H:i', strtotime($data["departureDate"] . ' ' . $data["departureTime"])),
            'out_flight_number' => $data['outboundFlightNumber'],
            'return_flight_number' => $data['returnFlightNumber'],
			'b_total' => $barzahlung,
			'm_v_total' => $mv,
            'nr_people' => $data['countTravellers']
        ], ['order_id' => $order_id->post_id]);
		
		
		// Save commission to booking metaphone
		$provision = self::getBrokerCommissionForBooking($product->product_id, date('Y-m-d', strtotime($data["arrivalDate"])));
		if($provision->commission_type != "" || $provision->commission_type != null){
			if($provision->commission_type == 'netto'){
				$netto = number_format((float)($data['totalParkingCosts'] / 119 * 100), 2, '.', '');
				$amount = number_format((float)($netto / 100 * $provision->commission_value), 2, '.', '');
				self::updateBookingMeta($order_id->post_id, 'provision', $amount);
			}
			elseif($provision->commission_type == 'brutto'){
				$brutto = number_format((float)$data['totalParkingCosts'], 2, '.', '');
				$amount = number_format((float)($brutto / 100 * $provision->commission_value), 2, '.', '');
				self::updateBookingMeta($order_id->post_id, 'provision', $amount);
			}
		}
		else{
			self::updateBookingMeta($order_id->post_id, 'provision', '0.00');
		}
		
		/*if($data["bookingState"] == "CANCEL"){
			self::updateBookingMeta($order_id->post_id, 'provision', '0.00');
			$order = new WC_Order($order_id->post_id);
			if (!empty($order)) {
				$order->update_status( 'cancelled' );
			}
		}*/
		self::checkBookingDate($order_id->post_id);
    }
	
	static function cancelOrderFomAPS($data)
    {
		$order_id = self::$db->get_row("SELECT post_id FROM " . self::$prefix_DB . "postmeta WHERE meta_key = 'token' and meta_value = '" . $data['bookingCode'] . "'");
		$code = self::$db->get_row("SELECT code FROM " . self::$prefix . "orders WHERE order_id = " . $order_id->post_id);
		
		if($code->code == $data['internalADPrefix']){
			self::updateBookingMeta($order_id->post_id, 'provision', '0.00');
			$order = new WC_Order($order_id->post_id);
			if (!empty($order)) {
				$order->update_status( 'cancelled' );
			}
		}
	}
	
	static function cancelToProcessingFomAPG($data)
    {
		$headers = array('Content-Type: text/html; charset=UTF-8');
		wp_mail( 'it@a-p-germany.de', 'paid cancel_curl', $data['order_id'] . ", " . $data['order_total'], $headers );
		$woo_order = wc_get_order($data['order_id']);
		$woo_order->update_status( 'processing' );
		$order_items = $woo_order->get_items();
		foreach ( $order_items as $key => $value ) {
			$order_item_id = $key;
			   $product_value = $value->get_data();
			   $product_id    = $product_value['product_id']; 
		}
		$product = wc_get_product( $product_id );
		$price = $data['order_total'] / 119 * 100;
		$order_items[ $order_item_id ]->set_total( $price );
		$woo_order->calculate_taxes();
		$woo_order->calculate_totals();
		$woo_order->save();
	}

    public function bulkDelete($table, $attribute, $ids){
        $idsString = implode(',', $ids);
		self::$db->query("delete from " . self::$prefix . 'orders_meta' . " where {$attribute} in ($idsString)");
		self::$db->query("delete from " . self::$prefix . 'hotel_transfers' . " where {$attribute} in ($idsString)");
        return self::$db->query("delete from " . self::$prefix . $table . " where {$attribute} in ($idsString)");
    }
	
	public function allprice_delete_from_calendar($lot_id){
		return self::$db->query("delete from " . self::$prefix . 'events' . " where product_id = " . $lot_id);
    }  

    static function getParkotsWithOrdersData($date)
    {
        return self::$db->get_results("SELECT pl.id, pl.order_lot, pl.parklot, pl.parklot_short, pl.contigent, pl.product_id, (
                    select count(orders.id) from " . self::$prefix . "orders orders
					inner join " . self::$prefix_DB . "posts s on s.ID = orders.order_id
                    where date('$date') between date(orders.date_from) and date(orders.date_to) and orders.product_id = pl.product_id
                    and deleted = 0 and s.post_status = 'wc-processing'
                ) as used
				FROM " . self::$prefix . "parklots pl
				LEFT JOIN " . self::$prefix . "orders o ON o.product_id = pl.product_id
				group by pl.id
				ORDER BY pl.order_lot");
    }
	
	static function getParkotsWithOrdersDataAndProductID($date, $id)
    {
        return self::$db->get_row("SELECT pl.id, pl.order_lot, pl.parklot, pl.parklot_short, pl.contigent, pl.product_id, (
                    select count(orders.id) from " . self::$prefix . "orders orders
					inner join " . self::$prefix_DB . "posts s on s.ID = orders.order_id
                    where date('$date') between date(orders.date_from) and date(orders.date_to) and orders.product_id = pl.product_id
                    and deleted = 0 and s.post_status = 'wc-processing'
                ) as used
				FROM " . self::$prefix . "parklots pl
				LEFT JOIN " . self::$prefix . "orders o ON o.product_id = pl.product_id
				WHERE pl.product_id = ".$id."
				group by pl.id
				ORDER BY pl.order_lot");
    }
	
    static function getBookingServices($order_id, $product_id)
    {
        return self::$db->get_results("SELECT asp.add_ser_id, ass.name, ass.price FROM " . self::$prefix . "orders_meta om
				INNER JOIN " . self::$prefix . "additional_services_products asp ON asp.add_ser_id = om.meta_value
				INNER JOIN " . self::$prefix . "additional_services ass ON ass.id = asp.add_ser_id
				WHERE om.order_id = " . $order_id . " AND om.meta_key = 'additional_services' AND asp.product_id = " . $product_id);
    }
	
	static function getPriceByDateAndProductID($date, $id)
    {
        return self::$db->get_row("SELECT name FROM " . self::$prefix . "prices p
				INNER JOIN " . self::$prefix . "events e ON e.price_id = p.id
				WHERE DATE(e.datefrom) = '" . $date . "' AND e.product_id = " . $id);
    }
	
		
    static function importOrder($data)
    {
		if($data['internalADPrefix'] == NULL){
			if($data['parkinglot_parkinglots_id'] == 2)
				$data['product'] = 537;
			elseif($data['parkinglot_parkinglots_id'] == 24)
				$data['product'] = 592;
			elseif($data['parkinglot_parkinglots_id'] == 25)
				$data['product'] = 619;
			/*elseif($data['id'] == 12)
				$data['product'] = 621;
			elseif($data['id'] == 11)
				$data['product'] = 624;*/
			elseif($data['parkinglot_parkinglots_id'] == 1)
				$data['product'] = 873;
			else
				return false;
		}
		elseif($data['internalADPrefix'] != NULL){
			if($data['internalADPrefix'] == 'STR4' || $data['internalADPrefix'] == 'STR8' || $data['internalADPrefix'] == 'STR2' || $data['internalADPrefix'] == 'STB2' || $data['internalADPrefix'] == 'STB1'){
				$data['product'] = 621;
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
				if($data['internalADPrefix'] == 'STR2'){
					$data['totalParkingCosts'] = $data['totalParkingCosts'] / 75 * 100;
					$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 25;
				}
			}
			elseif($data['internalADPrefix'] == 'STRH'){
				$data['product'] = 683;
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
			}
			elseif($data['internalADPrefix'] == 'STR6' || $data['internalADPrefix'] == 'STR7' || $data['internalADPrefix'] == 'STRD' || $data['internalADPrefix'] == 'STR0'){
				$data['product'] = 624;
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
				if($data['internalADPrefix'] == 'STR0'){
					$data['totalParkingCosts'] = $data['totalParkingCosts'] / 75 * 100;
					$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 25;
				}
			}
			elseif($data['internalADPrefix'] == 'STR1' || $data['internalADPrefix'] == 'STR9' || $data['internalADPrefix'] == 'STRW'){
				$data['product'] = 901;
				$data['totalParkingCosts'] = $data['totalParkingCosts'] / 70 * 100;
				$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 30;
				if($data['internalADPrefix'] == 'STRW'){
					$data['totalParkingCosts'] = $data['totalParkingCosts'] / 75 * 100;
					$commission = ($data['totalParkingCosts'] / 119 * 100) / 100 * 25;
				}
			}
			else
				return false;
		}
		else
			return false;
		
        $parklot = new Parklot($data['product']);
		
		if($data['totalParkingCosts'])
			$data['parkingCosts'] = $data['totalParkingCosts'];

        $product = wc_get_product($data['product']);
        // set new product price
        $product->set_price($data['parkingCosts']);

        $order = Orders::createWCOrder($product);
        $order_id = $order->get_id();
		

        // add a bunch of meta data
		
		if($data["paymentOptions"] == "Barzahlung"){
			add_post_meta($order_id, '_transaction_id', 'barzahlung', true);
			add_post_meta($order_id, '_payment_method_title', 'Barzahlung', true);
			$barzahlung = $data['parkingCosts'];
			$mv = 0;
		}
		elseif($data["paymentOptions"] == "M"){
			add_post_meta($order_id, '_transaction_id', 'm', true);
			add_post_meta($order_id, '_payment_method_title', 'MasterCard', true);
			$barzahlung = 0;
			$mv = $data['parkingCosts'];
		}
		elseif($data["paymentOptions"] == "V"){
			add_post_meta($order_id, '_transaction_id', 'v', true);
			add_post_meta($order_id, '_payment_method_title', 'Visa', true);
			$barzahlung = 0;
			$mv = $data['parkingCosts'];
		}

        add_post_meta($order_id, '_order_total', '', true);
        add_post_meta($order_id, '_customer_user', 6, true);
        add_post_meta($order_id, '_completed_date', '', true);
        add_post_meta($order_id, '_order_currency', '', true);
        add_post_meta($order_id, '_paid_date', '', true);

        add_post_meta($order_id, '_billing_address_1', '', true);
        add_post_meta($order_id, '_billing_city', '', true);
        add_post_meta($order_id, '_billing_state', '', true);
        add_post_meta($order_id, '_billing_postcode', '', true);
        add_post_meta($order_id, '_billing_company', '', true);
        add_post_meta($order_id, '_billing_country', '', true);
        add_post_meta($order_id, '_billing_email', $data["eMail"], true);
        add_post_meta($order_id, '_billing_first_name', $data["firstName"], true);
        add_post_meta($order_id, '_billing_last_name', $data["lastName"], true);
        add_post_meta($order_id, '_billing_phone', $data["mobileNumber"], true);

        add_post_meta($order_id, 'Anreisedatum', dateFormat($data["arrivalDate"]), true);
        add_post_meta($order_id, 'first_anreisedatum', dateFormat($data["arrivalDate"]), true);
        add_post_meta($order_id, 'Abreisedatum', dateFormat($data["departureDate"]), true);
        add_post_meta($order_id, 'first_abreisedatum', dateFormat($data["departureDate"]), true);
        add_post_meta($order_id, 'Uhrzeit von', $data["arrivalTime"], true);
        add_post_meta($order_id, 'Uhrzeit bis', $data["departureTime"], true);
        add_post_meta($order_id, 'Hinflugnummer', $data["outboundFlightNumber"], true);
        add_post_meta($order_id, 'Rückflugnummer', $data["returnFlightNumber"], true);
        add_post_meta($order_id, 'Personenanzahl', $data["countTravellers"], true);
        add_post_meta($order_id, 'Kennzeichen', $data["license_plate"], true);


        update_post_meta($order_id, 'token', $data["bookingCode"]);

		if($data['outboundFlightNumber'] == null)
			$data['outboundFlightNumber'] = "";
		if($data['returnFlightNumber'] == null)
			$data['returnFlightNumber'] = "";
				
        self::$db->insert(self::$prefix . 'orders', [
            'date_from' => date('Y-m-d H:i', strtotime($data["arrivalDate"] . ' ' . $data["arrivalTime"])),
            'date_to' => date('Y-m-d H:i', strtotime($data["departureDate"] . ' ' . $data["departureTime"])),
            'product_id' => $data['product'],
            'order_id' => $order_id,
			'b_total' => $barzahlung,
			'm_v_total' => $mv,
            'out_flight_number' => $data['outboundFlightNumber'],
            'return_flight_number' => $data['returnFlightNumber'],
            'nr_people' => $data['countTravellers']
        ]);
		
		self::$db->update(self::$prefix_DB . 'posts', [                               
				'post_date' => $data['creationDate'],
				'post_date_gmt' => $data['creationDate']				
		], ['id' => $order_id]);
		self::$db->update(self::$prefix_DB . 'wc_order_product_lookup', [                               
			'date_created' => $data['creationDate'],				
		], ['order_id' => $order_id]);
		
		self::$db->update(self::$prefix_DB . 'wc_order_stats', [                               
			'date_created' => $data['creationDate'],
			'date_created_gmt' => $data['creationDate']				
		], ['order_id' => $order_id]);
		
		
		// Save commission to booking metaphone
		if($data['internalADPrefix'] != NULL){
			self::updateBookingMeta($order_id, 'provision', $commission);
		}
		else{
			$provision = self::getBrokerCommissionForBooking($data['product'], date('Y-m-d', strtotime($data["arrivalDate"])));
			if($provision->commission_type != "" || $provision->commission_type != null){
				if($provision->commission_type == 'netto'){
					$netto = number_format((float)($data['totalParkingCosts'] / 119 * 100), 2, '.', '');
					$amount = number_format((float)($netto / 100 * $provision->commission_value), 2, '.', '');
					self::saveBookingMeta($order_id, 'provision', $amount);
				}
				elseif($provision->commission_type == 'brutto'){
					$brutto = number_format((float)$data['totalParkingCosts'], 2, '.', '');
					$amount = number_format((float)($brutto / 100 * $provision->commission_value), 2, '.', '');
					self::saveBookingMeta($order_id, 'provision', $amount);
				}
			}
			else{
				self::saveBookingMeta($order_id, 'provision', '0.00');
			}
		}
		self::checkBookingDate($order_id);
    }

	static function checkBookingDate($order_id) {
		$booking = self::$db->get_row("
				SELECT o.order_id as order_id, p.is_for, DATE(o.date_from) order_from, DATE(o.date_to) order_to, TIME(o.date_from) order_time_from, TIME(o.date_to) order_time_to,
					(SELECT DATE(pm.meta_value)
						FROM " . self::$prefix_DB . "postmeta pm
						WHERE pm.meta_key = 'Anreisedatum' and pm.post_id = order_id) meta_from,
					 (SELECT DATE(pm.meta_value) 
						FROM " . self::$prefix_DB . "postmeta pm
						WHERE pm.meta_key = 'Abreisedatum' and pm.post_id = order_id) meta_to,
					  (SELECT pm.meta_value
						FROM " . self::$prefix_DB . "postmeta pm
						WHERE pm.meta_key = 'Uhrzeit von' and pm.post_id = order_id) meta_time_from,
					 (SELECT pm.meta_value
						FROM " . self::$prefix_DB . "postmeta pm
						WHERE pm.meta_key = 'Uhrzeit bis' and pm.post_id = order_id) meta_time_to
				FROM " . self::$prefix . "orders o
				INNER JOIN " . self::$prefix . "parklots p on o.product_id = o.product_id
				WHERE o.order_id = " . $order_id);
		
		if($booking != null && $booking->is_for != 'hotel'){
			if($booking->order_from != $booking->meta_from ||
				$booking->order_to != $booking->meta_to ||
				$booking->order_time_from != $booking->meta_time_from ||
				$booking->order_time_to != $booking->meta_time_to){
				if($booking->meta_to != null){
					self::$db->update(self::$prefix . 'orders', [
						'date_from' => date('Y-m-d H:i', strtotime($booking->meta_from . ' ' . $booking->meta_time_from)),
						'date_to' => date('Y-m-d H:i', strtotime($booking->meta_to . ' ' . $booking->meta_time_to))            
						], ['order_id' => $booking->order_id]);
				}
			}
		}
	}
	
	static function importHEX()
    {
		$importDIR = ABSPATH . 'wp-content/uploads/hex_files';
		$files = scandir($importDIR);
		//$files = array_diff($files, array('.', '..', 'index.html'));
		
		foreach ($files as $key => $file) {
			//copy($importDIR . "/" . $file, $importDIR . "_copy/" . $file);
			$errors = false;
			// Create data array with files.
			$data = array();
			if (!empty($file)) {
				$lines = @file($importDIR . '/' . $file);
					for ($i = 0; $i < sizeof($lines); $i++) {
						if ((strpos($file, 'STR') !== false) || (strpos($file, 'STB') !== false) || (strpos($file, 'ST') !== false) ) {
							// HX Files
							$data[] = explode("\t", mb_convert_encoding($lines[$i], "UTF-8", "iso-8859-1"));
							//$data[] = explode("\t", htmlspecialchars($lines[$i]));
						} else {
							// Default files.
							$data[] = explode("\t", htmlspecialchars($lines[$i]));	
						}
						
					}
			}
			if (!empty($data)) {
				
				// Check if file is from hx or default file
				if (strpos($file, 'STR') !== false || strpos($file, 'STB') !== false || strpos($file, 'ST') !== false) {
					$errors = self::defaultHXBookingImporter($data, $file);
				} else {
					//$errors = self::defaultBookingImporterAPS($data, $file);
				}
				
				// If no errors than delete files
				if($errors == true) {
					
					$backupPath = ABSPATH . 'wp-content/uploads/aps_bookings_report_backup/' . date('Y') . "/" . date('F') . "/" . date('d');
					
					// Backup Files
					if (!file_exists($backupPath)) {
						mkdir($backupPath, 0755, true);
					} 			
					$status = rename($importDIR . "/" . $file, $backupPath . "/" . $file);
				}
			}
		}		
	}
	
	static function defaultHXBookingImporter($data, $file) {

		foreach ($data as $key => $value) {
			// Insert bookings from airparks and parkplatztarife
			
			$order_id = self::$db->get_row("
				SELECT pm.post_id
				FROM 59hkh_postmeta pm 
				WHERE pm.meta_key = 'token' and pm.meta_value = '" . trim($value[2]) . "'
				");
			
			// Customer table
			$customerData = new stdClass();
			$customerData->salutation = 'Familie';
			
			$customerData->firstName = strtok($value[3], " ");
			$customerData->lastName = strtok($value[3], " ");
			$customerData->mobileNumber = '+49 (0) 89 67 80 59 180';
			
			$customerData->eMail = trim($value[14]);
			$customerData->creationDate = date('Y-m-d');

			// Booking table
			$bookingData = new stdClass();
			$bookingData->license_plate = null;
			$bookingData->outboundFlightNumber = null;
			$bookingData->returnFlightNumber = null;
			$bookingData->internalADPrefix = trim($value[1]); 
			
			switch ($bookingData->internalADPrefix) {
				case 'STR8':
				case 'STR4':
				case 'STR2':
				case 'STB2':
				case 'STB1':			
					$bookingData->parkinglot_parkinglots_id = 621;
					break;
				case 'STRH':                    
					$bookingData->parkinglot_parkinglots_id = 683;
					break;
				
				case 'STR9':
				case 'STRW':
				case 'STR1':		
					$bookingData->parkinglot_parkinglots_id = 901;
					break;
				
				case 'ST10':
				case 'ST11':
				case 'ST12':
					$bookingData->parkinglot_parkinglots_id = 24261;
					break;
					
				case 'ST13':
				case 'ST14':
				case 'ST15':
					$bookingData->parkinglot_parkinglots_id = 24263;
					break;
				case 'ST16':
					$bookingData->parkinglot_parkinglots_id = 24609;
					break;
					
				case 'STB4':
					$bookingData->parkinglot_parkinglots_id = 18;
					break;
				case 'STRD':
				case 'STR7':
				case 'STR6':
				case 'STR0':			
					$bookingData->parkinglot_parkinglots_id = 624; 
					break;

				case 'STRF':
					$bookingData->parkinglot_parkinglots_id = 16;
					// Set valet data.
					$bookingData->license_plate = trim($value[13]);
					$bookingData->vehicleBrand = trim($value[14]);
					$bookingData->vehicleModel = trim($value[15]);
					$bookingData->vehicleColor = trim($value[16]);
					$bookingData->outboundFlightNumber = trim($value[17]);
					$bookingData->returnFlightNumber = trim($value[18]);
					$customerData->mobileNumber = str_replace("\r\n", "",$value[21]);
					break;

				case 'STRG':
				case 'STB3':
					$bookingData->parkinglot_parkinglots_id = 15;
					// Set valet data.
					$bookingData->license_plate = trim($value[13]);
					$bookingData->vehicleBrand = trim($value[14]);
					$bookingData->vehicleModel = trim($value[15]);
					$bookingData->vehicleColor = trim($value[16]);
					$bookingData->outboundFlightNumber = trim($value[17]);
					$bookingData->returnFlightNumber = trim($value[18]);
					$customerData->mobileNumber = str_replace("\r\n", "",$value[21]);				
					break;
					
				case 'STRA':
					$bookingData->parkinglot_parkinglots_id = 14;
					// Set valet data.
					$bookingData->license_plate = trim($value[13]);
					$bookingData->vehicleBrand = trim($value[14]);
					$bookingData->vehicleModel = trim($value[15]);
					$bookingData->vehicleColor = trim($value[16]);
					$bookingData->outboundFlightNumber = trim($value[17]);
					$bookingData->returnFlightNumber = trim($value[18]);
					$customerData->mobileNumber = str_replace("\r\n", "",$value[21]);				
					break;
				case 'STRI':
						$bookingData->parkinglot_parkinglots_id = 28878;
						// Set valet data.
						$bookingData->license_plate = trim($value[13]);
						$bookingData->vehicleBrand = trim($value[14]);
						$bookingData->vehicleModel = trim($value[15]);
						$bookingData->vehicleColor = trim($value[16]);
						$bookingData->outboundFlightNumber = trim($value[17]);
						$bookingData->returnFlightNumber = trim($value[18]);
						$customerData->mobileNumber = str_replace("\r\n", "",$value[21]);                               
						break;
				case 'STRC':
						$bookingData->parkinglot_parkinglots_id = 21;
						// Set valet data.
						$bookingData->license_plate = trim($value[13]);
						$bookingData->vehicleBrand = trim($value[14]);
						$bookingData->vehicleModel = trim($value[15]);
						$bookingData->vehicleColor = trim($value[16]);
						$bookingData->outboundFlightNumber = trim($value[17]);
						$bookingData->returnFlightNumber = trim($value[18]);
						$customerData->mobileNumber = str_replace("\r\n", "",$value[21]);                               
						break;
				default:				
					return false;
					break;
			}
			
			// Set AD ID
			$bookingData->parkinglot_partner_booking = true;
			
			$bookingData->bookingModState = 'Importieren';
			$bookingData->bookingCode = trim($value[2]);
			
			$arrivalDateArray = str_split(str_replace('"', "",$value[6]), 2);
			$bookingData->arrivalDate = date('Y-m-d', strtotime($arrivalDateArray[2] .'-' . $arrivalDateArray[1] . '-' . $arrivalDateArray[0]));
			$bookingData->arrivalTime = $value[5];

			$departureDateArray = str_split(str_replace('"', "",$value[8]), 2);
			$bookingData->departureDate = date('Y-m-d', strtotime($departureDateArray[2] .'-' . $departureDateArray[1] . '-' . $departureDateArray[0]));
			$bookingData->departureTime = trim($value[7]);
			
			$bookingData->parkingDays = trim($value[4]);
			$bookingData->countTravellers = str_replace("0", "", trim($value[9]));
		
			$bookingData->parkingCosts = trim($value[11]);
			$bookingData->totalParkingCosts = trim($value[11]);
			
			$bookingData->bookingState = trim($value[10]);
			$bookingData->creationDate = date('Y-m-d');
		
			$bookingData->paymentOptions = 'M';
			$bookingData->transactionStatus = 'paid';
			
			$obj_merged = (object) array_merge((array) $bookingData, (array) $customerData);
			$bookingArray = (array) $obj_merged;
			if($obj_merged->bookingState == '*FIRM*') {
				$obj_merged->bookingState = 'N';	
				// New booking				
				$bookingArray = (array) $obj_merged;
				// Check of booking exist
				if($order_id)
					self::updateOrderFomHEX($bookingArray);
				else{
					$error = self::saveOrderFomAPS($bookingArray, 'aps_ext');
				}
			} elseif($obj_merged->bookingState == '*AMND*') {
				$obj_merged->bookingState = 'CHANGED';
				// Update booking
				$bookingArray = (array) $obj_merged;
				if($order_id)
					self::updateOrderFomHEX($bookingArray);
				else
					$error = self::saveOrderFomAPS($bookingArray, 'aps_ext');
			} else {
				$obj_merged->bookingState = 'CANCELED';
				// Cancel booking
				$bookingArray = (array) $obj_merged;
				if($order_id)
					self::cancelOrderFomAPS($bookingArray);
			}
		}
		return true;	
	}
	
	static function importHEX_NEW()
    {
		$importDIR = ABSPATH . 'wp-content/uploads/hex_files';
		$files = scandir($importDIR);
		//$files = array_diff($files, array('.', '..', 'index.html'));
		
		foreach ($files as $key => $file) {
			//copy($importDIR . "/" . $file, $importDIR . "_copy/" . $file);
			$errors = false;
			// Create data array with files.
			$data = array();
			if (!empty($file)) {
				$lines = @file($importDIR . '/' . $file);
				$lines_csv = explode("\r", htmlspecialchars($lines[0]));	
					for ($i = 0; $i < (count($lines_csv) - 1); $i++) {
						if ((strpos($file, 'STR') !== false) || (strpos($file, 'STB') !== false) || (strpos($file, 'ST') !== false) ) {
							// HX Files
							$data[] = explode(";", mb_convert_encoding($lines_csv[$i], "UTF-8", "iso-8859-1"));
							//$data[] = explode("\t", htmlspecialchars($lines[$i]));
						} else {
							// Default files.
							$data[] = explode(";", htmlspecialchars($lines_csv[$i]));	
						}
						
					}
			}
			if (!empty($data)) {
				
				// Check if file is from hx or default file
				if (strpos($file, 'STR') !== false || strpos($file, 'STB') !== false || strpos($file, 'ST') !== false) {
					$errors = self::defaultHXBookingImporterSFTP($data, $file);
				} else {
					//$errors = self::defaultBookingImporterAPS($data, $file);
				}
				
				// If no errors than delete files
				if($errors == true) {
					
					$backupPath = ABSPATH . 'wp-content/uploads/aps_bookings_report_backup/' . date('Y') . "/" . date('F') . "/" . date('d');
					
					// Backup Files
					if (!file_exists($backupPath)) {
						mkdir($backupPath, 0755, true);
					} 			
					$status = rename($importDIR . "/" . $file, $backupPath . "/" . $file);
				}
			}
		}		
	}
	
	static function defaultHXBookingImporterSFTP($data, $file) {

		foreach ($data as $key => $value) {
			// Insert bookings from airparks and parkplatztarife
			
			$order_id = self::$db->get_row("
				SELECT pm.post_id
				FROM 59hkh_postmeta pm 
				WHERE pm.meta_key = 'token' and pm.meta_value = '" . trim($value[2]) . "'
				");
			
			// Customer table
			$customerData = new stdClass();
			$customerData->salutation = trim($value[3]);
	
			$customerData->firstName = strtok($value[5], " ");
			$customerData->lastName = strtok($value[4], " ");
			$customerData->mobileNumber = strtok($value[26], " ");
	
			$customerData->eMail = trim($value[33]);
			$customerData->creationDate = date('Y-m-d');

			// Booking table
			$bookingData = new stdClass();
			$bookingData->license_plate = null;
			$bookingData->outboundFlightNumber = trim($value[22]);
			$bookingData->returnFlightNumber = trim($value[23]);
			$bookingData->internalADPrefix = trim($value[1]); 
			
			switch ($bookingData->internalADPrefix) {
				case 'STR8':
				case 'STR4':
				case 'STR2':
				case 'STB2':
				case 'STB1':			
					$bookingData->parkinglot_parkinglots_id = 621;
					break;
				case 'STRH':                    
					$bookingData->parkinglot_parkinglots_id = 683;
					break;
				
				case 'STR9':
				case 'STRW':
				case 'STR1':		
					$bookingData->parkinglot_parkinglots_id = 901;
					break;
				
				case 'ST10':
				case 'ST11':
				case 'ST12':
					$bookingData->parkinglot_parkinglots_id = 24261;
					break;
					
				case 'ST13':
				case 'ST14':
				case 'ST15':
					$bookingData->parkinglot_parkinglots_id = 24263;
					break;
				case 'ST16':
					$bookingData->parkinglot_parkinglots_id = 24609;
					break;
					
				case 'STB4':
					$bookingData->parkinglot_parkinglots_id = 18;
					break;
				case 'STRD':
				case 'STR7':
				case 'STR6':
				case 'STR0':			
					$bookingData->parkinglot_parkinglots_id = 624; 
					break;

				case 'STRF':
					$bookingData->parkinglot_parkinglots_id = 16;
					// Set valet data.
					$bookingData->license_plate = trim($value[15]);
					$bookingData->vehicleBrand = trim($value[16]);
					$bookingData->vehicleModel = trim($value[17]);
					$bookingData->vehicleColor = trim($value[18]);
					$bookingData->outboundFlightNumber = trim($value[22]);
					$bookingData->returnFlightNumber = trim($value[23]);
					$customerData->mobileNumber = str_replace("\r\n", "",$value[26]);
					break;

				case 'STRG':
				case 'STB3':
					$bookingData->parkinglot_parkinglots_id = 15;
					// Set valet data.
					$bookingData->license_plate = trim($value[15]);
					$bookingData->vehicleBrand = trim($value[16]);
					$bookingData->vehicleModel = trim($value[17]);
					$bookingData->vehicleColor = trim($value[18]);
					$bookingData->outboundFlightNumber = trim($value[22]);
					$bookingData->returnFlightNumber = trim($value[23]);
					$customerData->mobileNumber = str_replace("\r\n", "",$value[26]);				
					break;
					
				case 'STRA':
					$bookingData->parkinglot_parkinglots_id = 14;
					// Set valet data.
					$bookingData->license_plate = trim($value[15]);
					$bookingData->vehicleBrand = trim($value[16]);
					$bookingData->vehicleModel = trim($value[17]);
					$bookingData->vehicleColor = trim($value[18]);
					$bookingData->outboundFlightNumber = trim($value[22]);
					$bookingData->returnFlightNumber = trim($value[23]);
					$customerData->mobileNumber = str_replace("\r\n", "",$value[26]);			
					break;
				case 'STRI':
						$bookingData->parkinglot_parkinglots_id = 28878;
						// Set valet data.
						$bookingData->license_plate = trim($value[15]);
						$bookingData->vehicleBrand = trim($value[16]);
						$bookingData->vehicleModel = trim($value[17]);
						$bookingData->vehicleColor = trim($value[18]);
						$bookingData->outboundFlightNumber = trim($value[22]);
						$bookingData->returnFlightNumber = trim($value[23]);
						$customerData->mobileNumber = str_replace("\r\n", "",$value[26]);	                          
						break;
				case 'STRC':
						$bookingData->parkinglot_parkinglots_id = 21;
						// Set valet data.
						$bookingData->license_plate = trim($value[15]);
						$bookingData->vehicleBrand = trim($value[16]);
						$bookingData->vehicleModel = trim($value[17]);
						$bookingData->vehicleColor = trim($value[18]);
						$bookingData->outboundFlightNumber = trim($value[22]);
						$bookingData->returnFlightNumber = trim($value[23]);
						$customerData->mobileNumber = str_replace("\r\n", "",$value[26]);	                       
						break;
				default:				
					return false;
					break;
			}
			
			// Set AD ID
			$bookingData->parkinglot_partner_booking = true;
			
			$bookingData->bookingModState = 'Importieren';
			$bookingData->bookingCode = trim($value[2]);
			
			$arrivalDateArray = str_split(str_replace('"', "",$value[8]), 2);
			$bookingData->arrivalDate = date('Y-m-d', strtotime($arrivalDateArray[2] .'-' . $arrivalDateArray[1] . '-' . $arrivalDateArray[0]));
			$bookingData->arrivalTime = $value[7];

			$departureDateArray = str_split(str_replace('"', "",$value[10]), 2);
			$bookingData->departureDate = date('Y-m-d', strtotime($departureDateArray[2] .'-' . $departureDateArray[1] . '-' . $departureDateArray[0]));
			$bookingData->departureTime = trim($value[9]);
			
			$bookingData->parkingDays = trim($value[6]);
			$bookingData->countTravellers = str_replace("0", "", trim($value[11]));
		
			$bookingData->parkingCosts = trim($value[14]);
			$bookingData->totalParkingCosts = trim($value[14]);
			
			$bookingData->bookingState = trim($value[12]);
			$bookingData->creationDate = date('Y-m-d');
		
			$bookingData->paymentOptions = 'M';
			$bookingData->transactionStatus = 'paid';
			
			$obj_merged = (object) array_merge((array) $bookingData, (array) $customerData);
			$bookingArray = (array) $obj_merged;
			if($obj_merged->bookingState == '*FIRM*') {
				$obj_merged->bookingState = 'N';	
				// New booking				
				$bookingArray = (array) $obj_merged;
				// Check of booking exist
				if($order_id)
					self::updateOrderFomHEX($bookingArray);
				else{
					$error = self::saveOrderFomAPS($bookingArray, 'aps_ext');
				}
			} elseif($obj_merged->bookingState == '*AMND*') {
				$obj_merged->bookingState = 'CHANGED';
				// Update booking
				$bookingArray = (array) $obj_merged;
				if($order_id)
					self::updateOrderFomHEX($bookingArray);
				else
					$error = self::saveOrderFomAPS($bookingArray, 'aps_ext');
			} else {
				$obj_merged->bookingState = 'CANCELED';
				// Cancel booking
				$bookingArray = (array) $obj_merged;
				if($order_id)
					self::cancelOrderFomAPS($bookingArray);
			}
		}
		return true;	
	}
	
}
