<?php

//function getDaysBetween2Dates(DateTime $date1, DateTime $date2, $absolute = true)
//{
//    $interval = $date2->diff($date1);
//    // if we have to take in account the relative position (!$absolute) and the relative position is negative,
//    // we return negatif value otherwise, we return the absolute value
//    return (!$absolute and $interval->invert) ? - $interval->days : $interval->days;
//}
//
//function getParklotPrice($proId, $datefrom, $dateto){
//    global $wpdb;
//    $datefrom = new DateTime($datefrom);
//    $dateto = new DateTime($dateto);
//    $parklot = $wpdb->get_row("
//            select parklots.operator_id from {$wpdb->prefix}itweb_parklots parklots, {$wpdb->prefix}itweb_products products
//            where parklots.id = products.lotid and products.productid = {$proId}
//        ");
//    $sql = "select events.datefrom, prices.* from wp_itweb_events events, wp_itweb_prices prices where Date(datefrom) = Date('" . $datefrom->format('Y-m-d H:i:s') . "') and events.price_id = prices.id and prices.operator_id = {$parklot->operator_id};";
//    $row = $wpdb->get_row($sql);
//    $row = json_decode(json_encode($row), true);
//    $days = $datefrom->diff($dateto)->days;
//    return number_format((float)$row['day_' . $days], 2, '.', '');
//}

function get_product_reviews( $id, $fields = null ) {
    if ( is_wp_error( $id ) ) {
        return $id;
    }

    $comments = get_approved_comments( $id );
    $reviews  = array();

    foreach ( $comments as $comment ) {

        $reviews[] = array(
            'id'             => intval( $comment->comment_ID ),
            'created_at'     => $comment->comment_date_gmt,
            'review'         => $comment->comment_content,
            'rating'         => get_comment_meta( $comment->comment_ID, 'rating', true ),
            'reviewer_name'  => $comment->comment_author,
            'reviewer_email' => $comment->comment_author_email,
            'verified'       => wc_review_is_from_verified_owner( $comment->comment_ID ),
        );
    }

    return array( 'product_reviews' => apply_filters( 'woocommerce_api_product_reviews_response', $reviews, $id, $fields, $comments ) );
}