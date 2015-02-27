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
<?php if (have_posts()) : $count = 0; ?>
    <?php while (have_posts()) : the_post(); $count++; ?>
                <?php the_content(); ?>
        <?php comments_template(); ?>
    <?php endwhile; ?>
<?php endif; ?>
            </div>
            <?php get_sidebar(); ?>
        </div>
    </div>
<?php get_footer(); ?>