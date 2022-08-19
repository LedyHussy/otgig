<?php

// Create a helper function for easy SDK access.

if ( !function_exists( 'hf_fs' ) ) {
    // Create a helper function for easy SDK access.
    function hf_fs()
    {
        global  $hf_fs ;
        if ( !isset( $hf_fs ) ) {
            // Include Freemius SDK.
            // $freemius_wordpress_sdk = HELPIE_FAQ_PATH . "vendor/freemius/wordpress-sdk/start.php";
            // if (!file_exists($freemius_wordpress_sdk)) {
            // wp_die("composer package \"freemius/wordpress-sdk\" was not installed, Do run \"composer update.\"");
            // }
            // require_once $freemius_wordpress_sdk;
            $hf_fs = fs_dynamic_init( array(
                'id'              => '2442',
                'slug'            => 'helpie_faq',
                'type'            => 'plugin',
                'public_key'      => 'pk_65a62b52f2165799f7e9a3e1c9cd9',
                'is_premium'      => false,
                'premium_suffix'  => '',
                'has_addons'      => false,
                'has_paid_plans'  => true,
                'trial'           => array(
                'days'               => 7,
                'is_require_payment' => true,
            ),
                'has_affiliation' => 'selected',
                'menu'            => array(
                'slug'       => 'edit.php?post_type=helpie_faq',
                'first-path' => 'edit-tags.php?taxonomy=helpie_faq_group&post_type=helpie_faq',
            ),
                'is_live'         => true,
            ) );
        }
        return $hf_fs;
    }
    
    // Init Freemius.
    hf_fs();
    // Signal that SDK was initiated.
    do_action( 'hf_fs_loaded' );
    hf_fs()->add_filter( 'support_forum_url', 'hf_fs_support_forum_url' );
    hf_fs()->add_filter( 'show_first_trial_after_n_sec', 'start_trial' );
    function hf_fs_support_forum_url( $wp_support_url )
    {
        return 'https://wordpress.org/support/plugin/helpie-faq';
    }
    
    function start_trial( $day_in_sec )
    {
        return 1;
    }

}
