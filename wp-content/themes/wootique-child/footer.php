<div class="push"></div>
</div>
<?php $current_options=get_option('quality_options'); ?>
<div id="footer" class="qua_footer_area">
  <div class="container">
    <div class="col-md-12">
      <div id="footer-text"><p>
          <?php if($current_options['footer_customizations']!='') { echo $current_options['footer_customizations']; } ?></p>
      </div>
          <?php if($current_options['created_by_webriti_text']!=''){?>
        <div id="credits">
              <a target="_blank" rel="nofollow" href="<?php if($current_options['created_by_link']!='') { echo esc_url($current_options['created_by_link']); } ?>"><?php echo $current_options['created_by_webriti_text']; ?></a>
        </div>
          <?php } else { echo $current_options['created_by_webriti_text']; }?>
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
<?php
global $current_user;
get_currentuserinfo();
//print_r($current_user);

$ruolo=$current_user->roles;
$login=$current_user->user_login;
$role = array_shift($ruolo);
//echo "ruolo -> ".$ruolo[0];

/*scrivo un commenttino qui*/

/*if($ruolo[0]=="administrator"){
	echo $login;
}*/
if($role=="administrator" || $role=="vendor" ){
    echo "<div>";
    global $wp_query;
    echo $wp_query->request;
    echo "</div>";
    echo '<div id="bottom_debug">';
    $included_files = get_included_files();

    foreach ($included_files as $filename) {
        if (strpos($filename,'wootique') !== false OR strpos($filename,'woocommerce') !== false){
            echo "<br />"."$filename\n";
        }
    }
    echo '</div>';
}
?>
</body>
</html>