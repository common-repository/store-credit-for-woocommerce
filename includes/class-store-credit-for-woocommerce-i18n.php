<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://piwebsolution.com
 * @since      1.0.0
 *
 * @package    Store_Credit_For_Woocommerce
 * @subpackage Store_Credit_For_Woocommerce/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Store_Credit_For_Woocommerce
 * @subpackage Store_Credit_For_Woocommerce/includes
 * @author     rajehsingh520 <rajeshsingh520@gmail.com>
 */
class Store_Credit_For_Woocommerce_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'store-credit-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
