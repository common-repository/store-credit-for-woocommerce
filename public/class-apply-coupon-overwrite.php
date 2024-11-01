<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Pi_store_credit_apply_coupon_overwrite{

    protected static $instance = null;

    public static function get_instance( ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    protected function __construct( ){
        /**
         * Default apply_coupon event listener is removed and we add our own custom listener that ask for email id when it find the used coupon is our store credit coupon
         */
        add_action('wp_loaded', [__CLASS__, 'removeDefaultApplyCouponEvent'], PHP_INT_MAX);

        /**
         * Our custom apply_coupon even listener
         */
        add_action( 'wp_ajax_woocommerce_apply_coupon', array( __CLASS__, 'apply_coupon' ) );
		add_action( 'wp_ajax_nopriv_woocommerce_apply_coupon', array( __CLASS__, 'apply_coupon' ) );
		add_action( 'wc_ajax_apply_coupon', array( __CLASS__, 'apply_coupon' ) );

        /**
         * form that we have to show to collect email id, when they try to use our coupon
         */
        add_action('wp_footer', [__CLASS__, 'popupForm']);

       
    }

    static function removeDefaultApplyCouponEvent(){

        self::setCurrentUserInSession();

        remove_action( 'wp_ajax_woocommerce_apply_coupon', array( 'WC_AJAX', 'apply_coupon' ) );
		remove_action( 'wp_ajax_nopriv_woocommerce_apply_coupon', array( 'WC_AJAX', 'apply_coupon' ) );
		remove_action( 'wc_ajax_apply_coupon', array( 'WC_AJAX', 'apply_coupon' ) );
    }

    static function setCurrentUserInSession(){
        $current_user = wp_get_current_user();

        if(function_exists('WC') && isset(WC()->session) && is_object(WC()->session)){
            $session_email = WC()->session->get('billing_email', '');

            if(!empty($session_email)){
                return $session_email;
            }elseif(!empty($current_user->user_email)){
                WC()->session->set('billing_email', $current_user->user_email);
                return $current_user->user_email; 
            } 
        }

        return null;
    }

    static function getCurrentUserFromSession(){

        if(function_exists('WC') && isset(WC()->session) && is_object(WC()->session)){
            $session_email = WC()->session->get('billing_email', '');
            return strtolower($session_email);
        }

        return null;    
    }

    public static function apply_coupon() {

		check_ajax_referer( 'apply-coupon', 'security' );

		if ( ! empty( $_POST['coupon_code'] ) ) {
            $id = wc_get_coupon_id_by_code( wp_unslash( sanitize_text_field( $_POST['coupon_code'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            $session_email_id = self::getCurrentUserFromSession();

            if(Pi_store_credit_discount::isCreditCoupon($id) && !( isset($_POST['billing_email']) || !empty($session_email_id)) ){
                self::askForEmailId();
            }else{
                WC()->cart->add_discount( wc_format_coupon_code( wp_unslash(  $_POST['coupon_code'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            }

		} else {
			wc_add_notice( WC_Coupon::get_generic_coupon_error( WC_Coupon::E_WC_COUPON_PLEASE_ENTER ), 'error' );
		}

		wc_print_notices();
		wp_die();
	}

    static function askForEmailId(){
        ?>
        <ul class="woocommerce-error pi-trigger-email-form" role="alert">
			<li>
			<?php _e('This coupon is associated with an email id, so please insert your Email id', 'store-credit-for-woocommerce'); ?><button class="button pi-open"><?php _e('Insert email', 'store-credit-for-woocommerce'); ?></button>		
            </li>
	    </ul>
        <?php
        wp_die();
    }

    static function popupForm(){
        include_once 'partials/popup.php';
    }
}

Pi_store_credit_apply_coupon_overwrite::get_instance( );