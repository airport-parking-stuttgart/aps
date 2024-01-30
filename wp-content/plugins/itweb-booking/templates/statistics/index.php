<div class="page container-fluid <?php echo $_GET['page'] ?>">
	<div class="page-logo">
		<img class="adm-logo" src="<?php echo home_url(); ?>/wp-content/uploads/2021/08/AP-Management-System-klein.png" alt="" width="300" height="200">
	</div>

<?php
global $wpdb;

$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : null;
$startDate = isset($_GET['start-date']) ? $_GET['start-date'] : null;
$endDate = isset($_GET['end-date']) ? $_GET['end-date'] : null;
$currentMonth = (int)date('n');
$months = [];
$startMonth = $startDate ? (int)explode('.', $startDate)[1] : null;
$endMonth = $endDate ? (int)explode('.', $endDate)[1] : null;
$startDateEn = $startDate ? date_format(date_create($startDate), 'Y-m-d') : null;
$endDateEn = $endDate ? date_format(date_create($endDate), 'Y-m-d') : null;

$total_data = getTotal($year, $month, $startDate, $endDate, $startDateEn, $endDateEn);

function getTotal($year, $month, $startDate, $endDate, $startDateEn, $endDateEn)
{
    global $wpdb;
    $monthSql = '';
    if ($startDate) {
        $monthSql .= " and date(post.post_date) between date('{$startDateEn}') and date('{$endDateEn}')";
    } else {
        $monthSql .= " and year(post.post_date) = {$year}";
        if ($month) {
            $monthSql .= " and month(post.post_date) = {$month}";
        }
    }

    $total = $wpdb->get_row("select 
        sum(postmeta.meta_value/(1+0.19)) total_payments, 
        count(postmeta.meta_value) total_orders
        from {$wpdb->prefix}postmeta postmeta
        join {$wpdb->prefix}posts post on post.id = postmeta.post_id
        join {$wpdb->prefix}wc_order_product_lookup op on post.id = op.order_id
        join {$wpdb->prefix}itweb_parklots parklots on op.product_id = parklots.product_id
        where postmeta.meta_key = '_order_total'" . $monthSql);
    return $total;
}

foreach (_MONTHS as $key => $value) {
    if ($year == (int)date('Y') && $key > $currentMonth) {
        break;
    }
    if ($month && $month != $key) {
        continue;
    }
    if ($startDate && ($key < $startMonth || $key > $endMonth)) {
        continue;
    }
    $monthSql = " and year(post.post_date) = {$year} and month(post.post_date) = '" . $key . "'";
    if ($startDate) {
        $monthSql .= " and date(post.post_date) between date('{$startDateEn}') and date('{$endDateEn}')";
    }

    $total = $wpdb->get_row("select 
        sum(postmeta.meta_value/(1+0.19)) total_payments, 
        count(postmeta.meta_value) total_orders
        from {$wpdb->prefix}postmeta postmeta
        join {$wpdb->prefix}posts post on post.id = postmeta.post_id
        join {$wpdb->prefix}wc_order_product_lookup op on post.id = op.order_id
        join {$wpdb->prefix}itweb_parklots parklots on op.product_id = parklots.product_id
        where  postmeta.meta_key = '_order_total'" . $monthSql);
    $months[$key] = [
        'key' => $key,
        'month' => $value,
        'payments' => number_format((float)$total->total_payments, 2, '.', ''),
        'orders' => $total->total_orders
    ];
}
?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages': ['corechart']});
    google.charts.setOnLoadCallback(drawVisualization);

    function drawVisualization() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable([
            ['Monate', 'Zahlungen', 'Bestellungen'],
            <?php
            foreach ($months as $m) {
                $tmpMonth = str_pad($m['key'], 2, 0, STR_PAD_LEFT);
                echo "['{$year}/{$tmpMonth}', {$m['payments']}, {$m['orders']}],";
            }
            ?>
        ]);

        var options = {
            title: 'Zahlungen und Bestellungen',
            vAxis: {title: 'Menge'},
            hAxis: {title: 'Monat'},
            seriesType: 'bars',
            series: {5: {type: 'line'}}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('combochart'));
        chart.draw(data, options);
    }
</script>
<style>
    body {
        background: white;
    }
</style>
    <div class="page-title itweb_adminpage_head">
        <h3>Statistics</h3>
    </div>
    <div class="page-body">

        <div class="filters">
            <select name="year" class="statistics-item">
                <option value="">Jahr</option>
                <?php for ($i = 2000; $i <= (int)date('Y'); $i++) : ?>
                    <option value="<?php echo $i ?>" <?php echo $i == $year ? ' selected' : '' ?>>
                        <?php echo $i ?>
                    </option>
                <?php endfor; ?>
            </select>
            <select name="month" class="statistics-item">
                <option value="">Monat</option>
                <?php foreach (_MONTHS as $key => $value) : ?>
                    <?php
                    if ($year == (int)date('Y') && $currentMonth < $key) {
                        break;
                    }
                    ?>
                    <option value="<?php echo $key ?>" <?php echo $key == $month ? ' selected' : '' ?>>
                        <?php echo $value ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <a href="" class="if-filters-a">Filter zwischen Datum</a>
        <div class="interval-filters" style="<?php echo $startDate ? '' : 'display: none;' ?>">
            <div class="if-form">
                <input type="text" id="ifStartDate" class="air-datepicker" value="<?php
                echo isset($_GET['start-date']) ? date('Y-m-d', strtotime($_GET['start-date'])) : '';
                ?>">
                <input type="text" id="ifEndDate" class="air-datepicker" value="<?php
                echo isset($_GET['end-date']) ? date('Y-m-d', strtotime($_GET['end-date'])) : ''
                ?>">
                <a href="" class="btn btn-primary" id="ifSubmit">Filter</a>
            </div>
        </div>
        <div class="top-data">
            <div class="top-data-left">
                <div class="period">
                    <?php if ($startDate) : ?>
                        <p><span class="dashicons dashicons-calendar-alt"></span></span>
                            <?php echo $startDate ?> <span
                                    class="dashicons dashicons-arrow-right-alt"></span> <?php echo $endDate ?></p>
                    <?php else : ?>
                        <p>
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php echo $year ?><?php if ($month) : ?>.<?php echo str_pad($month, 2, 0, STR_PAD_LEFT) ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="orders-data">
                    <p>€ <?php echo number_format((float)$total_data->total_payments, 2, '.', '') ?></p>
                    <p><?php echo $total_data->total_orders ?> Summe Buchungen</p>
                </div>
            </div>
            <div class="icon">
                <span class="dashicons dashicons-tickets-alt"></span>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="chart" id="combochart"></div>
    </div>
</div>