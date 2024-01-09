<?php
add_action('elementor/query/itweb_filter', function ($query) {
    global $wpdb;
    $sql = "
        select 
            parklots.*,
            count(orders.product_id) as ordersTotal,
            orders.order_id
        from 
             {$wpdb->prefix}itweb_parklots parklots
        left join
            {$wpdb->prefix}itweb_orders orders on
            parklots.product_id = orders.product_id
        where 1
    ";
    if(isset($_GET['datefrom'])){
        $sql .= " and ('" . dateFormat($_GET['datefrom']) . "' between parklots.datefrom and parklots.dateto
            or '" . dateFormat($_GET['dateto']) . "' between parklots.datefrom and parklots.dateto)";
    }
    $sql .= " 
    group by parklots.id
    having parklots.contigent > ordersTotal
    order by parklots.id";
    // die($sql);
    $results = $wpdb->get_results($sql, ARRAY_A);
    // die(var_dump($results));
    $post_ids = wp_list_pluck($results, 'product_id');

    if (count($post_ids) <= 0) {
        $post_ids = [-1];
    }
    $query->set('post_type', 'product');
    $query->set('post_status', 'publish');
    $query->set('post__in', $post_ids);
});