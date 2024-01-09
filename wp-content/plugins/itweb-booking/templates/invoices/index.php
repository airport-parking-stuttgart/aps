<?php
global $wpdb;
$sql = "select 
orders.order_id,
orders.date_created,
parklots.parklotname,
itweb_orders.date, itweb_orders.time_from, itweb_orders.time_to, itweb_orders.ships_nr,
pm.meta_value customer_firstname
 from 
 {$wpdb->prefix}wc_order_product_lookup orders,
 {$wpdb->prefix}itweb_products products, 
 {$wpdb->prefix}itweb_parklots parklots, 
 {$wpdb->prefix}itweb_categories categories,
 {$wpdb->prefix}postmeta pm,
 {$wpdb->prefix}itweb_orders itweb_orders
 where orders.product_id = products.productid and products.lotid = parklots.id 
 and itweb_orders.order_id = orders.order_id and categories.id = parklots.category
 and pm.post_id = itweb_orders.order_id and pm.meta_key = '_billing_first_name'";

if(isset($_GET['date-from']) && isset($_GET['date-to'])){
    $datefrom = date('Y-m-d', strtotime($_GET['date-from']));
    $dateto = date('Y-m-d', strtotime($_GET['date-to']));
    $sql .= " and date(itweb_orders.date) between date('".$datefrom."') and date('".$dateto."')";
}
if(isset($_GET['cat'])){
    $sql .= " and categories.id = " . $_GET['cat'];
}

$args = array(
    'role' => 'operator',
    'orderby' => 'user_nicename',
    'order' => 'ASC'
);
$users = get_users($args);

$orders = $wpdb->get_results($sql);

?>
<h1>Buchungen</h1>
<div class="inv-filters">
    <div class="filter-item">
        <label for="">Date From</label>
        <input type="text" data-language="de" class="date-from single-datepicker"
               value="<?php echo isset($_GET['date-from']) ? date('d.m.Y', strtotime($_GET['date-from'])) : '' ?>">
    </div>
    <div class="filter-item">
        <label for="">Date To</label>
        <input type="text" data-language="de" class="date-to single-datepicker"
               value="<?php echo isset($_GET['date-to']) ? date('d.m.Y', strtotime($_GET['date-to'])) : '' ?>">
    </div>
    <div class="filter-item">
        <label for="">Category</label>
        <select name="" id="category">
            <option value=""></option>
            <?php foreach(Database::getInstance()->getCategories() as $category) : ?>
            <option value="<?php echo $category->id ?>" <?php echo $category->id == $_GET['cat'] ? 'selected' : '' ?>>
                <?php echo $category->category ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-item">
        <label for="">&nbsp;</label>
        <button class="button button-primary" id="invFilter">
            Filter
        </button>
    </div>
</div>
<div class="order-invoices">
    <table class="datatable" border="1">
        <thead>
        <tr>
            <th>Buchungs-Nr.</th>
            <th>Name</th>
            <th>Buchungsdatum</th>
            <th>Kunde</th>
            <th>Anreisedatum</th>
            <th>Anreisezeit</th>
<!--            <th>Abreisedatum</th>-->
            <th>Abreisezeit</th>
            <th>Anzahl der Schiffe</th>
            <th>Preis</th>
            <th>Zahlungsmethode</th>
            <th>Status</th>
            <th>Edit</th>
            <!--            <th>Generate Invoice</th>-->
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order) : ?>
            <?php $woo_order = wc_get_order($order->order_id); ?>
            <tr>
                <td>
                    <?php echo get_post_meta($woo_order->get_id(), 'token', true) ?>
                </td>
                <td><?php echo $order->parklotname ?></td>
                <td><?php echo date_format(date_create($order->date_created), 'd.m.Y') ?></td>
                <td><?php echo $order->customer_firstname ?></td>
                <td><?php echo date_format(date_create($order->date), 'd.m.Y') ?></td>
                <td><?php echo date_format(date_create($order->time_from), 'H:i') ?></td>
<!--                <td>--><?php //echo date_format(date_create($order->dateto), 'd.m.Y') ?><!--</td>-->
                <td><?php echo date_format(date_create($order->time_to), 'H:i') ?></td>
                <td class="text-right">
                    <?php echo $order->ships_nr ?>
                </td>
                <td class="text-right"><?php echo $woo_order->get_total() ?> €</td>
                <td>
                    <?php echo $woo_order->get_payment_method_title() ?>
                </td>
                <td>
                    <?php echo $woo_order->get_status() ?>
                </td>
                <td>
                    <a target="_blank" href="/wp-admin/post.php?post=<?php echo $woo_order->get_id() ?>&action=edit">
                        Edit
                    </a>
                </td>
                <!--                <td>-->
                <!--                    <a target="_blank" href="/wp-admin/admin-ajax.php?action=generate_wpo_wcpdf&document_type=invoice&order_ids=-->
                <?php //echo $order->order_id ?><!--&_wpnonce=f29d84767b">Generate Invoice</a>-->
                <!--                </td>-->
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<!--    <div class="custom-filter-parent">-->
<!--        --><?php //if (Helper::getInstance()->isAdmin()) : ?>
<!--            <select name="operator_id" id="operatorInvoices">-->
<!--                <option value="">Betreiber</option>-->
<!--                --><?php //foreach ($users as $user) : ?>
<!--                    <option value="--><?php //echo $user->display_name ?><!--">--><?php //echo $user->display_name ?><!--</option>-->
<!--                --><?php //endforeach; ?>
<!--            </select>-->
<!--        --><?php //endif; ?>
<!--    </div>-->
</div>