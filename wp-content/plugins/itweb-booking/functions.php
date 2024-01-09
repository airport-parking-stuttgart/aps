<?php

function dateFormat($date, $format = 'en')
{
    $d = null;
    switch ($format) {
        case 'de':
            return date('d.m.Y', strtotime($date));
            break;
        case 'en':
        default:
            return date('Y-m-d', strtotime($date));
            break;
    }
}
function isok($arr, $param)
{
    return isset($arr[$param]) && !empty($arr[$param]);
}

function array_csv_download($title, $date, $array, $filename = "export.csv", $delimiter = ",")
{
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    // clean output buffer
    ob_end_clean();

    $handle = fopen('php://output', 'w');
    fputs($handle, $title . "\n\n");
    fputcsv($handle, ['Datum', $date], $delimiter);
    fputs($handle, "\n");

    // use keys as column titles
    fputcsv($handle, array_keys($array['0']));

    foreach ($array as $value) {
        fputcsv($handle, $value, $delimiter);
    }

    fclose($handle);

    // flush buffer
    ob_flush();

    // use exit to get rid of unexpected output afterward
    exit();
}


function addDay($date, $day)
{
    $dateObj = new DateTime($date);
    $tmp = $dateObj->modify('+' . $day . ' day');
    return $tmp->format('Y-m-d');
}

/**
 * Method to delete Woo Product
 *
 * @param int $id the product ID.
 * @param bool $force true to permanently delete product, false to move to trash.
 * @return \WP_Error|boolean
 */
function deleteWCProduct($id, $force = FALSE)
{
    $product = wc_get_product($id);

    if (empty($product))
        return new WP_Error(999, sprintf(__('No %s is associated with #%d', 'woocommerce'), 'product', $id));

    // If we're forcing, then delete permanently.
    if ($force) {
        if ($product->is_type('variable')) {
            foreach ($product->get_children() as $child_id) {
                $child = wc_get_product($child_id);
                $child->delete(true);
            }
        } elseif ($product->is_type('grouped')) {
            foreach ($product->get_children() as $child_id) {
                $child = wc_get_product($child_id);
                $child->set_parent_id(0);
                $child->save();
            }
        }

        $product->delete(true);
        $result = $product->get_id() > 0 ? false : true;
    } else {
        $product->delete();
        $result = 'trash' === $product->get_status();
    }

    if (!$result) {
        return new WP_Error(999, sprintf(__('This %s cannot be deleted', 'woocommerce'), 'product'));
    }

    // Delete parent product transients.
    if ($parent_id = wp_get_post_parent_id($id)) {
        wc_delete_product_transients($parent_id);
    }
    return true;
}

function to_float($nr)
{
    return number_format((float)$nr, 2, '.', '');
}


// Function to get all the dates in given range
function getTimesFromRange($a, $b)
{
    $array = [];

    // convert the strings to unix timestamps
    $a = strtotime($a);
    $b = strtotime($b);

    // loop over every hour (3600sec) between the two timestamps
    for ($i = 0; $i <= $b - $a; $i += 3600) {
        // add the current iteration and echo it
        $array[] = date('H:i', $a + $i);
    }

    return $array;
}

// String ends with function
function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if (!$length) {
        return true;
    }
    return substr($haystack, -$length) === $needle;
}


/*
 * Start: Token Generator
 */
function crypto_rand_secure($min, $max)
{
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int)($log / 8) + 1; // length in bytes
    $bits = (int)$log + 1; // length in bits
    $filter = (int)(1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}

function getToken($length)
{
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet .= "0123456789";
    $max = strlen($codeAlphabet); // edited

    for ($i = 0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, $max - 1)];
    }

    return $token;
}

function generateToken($length)
{
    $token = getToken($length);
    $orders = wc_get_orders(array(
        'limit' => -1, // Query all orders
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_key' => 'token', // The postmeta key field
        'meta_value' => $token,
        'meta_compare' => 'EXISTS', // The comparison argument
    ));
    $token = getToken(5);
    if (count($orders) > 0) {
        return generateToken(5);
    }
    return $token;
}
