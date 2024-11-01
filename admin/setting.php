<?php

class pi_store_credit_settings{

    public $plugin_name;

    private $setting = array();

    private $active_tab;

    private $this_tab = 'default';

    private $setting_key = 'pi_store_credit_main_setting';


    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;

        $this->tab_name = __("Basic setting",'store-credit-for-woocommerce');

        $this->tab = sanitize_text_field(filter_input( INPUT_GET, 'tab'));

        $this->active_tab = $this->tab != "" ? $this->tab : 'default';

        $this->settings = array(
            
            array('field'=>'pi_store_credit_auto_apply', 'label'=>__('Auto apply store credit','store-credit-for-woocommerce'),'type'=>'select', 'default'=>'yes-max', 'desc'=>__('Auto apply the store credit for logged in customer','store-credit-for-woocommerce'), 'value'=> ['no' => __("Do not auto apply store credit", 'store-credit-for-woocommerce'), 'yes-max' => __('Auto apply store credit with max balance','store-credit-for-woocommerce')]),
             
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
        <span class="dashicons dashicons-dashboard"></span> <?php echo $this->tab_name; ?> 
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

new pi_store_credit_settings( $this->plugin_name );
