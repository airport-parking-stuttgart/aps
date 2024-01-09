<?php
/**
 * Single product rating summery
 * @version 1.0.0
 * @author Joules Labs
 */

if( ! defined( 'ABSPATH' ) ) {
	exit();
}

global $product, $reviewx_shortcode;
$user_id 					= get_current_user_id();
if( isset($reviewx_shortcode) && isset($reviewx_shortcode['rx_product_id']) ) {
	$post_id                = $reviewx_shortcode['rx_product_id'];
} else {
	$post_id                = get_the_ID();
}
$settings 					= \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_option_settings();
$graph_style 				= $settings->graph_style;
$review_criteria 			= $settings->review_criteria;
$allow_recommendation 		= get_option( '_rx_option_allow_recommendation' );
$color_theme 				= $settings->color_theme;

$arg = array(
	'post_type' 			=> 'product',
	'post_id' 				=> $post_id,
	'type'  				=> 'review',
);

$comment_lists 				= get_comments($arg);
$criteria_arr 				= array();
$criteria_count 			= array();
$total_review_count 		= array();
$rx_total_rating_sum        = 0;

if( ! empty( $comment_lists ) ) {
    foreach ($comment_lists as $comment_list) {

        $get_criteria = get_comment_meta($comment_list->comment_ID, 'reviewx_rating', true);
        $criteria_query_array = $get_criteria;
        $criteria_query_obj = (object)$get_criteria;

        $get_rating_val = get_comment_meta($comment_list->comment_ID, 'rating', true);
        if (!empty($get_rating_val)) {
            $rx_total_rating_count = array_push($total_review_count, $get_rating_val);
            $rx_total_rating_sum += $get_rating_val;
        }

        if (is_array($review_criteria)) {

            foreach ($review_criteria as $key => $single_criteria) {
                if (!array_key_exists($key, $criteria_arr)) {
                    if (isset($criteria_query_obj->$key)) {
                        $criteria_arr[$key] = $criteria_query_obj->$key;
                        $criteria_count[$key] = 1;
                    }
                } else {
                    if (isset($criteria_query_obj->$key)) {
                        $criteria_arr[$key] += $criteria_query_obj->$key;
                        $criteria_count[$key] += 1;
                    }
                }
            }
        }
    }
}
?>

<div id="reviews" class="rx_review_summery_block">
	<div class="rx-reviewbox">
		<div class="rx-flex-grid-container">
            <div class="rx-flex-grid-50 rx_recommended_wrapper">
                <div class="rx-temp-rating ">
                    <div class="rx_average_rating">
                        <div class="rx-temp-rating-number">
                            <?php 
                                $rx_count_total_rating_avg = round( $rx_total_rating_sum / $rx_total_rating_count, 2);
                                if( is_nan($rx_count_total_rating_avg) ){
                                    $rx_count_total_rating_avg = 5;
                                }                                
                            ?>
                            <p class="temp-rating_avg"><?php echo esc_html( $rx_count_total_rating_avg ); ?></p><span class="temp-rating_5-star">/<?php esc_html_e('5', 'reviewx-pro' ); ?></span>
                        </div>
                        <div class="rx-temp-rating-star">
                            <?php echo reviewx_show_star_rating( $rx_count_total_rating_avg ); ?>
                        </div>
                    </div>
                    <div class="rx-temp-total-rating-count">
                        <p><?php echo sprintf( __('Based on %d rating(s)', 'reviewx-pro' ), $rx_total_rating_count ); ?></p>
                    </div>
                </div>
                <?php if( $allow_recommendation == 1 ) { ?>
                    <hr>
                    <div class="rx_recommended_box">
                        <div class="rx_recommended_icon_box">
                            <div class="rx_recommended_icon">
								<?php 
									$recommended_icon = get_option('_rx_option_recommend_icon_upload');
									if( !empty($recommended_icon) ){
								?>
									<img src="<?php echo esc_url( $recommended_icon ); ?>" alt="<?php esc_attr_e( 'ReviewX', 'reviewx-pro'); ?>">
								<?php } else { ?>
									<img src="<?php echo esc_url( plugins_url( '/', __FILE__ ) . '../../assets/images/recommendation_icon.png' ); ?>" alt="<?php esc_attr_e( 'ReviewX', 'reviewx-pro'); ?>">
								<?php } ?>	                                
                            </div>
                        </div>
                        <div class="rx_recommended_box-right">
                            <p class="rx_recommended_box_content">
                                <span class="rx_recommended_box_heading">
                                    <?php echo sprintf("%02d", reviewx_product_recommendation_count( $post_id )) ; ?>
                                </span>
                                <?php  if( get_post_type() == 'product' ) { ?>
                                    <?php echo ! empty(get_theme_mod('reviewx_customer_recommendation_label') ) ? get_theme_mod('reviewx_customer_recommendation_label') : __( 'Customer(s) recommended this item', 'reviewx-pro' ); ?>
                                <?php } else if( \ReviewX_Helper::check_post_type_availability( get_post_type() ) == TRUE ) { ?>
                                    <?php echo __( 'Reviewer(s) recommended this item', 'reviewx-pro' ); ?>
                                <?php } ?> 
                            </p>
                        </div>
                    </div>
                <?php } ?>
            </div>

			<!-- Start review chart -->
			<div class="rx-flex-grid-50 stfn_rate rx_rating_graph_wrapper">
                <?php
                    $cri        = apply_filters( 'reviewx_add_criteria', $review_criteria );
                    $label_title_value_arg = array();
                    $chart_data  = array();
                    $i=0;
                    $criteria_val_for_chart = 0;

                    if ( ! empty( $cri ) ) {
                        foreach ( $cri as $key => $single_criteria ) {
                            if ( isset( $criteria_arr[$key] ) && isset( $criteria_count[$key] ) ) {
                                $criteria_val_for_chart  = intval( round( ($criteria_arr[$key] / $criteria_count[$key])*100/5 ) );
                            }
                            $chart_data[$i]['y']     = $criteria_val_for_chart;
                            $chart_data[$i]['label'] = $single_criteria;
                            $i++;
                        }
                    }
                ?>

                <div id="rx_pie_chart" style="height: 100%; width: 100%;"></div>
        
            </div>
		</div>
	</div>
</div>

<script>

    jQuery(document).ready(function(){
        <?php if( ! empty( $pie_chart_color_array ) && count($pie_chart_color_array) ) { ?>
            CanvasJS.addColorSet("colorShades", [<?php echo '"'.implode('","', $pie_chart_color_array).'"' ?>]);
        <?php } ?>
        var chart = new CanvasJS.Chart("rx_pie_chart", {
            animationEnabled: true,
            colorSet: "colorShades",
            title: {
                text: ""
            },
            data: [{
                type: "pie",
                startAngle: 240,
                yValueFormatString: "##0.00\"%\"",
                indexLabel: "{label} {y}",
                dataPoints: <?php echo json_encode($chart_data); ?> 
            }],
        });
        chart.render();
    });

</script>