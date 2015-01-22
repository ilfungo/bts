if(window.navigator.standalone) {
	jQuery(document).ready(function(){		
		jQuery("body").css("margin-top", "21px");
		jQuery("a").live("click", function(){
										   
			
			var href = jQuery(this).attr("href")
			var target = jQuery(this).attr("target");
			
			if(target.indexOf("blank") !== -1)
				{
					new_window = confirm("This link will open up in Safari, would you like to continue?");
					if(new_window){
						return true;
					}
				}
			else if(href !== "#")
				{
					jQuery("body").animate({opacity: 0.5});
					jQuery.get(href, {webapp: 1},
					function(data) {
						//Fade in the new content
						jQuery("body").html(data).animate({opacity: 1});
						
						// Set post images
						jQuery(".thumbnail > img, .post-image img").css("maxWidth", (body_width-20)+"px");
				
						// Set the Slider Attributes
						jQuery(".slider, .slider ul li").css("width", (body_width+"px"));	
						jQuery(".slider").css("height", (jQuery(".slider img").height()+20)+"px");
						
					});		
					return false;
				}
		});		
	});
}