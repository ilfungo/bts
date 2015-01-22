<?php function oboxfb_pagination($container_class = "clearfix", $ul_class = "clearfix")
	{
		global $wp_query, $paged;
		$request = $wp_query->request;
		$showpages = 12;
		$numposts = $wp_query->found_posts;
		$startrow = 1;
	
		$pagenum = (ceil($numposts/get_option("posts_per_page")));
		
		if ( get_query_var('paged') ) :
		    $currentpage = get_query_var('paged');
		elseif ( get_query_var('page') ) :
		    $currentpage = get_query_var('page');
		endif;
		$paged = $currentpage;
		
		if($pagenum < $showpages) :
			$maxpages = $pagenum;
		elseif($currentpage > $showpages) :
			$startrow = ($currentpage-1);
			$maxpages = ($startrow + $showpages - 1);
			if($maxpages > $pagenum) :
				$startrow = ($startrow - ($maxpages - $pagenum));
				$maxpages = ($maxpages - ($maxpages - $pagenum));
			endif;
		else :
			$startrow = 1;
			$maxpages  = $showpages;
		endif;
		if($currentpage == 0) 
			$currentpage = 1;
		if((get_option("posts_per_page") && $numposts !== 0) && $numposts > get_option("posts_per_page")) :
	?>
    <ul class="pagination">
        <?php if($currentpage !== ceil($numposts/get_option("posts_per_page"))) : ?>
            <li class="next"><?php next_posts_link("Next","ocmx"); ?></li>
        <?php endif;
        if($currentpage != "1") : ?>
            <li class="previous"><?php previous_posts_link("Prev","ocmx"); ?></li>
        <?php endif; ?>
        <li class="page-count">Page <?php echo $currentpage?> of <?php echo $pagenum?></li>
    </ul>
<?php
		endif;
	} 
?>