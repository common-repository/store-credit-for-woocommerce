<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://piwebsolution.com
 * @since             1.0.49.41
 * @package           Store_Credit_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Store credit for woocommerce
 * Requires Plugins:   woocommerce
 * Plugin URI:        https://piwebsolution.com/store-credit
 * Description:       Offer store credit 
 * Version:           1.0.49.41
 * Author:            PI Websolution
 * Author URI:        https://piwebsolution.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       store-credit-for-woocommerce
 * Domain Path:       /languages
 * WC tested up to: 9.3.3
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/* 
    Making sure WooCommerce is there 
*/
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if( ! is_plugin_active( 'woocommerce/woocommerce.php') ){
	if( ! function_exists('pi_store_credit_error_notice') ){
		function pi_store_credit_error_notice() {
			?>
			<div class="error notice">
				<p><?php _e( 'Please Install and Activate WooCommerce plugin, without that this plugin cant work', 'pisol-dtt' ); ?></p>
			</div>
			<?php
		}
		add_action( 'admin_notices', 'pi_store_credit_error_notice' );
	}
    return;
}

/**
 * Currently plugin version.
 * Start at version 1.0.49.41 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'STORE_CREDIT_FOR_WOOCOMMERCE_VERSION', '1.0.49.41' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-store-credit-for-woocommerce-activator.php
 */
function activate_store_credit_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-store-credit-for-woocommerce-activator.php';
	Store_Credit_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-store-credit-for-woocommerce-deactivator.php
 */
function deactivate_store_credit_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-store-credit-for-woocommerce-deactivator.php';
	Store_Credit_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_store_credit_for_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_store_credit_for_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-store-credit-for-woocommerce.php';

if(!function_exists('pisol_store_credit_plugin_link')){
	add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ),  'pisol_store_credit_plugin_link' );

	function pisol_store_credit_plugin_link( $links ) {
		$links = array_merge( array(
			'<a href="' .  admin_url( '/admin.php?page=pisol-store-credit-setting' )  . '">' . __( 'Settings', 'store-credit-for-woocommerce' ) . '</a>',
			'<a style="color:#0a9a3e; font-weight:bold;" target="_blank" href="https://wordpress.org/support/plugin/store-credit-for-woocommerce/reviews/#bbp_topic_content">' . __( 'GIVE SUGGESTION','store-credit-for-woocommerce' ) . '</a>'
		), $links );
		return $links;
	}
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.49.41
 */
function run_store_credit_for_woocommerce() {

	$plugin = new Store_Credit_For_Woocommerce();
	$plugin->run();

}
run_store_credit_for_woocommerce();
