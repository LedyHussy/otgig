<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

if ( !class_exists( '\\Helpie_FAQ' ) ) {
    class Helpie_FAQ
    {
        public  $plugin_domain ;
        public  $views_dir ;
        public  $version ;
        public function __construct()
        {
            global  $Helpie_Faq_Collections ;
            $this->setup_autoload();
            $this->load_libraries();
            $this->load_faq_functions();
            $this->plugin_domain = HELPIE_FAQ_DOMAIN;
            $this->version = HELPIE_FAQ_VERSION;
            /*  FAQ Register Post types and its Taxonomies */
            $this->register_cpt_and_taxonomy();
            /*  FAQ Init Hook */
            add_action( 'init', array( $this, 'init_hook' ) );
            /*  FAQ Activation Hook */
            register_activation_hook( HELPIE_FAQ__FILE__, array( $this, 'hfaq_activate' ) );
            add_action( 'init', array( $this, 'apply_flush_rewrite_rules' ) );
            /**  Rest Endpoints */
            add_action( 'rest_api_init', array( new \HelpieFaq\Includes\Modules\Faq_Rest_Api\Faq_Rest_Api(), 'init' ) );
            /*  FAQ Admin Section Initialization Hook */
            add_action( 'admin_init', array( $this, 'load_admin_hooks' ) );
            /*  FAQ Enqueing Script Action hook */
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            /*  FAQ Shortcode */
            require_once HELPIE_FAQ_PATH . 'includes/shortcodes.php';
            /* All Plugins Loaded Hook */
            add_action( 'plugins_loaded', array( $this, 'plugins_loaded_action' ) );
            /* Notifications */
            // new \HelpieFaq\Includes\Notifications();
            /* Setup Post Meta For Auto-ordering */
            add_action( 'save_post', function ( $postId ) {
                global  $post ;
                if ( isset( $post ) && $post->post_type != HELPIE_FAQ_POST_TYPE ) {
                    return;
                }
                add_post_meta(
                    $postId,
                    'click_counter',
                    0,
                    true
                );
            } );
            add_action(
                'create_term',
                function ( $term_id, $tt_id, $taxonomy ) {
                // $term = get_term($term_id, $taxonomy);
                add_term_meta(
                    $term_id,
                    'click_counter',
                    0,
                    true
                );
            },
                10,
                3
            );
            add_action(
                'edit_term',
                function ( $term_id, $tt_id, $taxonomy ) {
                // $term = get_term($term_id, $taxonomy);
                add_term_meta(
                    $term_id,
                    'click_counter',
                    0,
                    true
                );
            },
                10,
                3
            );
            // $Upgrades = new \HelpieFaq\Includes\Upgrades();
            \HelpieFaq\Includes\Upgrades::add_actions();
            /*  FAQ Settings */
            new \HelpieFaq\Includes\Settings\Settings();
            /** Re-arranging FAQ Submenus */
            add_filter( 'custom_menu_order', array( $this, 'rearranging_faq_submenus' ) );
            /** FAQ Schema Snippets */
            $schema_generator = new \HelpieFaq\Includes\Services\Schema_Generator();
            add_filter( 'helpie_faq_schema_generator', array( $schema_generator, 'set' ) );
            $footer_content = new \HelpieFaq\Includes\Footer_Content();
            add_action( 'wp_footer', array( $footer_content, 'print_js_content' ) );
        }
        
        public function load_libraries()
        {
            if ( !class_exists( "\\Pauple\\Pluginator\\Library" ) ) {
                wp_die( "\"freemius/wordpress-sdk\" and \"Codestar Framework\" library was not installed, \"Helpie FAQ\" is depend on it. Do run \"composer update\"." );
            }
            $library = new \Pauple\Pluginator\Library();
            $library::register_libraries( [ 'codestar', 'freemius' ] );
        }
        
        public function load_faq_functions()
        {
            require_once HELPIE_FAQ_PATH . 'includes/functions.php';
        }
        
        public function load_components()
        {
            new \HelpieFaq\Features\Insights\Insights_Tease_Page();
        }
        
        public function init_hook()
        {
            /*  FAQ Ajax Hooks */
            require_once HELPIE_FAQ_PATH . 'includes/ajax-handler.php';
            /*  FAQ Widget */
            $this->load_widgets();
            $frontend_controller = new \HelpieFaq\Includes\Frontend();
            add_filter( 'helpie_faq/the_content', array( $frontend_controller, 'get_the_faq_content' ), 99 );
            add_filter( 'helpie_faq/read_more_content', array( $frontend_controller, 'get_read_more_content' ), 99 );
            // These components will handle the hooks internally, no need to call this in a hook
            $this->load_components();
        }
        
        public function plugins_loaded_action()
        {
            /*  Helpie KB Integration */
            new \HelpieFaq\Includes\Kb_Integrator();
            /*  Woo Commerce Integration */
            new \HelpieFaq\Includes\Woo_Integrator();
            /*  Helpie FAQ Plugin Translation  */
            load_plugin_textdomain( 'helpie-faq', false, basename( dirname( HELPIE_FAQ__FILE__ ) ) . '/languages/' );
        }
        
        public function load_admin_hooks()
        {
            $admin = new \HelpieFaq\Includes\Admin( $this->plugin_domain, $this->version );
            /* remove 'helpdesk_cateory' taxonomy submenu from Helpie FAQ Menu */
            $admin->remove_kb_category_submenu();
        }
        
        public function load_widgets()
        {
            // 1. Elementor widgets,
            if ( !class_exists( '\\Pauple\\Pluginator\\Widgetry' ) ) {
                return;
            }
            $widgetry = new \Pauple\Pluginator\Widgetry();
            $widgetry->init();
            $faq_widget_args = array(
                'id'          => 'helpie-faq-listing',
                'description' => 'Helpie FAQ Widget',
                'name'        => 'helpie-faq',
                'title'       => 'Helpie FAQ',
                'icon'        => 'fa fa-th-list',
                'categories'  => [ 'general' ],
                'model'       => new \HelpieFaq\Features\Faq\Faq_Model(),
                'view'        => new \HelpieFaq\Features\Faq\Faq(),
            );
            $widgetry->register_widget( $faq_widget_args );
            $faq_widget_args_dynamic_add = array(
                'id'          => 'helpie-faq-dynamic-add',
                'name'        => 'helpie-faq-dynamic-add',
                'title'       => 'Helpie FAQ - Dynamic Add',
                'description' => 'Helpie FAQ Dynamic Add Widget',
                'icon'        => 'fa fa-th-list',
                'categories'  => [ 'general' ],
                'model'       => new \HelpieFaq\Features\Faq\Dynamic_Widget\Faq_Model(),
                'view'        => new \HelpieFaq\Features\Faq\Dynamic_Widget\Faq(),
            );
            $widgetry->register_widget( $faq_widget_args_dynamic_add );
            // Only load if Gutenberg is available.
            
            if ( function_exists( 'register_block_type' ) ) {
                $faq_model = new \HelpieFaq\Features\Faq\Faq_Model();
                $fields = $faq_model->get_fields();
                $style_config = $faq_model->get_style_config();
                $gutenberg_blocks = new \HelpieFaq\Includes\Widgets\Blocks\Register_Blocks( $fields, $style_config );
                $gutenberg_blocks->load();
            }
        
        }
        
        public function register_cpt_and_taxonomy()
        {
            $cpt = new \HelpieFaq\Includes\Cpt();
            $cpt->register();
        }
        
        /**
         * @since 1.0.0
         * @access public
         * @deprecated
         *
         * @return string
         */
        public function get_version()
        {
            return helpie_FAQ_VERSION;
        }
        
        /**
         * Throw error on object clone
         *
         * The whole idea of the singleton design pattern is that there is a single
         * object therefore, we don't want the object to be cloned.
         *
         * @access public
         * @since 1.0.0
         * @return void
         */
        public function __clone()
        {
            // Cloning instances of the class is forbidden.
            _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'helpie-faq' ), '1.0.0' );
        }
        
        /**
         * Disable unserializing of the class
         *
         * @access public
         * @since 1.0.0
         * @return void
         */
        public function __wakeup()
        {
            // Unserializing instances of the class is forbidden.
            _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'helpie-faq' ), '1.0.0' );
        }
        
        /**
         * @static
         * @since 1.0.0
         * @access public
         * @return Plugin
         * Note: Check how this works
         */
        public static function instance()
        {
            
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
                do_action( 'elementor/loaded' );
            }
            
            return self::$instance;
        }
        
        protected function setup_constants()
        {
            if ( !defined( 'HELPIE_FAQ_PATH' ) ) {
                define( 'HELPIE_FAQ_PATH', __DIR__ );
            }
        }
        
        protected function setup_autoload()
        {
            require HELPIE_FAQ_PATH . '/vendor/autoload.php';
            require_once HELPIE_FAQ_PATH . '/includes/autoloader.php';
            \HelpieFaq\Autoloader::run();
        }
        
        public function hfaq_activate()
        {
            /* Register Post Type and its taxonomy only for setup demo content on activation */
            $cpt = new \HelpieFaq\Includes\Cpt();
            $cpt->register_helpie_faq_cpt();
            /** At the fresh installation should add the below option to DB for use to call flush_rewrite_rules fn() */
            add_option( 'helpie_faq_slug_updated' );
            /** inserting default helpie faq posts and terms content, after activating the plugin */
            $defaults = new \HelpieFaq\Includes\Utils\Defaults();
            $defaults->load_default_contents();
        }
        
        public function apply_flush_rewrite_rules()
        {
            /** Check the below option is exists from db or not
             * > If true then call 'flush_rewrite_rules' fn() and delete that option from db
             * > If false then don't do anything
             */
            $slug_changed = get_option( 'helpie_faq_slug_updated' );
            
            if ( $slug_changed == 1 ) {
                flush_rewrite_rules();
                delete_option( 'helpie_faq_slug_updated' );
            }
        
        }
        
        public function enqueue_scripts()
        {
            wp_enqueue_script(
                $this->plugin_domain . '-bundle',
                HELPIE_FAQ_URL . 'assets/bundles/main.app.js',
                array( 'jquery' ),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                $this->plugin_domain . '-bundle-styles',
                HELPIE_FAQ_URL . 'assets/bundles/main.app.css',
                array(),
                $this->version,
                'all'
            );
            $nonce = wp_create_nonce( 'helpie_faq_nonce' );
            $options = get_option( 'helpie-faq' );
            $plan = 'free';
            $show_submission = ( isset( $options['show_submission'] ) && $options['show_submission'] == 1 ? true : false );
            $helpie_faq_object = array(
                'nonce'              => $nonce,
                'ajax_url'           => admin_url( 'admin-ajax.php' ),
                'plan'               => $plan,
                'url'                => HELPIE_FAQ_URL,
                'enabled_submission' => $show_submission,
            );
            wp_localize_script( $this->plugin_domain . '-bundle', 'helpie_faq_object', $helpie_faq_object );
            // You Can Access these object from javascript
            $faq_strings = new \HelpieFaq\Languages\FAQ_Strings();
            $loco_strings = $faq_strings->get_strings();
            wp_localize_script( $this->plugin_domain . '-bundle', 'faqStrings', $loco_strings );
        }
        
        public function rearranging_faq_submenus( $menu_ord )
        {
            global  $submenu ;
            $new_helpie_faq_submenus = array();
            // 1. Get Helpie FAQ Submenus from global $submenu
            $helpie_faq_submenus = ( isset( $submenu['edit.php?post_type=helpie_faq'] ) ? $submenu['edit.php?post_type=helpie_faq'] : [] );
            if ( !is_array( $helpie_faq_submenus ) || empty($helpie_faq_submenus) || count( $helpie_faq_submenus ) == 0 ) {
                return;
            }
            foreach ( $helpie_faq_submenus as $index => $helpie_faq_submenu ) {
                
                if ( $helpie_faq_submenu[0] == 'All FAQ Groups' || $helpie_faq_submenu[0] == 'Add New FAQ Group' ) {
                    // 2. first adding FAQ Groups ( summary + create ) submenus and remove that submenu in $submenu global array
                    $new_helpie_faq_submenus[] = $helpie_faq_submenu;
                    unset( $submenu['edit.php?post_type=helpie_faq'][$index] );
                }
            
            }
            // 3. merging new submenus to $submenu global array
            
            if ( count( $helpie_faq_submenus ) > 0 ) {
                $new_helpie_faq_submenus = array_merge( $new_helpie_faq_submenus, $submenu['edit.php?post_type=helpie_faq'] );
                $submenu['edit.php?post_type=helpie_faq'] = $new_helpie_faq_submenus;
            }
            
            
            if ( $this->add_start_trial_menu() ) {
                /** Get upgrade menu index */
                $upgrade_menu_index = $this->get_upgrade_menu_index();
                array_push( $submenu['edit.php?post_type=helpie_faq'], array(
                    '<span class="fs-submenu-item helpie_faq pricing trial-mode">Start Trial&nbsp;&nbsp;&#x27a4;</span>',
                    'manage_options',
                    'edit.php?post_type=helpie_faq&billing_cycle=annual&trial=true&page=helpie_faq-pricing',
                    'Helpie FAQ'
                ) );
                /** Hide the Upgrade menu if add the start-trial menu */
                $hide_upgrade_menu = '#menu-posts-helpie_faq .wp-submenu li:nth-child(' . $upgrade_menu_index . ') {display:none;}';
                wp_add_inline_style( 'common', $hide_upgrade_menu );
            }
            
            return $menu_ord;
        }
        
        public function get_upgrade_menu_index()
        {
            global  $submenu ;
            $helpie_faq_submenus = ( isset( $submenu['edit.php?post_type=helpie_faq'] ) ? $submenu['edit.php?post_type=helpie_faq'] : [] );
            $upgrade_menu_index = 0;
            foreach ( $helpie_faq_submenus as $index => $helpie_faq_submenu ) {
                
                if ( $helpie_faq_submenu[2] == 'helpie_faq-pricing' ) {
                    /** XPath elements start with 1, so should increment the menu value by 1 if the current upgrade menu index */
                    $upgrade_menu_index = intval( $index ) + 1;
                    break;
                }
            
            }
            /** Also increment by 1, WP  default adding the first element(li)  */
            $upgrade_menu_index = intval( $upgrade_menu_index ) + 1;
            return $upgrade_menu_index;
        }
        
        public function add_start_trial_menu()
        {
            /** No need to add start-trial submenu if users already on trial period or have an license */
            if ( hf_fs()->can_use_premium_code__premium_only() == true ) {
                return false;
            }
            $freemius_sdk = hf_fs();
            $plugin_id = $freemius_sdk->get_slug();
            $admin_notice_manager = FS_Admin_Notice_Manager::instance( $plugin_id );
            $trial_menu_exists = $admin_notice_manager->has_sticky( 'trial_promotion' );
            /** Also no need to add the start-trial if already start-trial menus are exists  */
            if ( $trial_menu_exists ) {
                return false;
            }
            return true;
        }
        
        public function enqueuing_the_font_awesome_style( $options )
        {
            $custom_style_enabled = ( isset( $options['enable_faq_styles'] ) && $options['enable_faq_styles'] == 1 ? true : false );
            if ( !$custom_style_enabled ) {
                return;
            }
            $use_custom_toggle_icons = $this->check_using_custom_toggle_icons( $options );
            $use_title_icon = ( isset( $options['title_icon'] ) && !empty($options['title_icon']) ? true : false );
            $use_category_title_icon = ( isset( $options['category_title_icon'] ) && !empty($options['category_title_icon']) ? true : false );
            $icon_used = ( $use_custom_toggle_icons || $use_title_icon || $use_category_title_icon ? true : false );
            if ( !$icon_used ) {
                return false;
            }
            wp_enqueue_style(
                $this->plugin_domain . '-font-awesome',
                HELPIE_FAQ_URL . 'assets/libs/font-awesome/css/all.min.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                $this->plugin_domain . '-v4-shims',
                HELPIE_FAQ_URL . 'assets/libs/font-awesome/css/v4-shims.min.css',
                array(),
                $this->version,
                'all'
            );
        }
        
        public function check_using_custom_toggle_icons( $options )
        {
            $toggle_icon_type = ( isset( $options['toggle_icon_type'] ) ? $options['toggle_icon_type'] : '' );
            if ( $toggle_icon_type != 'custom' ) {
                return false;
            }
            $toggle_open = ( isset( $options['toggle_open'] ) && !empty($options['toggle_open']) ? true : false );
            $toggle_off = ( isset( $options['toggle_off'] ) && !empty($options['toggle_off']) ? true : false );
            return ( $toggle_open && $toggle_off ? true : false );
        }
        
        public function enqueuing_the_chosen_style_and_script( $options )
        {
            $show_submission = ( isset( $options['show_submission'] ) && $options['show_submission'] == 1 ? true : false );
            $ask_question = ( isset( $options['ask_question'] ) ? $options['ask_question'] : [] );
            if ( !$show_submission || !in_array( 'categories', $ask_question ) ) {
                return;
            }
            wp_enqueue_style(
                $this->plugin_domain . '-chosen',
                HELPIE_FAQ_URL . 'assets/libs/chosen/chosen.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_script(
                $this->plugin_domain . '-chosen',
                HELPIE_FAQ_URL . 'assets/libs/chosen/chosen.jquery.js',
                array( 'jquery' ),
                $this->version,
                'all'
            );
        }
    
    }
}
new Helpie_FAQ();