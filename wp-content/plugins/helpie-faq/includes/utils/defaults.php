<?php

namespace HelpieFaq\Includes\Utils;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Utils\Defaults')) {

    class Defaults
    {

        public function load_default_contents()
        {

            $args = array('post_type' => 'helpie_faq', 'post_status' => array('publish', 'pending', 'trash'));
            $the_query = new \WP_Query($args);
            $faq_group_id = '';

            // Create Post only if it does not already exists
            if ($the_query->post_count < 1) {
                /* Setup Demo FAQ Question And Answer */
                $utils_helper = new \HelpieFaq\Includes\Utils\Helpers();
                $category_term_id = $utils_helper->create_uncategorized_faq_term();
                /* Insert FAQ Group Term with Post */
                $args = $utils_helper->insert_term_with_post("helpie_faq", "Getting Started", "helpie_faq_group", "Your First FAQ Question", "Your relevent FAQ answer.");

                $post_id = isset($args[0]) ? $args[0] : 0;
                $faq_group_id = isset($args[1]) ? $args[1] : '';

                if (!empty($post_id)) {

                    /** linking the category term with default faq post */
                    $faq_catetory_id = array_map('intval', (array) $category_term_id);
                    wp_set_object_terms($post_id, $faq_catetory_id, 'helpie_faq_category');

                    $post = get_post($post_id);

                    $props = array(
                        'group_id' => $faq_group_id,
                        'category_id' => $category_term_id,
                    );
                    /* Inserting FAQ Groups Term-Metadata */
                    $utils_helper->insert_faq_group_metadata($post, $props);
                }
            }
            $this->create_page_on_activate($faq_group_id);
            wp_reset_postdata();
        }

        public function create_page_on_activate($faq_group_id)
        {
            $create_page = new \HelpieFaq\Includes\Utils\Create_Pages();
            $content = "[helpie_faq]";
            if (!empty($faq_group_id)) {
                $content = "[helpie_notices group_id='" . $faq_group_id . "'/]";
                $content .= "<p></p>";
                $content .= "[helpie_faq group_id='" . $faq_group_id . "'/]";
            }
            $create_page::create('helpie_faq_page', 'helpie_faq_page_id', 'Helpie FAQ', $content);
        }
    }
}
