<?php

namespace HelpieFaq\Features\Faq_Group;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Features\Faq_Group\Style_Controller')) {
    class Style_Controller
    {
        private $settings_data;

        public $css_output_selectors = array(
            'header' => array(
                'background' => array('.helpie-faq.accordions.groupSettings-{group_id}__enabled .accordion .accordion__item .accordion__header'),
                'color' => array('.helpie-faq.accordions.groupSettings-{group_id}__enabled .accordion .accordion__item .accordion__header .accordion__title'),
            ),
            'body' => array(
                'background' => array('.helpie-faq.accordions.groupSettings-{group_id}__enabled .accordion .accordion__item .accordion__body'),
                'color' => array(
                    '.helpie-faq.accordions.groupSettings-{group_id}__enabled .accordion .accordion__item .accordion__body',
                    '.helpie-faq.accordions.groupSettings-{group_id}__enabled .accordion .accordion__item .accordion__body p',
                    '.helpie-faq.accordions.groupSettings-{group_id}__enabled .accordion .accordion__item .accordion__body h1',
                    '.helpie-faq.accordions.groupSettings-{group_id}__enabled .accordion .accordion__item .accordion__body h2',
                    '.helpie-faq.accordions.groupSettings-{group_id}__enabled .accordion .accordion__item .accordion__body h3',
                    '.helpie-faq.accordions.groupSettings-{group_id}__enabled .accordion .accordion__item .accordion__body h4',
                    '.helpie-faq.accordions.groupSettings-{group_id}__enabled .accordion .accordion__item .accordion__body h5',
                    '.helpie-faq.accordions.groupSettings-{group_id}__enabled .accordion .accordion__item .accordion__body h6',
                ),
            ),
        );

        public function __construct($args)
        {
            $this->args = $args;
            $this->settings_data = get_term_meta($args['group_id'], 'faq_group_settings', true);
        }

        public function get_styles()
        {
            $style = '';

            foreach ($this->css_output_selectors as $prop_name => $prop_data) {
                $selected_props = isset($this->settings_data[$prop_name]) ? $this->settings_data[$prop_name] : [];

                if (empty($selected_props)) {
                    continue;
                }

                foreach ($prop_data as $style_prop => $output_elements) {
                    $style_prop_value = isset($selected_props[$style_prop]) ? $selected_props[$style_prop] : '';
                    if (empty($output_elements) || empty($style_prop_value)) {
                        continue;
                    }
                    $selectors = implode(', ', $output_elements);
                    $selectors = str_replace("{group_id}", $this->args['group_id'], $selectors);

                    /**
                     * TODO: Remove the "!important" attribute from the style. Actually, it not needed at all. But, In previous versions, we were add some styles in In-line.
                     * That's why I add this now. Later will remove this attribute..
                     */
                    $style .= $selectors . '{' . $style_prop . ': ' . $style_prop_value . ' !important;}';
                }
            }

            return '<style type="text/css">' . wp_strip_all_tags($style) . '</style>';
        }

    }
}