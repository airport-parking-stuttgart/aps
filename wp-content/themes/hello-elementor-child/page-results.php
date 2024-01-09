<?php

/**
 * Template Name: Result-Page
 */


function mydd($array, $name = 'data')
{
    echo "<div style='border: 2px solid black; position: relative; margin: 50px; padding: 50px; background-color: #ddd;'";
    highlight_string("<?php\n$$name =\n" . var_export($array, true) . ";\n?>");
    echo "</div>";
}

$preis_value_arr = [];


session_unset();
session_destroy();
global $wpdb;
$location = isset($_GET['location']) ? $_GET['location'] : null;
$ratingGet = isset($_GET['rating']) ? $_GET['rating'] : null;

//$parklotObj = new Parklot($product->get_id());

$startDate = isset($_GET['datefrom']) ? date_format(date_create($_GET['datefrom']), 'Y-m-d') : date('Y-m-d');
$endDate = isset($_GET['dateto']) ? date_format(date_create($_GET['dateto']), 'Y-m-d') : date('Y-m-d');
$currentLocation = $wpdb->get_row("select * from {$wpdb->prefix}itweb_locations where id = {$location}");
$locations = $wpdb->get_results("select * from {$wpdb->prefix}itweb_locations");

$dayDiff = getDaysBetween2Dates(new DateTime($startDate), new DateTime($endDate));
$startDateEn = date_format(date_create($startDate), 'Y-m-d H:i');
$endDateEn = date_format(date_create($endDate), 'Y-m-d H:i');
$startDateOnly = date_format(date_create($startDate), 'Y-m-d');
$endDateOnly = date_format(date_create($endDate), 'Y-m-d');

$conDate[0] = $startDateOnly;
$conDate[1] = $endDateOnly;
$allContingent = Database::getInstance()->getAllContingent($conDate);
foreach ($allContingent as $ac) {
    $set_con[$ac->date . "_" . $ac->product_id] = $ac->contingent;
}
$parkings = [];
//die(var_dump($parkings));
$_SESSION['startDateOnly'] = $startDateOnly;
$_SESSION['endDateOnly'] = $endDateOnly;

$period = new DatePeriod(
    new DateTime($startDateOnly),
    new DateInterval('P1D'),
    new DateTime($endDateOnly . '+1 day')
);
$periodCount = iterator_count($period);

$sql = "select parklots.*";

foreach ($period as $key => $value) {
	$date = $value->format('Y-m-d');
	$sql .= ", COUNT(DISTINCT CASE WHEN '{$date}' BETWEEN DATE(orders.date_from) AND DATE(orders.date_to) THEN orders.id END) AS used_{$value->format('Y_m_d')} ";
}
$sql .= "FROM {$wpdb->prefix}itweb_parklots parklots 
			LEFT JOIN {$wpdb->prefix}itweb_orders orders ON parklots.product_id = orders.product_id 
			LEFT JOIN {$wpdb->prefix}posts po ON po.ID = orders.order_id 
			WHERE ('$startDateOnly' BETWEEN parklots.datefrom AND parklots.dateto) AND ('$endDateOnly' BETWEEN parklots.datefrom AND parklots.dateto) 
			AND parklots.is_for = 'betreiber' AND parklots.type = 'shuttle' AND parklots.deleted = 0 and po.post_status = 'wc-processing' ";

// value="parkhaus" 
//  value="parkplatz" 
//  value="parkhaus_nicht"
//  value="parkplatz_nicht" 

// TYPE 
$parkhausGET = isset($_GET['parkhaus']) ? $_GET['parkhaus'] : null;
if ($parkhausGET != null) {
    if ($parkhausGET == 'parkhaus') {
        $sql .= " and (parklots.parkhaus = 'Parkhaus überdacht') ";
    } elseif ($parkhausGET == 'parkplatz') {
        $sql .= " and (parklots.parkhaus = 'Parkplatz überdacht' ) ";
    } elseif ($parkhausGET == 'parkhaus_nicht') {
        $sql .= " and (parklots.parkhaus = 'Parkhaus nicht überdacht') ";
    } elseif ($parkhausGET == 'parkplatz_nicht') {
        $sql .= " and (parklots.parkhaus = 'Parkplatz nicht überdacht' ) ";
    }
}

//Distance
$all_distances_sql = $wpdb->prepare("SELECT DISTINCT distance FROM {$wpdb->prefix}itweb_parklots");
$all_distances_val = $wpdb->get_results($all_distances_sql);
$distanceGET = isset($_GET['distance']) ? $_GET['distance'] : null;
if ($distanceGET != null) {
    $sql .= " and parklots.distance IN ({$distanceGET}) ";
}


$sql .= " GROUP BY parklots.id order by parklots.order_lot";

$results = $wpdb->get_results($sql);


$sql = "select parklots.*";

if (isset($_POST['max_distance'])) {
    $distance_sql = 'distance =  ' . $_POST['max_distance'];
} else {
    $distance_sql = 1;
}

foreach ($period as $key => $value) {
    $date = $value->format('Y-m-d');
    $sql .= ", COUNT(DISTINCT CASE WHEN '{$date}' BETWEEN DATE(orders.date_from) AND DATE(orders.date_to) THEN orders.id END) AS used_{$value->format('Y_m_d')} ";
}
$sql .= "FROM {$wpdb->prefix}itweb_parklots parklots 
			LEFT JOIN {$wpdb->prefix}itweb_orders orders ON parklots.product_id = orders.product_id 
			LEFT JOIN {$wpdb->prefix}posts po ON po.ID = orders.order_id 
			WHERE ('$startDateOnly' BETWEEN parklots.datefrom AND parklots.dateto) AND ('$endDateOnly' BETWEEN parklots.datefrom AND parklots.dateto) 
			AND parklots.is_for = 'vermittler' AND parklots.type = 'shuttle' AND parklots.deleted = 0 and po.post_status = 'wc-processing'
			GROUP BY parklots.id ";

$sql .= "order by parklots.order_lot";



$results_apg = $wpdb->get_results($sql);





$freeParklots = count($results);
//$freeParklots = 0;
//foreach ($results as $parklot) {
//    if ($parklot->used < $parklot->contigent) {
//        $freeParklots += 1;
//    }
//}
if ($freeParklots <= 0)
    $freeParklots = 0;
//die(var_dump($results));


//Filter Price
$preis_min = 0;
$preis_max = 9999999999999999999;
$preis = isset($_GET['preis']) ? $_GET['preis'] : null;

if ($preis != null) {
    $preis_max = $preis;
}


get_header();

//echo "<pre>"; print_r($results); echo "</pre>";
?>
<style>
    .airport-img {
        max-height: 175px;
        width: 100%;
    }


    @media (min-width: 760px) {
        .shift-left {
            margin-left: -32px;
        }
    }

    @media screen and (max-width: 558px) {
        .shift-left {
            margin-left: 0px;
        }
    }

    .red-text {
        color: red !important;
    }

    @media (min-width: 760px) {
        .no-adress {
            color: #2196f3;
            user-select: none;
        }
    }

    @media (min-width: 760px) {
        .no-title {
            color: white;
            user-select: none;
        }
    }


    .book-btn {
        background-color: #076320 !important;
    }

    .tooltip-inner {
        background-color: #ffffff;
        color: #ff0000;
        font-weight: bold;
        box-shadow: 0px 0px 4px #3b8ae3;
        opacity: 1 !important;
    }
</style>
<script src="/wp-content/plugins/itweb-booking/assets/bootstrap-4.5.3-dist/js/popper.min.js"></script>
<script src="/wp-content/plugins/itweb-booking/assets/bootstrap-4.5.3-dist/js/bootstrap.min.js"></script>
<div class="body-cover">
    <div class="container">
        <div class="row">
            <div class="col-md-5">
                <div aria-label="Breadcrumbs DE" role="navigation">
                    <ul itemscope="" itemtype="https://schema.org/BreadcrumbList" class="breadcrumb">
                        <li class="active"><span class="divider icon-location"></span></li>
                        <li itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem"><a itemprop="item" href="/de/" class="pathway"><span itemprop="name">Startseite</span></a>
                            <span class="divider">
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/inc/assets/images/arrow-parking.png" alt="">
                            </span>
                            <meta itemprop="position" content="1">
                        </li>
                        <li itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem" class="active">
                            <span itemprop="name"> Parkplätze am Flughafen </span>
                            <meta itemprop="position" content="2">
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row">

            <div class="col-sm-12 col-md-12 col-lg-3">
                <div class="left-sidebar">

                    <div class="panel panel-primary">
                        <div class="panel-heading ">
                            <h5 class="text-center text-white1 text-bold pl-margin-top-5 pl-margin-bottom-5"> Parkplatz
                                suchen</h5>
                        </div>
                        <div class="panel-body ad-airport">

                            <form method="GET" action="/results/">
                                <!--<div class="form-group formgroup-rez">
                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/inc/assets/images/plan.png"
                                         id="input_img-form" alt="">

                                    <label class="icon-text text-semibold">Von wo aus fliegen Sie?</label>

                                    <select class="form-control" name="location">
                                        <option></option>
                                        <?php foreach ($locations as $l) : ?>
                                            <?php if ($location && $l->id == $location) : ?>
                                                <option value="<?php echo $l->id ?>" selected>
                                                    <?php echo $l->location ?>
                                                </option>
                                            <?php else : ?>
                                                <option value="<?php echo $l->id ?>">
                                                    <?php echo $l->location ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <!-- <input id="selectedAirport" name="selectedAirport" type="text" class="form-control typeahead" placeholder="z.B. Stuttgart" autocomplete="off" value="Stuttgart">  -->
                                <!--</div>-->

                                <div class="form-group formgroup-rez">

                                    <label for="" class="icon-text text-bold">Anreisedatum</label>
                                    <div id="an-ab-reisdatum">
                                        <!--<img src="/wp-content/uploads/2023/10/Calendar.svg" id="input_img-form" alt="">-->                   
                                        <input id="" name="datefrom" type="text" class="form-control typeahead single-datepicker" autocomplete="off" data-value="<?php echo dateFormat($startDate) ?>" required>
                                    </div>
                                </div>
                                <div class="form-group formgroup-rez">

                                    <label for="" class="icon-text text-bold">Abreisedatum</label>
                                    <div id="an-ab-reisdatum">
                                        <!--<img src="/wp-content/uploads/2023/10/Calendar.svg" id="input_img-form" alt="">-->
                                        <input id="selectedAirport" name="dateto" type="text" class="form-control typeahead single-datepicker" autocomplete="off" data-value="<?php echo dateFormat($endDate) ?>" required>
                                    </div>
                                </div>
                                <button class="btn btn-primary btn-md pl-full-width btn-suchen pl-margin-top-10" type="submit">Suchen
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="panel- panel-primary-">
                        <!-- <div class="panel-heading ">
                            <h5 class="text-center text-white1 text-bold pl-margin-top-5 pl-margin-bottom-5">Type of Parking</h5>
                        </div> -->
                        <!-- <div class="panel-body ad-airport">
                            <h4 class="panel-body-title text-semibold">Filter nach Preis:
                            </h4>
                            <input type="range" min="0" max="400" value="<?= $_GET['preis'] ?? 200; ?>" id="discountMaxSlider">
                            <div style="display: flex; gap: 7px; justify-content: space-between;">
                                <div>0€</div>
                                <div>
                                    <span id="discountMaxValue"><?= $_GET['preis'] ?? 200; ?>€</span>
                                </div>
                                <div>400€</div>
                            </div>
                        </div> -->
                    </div>

                    <div class="panel panel-primary">
                        <!-- <div class="panel-heading "> -->
                        <!-- <h5 class="text-center text-white1 text-bold pl-margin-top-5 pl-margin-bottom-5">Filter
                                nach</h5> -->
                        <!-- </div> -->
                        <div class="panel-body ad-airport">
                            <h4 class="panel-body-title text-semibold">Bewertung</h4>
                            <form action="/action_page.php" class="check-box-form" id="filterForm">
                                <div>
                                    <input class="filter-lots" type="checkbox" id="rating_1" name="rating" value="5" <?php echo in_array('5', explode(',', $ratingGet)) ? 'checked' : '' ?>>
                                    <label for="rating_1"> 5 Sterne</label>
                                </div>
                                <div>
                                    <input class="filter-lots" type="checkbox" id="rating_2" name="rating" value="4" <?php echo in_array('4', explode(',', $ratingGet)) ? 'checked' : '' ?>>
                                    <label for="rating_2"> 4 Sterne</label>
                                </div>
                                <div>
                                    <input class="filter-lots" type="checkbox" id="rating_3" name="rating" value="3" <?php echo in_array('3', explode(',', $ratingGet)) ? 'checked' : '' ?>>
                                    <label for="rating_3"> 3 Sterne</label>
                                </div>
                                <div>
                                    <input class="filter-lots" type="checkbox" id="rating_4" name="rating" value="2" <?php echo in_array('2', explode(',', $ratingGet)) ? 'checked' : '' ?>>
                                    <label for="rating_4"> 2 Sterne</label>
                                </div>
                                <div>
                                    <input class="filter-lots" type="checkbox" id="rating_5" name="rating" value="1" <?php echo in_array('1', explode(',', $ratingGet)) ? 'checked' : '' ?>>
                                    <label for="rating_5"> 1 Stern</label>
                                </div>
                                <div>
                                    <input class="filter-lots" type="checkbox" id="rating_0" name="rating" value="0" <?php echo in_array('0', explode(',', $ratingGet)) ? 'checked' : '' ?>>
                                    <label for="rating_0"> 0 Stern</label>
                                </div>
                            </form>
                            <!-- <div class="border-bott-f"></div> -->
                        </div>
                    </div>

                    <div class="panel panel-primary">
                        <!-- <div class="panel-heading ">
                            <h5 class="text-center text-white1 text-bold pl-margin-top-5 pl-margin-bottom-5">Type of Parking</h5>
                        </div> -->

                        <!-- here  -->
                        <div class="panel-body ad-airport">
                            <h4 class="panel-body-title text-semibold">Parkplatz Arten</h4>
                            <form action="/action_page.php" class="check-box-form">

                                <!-- 'Parkhaus überdacht' or parklots.parkhaus ='Parkplatz überdacht' ) ";
                                'Parkhaus nicht überdacht' or parklots.parkhaus ='Parkplatz nicht überdacht' ) "; -->

                                <div>
                                    <input class="filter-lots" type="checkbox" id="überdacht" name="parkhaus" value="parkhaus" <?php echo in_array('parkhaus', explode(',', $parkhausGET)) ? 'checked' : '' ?>>
                                    <label for="überdacht"> Parkhaus überdacht</label>
                                </div>

                                <div>
                                    <input class="filter-lots" type="checkbox" id="überdacht" name="parkhaus" value="parkplatz" <?php echo in_array('parkplatz', explode(',', $parkhausGET)) ? 'checked' : '' ?>>
                                    <label for="überdacht"> Parkplatz überdacht</label>
                                </div>



                                <div>
                                    <input class="filter-lots" type="checkbox" id="nicht" name="parkhaus" value="parkhaus_nicht" <?php echo in_array('parkhaus_nicht', explode(',', $parkhausGET)) ? 'checked' : '' ?>>
                                    <label for="nicht">Parkhaus nicht überdacht</label>
                                </div>

                                <div>
                                    <input class="filter-lots" type="checkbox" id="nicht" name="parkhaus" value="parkplatz_nicht" <?php echo in_array('parkplatz_nicht', explode(',', $parkhausGET)) ? 'checked' : '' ?>>
                                    <label for="nicht">Parkplatz nicht überdacht</label>
                                </div>



                            </form>
                            <!-- <div class="border-bott-f"></div> -->
                        </div>
                    </div>

                    <div class="panel panel-primary">
                        <!-- <div class="panel-heading ">
                            <h5 class="text-center text-white1 text-bold pl-margin-top-5 pl-margin-bottom-5">Distance</h5>
                        </div> -->
                        <div class="panel-body ad-airport">
                            <h4 class="panel-body-title text-semibold">Anfahrtzeit Flughafen</h4>
                            <form action="/action_page.php" class="check-box-form">
                                <?php foreach ($all_distances_val as $d) : ?>
                                    <div>
                                        <input class="filter-lots" type="checkbox" id="<?= $d->distance; ?>" name="distance" value="<?= $d->distance; ?>" <?php echo in_array($d->distance, explode(',', $distanceGET)) ? 'checked' : '' ?>>
                                        <label for="<?= $d->distance; ?>"> <?= $d->distance; ?> Min. </label>
                                    </div>
                                <?php endforeach; ?>
                            </form>
                            <!-- <div class="border-bott-f"></div> -->
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-sm-12 col-md-12 col-lg-9">
                <div class="row">
                    <div class="col-12">
                        <?php

                        $date1 = date_create_from_format('Y-m-d', $startDate);
                        $date2 = date_create_from_format('Y-m-d', $endDate);
                        $today = date_create_from_format('Y-m-d', date('Y-m-d'));
                        $diff = date_diff($date1, $date2);
                        $diffToday = date_diff($today, $date1);
                        if ($diffToday->format("%R") == "-")
                            die("Anreisedatum liegt in der Vergangenheit.");
                        if ($diff->format("%R") == "-")
                            die("Abreisedatum muss nach Anreisedatum sein.");
                        //if($startDate == date("d.m.Y"))
                        //	die("Anreise ist nur am nächsten Tag möglich.");
                        ?>
                        <div class="products-div">
                            <!--<h1 class="product-div__maintitle">
                                <span class="free-parklots-nr">
                                    <?php echo $freeParklots ?>
                                </span>
                                <span class="parklot-text"><?php echo $freeParklots != 1 ? 'Parkplätze' : 'Parkplatz' ?></span>
                                gefunden</h1>-->
                            <div class="div-notifitacion">
                                <ul>
                                    <li>
                                        <img src="/wp-content/uploads/2023/10/Calendar.svg" alt="">
                                        <span class="card-body__starspan"><strong>Ankunft:</strong> <?php echo dateFormat($startDate, 'de') ?></span>
                                    </li>
                                    <li>
                                        <img src="/wp-content/uploads/2023/10/Calendar.svg" alt="">
                                        <span class="card-body__starspan"><strong>Abreise:</strong> <?php echo dateFormat($endDate, 'de') ?></span>
                                    </li>
                                    <li>
                                        <!-- <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/circle-icon.png"
                                             alt=""> -->
                                        <span class="card-body__starspan">Parkdauer <?php echo $dayDiff;
                                                                                    echo $dayDiff != 1 ? ' Tage' : ' Tag' ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <script>
                            function decreaseFreeParklots() {
                                $ = jQuery;
                                $freeParklotsNr = $('.free-parklots-nr');
                                $parklotText = $('.parklot-text');
                                $nr = Number($freeParklotsNr.text());
                                $nr--;
                                if ($nr < 0)
                                    $nr = 0;
                                $freeParklotsNr.text($nr);
                                if ($nr === 1) {
                                    $parklotText.text('Parkplatz');
                                } else {
                                    $parklotText.text('Parkplätze');
                                }
                            }
                        </script>
                        <div class="row">
                            <?php $showLot = 0;
                            $x = 0; ?>
                            <?php foreach ($results as $parklot) : ?>



                                <?php
                                $product = wc_get_product($parklot->product_id);
                                $product_url = get_permalink($product->get_id());
                                $rating = $product->get_average_rating();
                                $count = $product->get_rating_count();
                                $image_id = $product->get_image_id();
                                $ratingIng = (int)$rating;
                                $image_url = wp_get_attachment_image_url($image_id, 'full');
                                $image_url = $image_url ? $image_url : '/wp-content/uploads/woocommerce-placeholder-600x600.png';
                                $canBook = "";
                                $price = Pricelist::calculate($product->get_id(), $startDate, $endDate);
                                $rabatt = Discounts::getDiscounts($parklot->product_id, $price, dateFormat($startDate), dateFormat($endDate));

                                if (
                                    $ratingGet && !in_array($ratingIng, explode(',', $ratingGet))
                                    || $product->get_id() == __HOTEL_PRODUCT_ID
                                    //                                    || !$parklotObj->canOrderLeadTime($startDate, date('H:i'))
                                ) {
                                    echo '<script>decreaseFreeParklots();</script>';
                                    continue;
                                }

                                if ((int)Pricelist::calculate($product->get_id(), $startDate, $endDate) <= 0) {
                                    echo '<script>decreaseFreeParklots();</script>';
                                    continue;
                                }

                                foreach ($period as $key => $value) {
                                    $property = 'used_' . $value->format('Y_m_d');

                                    if ($set_con[$value->format('Y-m-d') . "_" . $parklot->product_id] != null)
                                        $con = $set_con[$value->format('Y-m-d') . "_" . $parklot->product_id];
                                    else
                                        $con = $parklot->contigent;

                                    // get APG used
                                    if ($parklot->product_id == 537) {
                                        foreach ($results_apg as $apg) {
                                            if ($apg->product_id == 595) {
                                                $parklot->$property += $apg->$property;
                                            }
                                        }
                                    }
                                    if ($parklot->product_id == 592) {
                                        foreach ($results_apg as $apg) {
                                            if ($apg->product_id == 3080) {
                                                $parklot->$property += $apg->$property;
                                            }
                                        }
                                    }
                                    if ($parklot->product_id == 619) {
                                        foreach ($results_apg as $apg) {
                                            if ($apg->product_id == 3081) {
                                                $parklot->$property += $apg->$property;
                                            }
                                        }
                                    }
                                    if ($parklot->product_id == 873) {
                                        foreach ($results_apg as $apg) {
                                            if ($apg->product_id == 3082) {
                                                $parklot->$property += $apg->$property;
                                            }
                                        }
                                    }
                                    if ($parklot->product_id == 24222) {
                                        foreach ($results_apg as $apg) {
                                            if ($apg->product_id == 24224) {
                                                $parklot->$property += $apg->$property;
                                            }
                                        }
                                    }
                                    if ($parklot->product_id == 24226) {
                                        foreach ($results_apg as $apg) {
                                            if ($apg->product_id == 24228) {
                                                $parklot->$property += $apg->$property;
                                            }
                                        }
                                    }
                                    if ($parklot->product_id == 80566) {
                                        foreach ($results_apg as $apg) {
                                            if ($apg->product_id == 82029) {
                                                $parklot->$property += $apg->$property;
                                            }
                                        }
                                    }
                                    if ($parklot->product_id == 80567) {
                                        foreach ($results_apg as $apg) {
                                            if ($apg->product_id == 82031) {
                                                $parklot->$property += $apg->$property;
                                            }
                                        }
                                    }

                                    if ($parklot->$property >= $con) {
                                        $canBook = "AUSGEBUCHT";
                                        echo '<script>decreaseFreeParklots();</script>';
                                        $showLot--;
                                    }
                                    //echo "<pre>"; print_r($parklot); echo "<pre>";
                                }

                                $restrictions = Database::getInstance()->getRestrictionsByProductId($parklot->product_id);
                                foreach ($restrictions as $key => $value) {
                                    if ($value->date == $startDate || $value->date == $endDate) {
                                        $canBook = "GESCHLOSSEN";
                                        //echo '<script>decreaseFreeParklots();</script>';
                                        $showLot--;
                                    }
                                }
                                //echo "<pre>";print_r($restrictions);echo "</pre>";

                                //								if ($parklot->used >= $parklot->contigent){
                                //									$canBook = false;
                                //									echo '<script>
                                //                                        decreaseFreeParklots();
                                //                                    </script>';
                                //								}

                                if (date_format(date_create($_GET['datefrom']), 'm') == '12' && date_format(date_create($_GET['datefrom']), 'd') == '31') {
                                    $canBook = "GESCHLOSSEN";
                                    $showLot--;
                                }
                                if (date_format(date_create($_GET['datefrom']), 'm') == '01' && date_format(date_create($_GET['datefrom']), 'd') == '01') {
                                    $canBook = "GESCHLOSSEN";
                                    $showLot--;
                                }

                                if (date_format(date_create($_GET['dateto']), 'm') == '12' && date_format(date_create($_GET['dateto']), 'd') == '31') {
                                    $canBook = "GESCHLOSSEN";
                                    $showLot--;
                                }
                                if (date_format(date_create($_GET['dateto']), 'm') == '01' && date_format(date_create($_GET['dateto']), 'd') == '01') {
                                    $canBook = "GESCHLOSSEN";
                                    $showLot--;
                                }
                                $showLot++;

                                if ($parklot->product_id == 41453 || $parklot->product_id == 41466 || $parklot->product_id == 41468 || $parklot->product_id == 41470 || $parklot->product_id == 41472 || $parklot->product_id == 41474) {
                                    $class1 = "shift-left";
                                    $class4 = "book-btn";
                                } else {
                                    $class1 = "";
                                    $class4 = "";
                                }

                                if ($x == 0) {
                                    $x++;
                                    //continue;
                                }

                                // $preis_value = Pricelist::calculateAndDiscount($product->get_id(), $startDate, $endDate);
                                $preis_value = Pricelist::calculate($product->get_id(), $startDate, $endDate);
                                if ($preis_value > $preis_min && $preis_value < $preis_max) :

                                    $preis_value_arr[] =  $preis_value;

                                ?>
                                    <div class="col-sm-12 col-md-6 col-lg-6 <?php echo $class1 ?>">
                                        <div class="product-cart">
                                            <div class="airport-div-card-head">
                                                <!-- <div class="airport-div_img"> -->
                                                <?php if (!empty($parklot->adress)) : ?>
                                                    <div class="airport-div_content-1">
                                                        <ul class="airport-div_content-card-1">
                                                            <li>
                                                                <?php if ($parklot->product_id == 41453 || $parklot->product_id == 41466 || $parklot->product_id == 41468 || $parklot->product_id == 41470 || $parklot->product_id == 41472 || $parklot->product_id == 41474) : ?>
                                                                    <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/blank-icon.png" alt="">
                                                                <?php else : ?>
                                                                    <img src="/wp-content/uploads/2023/10/map-pin.png" alt="">
                                                                <?php endif; ?>
                                                            </li>
                                                            <li>
                                                                <?php if ($parklot->product_id == 41453 || $parklot->product_id == 41466 || $parklot->product_id == 41468 || $parklot->product_id == 41470 || $parklot->product_id == 41472 || $parklot->product_id == 41474) : ?>
                                                                    <p class="color-white no-adress"><?php echo $parklot->adress ?></p>
                                                                <?php else : ?>
                                                                    <p class="color-white"><?php echo $parklot->adress ?></p>
                                                                <?php endif; ?>
                                                            </li>
                                                        </ul>
                                                    </div> <!-- end of airport-div_content-1 -->
                                                <?php endif; ?>

                                                <?php if ($rabatt[0] != "") : ?>
                                                    <div class="airport-div-card-head-note-green">
                                                        <?php echo $rabatt[1] . " (-" . $rabatt[0] . ")" ?>
                                                    </div>
                                                <?php elseif ($canBook != "") : ?>
                                                    <div class="airport-div-card-head-note-red">
                                                        <?php echo $canBook ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="product_image">
                                                    <img class="airport-img" src="<?php echo $image_url ?>" alt="">
                                                </div>


                                            </div>
                                            <div class="card-body">
                                                <?php if ($parklot->product_id == 41453 || $parklot->product_id == 41466 || $parklot->product_id == 41468 || $parklot->product_id == 41470 || $parklot->product_id == 41472 || $parklot->product_id == 41474) : ?>
                                                    <h4 class="card-body-title no-title"><?php echo $parklot->parklot ?></h4>
                                                <?php else : ?>
                                                    <h4 class="card-body-title"><?php echo $parklot->parklot ?></h4>
                                                <?php endif; ?>
                                                <div class="card-body__star-div">
                                                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                        <?php if ($i <= floor($rating)) : ?>
                                                            <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/star1-icon.png" alt="">
                                                        <?php else : ?>
                                                            <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/star2-icon.png" alt="">
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                    <span><?php echo $rating ?> von 5.0 / <a href="<?php echo $product_url ?>?datefrom=<?php echo $startDate ?>&dateto=<?php echo $endDate ?>&review=1"><span class="span-inspan"><?php echo $count ?> Meinungen</span></a></span>
                                                </div>

                                                <!--                                <p class="card-body__percent-div">88% Recommendation</p>-->
                                                <ul>
                                                    <li>
                                                        <?php if ($parklot->product_id == 41453 || $parklot->product_id == 41466 || $parklot->product_id == 41468 || $parklot->product_id == 41470 || $parklot->product_id == 41472 || $parklot->product_id == 41474) : ?>
                                                            <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/blank-icon.png" alt="">
                                                            <span class="red-text">Winter Spezial <?php echo $rabatt[0] ?> Rabatt</span>
                                                        <?php else : ?>
                                                            <img src="/wp-content/uploads/2023/10/fluent-mdl2_parking-location.png" alt="">
                                                            <span class="card-body__starspan"><?php echo $parklot->type ?></span>
                                                        <?php endif; ?>
                                                    </li>
                                                    <li>
                                                        <?php if ($parklot->product_id == 41453 || $parklot->product_id == 41466 || $parklot->product_id == 41468 || $parklot->product_id == 41470 || $parklot->product_id == 41472 || $parklot->product_id == 41474) : ?>
                                                            <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/blank-icon.png" alt="">
                                                            <span class="red-text">Nicht stornierbar</span>
                                                        <?php else : ?>
                                                            <img src="/wp-content/uploads/2023/10/uil_parking-square.png" alt="">
                                                            <?php $typ = str_replace("Parkhaus ", "", $parklot->parkhaus); ?>
                                                            <?php $typ = str_replace("Parkplatz ", "", $typ); ?>
                                                            <span class="card-body__starspan"><?php echo $typ ?></span>
                                                        <?php endif; ?>
                                                    </li>
                                                    <li>
                                                        <?php if ($parklot->product_id == 41453 || $parklot->product_id == 41466 || $parklot->product_id == 41468 || $parklot->product_id == 41470 || $parklot->product_id == 41472 || $parklot->product_id == 41474) : ?>
                                                            <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/blank-icon.png" alt="">
                                                            <span class="red-text">Nicht umbuchbar</span>
                                                        <?php else : ?>
                                                            <img src="/wp-content/uploads/2023/10/gis_route.png" alt="">
                                                            <span class="card-body__starspan"><?php echo $parklot->distance . " Min." ?></span>
                                                        <?php endif; ?>
                                                    </li>
                                                    <li>
                                                        <?php if ($parklot->product_id == 41453 || $parklot->product_id == 41466 || $parklot->product_id == 41468 || $parklot->product_id == 41470 || $parklot->product_id == 41472 || $parklot->product_id == 41474) : ?>
                                                            <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/blank-icon.png" alt="">
                                                            <span class="red-text">Onlinezahlung</span>
                                                        <?php else : ?>
                                                            <img src="/wp-content/uploads/2023/10/fluent_payment-24-regular.png" alt="">
                                                            <span class="card-body__starspan">Online-/Barzahlung</span>
                                                        <?php endif; ?>
                                                    </li>
                                                    <?php if ($parklot->type == "valet") : ?>
                                                        <li>
                                                            <span class="card-body__starspan"><?php echo "Bei Anreise 00 - 06 Uhr <br>+ 30€ Nachtzuschlag" ?></span>
                                                        </li>
                                                    <?php endif; ?>
                                                    <!--                                                <li>-->
                                                    <!--                                                    <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/distance-icon.png" alt="">-->
                                                    <!--                                                    <span class="card-body__starspan">--><?php //echo "ca. " . $parklot->distance 
                                                                                                                                                    ?><!-- Min.</span>-->
                                                    <!--                                                </li>-->
                                                </ul>
                                                <?php if ($canBook == "") : ?>
                                                    <button data-pid="<?php echo $product->get_id() ?> " data-discount="false" style="<?php
                                                                                                                                        echo Discounts::getDiscounts($product->get_id(), $price, $startDate, $endDate) ?
                                                                                                                                            'color: #3a8ae2; background: none !important; border: 1px solid #3a8ae2' : ''
                                                                                                                                        ?>" class="btn btn-primary btn-md pl-full-width btn-suchen-card pl-margin-top-10 btn-order-parklot  <?php echo $class4 ?>">
                                                        <?php echo number_format(Pricelist::calculate($product->get_id(), $startDate, $endDate), 2, '.', '') ?>
                                                        € - Zur Buchung
                                                    </button>
                                                    <?php if (Discounts::getDiscounts($product->get_id(), $price, $startDate, $endDate)) : ?>
                                                        <button data-pid="<?php echo $product->get_id() ?>" data-discount="true" data-did="<?= $rabatt[4] ?>" data-toggle="tooltip" data-placement="top" title="<?php echo $rabatt[1] . " (-" . $rabatt[0] . ")<br>" . $rabatt[2] ?>" data-html="true" class="btn btn-primary btn-md pl-full-width btn-suchen-card pl-margin-top-10 btn-order-parklot  <?php echo $class4 ?>">
                                                            <i class="fas fa-info"></i>&nbsp;
                                                            <?php echo number_format(Pricelist::calculateAndDiscount($product->get_id(), $startDate, $endDate), 2, '.', '') ?>
                                                            € - <?php echo $rabatt[1] ?>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php else : ?>
                                                    <button class="btn btn-primary btn-md pl-full-width btn-suchen-card pl-margin-top-10 <?php echo $class4 ?>" disabled>
                                                        <?php echo number_format(Pricelist::calculate($product->get_id(), $startDate, $endDate), 2, '.', '') ?>
                                                        € - <?php echo " " . $canBook; ?>
                                                    </button>
                                                    <?php if (Discounts::getDiscounts($product->get_id(), $price, $startDate, $endDate)) : ?>
                                                        <button data-toggle="tooltip" data-did="<?= $rabatt[4] ?>" data-placement="top" title="<?php echo $rabatt[1] . " (-" . $rabatt[0] . ")<br>" . $rabatt[2] ?>" data-html="true" class="btn btn-primary btn-md pl-full-width btn-suchen-card pl-margin-top-10 <?php echo $class4 ?>" disabled>
                                                            <i class="fas fa-info"></i>&nbsp;
                                                            <?php echo number_format(Pricelist::calculateAndDiscount($product->get_id(), $startDate, $endDate), 2, '.', '') ?>
                                                            € - <?php echo $canBook ?>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <span class="weitere-infos-result-page">
                                                    <!-- <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/key-icon.png" alt=""> -->
                                                    <span class="card-body__starspan">
                                                        <a href="<?php echo $product_url ?>?datefrom=<?php echo dateFormat($startDate, 'de') ?>&dateto=<?php echo dateFormat($endDate, 'de') ?>">
                                                            Weitere Infos
                                                        </a>
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                            <?php endif;
                            endforeach; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $minValue2 = 20;
        $preis_value_arr = array_map('intval', $preis_value_arr);
        $maxValue = max($preis_value_arr);
        $minValue2 = min($preis_value_arr);

        ?>

        <script>
            // document.addEventListener('DOMContentLoaded', function() {
            // var discountButtons = document.querySelectorAll('button[data-discount="true"]');
            // var maxValue = -Infinity;

            // discountButtons.forEach(function(button) {
            //     var value = parseFloat(button.innerText.trim().split("€")[0]);
            //     if (value > maxValue) {
            //         maxValue = value;
            //     }
            // });

            // // Set the max attribute of the range input to the maximum value obtained
            // var slider = document.getElementById("discountMaxSlider");
            // slider.max = maxValue;

            // var maxValue = <?= $maxValue; ?>

            // var slider = document.getElementById("discountMaxSlider");
            // // slider.min = 20;
            // slider.max = maxValue;
            // slider.value = 20;

            // Update the max value display
            // var maxDisplay = document.getElementById("discountMaxValue");
            // maxDisplay.textContent = 20;
            // });


            // document.addEventListener('DOMContentLoaded', function() {
            //     var discountButtons = document.querySelectorAll('button[data-discount="true"]');
            //     var minValue = Infinity;
            //     var maxValue = -Infinity;

            //     discountButtons.forEach(function(button) {
            //         var value = parseFloat(button.innerText.trim().split("€")[0]);
            //         if (value < minValue) {
            //             minValue = value;
            //         }
            //         if (value > maxValue) {
            //             maxValue = value;
            //         }
            //     });

            //     var slider = document.getElementById("discountMaxSlider");
            //     slider.min = minValue;
            //     slider.max = maxValue;
            //     slider.value = minValue; // Set default value to minValue

            //     var minDisplay = document.getElementById("discountMinValue");
            //     minDisplay.textContent = minValue;

            //     var maxDisplay = document.getElementById("discountMaxValue");
            //     maxDisplay.textContent = maxValue;
            // });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const slider = document.getElementById('discountMaxSlider');
                const currentValue = document.getElementById('discountMaxValue');

                slider.addEventListener('input', function() {
                    currentValue.textContent = this.value;
                });

                slider.addEventListener('change', function() {
                    setTimeout(function() {
                        // alert(`Current value is ${slider.value}`);
                        const currentValue = slider.value;
                        const currentURL = window.location.href;
                        const updatedURL = new URL(currentURL);
                        updatedURL.searchParams.set('preis', currentValue);
                        window.history.pushState({}, '', updatedURL);
                        window.location.href = updatedURL;
                    }, 2000);
                });
            });
        </script>