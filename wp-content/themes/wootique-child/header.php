<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    ``
    <![endif]-->
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php wp_title( '|', true, 'right' ); ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <!-- Theme Css -->
    <?php $current_options=get_option('quality_options'); ?>
    <?php if($current_options['upload_image_favicon']!=''){ ?>
    <link rel="shortcut icon" href="<?php  echo esc_url($current_options['upload_image_favicon']); ?>" />
    <?php } ?>
    <?php wp_head(); ?>
      <link href="<?php echo get_stylesheet_uri(); ?>" rel="stylesheet" />
      <link href="<?php echo dirname( get_bloginfo('stylesheet_url')) . '/fonts/stylesheet.css'; ?>" rel="stylesheet" />
      <link href="<?php echo dirname( get_bloginfo('stylesheet_url')) . '/css/media-responsive.css'; ?>" rel="stylesheet" />
  </head>
  <body <?php body_class(); ?>>
    <!--Header Logo & Menus-->
    <div id="container">
    <div class="container">
      <nav class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            </button>
            <div class="logo"><!-- pull-left -->
                  <?php
                  if($current_options['text_title'] =="on")
                  { ?>
                      <div class="qua_title_head">
                          <h1 class="qua-logo" ><a href="<?php echo home_url( '/' ); ?>"><?php echo get_bloginfo( ); ?></a></h1>
                      </div>
                  <?php
                  } else if($current_options['upload_image_logo']!='')
                  { ?>
                      <a href="<?php echo home_url( '/' ); ?>"><img src="<?php echo esc_url($current_options['upload_image_logo']); ?>" style="height:<?php if($current_options['height']!='') { echo $current_options['height']; }  else { "80"; } ?>px; width:<?php if($current_options['width']!='') { echo $current_options['width']; }  else { "200"; } ?>px;" /></a>
                  <?php } else { ?>
                      <a href="<?php echo home_url( '/' ); ?>"><img src="<?php echo QUALITY_TEMPLATE_DIR_URI; ?>/images/logo.png"></a>
                  <?php } ?>
              </div>
          </div>
            <div class="login pull-right">
            <?php
            global $current_user;
            if(is_user_logged_in()){

                //var_dump($current_user);
                $billing_last_name = get_user_meta($current_user->ID, "billing_last_name",true);
                $billing_first_name = get_user_meta($current_user->ID, "billing_first_name",true);
                $nickname = get_user_meta($current_user->ID, "nickname",true);
                //echo '<div id="myName"><div  id="myNameAligner"><a href="/?page_id=9">'.$nickname.'<br><small>'.$billing_first_name." ".$billing_last_name."</small></a></div></div>";
                echo '<div id="myName"><div  id="myNameAligner"><a href="/?page_id=9">'.$nickname.'</a></div></div>';
                ?>
                <?php
                //se l'utente è loggato mostro il pulsante di logout se no quello di login :)
                if ( is_user_logged_in() ) { ?>
                    <div id="logoutDiv"><a href="<?php echo wp_logout_url();?>" class="round-button export" title="Logout">Logout</a></div>
                <?php }?>
            <?php }else{?>
                <div id="logoutDiv"><a href="/?page_id=9" class="round-button export" title="Loging">Login</a></div>
            <?php } ?>
            </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          
            <?php /*	wp_nav_menu( array(  
              		'theme_location' => 'primary',
              		'container'  => 'nav-collapse collapse navbar-inverse-collapse',
              		'menu_class' => 'nav navbar-nav navbar-right',
              		'fallback_cb' => 'quality_fallback_page_menu',
              		'walker' => new quality_nav_walker()
              		)
              	); */	
               wp_nav_menu( array(
                'menu'              => 'primary',
                'theme_location'    => 'primary',
                'depth'             => 2,
                'container'         => 'div',
                'container_class'   => 'collapse navbar-collapse',
				'container_id'      => 'bs-example-navbar-collapse-1',
                'menu_class'        => 'nav navbar-nav',
                'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
                'walker'            => new wp_bootstrap_navwalker())
            );
			  
			  ?>
         
          <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
      </nav>
    </div>