<?php

namespace HelpieFaq\Features\Faq;

if (!class_exists('\HelpieFaq\Features\Faq')) {
    class Faq
    {
        public function __construct()
        {
            // Models
            $this->model = new \HelpieFaq\Features\Faq\Faq_Model();

            // Views
            $this->view = new \HelpieFaq\Features\Faq\Faq_View();

        }

        public function get_view($args)
        {
            global $Helpie_Faq_Collections;

            $html = '';

            $style = array();

            if (isset($args['style'])) {
                $style = $args['style'];
            }

            $viewProps = $this->model->get_viewProps($args);

            if (isset($viewProps['items']) && !empty($viewProps['items'])) {
                $html = $this->view->get($viewProps, $style);
            }

            /** use this below filter for generating faq-schema snippet */
            apply_filters('helpie_faq_schema_generator', $viewProps);

            $Helpie_Faq_Collections[] = $viewProps['collection'];

            return $html;
        }

    }
}