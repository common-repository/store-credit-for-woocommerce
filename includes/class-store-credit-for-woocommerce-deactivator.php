<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://piwebsolution.com
 * @since      1.0.0
 *
 * @package    Store_Credit_For_Woocommerce
 * @subpackage Store_Credit_For_Woocommerce/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Store_Credit_For_Woocommerce
 * @subpackage Store_Credit_For_Woocommerce/includes
 * @author     rajehsingh520 <rajeshsingh520@gmail.com>
 */
class Store_Credit_For_Woocommerce_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		/**
		 * this is needed as we want to run flush end point so we dont get 404 error for store credit page in my account section
		 */
	}

}
