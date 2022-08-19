<?php

namespace HelpieFaq\Includes\Modules\Faq_Rest_Api;

//
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\HelpieFaq\Includes\Modules\Faq_Rest_Api\Faq_Rest_Api')) {

    class Faq_Rest_Api
    {

        public $error_messages = array(
            'UNAUTHORIZED' => "You don't have an permission to access this resource",
            'REQUIRED_TERM_ID' => "Required, FAQ G table ID ",
            'INVALID_POST' => "Invalid, Tablesome post",
            'REQUIRED_RECORD_IDS' => "Required, Tablesome table record IDs",
            'UNABLE_TO_CREATE' => "Unable to create a post.",
        );

        public $namespace = 'helpie-faq/v1';

        public function __construct()
        {
        }

        public function init()
        {
            foreach ($this->get_routes() as $route) {
                /** Register the REST route */
                register_rest_route($this->namespace, $route['url'], $route['args']);
            }
        }

        public function get_routes()
        {
            return array(
                array(
                    'url' => '/update-faq-group-settings',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array($this, 'modify_faq_group_settings'),
                        'permission_callback' => array($this, 'api_access_permission'),
                    ),
                ),
            );
        }

        public function api_access_permission()
        {
            if (current_user_can('edit_posts')) {
                return true;
            }
            $error_code = "UNAUTHORIZED";
            return new \WP_Error($error_code, $this->get_error_message($error_code));
        }

        public function get_error_message($error_code)
        {
            $message = isset($this->error_messages[$error_code]) ? $this->error_messages[$error_code] : 'Something Went Wrong, try later';
            return $message;
        }

        public function modify_faq_group_settings($request)
        {
            $params = $request->get_params();
            $group_id = isset($params['group_id']) ? $params['group_id'] : 0;
            $category_ids = isset($params['category_ids']) ? $params['category_ids'] : array();
            $product_ids = isset($params['product_ids']) ? $params['product_ids'] : array();

            if (empty($group_id)) {
                $error_code = "REQUIRED_TERM_ID";
                return new \WP_Error($error_code, 'Required, FAQ Group ID ', array('status' => 400));
            }

            $settings = array(
                'categories' => $category_ids,
                'products' => $product_ids,
            );

            \update_term_meta($group_id, 'faq_group_settings', $settings);

            $settings_data = get_term_meta($group_id, 'faq_group_settings', true);
            return rest_ensure_response(array(
                'status' => 'success',
                'message' => 'FAQ Group Settings Updated',
                'data' => $settings_data,
            ));
        }
    }
}