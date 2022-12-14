<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="content-type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
    <meta name="description" content="<?php echo esc_attr( get_bloginfo( 'description' ) ); ?>" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
    <?php wp_head(); ?>
</head>
<header>
    <div class="container d-flex justify-content-center">
        <a href="/">
            <img src="<?php echo get_template_directory_uri() ?>/img/logo.svg" class="logo">
        </a>
        <p class="text">Попробуй историю<br>на вкус!</p>
    </div>
</header>
<body <?php body_class(); ?>>
