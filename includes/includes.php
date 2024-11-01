<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/pisol.class.form.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/review.php';

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-coupon.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/menu.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/setting.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/basic.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/plugins.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/reminder-email.php';

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-store-credit-product-handling.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/report.php';	

/**
 * As it has extended the WC_Product_Simple class 
 */
add_action('plugins_loaded', function(){
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-store-credit-product.php';
});

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-discount.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-discount-validation.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-apply-coupon-overwrite.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-credit-score-counting.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-email.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-disable-other-fields.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-my-account.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-reminder-email.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-reminder-email-scheduler.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-auto-apply-credit.php';