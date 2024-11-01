<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://piwebsolution.com
 * @since      1.0.0
 *
 * @package    Store_Credit_For_Woocommerce
 * @subpackage Store_Credit_For_Woocommerce/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div id='pi_store_credit_range' class='panel woocommerce_options_panel'>
    <?php
        woocommerce_wp_text_input( array(
            'id'          => '_min_amount',
            'label'       => __( 'Min store credit amount', 'store-credit-for-woocommerce' ),
            'placeholder' => '',
            'type' => 'number',
            'custom_attributes' => ['min'=> 0],
            'desc_tip'    => 'true',
            'description' => __( 'Min amount of store credit user can purchase', 'store-credit-for-woocommerce' ),
        ));

        woocommerce_wp_text_input( array(
            'id'          => '_max_amount',
            'label'       => __( 'Max store credit amount', 'store-credit-for-woocommerce' ),
            'placeholder' => '',
            'type' => 'number',
            'custom_attributes' => ['min'=> 0],
            'desc_tip'    => 'true',
            'description' => __( 'Max amount of store credit user can purchase', 'store-credit-for-woocommerce' ),
        ));
    ?>
</div>
<div id='pi_store_credit_options' class='panel woocommerce_options_panel'>
<?php
global $post;
$options = get_post_meta($post->ID, '_pi_options', true);
$val = is_array($options) ? implode(' | ', $options) : '';
        woocommerce_wp_text_input( array(
            'id'          => '_pi_options',
            'label'       => __( 'Insert the Gift coupons amount you want to sell', 'store-credit-for-woocommerce' ),
            'placeholder' => '',
            'type' => 'text',
            'custom_attributes' => [],
            'placeholder'=> '30 | 40 | 50 | 60',
            'desc_tip'    => 'true',
            'description' => __( 'Specify the Gift coupon amount you want to sell E.G: 20 | 30 | 40', 'store-credit-for-woocommerce' ),
            'value' => $val
        ));
?>
</div>

<div id='pi_store_credit_expiry' class='panel woocommerce_options_panel'>
<?php
woocommerce_wp_text_input( array(
    'id'          => '_expiry_days',
    'label'       => __( 'After how many days the coupon will expire', 'store-credit-for-woocommerce' ),
    'placeholder' => '',
    'type' => 'number',
    'custom_attributes' => ['min'=> 0, 'step'=>1],
    'desc_tip'    => 'true',
    'description' => __( 'Set the expiry days expiry date will be calculated based on this days, the counting will be done from the date coupon is issues (that is when order state is set to completed), If left empty the coupon will not expire until its balance finishes', 'store-credit-for-woocommerce' ),
));
?>
</div>
