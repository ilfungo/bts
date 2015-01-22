<?php  global $woocommerce; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?> xmlns:og="http://ogp.me/ns/fb#" xmlns:og="http://opengraphprotocol.org/schema/" xmlns:addthis="http://www.addthis.com/help/api-spec">
<head profile="http://gmpg.org/xfn/11">
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<!--Set Viewport for Mobile Devices -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
<title>
	<?php
		global $page, $paged;
		wp_title( '|', true, 'right' );
		bloginfo( 'name' );
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) )
			echo " | $site_description";
		if ( $paged >= 2 || $page >= 2 )
			echo ' | ' . sprintf( __( 'Page %s', 'ocmx' ), max( $paged, $page ) );
	?>
</title>

<!-- Setup OpenGraph support-->
<?php if(get_option("ocmx_open_graph") !="yes") {
	$default_thumb = get_option('ocmx_site_thumbnail');
	$fb_image = get_fbimage();
	if(is_home()) :
?>

<meta property="og:title" content="<?php bloginfo('name'); ?>"/>
<meta property="og:description" content="<?php bloginfo('description'); ?>"/>
<meta property="og:url" content="<?php echo home_url(); ?>"/>
<meta property="og:image" content="<?php if(isset($default_thumb) && $default_thumb !==""){echo $default_thumb; } else {echo $fb_image;}?>"/>
<meta property="og:type" content="<?php echo "website";?>"/>
<meta property="og:site_name" content="<?php bloginfo('name'); ?>"/>

<?php else : ?>
<meta property="og:title" content="<?php the_title(); ?>"/>
<meta property="og:description" content="<?php echo strip_tags($post->post_excerpt); ?>"/>
<meta property="og:url" content="<?php the_permalink(); ?>"/>
<meta property="og:image" content="<?php if($fb_image ==""){echo $default_thumb;} else {echo $fb_image;} ?>"/>
<meta property="og:type" content="<?php echo "article"; ?>"/>
<meta property="og:site_name" content="<?php bloginfo('name'); ?>"/>

<?php endif;
}?>

<!-- Begin Styling -->
<link href="<?php bloginfo('stylesheet_url'); ?>" rel="stylesheet" type="text/css" />
<?php if(get_option("oboxfb_custom_css") != "") : ?>
	<link type="text/css" href="<?php echo home_url('/'); ?>?obox-fb=1&amp;stylesheet=custom" rel="stylesheet" type="text/css" />
<?php endif; ?>
<!--[if lte IE 8]>
	<script src="<?php bloginfo('template_directory'); ?>/scripts/DOMAssistantCompressed-2.7.4.js" type="text/javascript"></script>
	<script src="<?php bloginfo('template_directory'); ?>/scripts/ie-css3.js" type="text/javascript"></script>
<![endif]-->
<?php wp_head(); ?>

<script type="text/javascript">
	jQuery().ready(function() {
	// validate the comment form when it is submitted
	jQuery("#commentform").validate();
	});
</script>

<script type="text/javascript">
	window.fbAsyncInit = function() {FB.Canvas.setSize();}
</script>

</head>

<body <?php body_class(); ?>>

<div id="fb-root"></div>
<script src="https://connect.facebook.net/en_US/all.js"></script>

<script>
	FB.init({
	appId  : '<?php echo get_option("fb_appid"); ?>',
	status : true, //check login status
	cookie : true, //enable cookies to allow the server to access the session
	xfbml  : true  //parse XFBML
	 });
	FB.Canvas.setAutoGrow(7);
</script>


<div id="container2">
	<div id="container1">

		<div id="col1">

			<div class="logo">
				<h1>
					<a href="<?php echo site_url(); ?>">
						<?php if(get_option("oboxfb_custom_logo")) : ?>
							<img src="<?php echo get_option("oboxfb_custom_logo"); ?>" alt="<?php bloginfo('name'); ?>" />
						<?php else : ?>
							<?php bloginfo('name'); ?>
						<?php endif; ?>
					</a>
				</h1>
			</div>

			<div class="content">
				<form role="search" method="get" id="searchform" action="<?php echo home_url(); ?>">
					<div>
						<input type="text" value="<?php the_search_query(); ?>" name="s" id="s" placeholder="<?php _e('Search for products', 'ocmx'); ?>" />
						<input type="submit" id="searchsubmit" value="<?php _e('Search', 'ocmx'); ?>" />
						<input type="hidden" name="obox-fb" value="1" />
						<input type="hidden" name="post_type" value="product" />
					</div>
				</form>
			</div>

			<div class="content navigation">
				<h3 class="widgettitle"><?php _e('Pages', 'woocommerce'); ?></h3>
				<?php if (function_exists("wp_nav_menu")) :
					wp_nav_menu(array(
							'menu' => 'Social Commerce Nav',
							'menu_id' => 'nav',
							'menu_class' => 'clearfix',
							'sort_column' 	=> 'menu_order',
							'theme_location' => 'oboxfb',
							'container' => 'ul',
							'fallback_cb' => 'ocmx_fallback')
					);
				endif; ?>
			</div>

			<?php dynamic_sidebar('social_sidebar'); ?>
			<?php if(get_option("oboxfb_custom_footer") !="") :
				echo get_option('oboxfb_custom_footer');
			endif; ?>
		</div>