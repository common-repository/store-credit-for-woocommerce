<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

class WC_Product_Pi_Store_Credit_Range extends WC_Product{

    public function __construct( $product ) {
        $this->product_type = 'pi_store_credit_range';
        parent::__construct( $product );
    }

    public function get_type() {
        return 'pi_store_credit_range';
    }

    public function get_min_amount( $context = 'view' ){
        return $this->get_meta( '_min_amount', true);
    }

    public function get_max_amount( $context = 'view' ){
        return $this->get_meta( '_max_amount', true );
    }

    public function get_expiry_days( $context = 'view' ){
        return $this->get_meta( '_expiry_days', true );
    }

     /**
     * woocommerce is_purchasable blocks product from purchase if price field is empty since our all product will have empty price earlier we will remove the product price check 
     */
    public function is_purchasable() {
		return apply_filters( 'woocommerce_is_purchasable', $this->exists() && ( 'publish' === $this->get_status() || current_user_can( 'edit_post', $this->get_id() ) ), $this );
	}

    public function is_virtual(){
        return true;
    }
}

class WC_Product_Pi_Store_Credit_Option extends WC_Product{

    public function __construct( $product ) {
        $this->product_type = 'pi_store_credit_option';
        parent::__construct( $product );
    }

    public function get_type() {
        return 'pi_store_credit_option';
    }

    public function is_purchasable() {
		return apply_filters( 'woocommerce_is_purchasable', $this->exists() && ( 'publish' === $this->get_status() || current_user_can( 'edit_post', $this->get_id() ) ), $this );
	}

    public function is_virtual(){
        return true;
    }

    public function get_amount_options(){
        return $this->get_meta( '_pi_options', true );
    }
}