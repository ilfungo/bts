<?php
get_header(); ?>
    <div class="page-seperator"></div>
    <div class="container">
        <div class="row">
            <div class="qua_page_heading">
                <h1><?php the_title(); ?></h1>
                <div class="qua-separator"></div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row qua_blog_wrapper">
            <div class="<?php if( is_active_sidebar('sidebar-primary')) { echo "col-md-8"; } else { echo "col-md-12"; } ?>">
                <?php
                //da qui

                $taxonomy     = 'product_cat';
                $orderby      = 'name';
                $show_count   = 0;      // 1 for yes, 0 for no
                $pad_counts   = 0;      // 1 for yes, 0 for no
                $hierarchical = 1;      // 1 for yes, 0 for no
                $title        = '';
                $empty        = 0;
                $args = array(
                    'taxonomy'     => $taxonomy,
                    'orderby'      => $orderby,
                    'show_count'   => $show_count,
                    'pad_counts'   => $pad_counts,
                    'hierarchical' => $hierarchical,
                    'title_li'     => $title,
                    'hide_empty'   => $empty
                );
                ?>
                <?php $all_categories = get_categories( $args );
                //print_r($all_categories);
                echo "<ul>";
                foreach ($all_categories as $cat) {
                    //print_r($cat);
                    if($cat->category_parent == 0) {
                        $category_id = $cat->term_id;

                        ?>

                        <?php

                        echo '<li><a href="'. get_term_link($cat->slug, 'product_cat') .'">'. $cat->name .'</a></li>'; ?>


                        <?php
                        $args2 = array(
                            'taxonomy'     => $taxonomy,
                            'child_of'     => 0,
                            'parent'       => $category_id,
                            'orderby'      => $orderby,
                            'show_count'   => $show_count,
                            'pad_counts'   => $pad_counts,
                            'hierarchical' => $hierarchical,
                            'title_li'     => $title,
                            'hide_empty'   => $empty
                        );
                        $sub_cats = get_categories( $args2 );
                        if($sub_cats) {
                            echo "<ul>";
                            foreach($sub_cats as $sub_category) {
                                echo '<li><a href="'. get_term_link($sub_category->slug, 'product_cat') .'">'. $sub_category->name ."</li>";
                            }
                            echo "</ul>";

                        } ?>



                    <?php }
                }
                echo "</ul>";
                //fino a qui
                ?>
                <?php the_post(); ?>

            </div>
            <?php get_sidebar(); ?>
        </div>
    </div>
<?php get_footer(); ?>