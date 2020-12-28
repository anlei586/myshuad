<?php

/**
 * Smart Variations Images for WooCommerce
 *
 * By default WooCommerce will only swap the main variation image when you select a product variation, not the gallery images below it.
 *
 * This extension allows visitors to your online store to be able to swap different gallery images when they select a product variation.
 * Adding this feature will let visitors see different images of a product variation all in the same color and style.
 *
 * This extension will allow the use of multiple images per variation, and simplifies it! How?
 * Instead of upload one image per variation, upload all the variation images to the product gallery and for each image choose the corresponding slug of the variation on the dropdown.
 * As quick and simple as that!
 *
 * @link              https://www.smart-variations.com/smart-variations-images-pro
 * @since             1.0.0
 * @package           svi
 *
 * @wordpress-plugin
 * Plugin Name:       Smart Variations Images for WooCommerce
 * Plugin URI:        https://www.smart-variations.com/smart-variations-images-pro
 * Description:       This is a WooCommerce extension plugin, that allows the user to add any number of images to the product images gallery and be used as variable product variations images in a very simple and quick way, without having to insert images p/variation.
 * Version:           4.0.77
 * WC requires at least: 4.0
 * WC tested up to: 4.8.0
 * Author:            David Rosendo
 * Author URI:        https://www.rosendo.pt
 * Text Domain:       svi
 * Domain Path:       /languages
 */
// don't call the file directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'svi_fs' ) ) {
    svi_fs()->set_basename( false, __FILE__ );
    return;
} else {
    
    if ( !function_exists( 'svi_fs' ) ) {
        // Create a helper function for easy SDK access.
        function svi_fs()
        {
            global  $svi_fs ;
            
            if ( !isset( $svi_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $svi_fs = fs_dynamic_init( array(
                    'id'             => '2228',
                    'slug'           => 'smart-variations-images',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_6a5f1fc0c8ab537a0b07683099ada',
                    'is_premium'     => false,
                    'premium_suffix' => 'SVI PRO',
                    'currency'       => 'auto',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                    'slug'    => 'woocommerce_svi',
                    'support' => true,
                    'network' => true,
                    'parent'  => array(
                    'slug' => 'woocommerce',
                ),
                ),
                    'is_live'        => true,
                ) );
            }
            
            return $svi_fs;
        }
        
        // Init Freemius.
        svi_fs();
        // Signal that SDK was initiated.
        do_action( 'svi_fs_loaded' );
    }
    
    if ( !function_exists( 'svi_fs_custom_icon' ) ) {
        function svi_fs_custom_icon()
        {
            return dirname( __FILE__ ) . '/assets/img/svi.png';
        }
    
    }
    svi_fs()->add_filter( 'plugin_icon', 'svi_fs_custom_icon' );
    if ( !function_exists( 'svi_fs_suport' ) ) {
        function svi_fs_suport()
        {
            return 'https://www.smart-variations.com';
        }
    
    }
    if ( !function_exists( 'svi_fs_is_submenu_visible' ) ) {
        function svi_fs_is_submenu_visible( $is_visible, $submenu_id )
        {
            switch ( $submenu_id ) {
                case 'support':
                    
                    if ( svi_fs()->is_plan( 'pro', true ) ) {
                        $return = true;
                    } else {
                        $return = false;
                    }
                    
                    break;
                case 'pricing':
                    
                    if ( svi_fs()->is_plan( 'pro', true ) ) {
                        $return = false;
                    } else {
                        $return = $is_visible;
                    }
                    
                    break;
                case 'contact':
                    $return = false;
                    break;
                default:
                    $return = $is_visible;
            }
            if ( !current_user_can( 'edit_products' ) ) {
                $return = false;
            }
            return $return;
        }
    
    }
    svi_fs()->add_filter(
        'is_submenu_visible',
        'svi_fs_is_submenu_visible',
        10,
        2
    );
    /**
     * VUE_SVI class
     *
     * @class VUE_SVI The class that holds the entire VUE_SVI plugin
     */
    final class VUE_SVI
    {
        /**
         * Plugin version
         *
         * @var string
         */
        public  $version = '4.0.77' ;
        /**
         * Holds various class instances
         *
         * @var array
         */
        private  $container = array() ;
        /**
         * Constructor for the VUE_SVI class
         *
         * Sets up all the appropriate hooks and actions
         * within our plugin.
         */
        public function __construct()
        {
            $this->define_constants();
            register_activation_hook( __FILE__, array( $this, 'activate' ) );
            register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
            $this->loadRedux();
            $this->init_plugin();
            add_action( 'admin_init', array( $this, 'deactivate_svipro' ) );
        }
        
        /**
         * Initializes the VUE_SVI() class
         *
         * Checks for an existing VUE_SVI() instance
         * and if it doesn't find one, creates it.
         */
        public static function init()
        {
            static  $instance = false ;
            if ( !$instance ) {
                $instance = new VUE_SVI();
            }
            return $instance;
        }
        
        /**
         * Magic getter to bypass referencing plugin.
         *
         * @param $prop
         *
         * @return mixed
         */
        public function __get( $prop )
        {
            if ( array_key_exists( $prop, $this->container ) ) {
                return $this->container[$prop];
            }
            return $this->{$prop};
        }
        
        /**
         * Magic isset to bypass referencing plugin.
         *
         * @param $prop
         *
         * @return mixed
         */
        public function __isset( $prop )
        {
            return isset( $this->{$prop} ) || isset( $this->container[$prop] );
        }
        
        /**
         * Define the constants
         *
         * @return void
         */
        public function define_constants()
        {
            define( 'SVI_VERSION', $this->version );
            define( 'SVI_FILE', __FILE__ );
            define( 'SVI_PATH', dirname( SVI_FILE ) );
            define( 'SVI_INCLUDES', SVI_PATH . '/includes' );
            define( 'SVI_URL', plugins_url( '', SVI_FILE ) );
            define( 'SVI_ASSETS', SVI_URL . '/assets' );
        }
        
        /**
         * Load the plugin after all plugis are loaded
         *
         * @return void
         */
        public function init_plugin()
        {
            $this->includes();
            $this->init_hooks();
        }
        
        /**
         * Loads the ReduxFramework Options Panel
         *
         *
         * @since 1.0.0
         * @return
         */
        public function loadRedux()
        {
            if ( !class_exists( 'ReduxFramework' ) && file_exists( SVI_INCLUDES . '/library/redux-framework/ReduxCore/framework.php' ) ) {
                require_once SVI_INCLUDES . '/library/redux-framework/ReduxCore/framework.php';
            }
            if ( $this->is_request( 'admin' ) ) {
                include_once SVI_INCLUDES . '/admin/options-init.php';
            }
        }
        
        /**
         * Placeholder for activation function
         *
         * Nothing being called here yet.
         */
        public function activate()
        {
            $installed = get_option( 'svi_installed' );
            if ( !$installed ) {
                update_option( 'svi_installed', time() );
            }
            update_option( 'svi_version', SVI_VERSION );
        }
        
        /**
         * Placeholder for activation function
         *
         * Nothing being called here yet.
         */
        public function deactivate_svipro()
        {
            // Check fro PRO SVI v3 and Deactivate it.
            if ( is_plugin_active( 'smart-variations-images-pro/svipro.php' ) ) {
                deactivate_plugins( 'smart-variations-images-pro/svipro.php' );
            }
        }
        
        /**
         * Placeholder for deactivation function
         *
         * Nothing being called here yet.
         */
        public function deactivate()
        {
        }
        
        /**
         * Include the required files
         *
         * @return void
         */
        public function includes()
        {
            require_once SVI_INCLUDES . '/class-assets.php';
            
            if ( $this->is_request( 'admin' ) ) {
                require_once SVI_INCLUDES . '/persist-admin-notices-dismissal/persist-admin-notices-dismissal.php';
                require_once SVI_INCLUDES . '/class-mobile.php';
                require_once SVI_INCLUDES . '/class-frontend.php';
                require_once SVI_INCLUDES . '/class-admin.php';
            }
            
            
            if ( $this->is_request( 'frontend' ) ) {
                require_once SVI_INCLUDES . '/class-mobile.php';
                require_once SVI_INCLUDES . '/class-frontend.php';
            }
        
        }
        
        /**
         * Initialize the hooks
         *
         * @return void
         */
        public function init_hooks()
        {
            add_action( 'init', array( $this, 'init_classes' ) );
            // Localize our plugin
            add_action( 'init', array( $this, 'localization_setup' ) );
        }
        
        /**
         * Instantiate the required classes
         *
         * @return void
         */
        public function init_classes()
        {
            
            if ( $this->is_request( 'admin' ) ) {
                $this->container['frontend'] = new SVIApp\Frontend();
                $this->container['admin'] = new SVIApp\Admin();
            }
            
            
            if ( $this->is_request( 'frontend' ) ) {
                $this->container['mobile'] = new SVIApp\Mobile_Detect();
                $this->container['frontend'] = new SVIApp\Frontend();
            }
            
            $this->container['assets'] = new SVIApp\Assets();
        }
        
        /**
         * Initialize plugin for localization
         *
         * @uses load_plugin_textdomain()
         */
        public function localization_setup()
        {
            load_plugin_textdomain( 'svi', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        }
        
        /**
         * What type of request is this?
         *
         * @param  string $type admin, ajax, cron or frontend.
         *
         * @return bool
         */
        private function is_request( $type )
        {
            switch ( $type ) {
                case 'admin':
                    return is_admin();
                case 'ajax':
                    return defined( 'DOING_AJAX' );
                case 'rest':
                    return defined( 'REST_REQUEST' );
                case 'cron':
                    return defined( 'DOING_CRON' );
                case 'frontend':
                    return (!is_admin() || defined( 'DOING_AJAX' )) && !defined( 'DOING_CRON' );
            }
        }
    
    }
    // VUE_SVI
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function run_svi()
    {
        $svi = VUE_SVI::init();
    }
    
    function svi_pre( $args )
    {
        if ( current_user_can( 'administrator' ) ) {
            echo  "<pre>" . print_r( $args, true ) . "</pre>" ;
        }
    }
    
    add_action( 'plugins_loaded', 'run_svi', 20 );
}
