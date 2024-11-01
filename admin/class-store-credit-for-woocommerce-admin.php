<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://piwebsolution.com
 * @since      1.0.0
 *
 * @package    Store_Credit_For_Woocommerce
 * @subpackage Store_Credit_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Store_Credit_For_Woocommerce
 * @subpackage Store_Credit_For_Woocommerce/admin
 * @author     rajehsingh520 <rajeshsingh520@gmail.com>
 */
class Store_Credit_For_Woocommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Store_Credit_For_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Store_Credit_For_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Store_Credit_For_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Store_Credit_For_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$screen = get_current_screen();

		if($screen->id == 'shop_coupon' || $screen->id == 'product'){
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/store-credit-for-woocommerce-admin.js', array( 'jquery' ), $this->version, false );

			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/store-credit-for-woocommerce-admin.css', array(), $this->version, 'all' );
		}

	}

}
