<?php
	$current_options = get_option('quality_options');
  	if (  $current_options['front_page'] != 'on' ) {
  		get_template_part('index');
  		}
	else {
		
		get_header();
		get_template_part('index', 'static');			
		
		//****** get index service  *********/
		if (  $current_options['service_enable'] == 'on' ) {
		get_template_part('index', 'service');
		}
		//****** get index Projects  *********/
		if (  $current_options['home_projects_enabled'] == 'on' ) {
		get_template_part('index', 'projects');
		}
		//****** get index Blog  *********/
		if (  $current_options['home_blog_enabled'] == 'on' ) {
		get_template_part('index', 'blog');
		}
		get_footer(); 
	}
?>