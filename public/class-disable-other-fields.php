<?php
/**
 * Disable other fields that we are not using for our coupon type
 */
class pi_store_credit_disable_other_fields{
    protected static $instance = null;

    public static function get_instance( ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    protected function __construct( ){
        add_filter('woocommerce_coupon_get_free_shipping', [$this, 'free_shipping'], 10, 2);

        
        add_filter( 'woocommerce_coupon_get_exclude_sale_items', [$this, 'exclude_sale_items'], 10, 2);

        add_filter( 'woocommerce_coupon_get_product_ids', [$this, 'product_ids'], 10, 2);
        add_filter( 'woocommerce_coupon_get_excluded_product_ids', [$this, 'excluded_product_ids'], 10, 2);

        
        add_filter( 'woocommerce_coupon_get_product_categories', [$this, 'product_categories'], 10, 2);
        add_filter( 'woocommerce_coupon_get_excluded_product_categories', [$this, 'excluded_product_categories'], 10, 2);
        add_filter( 'woocommerce_coupon_get_usage_limit', [$this, 'usage_limit'], 10, 2);
        add_filter( 'woocommerce_coupon_get_limit_usage_to_x_items', [$this, 'limit_usage_to_x_items'], 10, 2);
        add_filter( 'woocommerce_coupon_get_usage_limit_per_user', [$this, 'usage_limit_per_user'], 10, 2);
    }

    function free_shipping( $val, $coupon ){
        if( ! Pi_store_credit_discount::isCreditCoupon( $coupon->get_id() ) ) return $val;

        return ;
    }

    static function exclude_sale_items($val, $coupon){
        if( ! Pi_store_credit_discount::isCreditCoupon($coupon->get_id()) ) return $val;
            
        return;
    }

    static function product_ids($val, $coupon){
        if( ! Pi_store_credit_discount::isCreditCoupon($coupon->get_id()) ) return $val;
            
        return [];
    }

    static function excluded_product_ids($val, $coupon){
        if( ! Pi_store_credit_discount::isCreditCoupon($coupon->get_id()) ) return $val;
            
        return [];
    }

    static function product_categories($val, $coupon){
        if( ! Pi_store_credit_discount::isCreditCoupon($coupon->get_id()) ) return $val;
            
        return [];
    }

    static function excluded_product_categories($val, $coupon){
        if( ! Pi_store_credit_discount::isCreditCoupon($coupon->get_id()) ) return $val;
            
        return [];
    }

    static function usage_limit($val, $coupon){
        if( ! Pi_store_credit_discount::isCreditCoupon($coupon->get_id()) ) return $val;
            
        return;
    }

    static function limit_usage_to_x_items($val, $coupon){
        if( ! Pi_store_credit_discount::isCreditCoupon($coupon->get_id()) ) return $val;
            
        return;
    }

    static function usage_limit_per_user($val, $coupon){
        if( ! Pi_store_credit_discount::isCreditCoupon($coupon->get_id()) ) return $val;
            
        return;
    }

}

pi_store_credit_disable_other_fields::get_instance( );