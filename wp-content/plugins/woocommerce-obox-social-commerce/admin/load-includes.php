<?php 
//Include all the OCMX files
foreach($include_folders as $inc_folder) :
	$include_folders = new oboxfb_include_folder();
	$folder = $inc_folder;
	$include_folders->trawl_folder($folder);
endforeach;
?>