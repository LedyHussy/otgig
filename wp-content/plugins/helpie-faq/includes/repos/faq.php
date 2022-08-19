<?php

namespace HelpieFaq\Includes\Repos;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Repos\Faq')) {
    class Faq
    {
        public function update_post($postId)
        {
            // 1. get current post by postId
            $post = get_post($postId);

            // 2. get current post terms
            $terms = get_the_terms($post->ID, 'helpie_faq_group');
            // 3. return, if the terms is empty
            if (isset($terms) && empty($terms)) {
                return;
            }

            $faq_group_repo = new \HelpieFaq\Includes\Repos\Faq_Group();
            foreach ($terms as $faq_group_term) {
                // 4.1 get current term faq group items
                $faq_group_items = $faq_group_repo->get_faq_group_items($faq_group_term->term_id);

                // 4.2 update the current post content
                $faq_group_items = $faq_group_repo->modify_faq_group_items('update', $post->ID, $faq_group_items);

                // 4.3 update the faq group items
                $faq_group_repo->update_faq_group_term_meta($faq_group_term->term_id, $faq_group_items);
            }
        }

        public function remove_post($postId)
        {
            // 1. get the current post
            $post = get_post($postId);

            // 2. get all current post terms
            $terms = get_the_terms($post->ID, 'helpie_faq_group');

            if (isset($terms) && empty($terms) || count($terms) == 0) {
                return;
            }

            $faq_group_repo = new \HelpieFaq\Includes\Repos\Faq_Group();
            foreach ($terms as $faq_group_term) {
                // 3.1 get current term faq group items
                $faq_group_items = $faq_group_repo->get_faq_group_items($faq_group_term->term_id);

                // 3.2 remove the current post in a faq group items
                $faq_group_items = $faq_group_repo->modify_faq_group_items('remove', $post->ID, $faq_group_items);

                // 3.3 update the faq group items
                $faq_group_repo->update_faq_group_term_meta($faq_group_term->term_id, $faq_group_items);
            }

        }

        public function get_post_content($post)
        {
            return array(
                'post_id' => $post->ID,
                'title' => $post->post_title,
                'content' => $post->post_content,
            );
        }

        public function updating_the_post_status($new_status, $old_status, $post)
        {
            if (isset($post) && $post->post_type != HELPIE_FAQ_POST_TYPE) {
                return;
            }
            $allowed_old_post_status = ['draft', 'trash'];
            if ($new_status == 'publish' && in_array($old_status, $allowed_old_post_status)) {

                /** get all terms for this post */
                $faq_group_terms = get_the_terms($post->ID, 'helpie_faq_group');

                if (empty($faq_group_terms)) {
                    return;
                }
                $faq_group_repo = new \HelpieFaq\Includes\Repos\Faq_Group();

                foreach ($faq_group_terms as $faq_group_term) {
                    // get current group item
                    $faq_group_items = $faq_group_repo->get_faq_group_items($faq_group_term->term_id);

                    // add new item
                    $faq_group_items = $faq_group_repo->modify_faq_group_items('add', $post->ID, $faq_group_items);

                    // update the group
                    $faq_group_repo->update_faq_group_term_meta($faq_group_term->term_id, $faq_group_items);
                }
            }
        }

        public function save_post($post_id, $post, $update)
        {
            if (isset($post) && HELPIE_FAQ_POST_TYPE != $post->post_type) {
                return;
            }

            $validation_map = array(
                'action' => 'String',
            );
            $sanitized_data = hfaq_get_sanitized_data("POST", $validation_map);
            $action = isset($sanitized_data['action']) ? $sanitized_data['action'] : '';

            /** Don't do anything, if the post edited by post page or in-line edit */
            if ($action == 'inline-save' || $action == 'helpie_faq_submission' || $update == 1) {
                return;
            }

            $terms = get_the_terms($post->ID, 'helpie_faq_category');
            if (empty($terms)) {
                $helpers = new \HelpieFaq\Includes\Utils\Helpers();
                $term_id = $helpers->get_default_category_term_id();
                $cat_ids = array_map('intval', (array) $term_id);
                /** Set the faq category term when create a new faq post */
                wp_set_object_terms($post->ID, $cat_ids, 'helpie_faq_category');
            }
        }
    }
}
