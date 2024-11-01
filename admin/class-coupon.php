<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Pi_store_credit_coupon{
    protected static $instance = null;

    static $event = 'pi_send_store_credit_email';

    public static function get_instance( ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    protected function __construct( ){
        add_filter( 'woocommerce_coupon_discount_types', [ $this,'customDiscountType' ], 10, 1 );

        add_filter( 'woocommerce_coupon_data_tabs', [ $this, 'storeCreditUsedDetailTab' ]);

        add_action( 'woocommerce_coupon_data_panels', [ $this, 'storeCreditDetailsPanel' ], 10, 2);

        add_action( 'woocommerce_coupon_options', [ $this, 'couponExtraFields' ], 10, 2);

        add_action( 'woocommerce_coupon_object_updated_props', [$this, 'saveExtraFields'], 10, 2 );

        add_action('admin_notices',  [ $this, 'checkOffer' ] );

        add_action( 'wp_ajax_pi_send_store_credit_emails', [$this, 'sendEmailsEventSchedule'] );

        add_action( 'wp_ajax_pi_send_store_credit_email', [$this, 'sendIndividualEmail'] );

        add_action(self::$event, [$this, 'sendEmail'], 10, 2);
    }

    function customDiscountType( $discount_types ){
        $discount_types[ 'pi_store_credit' ] = __( 'Store credit', 'store-credit-for-woocommerce' );
        return $discount_types;
    }

    function storeCreditUsedDetailTab( $tabs ){
        $tabs['store_credit_usage'] = array(
            'label'  => __( 'Credit usage', 'store-credit-for-woocommerce' ),
            'target' => 'store_credit_coupon_data',
            'class'  => 'store_credit_coupon_data',
        );
        return $tabs;
    }

    function storeCreditDetailsPanel( $id, $coupon ){
        $allowed_emails = get_post_meta( $id, 'customer_email', true );
        $usage_data = $this->couponUsageDetail( $coupon );
        $coupon_amt = get_post_meta($coupon->get_id(), 'coupon_amount', true);
        $send_emails = add_query_arg(['action' => "pi_send_store_credit_emails", 'id' => $id, '_wpnonce' => wp_create_nonce('send-email')], admin_url('admin-ajax.php'));
        ?>
        <div id="store_credit_coupon_data" class="panel woocommerce_options_panel">
        <div style="margin:10px; text-align:right;">
        <a class="button" id="send-store-credit-emails" href="javascript:void(0);" data-href="<?php echo $send_emails; ?>">Send store credit email to all</a>
        </div>    
        <table id="store_credit_detail_table">
                <tr>
                    <th><?php _e('State', 'store-credit-for-woocommerce'); ?></th>
                    <th><?php _e('Email id', 'store-credit-for-woocommerce'); ?></th>
                    <th><?php _e('Credit remaining', 'store-credit-for-woocommerce'); ?></th>
                    <th><?php _e('Send email', 'store-credit-for-woocommerce'); ?></th>
                </tr>
                <?php
                    if(!empty($usage_data) && is_array($usage_data)){
                        foreach($usage_data as $email => $data){
                            $send_email = add_query_arg(['action' => "pi_send_store_credit_email", 'id' => $id, '_wpnonce' => wp_create_nonce('send-email'), 'email' => $email], admin_url('admin-ajax.php'));
                            $state = in_array($email, $allowed_emails ) ? __('Active', 'store-credit-for-woocommerce') : __('Disabled', 'store-credit-for-woocommerce');
                            echo '<tr>';
                                echo '<td>'.esc_html($state).'</td>';
                                echo '<td>'.esc_html($email).'</td>';
                                echo '<td>'.esc_html($data['credit_available']).'</td>';
                                echo '<td>'.(in_array($email, $allowed_emails ) ? '<a class="button" id="send-store-credit-email" href="javascript:void(0);" data-href="'.$send_email.'">Send store credit email</a>' : '-').'</td>';
                            echo '</tr>';
                        }
                    }else{
                ?>
                    <tr><td colspan="3"><?php _e('No data found', 'store-credit-for-woocommerce'); ?></td></tr>
                <?php } ?>
            </table>
        </div>
        <?php
    }

    function couponUsageDetail( $coupon ){
        
        $emails = Pi_store_credit_discount::getAllEmailId( $coupon->get_id() );
        
        $usage_data = [];

        if(!empty($emails)){
            foreach($emails as $email){
                    $usage_data[$email] = [
                        'credit_available' => Pi_store_credit_discount::getUserAvailableDiscountAmount($coupon, $email)
                    ];
            }
        }

        return $usage_data;
    }



    function checkOffer( ){
        $screen = get_current_screen();
        if ( $screen->id == 'shop_coupon' ){
            global $post;
            $id = $post->ID;
            $errors = $this->checkErrors( $id );

            if(!empty($errors)){
                $this->displayError( $errors, __('Error in Store credit coupon', 'store-credit-for-woocommerce') );
            }
        }
    }

    function checkErrors( $id ){
        $errors = [];

        if( ! Pi_store_credit_discount::isCreditCoupon( $id ) ) return $errors;

        $this->customEmailPresent( $id, $errors );

        return $errors;
    }

    function customEmailPresent( $id, &$errors ){
       $email = get_post_meta( $id, 'customer_email', true );
       if(empty( $email )){
        $errors[] = __('Usage restriction >> Allowed emails cant be left empty', 'store-credit-for-woocommerce');
       }
    }

    function displayError( $errors, $title ){
        $error_msg = '<ol>';
        foreach($errors as $error){
            $error_msg .= '<li>'.$error.'</li>';
        }
        $error_msg .= '</ol>';
        echo '<div class="error">';
        echo '<h4>'.esc_html($title).'</h4>';
        echo wp_kses($error_msg, ['li'=>[], 'strong' => []]);
        echo '</div>';
    }

    function sendIndividualEmail(){
        check_ajax_referer('send-email');

        if(!current_user_can('administrator')) die;

        $id = sanitize_text_field( $_GET['id'] );
        $email = sanitize_text_field( $_GET['email'] );

        $this->sendEmail($id, $email);
    }

    function sendEmailsEventSchedule(){
        check_ajax_referer('send-email');

        if(!current_user_can('administrator')) die;

        $gap_between_two_email = apply_filters('pi_store_credit_email_gap', 100);

        $id = sanitize_text_field( $_GET['id'] );
        $emails = get_post_meta( $id, 'customer_email', true);

        if(is_array($emails) && !empty($emails)){
            $count = 10;
            foreach($emails as $email){
                $this->scheduleEmail( $id, $email, $count);
                $count += $gap_between_two_email;
            }
        }
        die;
    }

    function scheduleEmail( $id, $email, $count = 10 ){
        if ( ! wp_next_scheduled( self::$event, [$id, $email] ) ) {
            wp_schedule_single_event( time() + $count, self::$event, [$id, $email]);
        }
    }

    function sendEmail($id, $email){
        if( ! Pi_store_credit_discount::isCreditCoupon( $id ) ) return;

        pi_store_credit_email::sendStoreCreditEmail($id, $email);
    }

    function couponExtraFields($coupon_id, $coupon){
        woocommerce_wp_select(
            array(
                'id'      => 'restrict_credit_amount_by',
                'label'   => __( 'Max Store credit that can be used in one order', 'store-credit-for-woocommerce' ),
                'options' => [''=>__('No restriction', 'store-credit-for-woocommerce'), 'fixed-amount' => __('Fixed amount', 'store-credit-for-woocommerce'), 'percent-of-cart-subtotal' => __('Percentage of order subtotal', 'store-credit-for-woocommerce')],
                'value'   => get_post_meta($coupon_id, 'restrict_credit_amount_by', true),
            )
        );
        echo '<div id="pi-coupon-restricted-amount">';
        woocommerce_wp_text_input(
            array(
                'id'          => 'restricted_credit_amount',
                'label'       => __( 'Restricted amount', 'woocommerce' ),
                'placeholder' => wc_format_localized_price( 0 ),
                'value'       => get_post_meta($coupon_id, 'restricted_credit_amount', true),
                'type' => 'number',
                'custom_attributes' => ['min'=>0, 'step' => '0.01']
            )
        );
        echo '</div>';
    }

    function saveExtraFields( $coupon, $updated_props ){
        if(isset($_POST['restrict_credit_amount_by'])){
            update_post_meta( $coupon->get_id(), 'restrict_credit_amount_by', sanitize_text_field( $_POST['restrict_credit_amount_by'] ));
        }

        if(isset($_POST['restricted_credit_amount'])){
            update_post_meta( $coupon->get_id(), 'restricted_credit_amount', sanitize_text_field( $_POST['restricted_credit_amount'] ));
        }
    }
}

Pi_store_credit_coupon::get_instance();