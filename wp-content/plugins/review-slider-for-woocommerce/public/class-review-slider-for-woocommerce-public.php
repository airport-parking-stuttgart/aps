<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Summary_Reviews
 * @subpackage Summary_Reviews/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Summary_Reviews
 * @subpackage Summary_Reviews/public
 * @author     Your Name <email@example.com>
 */
class SRFW_Summary_Reviews_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugintoken    The ID of this plugin.
	 */
	private $plugintoken;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugintoken       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugintoken, $version ) {

		$this->_token = $plugintoken;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Summary_Reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Summary_Reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		//-----only enqueue styles for templates actually used.----

		wp_register_style( 'review-slider-for-woocommerce-public_template1', plugin_dir_url( __FILE__ ) . 'css/srfw-public_template1.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'review-slider-for-woocommerce-public_template1' );

		wp_register_style( 'srfw_w3', plugin_dir_url( __FILE__ ) . 'css/srfw_w3.css', array(), $this->version, 'all' );
		
		//register slider stylesheet
		wp_register_style( 'unslider', plugin_dir_url( __FILE__ ) . 'css/srfw_unslider.css', array(), $this->version, 'all' );
		wp_register_style( 'unslider-dots', plugin_dir_url( __FILE__ ) . 'css/srfw_unslider-dots.css', array(), $this->version, 'all' );

		wp_enqueue_style( 'srfw_w3' );
		wp_enqueue_style( 'unslider' );
		wp_enqueue_style( 'unslider-dots' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Summary_Reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Summary_Reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->_token."_plublic", plugin_dir_url( __FILE__ ) . 'js/srfw-public.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->_token."_unslider-min", plugin_dir_url( __FILE__ ) . 'js/srfw-unslider-min.js', array( 'jquery' ), $this->version, true );

	}
	
	/**
	 * Register the Shortcode for the public-facing side of the site to display the template.
	 *
	 * @since    1.0.0
	 */
	public function shortcode_srfw_usetemplate() {
	
				add_shortcode( 'srfw_usetemplate', array($this,'srfw_usetemplate_func') );
	}	 
	public function srfw_usetemplate_func( $atts, $content = null ) {
		//get attributes
		    $a = shortcode_atts( array(
				'tid' => '0',
				'bar' => 'something',
			), $atts );		//$a['tid'] to get id
	
				ob_start();
				include plugin_dir_path( __FILE__ ) . '/partials/review-slider-for-woocommerce-public-display.php';
				return ob_get_clean();
	}
}
