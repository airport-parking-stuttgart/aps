<?php
/**
 * Customer new account email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-new-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;


//do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

    <style>
        * {
            color: black !important;
        }

        h1 {
            text-align: center;
        }

        .header {
            margin-top: 30px;
            margin-bottom: 50px;
        }

        .col .left {
            width: 50%;
            float: left;
        }

        .col .right {
            float: right;
            width: 50%;
        }

        .header .right {
            text-align: right;
        }

        .content {
            max-width: 600px;
            margin: 15px auto;
            padding: 10px;
            background: white;
        }

        .container {
            background: #f0f0f0;
            padding: 20px 0;
        }

        .footer {
            background: #262626;
            padding: 15px 0;
        }

        .footer * {
            color: white !important;
        }

        .footer .content {
            background: #262626 !important;
        }

        .wir-sind {
            text-align: center;
        }

        .parklot-img,
        .so-kontakt {
            padding-right: 10px;
            box-sizing: border-box;
        }

        .social-media-icons img {
            width: 50px;
            height: 50px;
        }

        .clear {
            clear: both;
        }

        /* ----------- iPhone 6, 6S, 7 and 8 ----------- */

        /* Portrait */

        @media only screen

        and (max-device-width: 667px){
            .left,
            .right {
                display: block !important;
                float: none !important;
                clear: both !important;
                width: 100% !important;
            }
        }
    </style>
    <div class="header col">
        <div class="content">
            <div class="left">
                <?php if (get_theme_mod('itweb24_theme_logo')) : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <img src="<?php echo esc_url(get_theme_mod('itweb24_theme_logo')); ?>"
                             alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
                    </a>
                <?php else : ?>
                    <div class="shop-name"><h3><?php $this->shop_name(); ?></h3></div>
                <?php endif; ?>
            </div>
            <div class="right">
                <a href="<?php echo url() ?>/kontakt">Kontakt</a>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <div class="container">
        <div class="content">
            <h3>
                Ihre Registrierung bei Airport-Parking-Stuttgart.de
            </h3>
            <hr>
			<?php /* translators: %s: Customer username */ ?>
			<p><?php printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $user_login ) ); ?></p>
			<?php /* translators: %1$s: Site title, %2$s: Username, %3$s: My account link */ ?>
			<p><?php printf( esc_html__( 'Thanks for creating an account on %1$s. Your username is %2$s. You can access your account area to view orders, change your password, and more at: %3$s', 'woocommerce' ), esc_html( $blogname ), '<strong>' . esc_html( $user_login ) . '</strong>', make_clickable( esc_url( wc_get_page_permalink( 'myaccount' ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			<?php if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) && $password_generated ) : ?>
				<?php /* translators: %s: Auto generated password */ ?>
				<p><?php printf( esc_html__( 'Your password has been automatically generated: %s', 'woocommerce' ), '<strong>' . esc_html( $user_pass ) . '</strong>' ); ?></p>
			<?php endif; ?>
            <br>
            <br>
        </div>
        <div class="content wir-sind">
            <h1>Wir sind für Sie da!</h1>
            <hr>
            <p>
                Sie haben noch Fragen? Schreiben Sie uns einfach eine E-Mail an
                <a href="mailto:info@a-p-germany.de">info@a-p-germany.de</a> oder rufen Sie uns
                unter <a href="tel:+49 711 22 051 247">+49 711 22 051 247</a> an.
            </p>
            <p>
                Montag bis Freitag von 11:00 bis 19:00 Uhr.
                Aus dem dt. Festnetz zum Ortstarif. Mobilfunkkosten abweichend.
            </p>
        </div>
        <!--<div class="content">
            <div class="col">
                <div class="left so-kontakt">
                    <h1>Bleiben Sie in Kontakt mit uns</h1>
                </div>
                <div class="right social-media-icons">
                    <a href="">
                        <img src="<?php echo get_template_directory_uri() . '/inc/assets/images/square-facebook.png' ?>" alt="">
                    </a>
                </div>
                <div class="clear"></div>
            </div>
        </div> -->
    </div>
    <div class="footer">
        <div class="content">
            <div class="col">
                <div class="left">
                    © 2020 Airport Parking Germany<br/>
                    Raiffeisenstraße 18, 70794 Filderstadt
                </div>
                <div class="right">
                    <a href="<?php echo url() ?>/impressum">Impressum</a> | <a href="<?php echo url() ?>">www.airport-parking-germany.de</a>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>






<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
/*
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}
do_action( 'woocommerce_email_footer', $email );
*/
