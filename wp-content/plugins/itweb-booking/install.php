<?php

$ordersTB = $wpdb->prefix . 'itweb_orders';
$clientsTB = $wpdb->prefix . 'itweb_clients';
$pricesTB = $wpdb->prefix . 'itweb_prices';
$additionalServicesTB = $wpdb->prefix . 'itweb_additional_services';
$additionalServicesProductsTB = $wpdb->prefix . 'itweb_additional_services_products';
$eventsTB = $wpdb->prefix . 'itweb_events';
$parklotsTB = $wpdb->prefix . 'itweb_parklots';
$restrictionsTB = $wpdb->prefix . 'itweb_restrictions';
$discountsTB = $wpdb->prefix . 'itweb_discounts';
$orderCancellationsTB = $wpdb->prefix . 'itweb_order_cancellations';
$brokersTB = $wpdb->prefix . 'itweb_brokers';
$brokersProductsTB = $wpdb->prefix . 'itweb_brokers_products';
$hotelTransfersTB = $wpdb->prefix . 'itweb_hotel_transfers';
$charset_collate = $wpdb->get_charset_collate();

$ordersSql = "CREATE TABLE $ordersTB (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`date_from` timestamp default current_timestamp,
		`date_to` timestamp default current_timestamp,
		`product_id` int(11) DEFAULT NULL,
		`order_id` int(11) NOT NULL,
		`out_flight_number` varchar(64) NOT NULL,
		`return_flight_number` varchar(64) NOT NULL,
		`nr_people` int(11) NOT NULL,
		`deleted` boolean default false,
		PRIMARY KEY (`id`)
	) $charset_collate;";

$clientsSql = "CREATE TABLE $clientsTB (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`client` varchar(50) not null,
		`location` varchar(50) not null,
		`tax_number` varchar(50) not null,
		`contact` varchar(50) not null,
		`email` varchar(50) not null,
		`tel` varchar(50) not null,
		`address` text not null,
		`inv_date` int(2),
		PRIMARY KEY (`id`)
	) $charset_collate;";

$additionalServicesProductsSql = "CREATE TABLE $additionalServicesProductsTB (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`add_ser_id` int(11) not null,
		`product_id` int(11) not null,
		PRIMARY KEY (`id`)
	) $charset_collate;";

$additionalServicesSql = "CREATE TABLE $additionalServicesTB (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(64) not null,
		`price` double not null,
		PRIMARY KEY (`id`)
	) $charset_collate;";

$pricesSql = "CREATE TABLE $pricesTB (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(255) DEFAULT NULL,
		`day_1` int(11) DEFAULT NULL,
		`day_2` int(11) DEFAULT NULL,
		`day_3` int(11) DEFAULT NULL,
		`day_4` int(11) DEFAULT NULL,
		`day_5` int(11) DEFAULT NULL,
		`day_6` int(11) DEFAULT NULL,
		`day_7` int(11) DEFAULT NULL,
		`day_8` int(11) DEFAULT NULL,
		`day_9` int(11) DEFAULT NULL,
		`day_10` int(11) DEFAULT NULL,
		`day_11` int(11) DEFAULT NULL,
		`day_12` int(11) DEFAULT NULL,
		`day_13` int(11) DEFAULT NULL,
		`day_14` int(11) DEFAULT NULL,
		`day_15` int(11) DEFAULT NULL,
		`day_16` int(11) DEFAULT NULL,
		`day_17` int(11) DEFAULT NULL,
		`day_18` int(11) DEFAULT NULL,
		`day_19` int(11) DEFAULT NULL,
		`day_20` int(11) DEFAULT NULL,
		`day_21` int(11) DEFAULT NULL,
		`day_22` int(11) DEFAULT NULL,
		`day_23` int(11) DEFAULT NULL,
		`day_24` int(11) DEFAULT NULL,
		`day_25` int(11) DEFAULT NULL,
		`day_26` int(11) DEFAULT NULL,
		`day_27` int(11) DEFAULT NULL,
		`day_28` int(11) DEFAULT NULL,
		`day_29` int(11) DEFAULT NULL,
		`day_30` int(11) DEFAULT NULL,
		`user_id` BIGINT(20) UNSIGNED,
		PRIMARY KEY (`id`)
	) $charset_collate;";

$eventsSql = "CREATE TABLE $eventsTB (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`datefrom` timestamp default current_timestamp,
		`dateto` timestamp default current_timestamp,
		`price_id` int(11) NOT NULL,
		`product_id` int(11) default null,
		constraint `event_price_fk` foreign key(`price_id`) references " . $wpdb->prefix . "itweb_prices(`id`) on delete cascade,
		PRIMARY KEY (`id`)
	) $charset_collate;";

$parklotsSql = "CREATE TABLE $parklotsTB (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`parkhaus` varchar(20) not null,
		`parklot` varchar(20) not null,
		`datefrom` timestamp default current_timestamp,
		`dateto` timestamp default current_timestamp,
		`type` varchar(10) not null,
		`contigent` int(11) not null,
		`booking_lead_time` int(11) not null,
		`product_id` int(11) default null,
		`client_id` int(11) default null,
		PRIMARY KEY (`id`)
	) $charset_collate;";

$restrictionsSql = "CREATE TABLE $restrictionsTB (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`darum` varchar(64) not null,
		`date` date default current_date,
		`time` time default current_time,
		`product_id` int(11) default null,
		PRIMARY KEY (`id`)
	) $charset_collate;";

$discountsSql = "CREATE TABLE $discountsTB (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(20) not null,
		`interval_from` date null,
		`interval_to` date null,
		`type` varchar(10) not null,
		`value` varchar(10) not null,
		`days_before` int(11) null,
		`discount_contigent` int(11) null,
		`product_id` int(11) default null,
		`cancel` varchar(2) DEFAULT NULL,
		`message` text null,
		PRIMARY KEY (`id`)
	) $charset_collate;";

$orderCancellationsSql = "CREATE TABLE $orderCancellationsTB (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`hours_before` varchar(10) not null,
		`type` varchar(10) not null,
		`value` int(11) not null,
		`product_id` int(11) default null,
		PRIMARY KEY (`id`)
	) $charset_collate;";

$brokersSql = "CREATE TABLE $brokersTB (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`company` varchar(20) not null,
		`title` varchar(5) not null,
		`firstname` varchar(20) not null,
		`lastname` varchar(20) not null,
		`street` varchar(64) not null,
		`zip` varchar(20) not null,
		`location` varchar(20) not null,
		PRIMARY KEY (`id`)
	) $charset_collate;";

$brokersProductsSql = "CREATE TABLE $brokersProductsTB (
		`product_id` int(11) default null,
		`broker_id` int(11) default null
	) $charset_collate;";

$hotelTransfersSql = "CREATE TABLE $hotelTransfersTB (
        `id` int(11) NOT NULL AUTO_INCREMENT,
		`product_id` int(11) not null,
		`user_id` int(11) default null,
		`order_id` int(11) not null,
		`datefrom` date default null,
		`dateto` date default null,
		`transfer_vom_hotel` time default null,
		`ankunftszeit_ruckflug` time default null,
		`hinflug_nummer` varchar(20) not null,
		`ruckflug_nummer` varchar(20) not null,
		`token` varchar(10) not null,
		PRIMARY KEY (`id`)
	) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

dbDelta($ordersSql);
dbDelta($clientsSql);
dbDelta($pricesSql);
dbDelta($additionalServicesProductsSql);
dbDelta($additionalServicesSql);
dbDelta($eventsSql);
dbDelta($parklotsSql);
dbDelta($restrictionsSql);
dbDelta($discountsSql);
dbDelta($orderCancellationsSql);
dbDelta($brokersSql);
dbDelta($brokersProductsSql);
dbDelta($hotelTransfersSql);