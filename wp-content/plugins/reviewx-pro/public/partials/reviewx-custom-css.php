<?php 
    $settings    = \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_option_settings();
    $color_theme = $settings->color_theme;
    if( $color_theme ) {
?>
	<style id="custom-style">
        .vertical .progress-fill{
			background-color: <?php echo esc_attr( $color_theme ); ?>;
        }
        .rx_meta .rx-admin-reply{
            background-color: <?php echo esc_attr( $color_theme ); ?>;
            border: 1px solid <?php echo esc_attr( $color_theme ); ?>;
        }
        .rx_admin_heighlights span svg{
            color: <?php echo esc_attr( $color_theme ); ?>;
        }
        .rx_listing .rx_helpful_style_1_svg svg{
            fill: <?php echo esc_attr( $color_theme ); ?>;
        }
        .rx_listing_style_2 .rx_helpful_style_2_svg svg{
            fill: <?php echo esc_attr( $color_theme ); ?>;
        }
        .rx_listing_style_2 .rx_review_block .children .rx_thumb svg .st0{
            fill: <?php echo esc_attr( $color_theme ); ?>;
        }
	</style>
<?php } ?>