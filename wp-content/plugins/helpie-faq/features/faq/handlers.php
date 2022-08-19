<?php

namespace HelpieFaq\Features\Faq;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Features\Faq\Handlers')) {
    class Handlers
    {

        /**
         * use of the method for removing the category term if the category term doesn't have posts
         *
         * @param [array] $props
         * @return $props
         */
        public function get_non_empty_items_props($props)
        {
            foreach ($props as $key => $prop) {
                if (empty($prop['children'])) {
                    unset($props[$key]);
                }
            }
            $props = array_values($props); // 'reindex' array (edited)

            return $props;
        }

        public function get_context()
        {
            $context = array();
            // Woo Product
            if (is_singular('product') && is_single(get_the_ID())) {
                $context['woo-product'] = get_queried_object()->ID;
            } elseif (is_tax('helpdesk_category')) {
                $context['kb-category'] = get_queried_object()->term_id;
            }
            // Wiki KB Category

            return $context;
        }

        /***
         * @since v1.6.5
         * method name changed, faq/faq_model/map_domain_props_category() to faq/handlers/map_category_props_to_view_item_props()
         */
        public function map_category_props_to_view_item_props($category)
        {
            $props = array(
                'title' => $category->name,
                'slug' => $category->slug,
                'content' => $category->description,
                'term_id' => $category->term_id,
                'count' => get_term_meta($category->term_id, 'click_counter', false),
            );
            return $props;
        }

        /***
         * @since v1.6.5
         * method name changed, faq/faq_model/map_domain_props_to_view_item_props() to faq/handlers/map_post_props_to_view_item_props()
         */
        public function map_post_props_to_view_item_props($post, $args)
        {
            $props = array(
                'slug' => $post->post_name,
                'title' => $post->post_title,
                'content' => $post->post_content,
                'post_id' => $post->ID,
                'count' => get_post_meta($post->ID, 'click_counter', false),
                'excerpt' => $post->post_excerpt,
            );
            $search_tags_enabled = (isset($args['search_by_tags']) && $args['search_by_tags'] == 1) ? true : false;
            if (hf_fs()->can_use_premium_code__premium_only() && $search_tags_enabled) {
                $tags = new \HelpieFaq\Features\Faq\Tags();
                $props = $tags->get_tags($props);
            }

            return $props;
        }

        public function get_total_no_of_pages($args)
        {
            /***
             *  dont need to calculate the total no of pages count when the limit value is -1
             *
             */
            if ($args['limit'] < 1) {
                return 0;
            }
            /** Get the limit from the settings (what we configured in settings) */
            $page_limit = $args['limit'];
            /** overrite the limit option value as -1 for get all the FAQ posts  */
            $args['limit'] = -1;
            $faq_repo = new \HelpieFaq\Includes\Repos\Faq_Repo();
            $posts = $faq_repo->get_faqs($args);
            $posts_count = count($posts);
            /** Calculating the total no of pages count */
            $total_no_pages = ceil($posts_count / $page_limit);
            return ($total_no_pages > 1) ? (($total_no_pages) - 1) : 0;
        }

        public function boolean_conversion($args)
        {
            foreach ($args as $key => $arg) {
                if ($arg == 'on') {
                    $args[$key] = true;
                } else if ($arg == 'off') {
                    $args[$key] = false;
                }
            }
            return $args;
        }
    }
}