<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Pi_store_credit_discount_validation{

    protected static $instance = null;

    public static function get_instance( ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    protected function __construct( ){
        add_filter( 'woocommerce_coupon_is_valid', [$this, 'validate'], 10, 3 );

        /**
         * when we are forcing to use registered email id instead of billing email id in that case WooCommerce core error comes up as it consider the billing email id so to suppress that error we use this
         */
        add_filter( 'woocommerce_coupon_get_email_restrictions', [__CLASS__, 'avoidEmailValidation'], 10, 2 );
    }

    function validate($return, $coupon, $discount){
        if(!Pi_store_credit_discount::isCreditCoupon($coupon->get_id())) return $return;

        $this->validateEmailId($coupon, $discount);
        //throw new Exception( 'Test', WC_Coupon::E_WC_COUPON_USAGE_LIMIT_REACHED );
        return $return;
    }

    function validateEmailId($coupon, $discount){

        $this->validateEmailPresence($coupon, $discount);
    }

    function validateEmailPresence($coupon, $discount){
        
        $billing_email_id = $this->getEmailId( $coupon, $discount );

        $button = '<button class="pi-open">'.__('Insert email', 'store-credit-for-woocommerce').'</button>';

        if(empty($billing_email_id)){
            throw new Exception( __('Please insert email id to use this coupon ', 'store-credit-for-woocommerce').$button, WC_Coupon::E_WC_COUPON_NOT_YOURS_REMOVED);
        }

        $coupon_email_id = get_post_meta($coupon->get_id(), 'customer_email', true);

        if(is_array($coupon_email_id)){
            $coupon_email_id = array_map('strtolower', $coupon_email_id);
        }

        if(empty( $coupon_email_id ) || !in_array( $billing_email_id, $coupon_email_id)){
            throw new Exception( sprintf(__('Email id %s is not associated with this coupon','store-credit-for-woocommerce'), $billing_email_id).$button, WC_Coupon::E_WC_COUPON_NOT_YOURS_REMOVED);
        }
    }

    function getEmailId( $coupon, $discount ){
        $object = $discount->get_object();
        $billing_email_id = '';
        if ( $object instanceof WC_Order ) {
            $billing_email_id = $object->get_billing_email();
        } elseif(isset($_POST['billing_email'])) {
            $current_user = wp_get_current_user();
            if(!empty($current_user->user_email) && apply_filters('pisol_scfw_force_registered_email_id', false)){
                $billing_email_id = $current_user->user_email;
            }else{
                $billing_email_id = sanitize_text_field($_POST['billing_email']);
            }

            if(!empty($billing_email_id) && function_exists('WC') && is_object(WC()->session)){
                WC()->session->set('billing_email', $billing_email_id);
            }
        }elseif(function_exists('WC') && is_object(WC()->session)){
            $billing_email_id = WC()->session->get('billing_email', '');
        }

        return strtolower($billing_email_id);
    }

    static function avoidEmailValidation($val, $coupon){
        
        $id = $coupon->get_id();
        /**
         * we cant use get type function here as we have changed the type to fixed_cart to apply this coupon on cart
         */
        $type = get_post_meta($id, 'discount_type', true);
        if($type != 'pi_store_credit') return $val;

        if(apply_filters('pisol_scfw_force_registered_email_id', false)){
            return [];
        }

        return $val;
    }

}

Pi_store_credit_discount_validation::get_instance( );