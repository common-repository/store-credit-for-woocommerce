<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

class pi_store_credit_count{

    protected static $instance = null;

    public static function get_instance( ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    protected function __construct( ){
        /*
        add_action( 'woocommerce_order_status_pending', [$this, 'storeCreditCount'] );
        add_action( 'woocommerce_order_status_completed', [$this, 'storeCreditCount'] );
        add_action( 'woocommerce_order_status_processing', [$this, 'storeCreditCount'] );
        add_action( 'woocommerce_order_status_on-hold', [$this, 'storeCreditCount'] );
        add_action( 'woocommerce_order_status_cancelled', [$this, 'storeCreditCount'] );
        */
        add_action( 'woocommerce_update_order', [$this, 'storeCreditCount'] );
        //add_action( 'woocommerce_order_status_changed', [$this, 'storeCreditCount'] );
    }

    function storeCreditCount( $order_id ){
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        $coupons = $order->get_coupon_codes();

        if ( count( $coupons ) > 0 ) {
                foreach ( $coupons as $code ) {

                    if ( ! $code ) {
                        continue;
                    }

                    $coupon  = new WC_Coupon( $code );

                    if(!Pi_store_credit_discount::isCreditCoupon($coupon->get_id())) continue;

                    $billing_email = $order->get_billing_email();

                    if(apply_filters('pisol_scfw_force_registered_email_id', false)){
                        $user_id = $order->get_user_id();
                        if(!empty($user_id)){
                            $user = get_user_by('id', $user_id);
                            if(!empty($user->user_email)){
                                $billing_email = $user->user_email;
                            }
                        }
                    }

                    if(empty($billing_email)) continue;

                    $existing_order_ids = get_post_meta( $coupon->get_id(), $billing_email);

                    if(empty($existing_order_ids) || !in_array($order->get_id(), $existing_order_ids)){
                        $billing_email = strtolower($billing_email);
                        add_post_meta($coupon->get_id(), $billing_email, $order->get_id());
                    }
                }
        }
    }
}

pi_store_credit_count::get_instance( );