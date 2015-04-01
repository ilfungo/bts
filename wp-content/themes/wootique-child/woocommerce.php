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
       $product_cat = wp_get_object_terms($post->ID, 'product_cat');
       $rekeyed_array = array_values($product_cat);
       $product_cat = $rekeyed_array[0];

      //prendo lo slug della product cat e con la funzione get_term link tiro fuori l'url della product_cat
      global $wp_query;
      $cat = $wp_query->get_queried_object();
      $urlClass = get_term_link($product_cat, 'product_cat' );


       $_SESSION['class_slug']=$product_cat->slug;
       $_SESSION['class_name']=$product_cat->name;
       $_SESSION['class_url']=$urlClass;
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
           $i=0;
         ?>
         <?php while ( have_posts() ) : the_post(); ?>
         <?php $results = $wpdb->get_row($wpdb->prepare(
                   "SELECT post_title FROM $wpdb->posts WHERE post_parent =  %s",
                   $post->ID
               ));
               if($results->post_title=="annuario"){
                   if($i==0){echo "<h2>Annuario</h2>";}
                   wc_get_template_part( 'content', 'product' );
                   //$_SESSION[pic_type]="annuario";
                   $i++;
               }
               if($results->post_title=="foto di classe"){
                   $fclasse=true;
                    //$_SESSION[pic_type]="classe";
               }
               if($results->post_title=="foto focus"){
                   $ffocus=true;
                   //$_SESSION[pic_type]="focus";
               }
               endwhile; // end of the loop.
               ?>

         <?php if($fclasse){?>
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
         <h2>Foto ritratto</h2>
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

         <?php do_action('woocommerce_after_shop_loop');
           unset($_SESSION[pic_type]);
           ?>

      <?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

         <?php wc_get_template( 'loop/no-products-found.php' ); ?>

      <?php endif;

   //}
   }
    ?>
   <?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>