<?php
add_action('woocommerce_before_single_product', 'product_details');
function product_details()
{
    global $product, $wpdb;
    $startDate = $_GET['datefrom'];
    $endDate = $_GET['dateto'];
    $rating = $product->get_average_rating();
    $count = $product->get_rating_count();
    $locations = $wpdb->get_results("select * from {$wpdb->prefix}itweb_locations");
    $parklot = $wpdb->get_row($wpdb->prepare("select parklots.* from {$wpdb->prefix}itweb_parklots parklots where parklots.product_id = %d", $product->get_id()));

    $startDateEn = date_format(date_create($startDate), 'Y-m-d H:i');
    $endDateEn = date_format(date_create($endDate), 'Y-m-d H:i');

    $services = Database::getInstance()->getProductAdditionalServices($product->get_id());

    $sql = "
    select parklots.*,
    (
        select count(orders.id) from {$wpdb->prefix}itweb_orders orders
        where (date(orders.date_from) between date('{$startDateEn}') and date('{$endDateEn}') or 
            date(orders.date_to) between date('{$startDateEn}') and date('{$endDateEn}')) and parklots.product_id = orders.product_id
            and orders.deleted = 0
    ) as used
    from
    {$wpdb->prefix}itweb_parklots parklots
    where parklots.product_id = " . $product->get_id();

    $p = $wpdb->get_row($sql);

    $isAvailableAtDates = $wpdb->get_row("
        select parklots.* from {$wpdb->prefix}itweb_parklots parklots
        where (date('" . dateFormat($startDate) . "') between date(parklots.datefrom) and date(parklots.dateto))
        and (date('" . dateFormat($endDate) . "') between date(parklots.datefrom) and date(parklots.dateto))
        and parklots.product_id = " . $product->get_id());

    $isParklotOk = $p && $p->used < $p->contigent && $isAvailableAtDates ? true : null;
    $price = Pricelist::calculate($product->get_id(), $startDate, $endDate);
    $rabatt = Discounts::getDiscounts($parklot->product_id, $price, dateFormat($startDate), dateFormat($endDate));
    ?>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBlWNGOEAiZWFEjf1lJrth1L2v8YSiCI2M&callback=initMap&libraries=&v=weekly"
            defer></script>
    <div class="body-cover">
        <div class="container">

            <div class="row">


                <div class="col-sm-12 col-md-12 col-lg-3">
                    <div class="left-sidebar">

                        <div class="panel panel-primary">
                            <div class="panel-heading ">
                                <h5 class="text-center text-white1 text-bold pl-margin-top-5 pl-margin-bottom-5">
                                    Parkplatz
                                    suchen</h5>
                            </div>
                            <div class="panel-body ad-airport">

                                <form action="/search-result" method="GET">

                                    <!--<div class="form-group formgroup-rez">
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/inc/assets/images/plan.png" id="input_img-form" alt="">

                                        <label class="icon-text text-semibold">Von wo aus fliegen Sie?</label>

                                        <select class="form-control" name="country">
                                            <option></option>
                                            <?php foreach ($locations as $location) : ?>
                                                <?php if (isset($_GET['location']) && $_GET['location'] == $location->id) : ?>
                                                    <option value="<?php echo $location->id ?>" selected>
                                                        <?php echo $location->location ?>
                                                    </option>
                                                <?php else : ?>
                                                    <option value="<?php echo $location->id ?>">
                                                        <?php echo $location->location ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                        <!-- <input id="selectedAirport" name="selectedAirport" type="text" class="form-control typeahead" placeholder="z.B. Stuttgart" autocomplete="off" value="Stuttgart"> 
                                    </div>-->

                                    <div class="form-group formgroup-rez">
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/inc/assets/images/calendar-small.png" id="input_img-form" alt="">

                                        <label for="" class="icon-text text-bold">Anreisedatum</label>
                                        <input type="text" class="form-control typeahead single-datepicker" autocomplete="off" name="datefrom" data-value="<?php echo dateFormat($_GET['datefrom']) ?>">
                                    </div>
                                    <div class="form-group formgroup-rez">
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/inc/assets/images/calendar-small.png" id="input_img-form" alt="">

                                        <label for="" class="icon-text text-bold">Abreisedatum</label>
                                        <input type="text" class="form-control typeahead single-datepicker" autocomplete="off" name="dateto" data-value="<?php echo dateFormat($_GET['dateto']) ?>">
                                    </div>


                                    <button class="btn btn-primary btn-md pl-full-width btn-suchen pl-margin-top-10">
                                        Datum ändern
                                    </button>


                                </form>

                            </div>


                        </div>
                        <div class="rate-div ">
                            <div class="row text-center">
                                <div class="col-12">
                                    <!--                            <div class="rating-like text-semibold">Excellent</div>-->
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-12">
                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/inc/assets/images/like-img-rate.png" class="padding-5" alt="">

                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-12">
                                    <span class="text-semibold"><?php echo $rating ?> von 5.0</span>
                                    <div class="card-body__star-div">
                                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                                            <?php if ($i <= floor($rating)) : ?>
                                                <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/star1-icon.png" alt="">
                                            <?php else : ?>
                                                <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/star2-icon.png" alt="">
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-12">
                                    <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/users.png" alt="">
                                    <span class="card-body__starspan gray-color"><?php echo $count ?> Meinungen</span>
                                </div>
                            </div>


                        </div>


                    </div>
                </div>


                <div class="col-sm-12 col-md-12 col-lg-9">
                    <div class="products-div">
                        <h1 class="product-div__maintitle">
                            <?php echo $parklot->parklot ?>
                        </h1>

                        <div class="div-notifitacion">
                            <ul>
                                <li>
                                    <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/calendar-small.png" alt="">
                                    <span class="card-body__starspan">
                                        <?php echo $parklot->adress ?>
                                    </span>
                                </li>

                                <li>
                                    <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/circle-icon.png" alt="">
                                    <!-- Trigger/Open The Modal -->

                                    <a href="#"><span class="card-body__starspan show-map" id="myBtn">Karte anzeigen</span></a>
                                </li>
                            </ul>

                        </div>

                        <!-- <div class="container"> -->
						<?php if($_GET['rating'] == null): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="notifitacion card tab-card">
                                    <div class="card-header tab-card-header">
                                        <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="one-tab" data-toggle="tab" href="#one" role="tab" aria-controls="One" aria-selected="true">Info</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="two-tab" data-toggle="tab" href="#two" role="tab" aria-controls="Two" aria-selected="false">Verfügbarkeit prüfen</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="three-tab" data-toggle="tab" href="#three" role="tab" aria-controls="Three" aria-selected="false">Sicherheit & Service</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="four-tab" data-toggle="tab" href="#four" role="tab" aria-controls="Four" aria-selected="false">Kundenbewertungen</a>
                                            </li>

                                        </ul>
                                    </div>

                                    <div class="tab-content" id="myTabContent">
                                        <div class="tab-pane fade show active p-3" id="one" role="tabpanel" aria-labelledby="one-tab">
                                            <!-- <h5 class="card-title">Info</h5> -->
                                            <?php
                                            $attachment_ids = $product->get_gallery_image_ids();

                                            ?>
                                            <div class="slider-single">
                                                <?php foreach ($attachment_ids as $attachment_id) :
                                                    $image_link = wp_get_attachment_url($attachment_id);
                                                    ?>
                                                    <div>
                                                        <img src="<?php echo $image_link; ?>" id="input_img-form" alt="">
                                                        <!-- <img src="https://images.unsplash.com/photo-1494548162494-384bba4ab999?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&w=1000&q=80" alt=""> -->
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="slider-nav">
                                                <?php foreach ($attachment_ids as $attachment_id) :
                                                    $image_link = wp_get_attachment_url($attachment_id);
                                                    ?>
                                                    <div>
                                                        <img src="<?php echo $image_link; ?>" id="input_img-form" alt="">
                                                        <!-- <img src="https://images.unsplash.com/photo-1494548162494-384bba4ab999?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&w=1000&q=80" alt=""> -->
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="product-description">
                                                <br>
                                                <?php echo $product->get_description() ?>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade p-3" id="two" role="tabpanel" aria-labelledby="two-tab">
                                            <div class="card">
                                                <div class="card-header-info">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="card-header-info__date text-white">
                                                                <?php echo dateFormat($_GET['datefrom'], 'de') ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="card-header-info__date text-white">
                                                                <?php echo dateFormat($_GET['dateto'], 'de') ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-6 col-xl-3 col-sm-12 col-md-6">
                                                            <h5 class="card-title">Parkplatz</h5>
                                                            <p class="card-text">
                                                                <?php echo $parklot->parklot ?>
                                                            </p>

                                                        </div>
                                                        <div class="col-lg-6 col-xl-3 col-sm-12 col-md-6">
                                                            <h5 class="card-title">Parkdauer in Tagen</h5>
                                                            <p class="card-text">
                                                                <?php
                                                                echo getDaysBetween2Dates(new DateTime($_GET['datefrom']), new DateTime($_GET['dateto']));
                                                                ?>
                                                            </p>

                                                        </div>
                                                        <div class="col-lg-6 col-xl-3 col-sm-12 col-md-6">
                                                            <h5 class="card-title">Parkgebühren</h5>
                                                            <p class="card-text">
                                                                <span class="product-price">
                                                                    <?php echo Pricelist::calculateAndDiscount($product->get_id(), $_GET['datefrom'], $_GET['dateto']) ?>
                                                                </span>
                                                                €
                                                            </p>

                                                        </div>
                                                        <div class="col-lg-6 col-xl-3 col-sm-12 col-md-6">
                                                            <h5 class="card-title">Verfügbarkeit</h5>
                                                            <p class="card-text">
                                                                <?php if ($isParklotOk) : ?>
                                                                    <i class="fas fa-check"></i>
                                                                <?php else : ?>
                                                                    <i class="fas fa-times"></i>
                                                                <?php endif; ?>
                                                            </p>

                                                        </div>

                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="custom-fields">
                                                                <input type="hidden" class="pro-id" value="<?php echo $product->get_id() ?>">
<!--                                                                <div class="custom-field-row">-->
<!--                                                                    <label style="display: block">-->
<!--                                                                        <strong>Time from*</strong>-->
<!--                                                                    </label>-->
<!--                                                                    <input type="text" class="product-page-timefrom" name="timefrom">-->
<!--                                                                </div>-->
<!--                                                                <div class="custom-field-row">-->
<!--                                                                    <label style="display: block">-->
<!--                                                                        <strong>Time to*</strong>-->
<!--                                                                    </label>-->
<!--                                                                    <input type="text" class="product-page-timeto" name="timeto">-->
<!--                                                                </div>-->
                                                                <?php if ($isParklotOk && count($services) > 0) : ?>
                                                                    <br>
                                                                    <hr>
                                                                    <br>
                                                                    <div class="custom-field-row">
                                                                        <p>
                                                                            <b>Additional Services</b>
                                                                        </p>
                                                                        <input type="hidden" id="chSer" value="">
                                                                        <?php foreach ($services as $service) : ?>
                                                                            <label for="addSer-<?php echo $service->id ?>" style="display: block">
                                                                                <input type="checkbox" data-id="<?php echo $service->id ?>"
                                                                                       data-price="<?php echo $service->price ?>" class="front-additional-service"
                                                                                       id="addSer-<?php echo $service->id ?>">
                                                                                <?php echo $service->name ?>
                                                                            </label>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <div class="custom-field-row">
                                                                    <div class="alert alert-danger product-page-errors" role="alert" style="display: none;"></div>
                                                                </div>
                                                            </div>
                                                            <div class="itweb-loader">
                                                                <span class="fas fa-spinner fa-spin"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row border-top-1">
                                                        <div class="col-12 ">
                                                            <div class="card-border-top-2">

                                                            </div>
                                                            <?php if ($isParklotOk) : ?>
                                                                <a href="#" data-discount="<?= count($rabatt) > 0 ? 'true' : 'false' ?>" data-did="<?= $rabatt[4] ?>" data-pid="<?php echo $product->get_id() ?>" class="btn btn-primary btn-order-parklot">Jetzt buchen</a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                        <div class="tab-pane fade p-3" id="three" role="tabpanel" aria-labelledby="three-tab">
                                            <div class="row border-security">
                                                <div class="col-lg-4 col-xl-4 col-sm-12 col-md-4">
                                                    <h4>Sicherheit</h4>
                                                    <ul class="security-list">
                                                        <?php
                                                        $securityAttr = explode(',', $product->get_attribute('pa_parklot-security'));
                                                        ?>
                                                        <?php foreach ($securityAttr as $attr) : ?>
                                                            <li>
                                                                <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/check-yes.png" id="input_img-form" alt=""><span> <?php echo $attr ?>
                                                                </span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                                <div class="col-lg-5 col-xl-5 col-sm-12 col-md-5">
                                                    <h4>Shuttle Service</h4>
                                                    <ul class="security-list">
                                                        <?php
                                                        $shuttleServiceAttr = explode(',', $product->get_attribute('pa_parklot-shuttle-service'));
                                                        ?>
                                                        <?php foreach ($shuttleServiceAttr as $attr) : ?>
                                                            <li>
                                                                <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/check-yes.png" id="input_img-form" alt=""><span> <?php echo $attr ?>
                                                                </span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                                <div class="col-lg-3 col-xl-3 col-sm-12 col-md-3">
                                                    <h4>Parkhausserivce</h4>
                                                    <ul class="security-list">
                                                        <?php
                                                        $serviceAttr = explode(',', $product->get_attribute('pa_parklot-service'));
                                                        ?>
                                                        <?php foreach ($serviceAttr as $attr) : ?>
                                                            <li>
                                                                <img src="/wp-content/themes/hello-elementor-child/inc/assets/images/check-yes.png" id="input_img-form" alt=""><span> <?php echo $attr ?>
                                                                </span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade p-3" id="four" role="tabpanel" aria-labelledby="four-tab">
                                            <div class="card">
                                                <div class="card-header reviews-card text-semibold">
                                                    Tipps können anderen Kunden helfen ihren passenden Parkplatz zu finden
                                                </div>
                                                <div class="card-body parklot-reviews">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
						<script>
						jQuery(document).ready(function($) {
                            //$('.woocommerce-Reviews').appendTo('.parklot-reviews');
							$('#tab-reviews').appendTo('.parklot-reviews');
                        });
						</script>
						<?php if($_GET['review']) :?>
						<script>
						jQuery(document).ready(function($) {
                            //$('.woocommerce-Reviews').appendTo('.parklot-reviews');
							$('#four-tab')[0].click(function(){});
                        });
						</script>
						<?php endif; ?>
						
						<?php else: ?>
						<style>
							.rx-reviewbox, .rx-filter-bar-style-2, .rx_review_sort_list{
									display: none;
							}
						</style>
						<script>
						jQuery(document).ready(function($) {
                            //$('.woocommerce-Reviews').appendTo('.parklot-reviews');
							//$('.rx_comment_form__wrapper').appendTo('.parklot-reviews');
							$('#tab-reviews').appendTo('.parklot-reviews');
                        });
						</script>
						<div class="card-header reviews-card text-semibold">
							Tipps können anderen Kunden helfen ihren passenden Parkplatz zu finden
							<p>Beim Absenden der Bewertung bin ich mit der Speicherung und Veröffentlichung dieser Bewertung (Rating, Name und Text) einverstanden.</p>
						</div>
						<div class="card-body parklot-reviews"></div>
											
						<?php endif; ?>
                        <!-- </div> -->

                    </div>


                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }

                        #primary {
                            width: 100% !important;
                            max-width: 100% !important;
                        }

                        /* The Modal (background) */
                        .modal {
                            display: none;
                            /* Hidden by default */
                            position: fixed;
                            /* Stay in place */
                            z-index: 1;
                            /* Sit on top */
                            padding-top: 100px;
                            /* Location of the box */
                            left: 0;
                            top: 0;
                            width: 100%;
                            /* Full width */
                            height: 100%;
                            /* Full height */
                            overflow: auto;
                            /* Enable scroll if needed */
                            background-color: rgb(0, 0, 0);
                            /* Fallback color */
                            background-color: rgba(0, 0, 0, 0.4);
                            /* Black w/ opacity */
                        }

                        /* Modal Content */
                        .modal-content {
                            position: relative;
                            background-color: #fefefe;
                            margin: auto;
                            padding: 0;
                            border: 1px solid #888;
                            width: 70%;
                            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                            -webkit-animation-name: animatetop;
                            -webkit-animation-duration: 0.4s;
                            animation-name: animatetop;
                            animation-duration: 0.4s;

                        }

                        /* Add Animation */
                        @-webkit-keyframes animatetop {
                            from {
                                top: -300px;
                                opacity: 0
                            }

                            to {
                                top: 0;
                                opacity: 1
                            }
                        }

                        @keyframes animatetop {
                            from {
                                top: -300px;
                                opacity: 0
                            }

                            to {
                                top: 0;
                                opacity: 1
                            }
                        }

                        /* The Close Button */
                        .close {
                            color: white;
                            float: right;
                            font-size: 28px;
                            font-weight: bold;

                        }

                        .close:hover,
                        .close:focus {
                            color: #000;
                            text-decoration: none;
                            cursor: pointer;
                        }

                        .modal-header {
                            padding: 2px 16px;
                            /* background-color: #5cb85c; */
                            color: white;
                        }

                        .modal-header .close {
                            margin-top: 8px !important;
                            color: #3b8ae3;
                        }

                        .btn-modal {
                            background-color: #3b8ae3 !important;
                            color: #fff;
                            border: none;
                        }

                        .modal-body {
                            padding: 30px 16px 16px 16px;
                        }

                        .modal-body input {
                            border: none;
                            border-bottom: 1px solid #B5B5B5;
                            padding: 10px;
                            width: 100%;
                        }

                        .modal-body input:focus {
                            outline: none !important;
                        }

                        .modal-footer {
                            padding: 2px 16px;
                            /* background-color: #5cb85c; */
                            color: white;
                        }

                        .width-100 {
                            width: 100%;
                        }

                        .product .woocommerce-product-gallery,
                        .tabs.wc-tabs,
                        .product .summary,
                        .woocommerce-Tabs-panel .woocommerce-Reviews,
                        #secondary,
                        .woocommerce-Reviews-title,
                        .product .woocommerce-tabs {
                            display: none !important;
                        }
                    </style>
                    </head>

                    <body>


                    <!-- The Modal -->
                    <div id="myModal" class="modal">

                        <!-- Modal content -->
                        <div class="modal-content">
                            <div class="modal-header">
                                <div class="row width-100">
                                    <div class="col-11">
                                        <h2>Routenplaner – Sicher am Ziel ankommen</h2>
                                    </div>
                                    <div class="col-1">
                                        <span class="close">&times;</span>
                                    </div>
                                </div>


                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-4">
                                        <input type="text" class="from-address" value="Stuttgart, Germany" placeholder="Start eingeben">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="to-address" value="<?php echo $parklot->adress ?>" placeholder="Ziel eingeben" readonly>
                                    </div>
                                    <div class="col-3">
                                        <button id="calculateRoute" class="btn btn-primary btn-md pl-full-width btn-modal pl-margin-top-10-1">
                                            Route berechnen
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-5">
                                    <div class="col-12">
                                        <div id="map"></div>
                                    </div>
                                </div>

                            </div>
                            <!-- <div class="modal-footer">
                            <h3>Modal Footer</h3>
                        </div> -->
                        </div>

                    </div>

                    <script>
                        // Get the modal
                        var modal = document.getElementById("myModal");

                        // Get the button that opens the modal
                        var btn = document.getElementById("myBtn");

                        // Get the <span> element that closes the modal
                        var span = document.getElementsByClassName("close")[0];

                        // When the user clicks the button, open the modal
                        btn.onclick = function() {
                            modal.style.display = "block";
                        }

                        // When the user clicks on <span> (x), close the modal
                        span.onclick = function() {
                            modal.style.display = "none";
                        }

                        // When the user clicks anywhere outside of the modal, close it
                        window.onclick = function(event) {
                            if (event.target == modal) {
                                modal.style.display = "none";
                            }
                        }

                        jQuery(document).ready(function($) {
                            $('.woocommerce-Reviews').appendTo('.parklot-reviews');
                        });
                    </script>


                </div>
            </div>
        </div>
    </div>
    <script>
        "use strict";

        function initMap() {
            const directionsService = new google.maps.DirectionsService();
            const directionsRenderer = new google.maps.DirectionsRenderer();
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 16,
                center: {
                    lat: 41.85,
                    lng: -87.65
                }
            });
            directionsRenderer.setMap(map);

            calculateAndDisplayRoute(directionsService, directionsRenderer);
        }

        jQuery('#calculateRoute, .show-map').on('click', function(){
            initMap();
        });

        function calculateAndDisplayRoute(directionsService, directionsRenderer) {
            directionsService.route(
                {
                    origin: {
                        query: jQuery('.from-address').val()
                    },
                    destination: {
                        query: jQuery('.to-address').val()
                    },
                    travelMode: google.maps.TravelMode.DRIVING
                },
                (response, status) => {
                    if (status === "OK") {
                        directionsRenderer.setDirections(response);
                    } else {
                        window.alert("Directions request failed due to " + status);
                    }
                }
            );
        }
    </script>
    <?php
}