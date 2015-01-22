<?php 
function social_ecommerce_sidebar(){
    register_sidebar(array("name" => "Social Commerce Sidebar", "id" => "social_sidebar" , "description" => "Add all your Social Commerce widgets to this sidebar",'before_widget' => '<div class="content">', 'after_widget' => '</div>')
   );
}
add_action( 'wp_loaded', 'social_ecommerce_sidebar' );
?>