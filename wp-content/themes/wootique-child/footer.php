<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
?>

</div><!-- #main -->

<footer id="colophon" class="site-footer" role="contentinfo">

    <?php get_sidebar( 'footer' ); ?>

    <div class="site-info">
        <?php do_action( 'twentyfourteen_credits' ); ?>
        <a href="<?php echo esc_url( __( 'http://wordpress.org/', 'twentyfourteen' ) ); ?>"><?php printf( __( 'Proudly powered by %s', 'twentyfourteen' ), 'WordPress' ); ?></a>
    </div><!-- .site-info -->
</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>
<?php
global $current_user;
get_currentuserinfo();
//print_r($current_user);

$ruolo=$current_user->roles;
$login=$current_user->user_login;
//echo "ruolo -> ".$ruolo[0];


/*if($ruolo[0]=="administrator"){
	echo $login;
}*/
if($ruolo[0]=="administrator"){
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



