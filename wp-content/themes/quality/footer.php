<?php $current_options=get_option('quality_options'); ?>
<div class="qua_footer_area">
  <div class="container">
    <div class="col-md-12">
      <p><?php if($current_options['footer_customizations']!='') { echo $current_options['footer_customizations']; } ?>
	  <?php if(is_home() && $current_options['created_by_webriti_text']!=''){?>
        <a target="_blank" rel="nofollow" href="<?php if($current_options['created_by_link']!='') { echo esc_url($current_options['created_by_link']); } ?>"><?php echo $current_options['created_by_webriti_text']; ?></a>
		<?php } else { echo $current_options['created_by_webriti_text']; }?>
      </p>
    </div>
  </div>
</div>
<?php
  if($current_options['quality_custom_css']!='') {  ?>
<style>
  <?php echo htmlspecialchars_decode($current_options['quality_custom_css']); ?>
</style>
<?php } ?>	
<!-- /Footer Widget Secton -->
<?php wp_footer(); ?>
</body>
</html>