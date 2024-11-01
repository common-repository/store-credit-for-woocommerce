<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Step 1: Customer login 
 * Step 2: find the max discount coupon and tries to apply
 * step 3: if fails or pass it sets Disable auto apply
 */

class pi_store_credit_auto_apply{
    protected static $instance = null;

    public static function get_instance( ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    function __construct(){
        add_action('woocommerce_after_calculate_totals', [$this, 'autoApplyStoreCredit'], 1000);

        add_filter('woocommerce_coupon_sort', [$this, 'make_store_credit_last'], 10, 2);
    }

    function autoApplyStoreCredit(){
        $auto_apply = get_option('pi_store_credit_auto_apply', 'yes-max');

        if($auto_apply == 'no') return;

        $auto_apply_disable_for_session = self::autoApplyIsBeenDisabledForSession();

        if($auto_apply_disable_for_session) return;

        $user_has_already_applied_other_store_credit = self::otherStoreCreditAlreadyApplied();

        if( $user_has_already_applied_other_store_credit ) return;

        $email_id = $this->getCurrentUserEmail();

        $all_coupons = $this->getAllStoreCreditForCustomer($email_id);

        $coupon_with_max_balance = self::getCouponWithMaxBalance( $all_coupons, $email_id );

        self::autoApplyCoupon( $coupon_with_max_balance );
    }

    function getAllStoreCreditForCustomer( $email_id ){
        
        if(empty($email_id)) return null;

        $email_id = strtolower($email_id);

        $all_coupons = pi_store_credit_my_account::getStoreCreditCoupons( $email_id );

        return $all_coupons;
    }

    function getCurrentUserEmail(){
        $current_user = wp_get_current_user();
        $session_email = WC()->session->get('billing_email', '');

        if(!empty($session_email)){
            return strtolower($session_email);
        }elseif(!empty($current_user->user_email)){
            WC()->session->set('billing_email', $current_user->user_email);
            return strtolower($current_user->user_email); 
        } 

        return null;
    }

    static function getCouponWithMaxBalance( $all_coupons, $email_id ){
        $coupon_with_max_balance = false;
        $coupon_balance = 0;

        if(empty($all_coupons) || !is_array($all_coupons)) return false;

        foreach( $all_coupons as $coupon_id ){
            $coupon = new WC_Coupon( $coupon_id );
            if(! self::couponExpired( $coupon )){
                $available_balance = Pi_store_credit_discount::getUserAvailableDiscountAmount($coupon, $email_id);
                if($available_balance > $coupon_balance){
                    $coupon_with_max_balance = $coupon;
                    $coupon_balance = $available_balance;
                }
            }
        }

        return $coupon_with_max_balance;
    }

    static function autoApplyCoupon( $coupon )
    {    
        if(is_admin() || !is_object($coupon))
        {
            return;   
        }
        
        $cart = (is_object(WC()) && isset(WC()->cart)) ? WC()->cart : null;
        
        if(is_object($cart) && is_callable(array($cart, 'is_empty')) && !$cart->is_empty())
        {   
            $coupon_code = wc_sanitize_coupon_code($coupon->get_code());

            if( ! self::couponAlreadyApplied( $coupon_code ) ){
                if( WC()->cart->add_discount($coupon_code) ){
                    WC()->session->set('disable_auto_apply', 1);
                }else{
                    /**
                     * We do not want to keep trying to apply coupon when it failed on first atempt as it will fail again and create too many notification
                     */
                    WC()->session->set('disable_auto_apply', 1);
                }
            }
        }
    }

    static function couponExpired( $coupon ){
        $exp_date = $coupon->get_date_expires();

        if(empty($exp_date)) return false;

        $exp_timestamp = $exp_date->getTimestamp();

        return current_time('timestamp') > $exp_timestamp ? true : false;

    }

    static function couponAlreadyApplied( $coupon_code ){
        $applied_coupons = WC()->cart->get_applied_coupons();
        if(empty($applied_coupons)) return false;

        if(in_array($coupon_code, $applied_coupons)) return true;

        return false;
    }

    static function otherStoreCreditAlreadyApplied(){
        $applied_coupons = WC()->cart->get_applied_coupons();
        if(empty($applied_coupons)) return false;

        foreach($applied_coupons as $code){
            $coupon = new WC_Coupon( $code );

            if( Pi_store_credit_discount::isCreditCoupon($coupon->get_id()) ) return true;
        }
        
        return false;
    }

    static function autoApplyIsBeenDisabledForSession(){
        $removed = WC()->session->get('disable_auto_apply', '');
        if(empty( $removed )) return false;

        return true;
    }

    /**
     * if user has store credit (since this is like there own money), and they have some discount coupon, in normal case store credit is always applied first so the customer looses money as they have to pay from there own pocket and discount coupon is applied afterwords which is not good. so we changed the sequence of the store credit to apply at the end so that customer can use there discount coupon.
     */
    static function make_store_credit_last($sort, $coupon){
        if( Pi_store_credit_discount::isCreditCoupon($coupon->get_id()) ){
            /**
             * coupon whose expiry is coming near will get applied first
             */
            $exp_date = $coupon->get_date_expires();
            if(!empty($exp_date)){
                $exp_timestamp = $exp_date->getTimestamp();
                $sort_position = $exp_timestamp;
            }else{
                $sort_position = PHP_INT_MAX;
            }
            return $sort_position;
        }
        return $sort;
    
    }
}

pi_store_credit_auto_apply::get_instance( );