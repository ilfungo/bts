<?php 
  get_header(); ?>
<div class="page-seperator"></div>

<div class="container">
  <div class="row qua_blog_wrapper">
      <?php the_post(); ?>
	  <div class="qua_blog_section" >
          <?php the_content(); ?>
      </div>

    <?php get_sidebar(); ?>		
  </div>
</div>
<?php get_footer(); ?>