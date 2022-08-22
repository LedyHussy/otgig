<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <?php
    // Post thumbnail.
    // the_post_thumbnail();
  ?>



  <div class="entry-content">
    <?php
      /* translators: %s: Name of current post */
      the_content( sprintf(
        __( 'Continue reading %s', 'bootstrap-four' ),
        the_title( '<span class="screen-reader-text">', '</span>', false )
      ) );

      comments_template();

     // get_template_part( 'postmeta' );

      if ( comments_open() ) :
?>

<?php
      endif;

      wp_link_pages( array(
        'before'      => '<ul class="pagination">',
        'after'       => '</ul>',
        'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'bootstrap-four' ) . ' </span>%',
        'separator'   => '<span class="screen-reader-text">, </span>',
      ) );

    ?>
  </div><!-- .entry-content -->

  <?php
    // Author bio.
    // if ( is_single() && get_the_author_meta( 'description' ) ) :
    //   get_template_part( 'author-bio' );
    // endif;
  ?>

  <footer class="entry-footer">
    <?php edit_post_link( __( 'Edit', 'bootstrap-four' ), '<span class="edit-link">', '</span>' ); ?>
  </footer><!-- .entry-footer -->

</article><!-- #post-## -->
