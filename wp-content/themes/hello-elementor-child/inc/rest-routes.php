<?php

// get events
add_action('rest_api_init', 'getEvents');
function getEvents()
{
    register_rest_route('api', 'events', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => function () {
            global $wpdb;
            $parklot_id = (int)$_GET['lotid'];
            $operator_id = (int)$_GET['oid'];
            if(isset($_GET['oid'])){
                return $wpdb->get_results("select events.id, events.datefrom as start, events.dateto as end, prices.name as title from " . $wpdb->prefix . "itweb_events events,  " . $wpdb->prefix . "itweb_prices prices where events.price_id = prices.id and prices.operator_id = " . $operator_id);
            }
            return $wpdb->get_results("select events.id, events.datefrom as start, events.dateto as end, prices.name as title from " . $wpdb->prefix . "itweb_events events,  " . $wpdb->prefix . "itweb_prices prices where events.price_id = prices.id and events.parklot_id = " . $parklot_id);
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
            $parklot_id = (int)$_POST['parklot_id'];
            $res = $wpdb->query("SELECT * FROM `wp_itweb_events` WHERE ('" . $_POST['datefrom'] . "' BETWEEN datefrom AND dateto) and parklot_id = " . $parklot_id);
            if ($res > 0) {
                return false;
            }
            $sql = "insert into " . $wpdb->prefix . "itweb_events(`datefrom`, `dateto`, `price_id`, `parklot_id`) values('" . $_POST['datefrom'] . "', '" . $_POST['dateto'] . "', " . $price_id . ", " . $parklot_id . ")";
            $wpdb->query($sql);
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
            $wpdb->query("delete from " . $wpdb->prefix . "itweb_events where id = " . $id);
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
            $res = $wpdb->query("SELECT * FROM `wp_itweb_events` WHERE ('" . $_POST['datefrom'] . "' BETWEEN datefrom AND dateto)");
            if ($res > 0) {
                return false;
            }
//            return "update ".$wpdb->prefix."itweb_events set datefrom = '".$_POST['datefrom']."', dateto = '".$_POST['dateto']."' where id = " . $id;
            $wpdb->query("update " . $wpdb->prefix . "itweb_events set datefrom = '" . $_POST['datefrom'] . "', dateto = '" . $_POST['dateto'] . "' where id = " . $id);
        }
    ]);
}

// create price
add_action('rest_api_init', 'createPrice');
function createPrice()
{
    register_rest_route('api', 'create-price', [
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => function () {
            global $wpdb;
            $wpdb->insert($wpdb->prefix . 'itweb_prices', $_POST);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    ]);
}

// delete price
add_action('rest_api_init', 'deletePrice');
function deletePrice()
{
    register_rest_route('api', 'delete-price', [
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => function () {
            global $wpdb;
            parse_str(file_get_contents('php://input'), $_DELETE);
            $res = $wpdb->delete($wpdb->prefix . 'itweb_prices', ['id' => (int)$_DELETE['id']]);
            return $res ? ['statusCode' => 200] : ['statusCode' => 500];
        }
    ]);
}

// update price
add_action('rest_api_init', 'updatePrice');
function updatePrice()
{
    register_rest_route('api', 'update-price', [
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => function () {
            global $wpdb;
            $id = (int)$_POST['id'];
            unset($_POST['id']);
            $wpdb->update($wpdb->prefix . 'itweb_prices', $_POST, ['id' => $id]);

            $url = $_SERVER['HTTP_REFERER'];
            $parsed = parse_url($url);
            $query = $parsed['query'];
            parse_str($query, $params);
            unset($params['edit_price']);
            $string = http_build_query($params);
            $string = '/wp-admin/admin.php?' . $string;
            header('Location: ' . $string);
            exit;
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
                return ['error' => 'Please enter token!'];
            }
			
			if(get_post_meta($_GET['token'], 'Nicht_stornierbar', true) == 1)
				return ['error' => 'Buchung kann nicht storniert werden.'];

            $isApi = strlen($_GET['token']) === 6 || strlen($_GET['token']) === 7;
            $bookingRef = $_GET['token'];
            $data = array(
                'limit' => -1, // Query all orders
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_value' => $_GET['token'],
                'meta_compare' => 'EXISTS', // The comparison argument
            );

            if($isApi){
                $data['meta_key'] = '_booking_ref';
            }else{
                $data['meta_key'] ='token';
            }
            $orders = wc_get_orders($data);

            if (count($orders) <= 0) {
                return ['message' => 'No orders found'];
            }

            foreach ($orders as $order) {
                $order->update_status('cancelled');
            }

            if(!$isApi){
                return ['message' => 'ok'];
            }
            $ch = curl_init();
            $searchParams = "?ABTANumber=".HOLIDAYEXTRA_ABTANumber."&Password=".HOLIDAYEXTRA_PASSWORD."&key=".HOLIDAYEXTRA_KEY."&System=ABG&ConfirmCancel=N&CancelRef=" . $bookingRef;
            curl_setopt($ch, CURLOPT_URL, HOLIDAYEXTRA_API . "/booking/".$bookingRef."/".$searchParams);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = simplexml_load_string(curl_exec($ch));
            $server_output = json_decode(json_encode($server_output), true);
            curl_close($ch);
            if($server_output['@attributes']['Result'] == 'OK'){
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, HOLIDAYEXTRA_API . "/booking/".$bookingRef);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,
                    http_build_query(array(
                        'ABTANumber' => HOLIDAYEXTRA_ABTANumber,
                        'key' => HOLIDAYEXTRA_KEY,
                        'Password' => HOLIDAYEXTRA_PASSWORD,
                        'System' => 'ABG',
                        'ConfirmCancel' => 'Y',
                        'CancelRef' => $bookingRef
                    )));

// Receive server response ...
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $server_output = simplexml_load_string(curl_exec($ch));
                $server_output = json_decode(json_encode($server_output), true);
                curl_close($ch);
            }else{
                return ['error' => $server_output['Error']['Message']];
            }

            return ['message' => 'ok'];
        }
    ]);
}

// edit comment order
add_action('rest_api_init', 'approveComment');
function approveComment()
{
    register_rest_route('api', 'edit-comment', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => function () {
            global $wpdb;
            $table = $wpdb->prefix . 'comments';

//            if ((!isset($_GET['ca']) && empty($_GET['ca'])) || (!isset($_GET['cu']) && empty($_GET['cu']))) {
//                return ['error' => 'Please enter comment ID!'];
//            }

            if(isset($_GET['ca'])){
                $wpdb->update($table, ['comment_approved' => 1], ['comment_ID'=>(int)$_GET['ca']]);
            }else if(isset($_GET['cu'])){
                $wpdb->update($table, ['comment_approved' => 0], ['comment_ID'=>(int)$_GET['cu']]);
            }
            header('Location: /wp-admin/edit-comments.php');
            exit;
        }
    ]);
}

// delete event
add_action('rest_api_init', 'deleteCTS');
function deleteCTS()
{
    register_rest_route('api', 'delete-cts', [
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => function () {
            global $wpdb;
            $id = (int)$_GET['id'];
            $table = $_GET['table'];
            $wpdb->query("delete from " . $wpdb->prefix . "itweb_{$table} where id = " . $id);
        }
    ]);
}

// save api code
add_action('rest_api_init', 'saveApiCode');
function saveApiCode()
{
    register_rest_route('api', 'save-api-code', [
        'methods' => [WP_REST_Server::CREATABLE],
        'callback' => function () {
            global $wpdb;
            $table = $wpdb->prefix . 'holidayextra_whitelist';
            $code = $_POST['code'];
            $city = $_POST['city'];
            $name = $_POST['name'];
            $row = $wpdb->get_row("select * from $table where code = '$code'");
            if ($row) {
                $wpdb->delete($table, ['code' => $code]);
                return ['msg' => 'deleted'];
            } else {
                $wpdb->insert($table, [
                    'code' => $code,
                    'name' => $name,
                    'country' => $city
                ]);
                return ['msg' => 'inserted'];
            }
        }
    ]);
}