<?php
get_header(); ?>
<div class="page-seperator"></div>
<div class="container">
   <?php if ( is_singular( 'product' ) ) {

      while ( have_posts() ) : the_post();

         wc_get_template_part( 'content', 'single-product' );

      endwhile;

   } else {

       global $current_user;
       $ruolo=$current_user->roles;
       $role = array_shift($ruolo);

    //if($role=="administrator"){

       ?>
       <div class="container">
           <div class="row">
               <div class="qua_page_heading">
                   <h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
                   <div class="qua-separator"></div>
               </div>
           </div>
       </div>
      <?php if ( apply_filters( 'woocommerce_show_page_title', true ) && 1==2 ) : ?>

         <h1 class="page-title"><?php woocommerce_page_title(); ?></h1>

      <?php endif; ?>

      <?php do_action( 'woocommerce_archive_description' ); ?>

      <?php if ( have_posts() ) : ?>

         <?php do_action('woocommerce_before_shop_loop'); ?>

         <?php woocommerce_product_loop_start(); ?>

         <?php woocommerce_product_subcategories();?>
         <?php
           $annuario=false;
           $fclasse=false;
           $ffocus=false;
         ?>
         <h2>Annuario</h2>
         <?php while ( have_posts() ) : the_post(); ?>
         <?php $results = $wpdb->get_row($wpdb->prepare(
                   "SELECT post_title FROM $wpdb->posts WHERE post_parent =  %s",
                   $post->ID
               ));
               if($results->post_title=="annuario")
                wc_get_template_part( 'content', 'product' );
               if($results->post_title=="foto di classe")
                   $fclasse=true;
               if($results->post_title=="foto focus")
                   $ffocus=true;
               ?>
         <?php endwhile; // end of the loop.
         if($fclasse){?>
           <div style="clear:both"></div>
           <h2>Foto di classe</h2>
         <?php }
              while ( have_posts() ) : the_post(); ?>
               <?php $results = $wpdb->get_row($wpdb->prepare(
                   "SELECT post_title FROM $wpdb->posts WHERE post_parent =  %s",
                   $post->ID
               ));
               if($results->post_title=="foto di classe")
                   wc_get_template_part( 'content', 'product' ); ?>
         <?php endwhile; // end of the loop.
           if($ffocus){?>
         <div style="clear:both"></div>
         <h2>Foto focus</h2>
         <?php }
               while ( have_posts() ) : the_post(); ?>
               <?php $results = $wpdb->get_row($wpdb->prepare(
                   "SELECT post_title FROM $wpdb->posts WHERE post_parent =  %s",
                   $post->ID
               ));
               if($results->post_title=="foto focus")
                   wc_get_template_part( 'content', 'product' ); ?>
         <?php endwhile; // end of the loop. ?>
         <?php woocommerce_product_loop_end(); ?>

         <?php do_action('woocommerce_after_shop_loop'); ?>

      <?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

         <?php wc_get_template( 'loop/no-products-found.php' ); ?>

      <?php endif;

   //}
   }
    ?>
   <?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>