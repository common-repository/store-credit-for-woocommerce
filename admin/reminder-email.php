<?php

class pi_store_credit_reminder_email_options{

    public $plugin_name;

    private $setting = array();

    private $active_tab;

    private $this_tab = 'reminder-email';

    private $setting_key = 'pi_store_credit_reminder_setting';


    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;

        $this->tab_name = __("Credit expiry email",'store-credit-for-woocommerce');

        
        $this->tab = sanitize_text_field(filter_input( INPUT_GET, 'tab'));

        $this->active_tab = $this->tab != "" ? $this->tab : 'default';

        $this->settings = array(
            array('field'=>'title', 'class'=> 'bg-primary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__("Store Credit expiry reminder", 'store-credit-for-woocommerce'), 'type'=>"setting_category"),

            array('field'=>'pi_store_credit_enable_expiry_email','desc'=>'Plugin will send a store credit expiring email to the customer', 'label'=>__('Send store credit expiring reminder email','pi-dcw'),'type'=>'switch', 'default'=> 1),

            array('field'=>'pi_store_credit_first_expiry_reminder_email','desc'=>'How may days before the expiry reminder of expiry should be send', 'label'=>__('Send expiry reminder email x days before expiry'),'type'=>'number', 'default'=>7, 'min'=>0, 'step'=>1),

            array('field'=>'title', 'class'=> 'bg-primary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__("Credit expiry reminder Email labels", 'store-credit-for-woocommerce'), 'type'=>"setting_category"),

            array('field'=>'pi_store_credit_expiry_email_subject', 'label'=>__('Email subject','store-credit-for-woocommerce'),'type'=>'text', 'default'=>__('Your store credit will expire on [expiry_date]', 'store-credit-for-woocommerce'), 'desc'=>""),

            array('field'=>'pi_store_credit_expiry_email_header', 'label'=>__('Email header','store-credit-for-woocommerce'),'type'=>'text', 'default'=>__('Store credit of [amount] will expire on [expiry_date]', 'store-credit-for-woocommerce'), 'desc'=>__('You can use short code [amount] for coupon discount amount, [code] for coupon code and [expiry_date] for the expiry date of the coupon', 'store-credit-for-woocommerce')),

            array('field'=>'pi_store_credit_expiry_email_expiry_msg', 'label'=>__('Coupon will expire on','store-credit-for-woocommerce'),'type'=>'text', 'default'=>__('The coupon will expire on [expiry_date]', 'store-credit-for-woocommerce'), 'desc'=>__('You can use short code [amount] for coupon discount amount and [code] for coupon code', 'store-credit-for-woocommerce')),

            array('field'=>'pi_store_credit_expiry_email_top_desc', 'label'=>__('Top part description','store-credit-for-woocommerce'),'type'=>'textarea', 'default'=>__('To redeem your store credit use the following code during checkout', 'store-credit-for-woocommerce'), 'desc'=>__('You can use short code [amount] for coupon discount amount, [code] for coupon code and [description] for the description of the coupon', 'store-credit-for-woocommerce')),

            array('field'=>'pi_store_credit_expiry_email_read_more_url', 'label'=>__('Read more button link'),'type'=>'text', 'default'=>'', 'desc'=>"Give a link of the page where user can read more about the credit score, If you do not add a link then Read more button will not be shown"),

            array('field'=>'pi_store_credit_expiry_email_read_more', 'label'=>__('Read more button text'),'type'=>'text', 'default'=>__('Read more', 'store-credit-for-woocommerce'), 'desc'=>""),

            array('field'=>'pi_store_credit_expiry_email_bottom_desc', 'label'=>__('Bottom part description','store-credit-for-woocommerce'),'type'=>'textarea', 'default'=>__('This credit can be used multiple times till its balance finishes or it expires', 'store-credit-for-woocommerce'), 'desc'=>__('You can use short code [amount] for coupon discount amount, [code] for coupon code and [description] for the description of the coupon', 'store-credit-for-woocommerce')),
             
        );

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
        }

        add_action($this->plugin_name.'_tab', array($this,'tab'),1);

        $this->register_settings();
        
    }

    function register_settings(){   

        foreach($this->settings as $setting){
            register_setting( $this->setting_key, $setting['field']);
        }
    
    }

    function tab(){
        ?>
        <a class=" pi-side-menu  <?php echo ($this->active_tab == $this->this_tab ? 'bg-primary' : 'bg-secondary'); ?>" href="<?php echo admin_url( 'admin.php?page='.sanitize_text_field($_GET['page']).'&tab='.$this->this_tab ); ?>">
        <span class="dashicons dashicons-email"></span> <?php echo $this->tab_name; ?> 
        </a>
        <?php
    }

    function tab_content(){
       ?>
        <form method="post" action="options.php"  class="pisol-setting-form">
        <?php settings_fields( $this->setting_key ); ?>
        <?php
            foreach($this->settings as $setting){
                new pisol_class_form_store_credit($setting, $this->setting_key);
            }
        ?>
        <input type="submit" class="mt-3 btn btn-md btn-primary" value="<?php _e('Save Option','store-credit-for-woocommerce'); ?>" />
        </form>
       <?php
    }

}

new pi_store_credit_reminder_email_options( $this->plugin_name );
