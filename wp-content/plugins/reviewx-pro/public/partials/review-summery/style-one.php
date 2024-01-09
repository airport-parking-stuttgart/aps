<?php
/**
 * Single product rating summery
 * @version 1.0.0
 * @author Joules Labs
 */

if( ! defined( 'ABSPATH' ) ) {
	exit();
}

$rx_summary = $this->get_review_summary();
?>

<div id="reviews" class="rx_review_summery_block">
	<div class="rx-reviewbox">
		<div class=" rx-flex-grid-container">
            <div class="rx-flex-grid-50 rx_recommended_wrapper">
                <div class="rx-temp-rating ">
                    <div class="rx_average_rating">
                        <div class="rx-temp-rating-number">
                            <p class="temp-rating_avg"><?php echo esc_attr( $rx_summary['total_rating_average'] ); ?></p><span class="temp-rating_5-star">/<?php esc_html_e( '5', 'reviewx-pro' ); ?></span>
                        </div>
                        <div class="rx-temp-rating-star">
                            <?php echo reviewx_show_star_rating( $rx_summary['total_rating_average'] ); ?>
                        </div>
                    </div>
                    <div class="rx-temp-total-rating-count">
                        <p>
							<?php
								if(  $rx_summary['total_rating_count'] > 0 ){
									echo sprintf( __('Based on %02d rating(s)', 'reviewx-pro' ), $rx_summary['total_rating_count'] ); 
								} else {
									echo sprintf( __('Based on %d rating(s)', 'reviewx-pro' ), $rx_summary['total_rating_count'] ); 
								}								
							?>
						</p>
                    </div>
                </div>
                <?php if( $rx_summary['recommendation'] == 1 ) { ?>
                    <hr>
                    <div class="rx_recommended_box">
                        <div class="rx_recommended_icon_box">
                            <div class="rx_recommended_icon">
                                <?php 									
									if( !empty($rx_summary['recommended_icon']) ) {
								?>
                                    <img src="<?php echo esc_url( $rx_summary['recommended_icon'] ); ?>" alt="<?php esc_attr_e( 'ReviewX', 'reviewx-pro'); ?>">
                                <?php } else { ?>
                                    <img src="<?php echo esc_url( plugins_url( '/', __FILE__ ) . '../../assets/images/recommendation_icon.png' ); ?>" alt="<?php esc_attr_e( 'ReviewX', 'reviewx-pro'); ?>">
                                <?php } ?>	                                
                            </div>
                        </div>
                        <div class="rx_recommended_box-right">
                            <p class="rx_recommended_box_content">
                                <?php  if( get_post_type($rx_summary['prod_id']) == 'product' ) { ?>
                                    <span class="rx_recommended_box_heading">
                                        <?php
											if( $rx_summary['recommendation_count'] > 0 ){
												echo sprintf("%02d", $rx_summary['recommendation_count']);
											} else {
												echo sprintf("%d", $rx_summary['recommendation_count']);
											}
										?>
                                    </span>                                    
                                    <?php echo ! empty(get_theme_mod('reviewx_customer_recommendation_label') ) ? get_theme_mod('reviewx_customer_recommendation_label') : __( 'Customer(s) recommended this item', 'reviewx-pro' ); ?>
                                <?php } else if( \ReviewX_Helper::check_post_type_availability( get_post_type($rx_summary['prod_id']) ) == TRUE ) { ?>
                                    <?php echo __( 'Recommended by ', 'reviewx-pro' ); ?>
                                    <?php
                                        if( $rx_summary['recommendation_count'] > 0 ){
                                            echo sprintf("%02d", $rx_summary['recommendation_count']);
                                        } else {
                                            echo sprintf("%d", $rx_summary['recommendation_count']);
                                        }
									?>
                                    <?php echo __( ' reviewer(s)', 'reviewx-pro' ); ?>
                                <?php } ?>                                
                            </p>
                        </div>
                    </div>
                <?php } ?>
            </div>

			<!-- Start review chart 555 -->
			<div class="rx-flex-grid-50 stfn_rate rx_rating_graph_wrapper">
				<div class="rx-horizontal flat rx-graph-style-2">
                    <?php
                        if( \ReviewX_Helper::is_multi_criteria( get_post_type($rx_summary['prod_id']) ) ) {
						    $inc = 0;
							$cri = apply_filters( 'reviewx_add_criteria', $rx_summary['review_criteria'] );	
							$criteria_arr       = $rx_summary['criteria_arr'];
							$criteria_count     = $rx_summary['criteria_count'];
                            $percentage = 0;

                            if ( ! empty( $cri ) ) {
                                foreach ( $cri as $key => $single_criteria ) {
                                    if ( isset( $criteria_arr[$key] ) && isset( $criteria_count[$key] ) ) {
                                        $percentage = intval( round( ($criteria_arr[$key] / $criteria_count[$key])*100/5 ) ); 
                                    }
                                ?>
                                <div class="progress-bar">
                                    <span class="progress-bar-t"><?php echo esc_html( str_replace( '-', ' ', $single_criteria ) ); ?></span>
                                    <div class="progress-track">
                                        <div class="rx_style_one_progress orange">
                                            <?php if( $percentage > 0 ): ?>
                                                <div class="rx_style_one_progress-bar" style="width: <?php echo esc_attr( $percentage ); ?>%; height: 100%;">
                                                    <span class="rx_style_one_progress-icon">
                                                        <img src="<?php echo esc_url( plugins_url( '/', __FILE__ ) . '../../assets/images/rocket.svg' ); ?>" class="img-fluid" alt="<?php esc_attr_e( 'ReviewX', 'reviewx-pro' ); ?>">
                                                    </span>
                                                    <div class="rx_style_one_progress-value"><span><?php echo esc_attr( $percentage ); ?></span><?php esc_html_e( '%', 'reviewx-pro' ); ?></div>
                                                </div>
                                            <?php else : ?>
                                                <div class="rx_style_one_progress-bar" style="width: 100%; height: 100%;">
                                                    <span class="rx_style_one_progress-icon">
                                                        <img src="<?php echo esc_url( plugins_url( '/', __FILE__ ) . '../../assets/images/rocket.svg' ); ?>" class="img-fluid" alt="<?php esc_attr_e( 'ReviewX', 'reviewx-pro' ); ?>">
                                                    </span>
                                                    <div class="rx_style_one_progress-value"><span><?php esc_html_e('100', 'reviewx-pro' ); ?>%</span></div>
                                                </div>                                        
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    $inc++; 
                                } 
                            }
                        } else { 
                            $rating_info = \ReviewX_Helper::total_rating_count($rx_summary['prod_id']);
                            rsort($rating_info['rating_count']);
                            foreach( $rating_info['rating_count'] as $rt ){
                                $percentage = \ReviewX_Helper::get_percentage($rating_info['review_count'][0]->total_review, isset($rt['total_review'])?$rt['total_review']:0);                           
                            ?>
                            <div class="progress-bar">
                                <span class="progress-bar-t"><?php printf( __( '%s Star', 'reviewx-pro' ), round( $rt['rating'] ) ); ?></span>
                                <div class="progress-track rx-tooltip" data-rating="<?php echo esc_attr( round( $rt['rating'] ) ); ?>">
                                    <div class="rx_style_one_progress orange">
                                        <div class="rx_style_one_progress-bar" style="width: <?php echo esc_attr($percentage); ?>%; height: 100%;">
                                            <span class="rx_style_one_progress-icon">
                                                <img src="<?php echo esc_url( plugins_url( '/', __FILE__ ) . '../../assets/images/rocket.svg' ); ?>" class="img-fluid" alt="<?php esc_attr_e( 'ReviewX', 'reviewx-pro' ); ?>">
                                            </span>
                                            <div class="rx_style_one_progress-value"><span><?php echo esc_attr($percentage); ?></span>%</div>
                                        </div> 
                                        <span class="rx-tooltiptext"><?php echo sprintf( __('%d review(s)', 'reviewx-pro' ), isset($rt['total_review'])?$rt['total_review']:0 ); ?></span>                                    
                                    </div>
                                </div>
                            </div>                
                        <?php
                        }
                    }  
				    ?>
				</div>
			</div>

		</div>

	</div>

</div>