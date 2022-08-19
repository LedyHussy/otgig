<?php

namespace HelpieFaq\Features\Insights;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Features\Insights\Insights_Page')) {
    class Insights_Page extends \HelpieFaq\Features\Insights\Admin_Page
    {

        private $options;
        private $page_name = 'insights_model_page';
        private $opts_grp = 'pauple_insights_model';

        public function __construct()
        {
            parent::__construct();
            $this->view = new \HelpieFaq\Features\Insights\View();

            add_action('admin_menu', array($this, 'add_insight_page'));
            add_action('admin_init', array($this, 'page_init'));
            add_action('helpie_faq_admin_localize_script', array($this, 'get_insights_localize_data'));
        }

        public function get_insights_localize_data()
        {
            $validation_map = array(
                'post_type' => 'String',
                'page' => 'String',
            );
            $sanitized_data = hfaq_get_sanitized_data("GET", $validation_map);

            /***
             * Load the insights localized data to the FAQ-Insights page only. otherwise don't need.
             */
            $post_type = isset($sanitized_data['post_type']) ? $sanitized_data['post_type'] : '';
            $page = isset($sanitized_data['page']) ? $sanitized_data['page'] : '';
            $is_insights_page = ($post_type == HELPIE_FAQ_POST_TYPE && $page == 'helpie-faq-insights');

            /** Return, If the current page is not a helpie-faq insights page */
            if (!$is_insights_page) {return;}

            $this->insights_controller = new \HelpieFaq\Features\Insights\Controller;
            $insights = $this->insights_controller->get_insights();

            $insights_for_js = array(
                'click' => array(
                    'last_30days' => $insights['click']['last_30days'],
                    'last_year' => $insights['click']['last_year'],
                ),
                'search' => array(
                    'last_30days' => $insights['search']['last_30days'],
                    'last_year' => $insights['search']['last_year'],
                ),
            );
            // error_log('$insights_for_js : ' . print_r($insights_for_js, true));

            wp_localize_script(HELPIE_FAQ_DOMAIN . '-bundle-admin-scripts', 'HelpieFaqInsights', $insights_for_js);

        }

        public function add_insight_page()
        {
            $insights = __('Insights', 'pauple-helpie');
            // This page will be under "Settings"
            add_submenu_page('edit.php?post_type=helpie_faq', $insights, $insights,
                'manage_options', 'helpie-faq-insights', array($this, 'show_insight_page')
            );
        }

        /**
         * Options page callback.
         */
        public function show_insight_page()
        {
            $insights = $this->insights_controller->get_insights();
            $content = $this->view->get_view($insights);
            hfaq_safe_echo($content);
        }

        /**
         * Register and add settings.
         */
        public function page_init()
        {
            add_settings_section(
                'helpie_core_settings', // ID
                __('Helpie Insights', 'pauple-helpie'), // Title
                array($this, 'print_core_settings'), // Callback
                $this->page_name// Page
            );
        }

        public function print_core_settings()
        {
            echo "<span class='sub-title1'>" . __('Insights to a better Knowledge base.', 'pauple-helpie') . "</span>";
        }

    } // END CLASS
}