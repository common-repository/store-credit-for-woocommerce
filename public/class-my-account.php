<?php

class pi_store_credit_my_account{

    private $endpoint = 'pi-my-store-credit';

    protected static $instance = null;

    public static function get_instance( ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    protected function __construct(){

        $this->endpoint = apply_filters('pi_store_credit_my_store_credit_tab_slug', $this->endpoint);

        add_filter('woocommerce_account_menu_items', array($this, 'myAccountSubLink'));
        add_action( 'init', array($this, 'add_endpoint') );

        add_action( "woocommerce_account_{$this->endpoint}_endpoint", array($this, 'endpoint_content') );
    }

    function myAccountSubLink($menu_links){

        $menu_links[$this->endpoint] = __('Store credit', 'store-credit-for-woocommerce');
        return $menu_links;
    }

    function add_endpoint() {
        add_rewrite_endpoint( $this->endpoint, EP_PAGES );
    }

    function endpoint_content() {
        $current_user = wp_get_current_user();
        if(!empty($current_user->user_email)){
            $email_id = $current_user->user_email;
            $coupons = self::getStoreCreditCoupons( $email_id );
            include 'partials/store-credit.php';
        }   
    }    

    static function getStoreCreditCoupons( $email_id ){
        $email_id = strtolower($email_id);
        $results = get_posts([
            'numberposts' => -1, 
            'post_type' => 'shop_coupon',
            'fields' => 'ids',
            'meta_query' => array(
                'relation'=>'AND',
                array(
                    'key' => 'customer_email',
                    'value' => $email_id,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'discount_type',
                    'compare' => '=',
                    'value'   => 'pi_store_credit',
                )
            )
        ]);

        return $results;
    }

    function getExpiryDate($expiry_obj){
        if(empty($expiry_obj)) return;

        $format = apply_filters('pi_store_credit_expiry_date_format', 'M d, Y');
        return $expiry_obj->date( $format );
    }

    static function couponExpired( $coupon ){
        $exp_date = $coupon->get_date_expires();

        if(empty($exp_date)) return false;

        $exp_timestamp = $exp_date->getTimestamp();

        return current_time('timestamp') > $exp_timestamp ? true : false;

    }
}

pi_store_credit_my_account::get_instance( );
