<?php

class Parklot
{
    private $parklot;

    public function __construct($id)
    {
        $this->parklot = Database::getInstance()->getParklotByProductId($id);
    }

    public function canOrderLeadTime($date, $time)
    {
        $dateFrom = date('Y-m-d H:i', strtotime($date . ' ' . $time));
        $now = date('Y-m-d H:i');
        $now = date('Y-m-d H:i', strtotime('+1 hour', strtotime($now)));

        return round((strtotime($dateFrom) - strtotime($now)) / (60 * 60)) >= $this->parklot->booking_lead_time;
    }

    public function getContigent()
    {
        return $this->parklot->contigent;
    }

    public function getUsedTimes($productId, $date)
    {
        global $wpdb;
        $sql = "
        select
            orders.*,
            count(orders.id) as total_orders 
        from 
            {$wpdb->prefix}itweb_orders orders
        where
            product_id = $productId and date(`date_from`) = date('$date')
        group
            by orders.id
        having
            total_orders >= " . $this->getContigent() . "
        order
            by orders.id
        ";

        return $wpdb->get_results($sql);
    }
}
