post_page = oboxfb.ajaxurl;
function check_nan(element, element_value, max_value)
	{
		var len = element_value.length;
		if(isNaN(element_value))
			{
				alert("Only number vlues are allow in this input.");
				element.value = element_value.substring(0, (len/1)-1);
			}
			
		if(max_value && ((element_value/1) > (max_value/1)))
			{
				alert("The maximum value allowed for this input is "+max_value);
				element.value = max_value;
			}
	}
function check_linked(this_id, link_id)
	{
		this_id = "#"+this_id;
		link_div_id = "#"+link_id+"_div";
		link_id = "#"+link_id;
		
		if(jQuery(this_id).attr("value") !== "0")
			{
				jQuery(link_div_id).slideUp();
				jQuery(link_id).attr("disabled", "true");
			}
		else
			{
				jQuery(link_div_id).slideDown();
				jQuery(link_id).removeAttr("disabled");
			}
		
	}
	

jQuery(document).ready(function()
	{
		jQuery("#fb_appid").live("keyup", function(){
			jQuery("#fb_appid_link").attr("href", "https://www.facebook.com/dialog/pagetab?app_id="+jQuery(this).val()+"&next="+oboxfb.url);
		});
		jQuery(".contained-forms input, .contained-forms select").live("change", function(){
			relid = jQuery(this).attr("name");
			element = jQuery(this);

			jQuery("[rel^='"+relid+"']").each(function(){
				
				if(element.val() == "off" || element.val() == "no")
					{jQuery(this).slideUp();}
				else if(element.attr("type") == "checkbox" && element.attr("checked") == "checked")
					{jQuery(this).slideDown();}
				else if(element.attr("type") == "checkbox")
					{jQuery(this).slideUp();}
				else
					{jQuery(this).slideDown();}
			});
		});
		
		jQuery("#oboxfb-options").submit(function(){
			formvalues = jQuery("#oboxfb-options").serialize();
			jQuery("#content-block").animate({opacity: 0.50}, 500)
			
			if(document.getElementById("ocmx-note"))
				{jQuery("#ocmx-note").html("<p>Saving...</p>");}
			else
				{jQuery("<div id=\"ocmx-note\" class=\"updated below-h2\"><p>Saving...</p></div>").insertBefore("#header-block");}
						
			jQuery.post(post_page,
				{action : 'oboxfb_save-options', data: formvalues}, 
				function(data)
						{
							setTimeout(function(){
								jQuery("#content-block").animate({opacity: 1}, 500)
								jQuery("#ocmx-note").html("<p>Your changes were successful.</p>");
							}, 500);
						}
					);
			return false;
		});
		
		jQuery("[id^='ocmx-reset']").click(function(){
			sure_reset = confirm("Are you sure you want reset these options to default?");
			if(sure_reset)
				{
					formvalues = jQuery("#ocmx-options").serialize();
					jQuery("#content-block").animate({opacity: 0.50}, 500)
					
					if(document.getElementById("ocmx-note"))
						{jQuery("#ocmx-note").html("<p>Saving...</p>");}
					else
						{jQuery("<div id=\"ocmx-note\" class=\"updated below-h2\"><p>Saving...</p></div>").insertBefore("#header-block");}
					jQuery.post(post_page,
						{action : 'ocmx_reset-options', data: formvalues}, 
						function(data)
								{
									setTimeout(function(){
										jQuery("#ocmx-note").html("<p>Refreshing Page...</p>");
										jQuery("#content-block").animate({opacity: 1}, 500)
										window.location = jQuery("#ocmx-options").attr("action").replace("&changes_done=1", "")+"&options_reset=1";
									}, 500);
								}
							);
				}
			else
				return false;
		});
		
		
		jQuery("#tabs a").click(function()
			{
				oldtabid = jQuery(".selected").children("a").attr("rel");
				tabid = jQuery(this).attr("rel");
				//$new_class = jQuery($oldtabid).attr("class");
				if(!(jQuery(this).parent().hasClass("selected")))
					{
						jQuery(".selected").removeClass("selected");
						jQuery(this).parent().addClass("selected");
						jQuery(oldtabid).slideUp();
						jQuery(tabid).slideDown();
						
						formaction = jQuery("form").attr("action");
						findtab = formaction.indexOf("tab=");
						action_len = formaction.length;
						tabno = jQuery(this).attr("rel").replace("#tab-", "");
						if(findtab == -1)
							{
								jQuery("form").attr("action", formaction+"&current_tab="+tabno);
							}
						else
							{
								formaction = formaction.substr(0,(findtab+4));
								jQuery("form").attr("action", formaction+tabno);
							}
						jQuery(oldtabid+"-href").fadeOut();
						jQuery(tabid+"-href").fadeIn();
						jQuery(oldtabid+"-href-1").fadeOut();
						jQuery(tabid+"-href-1").fadeIn();
					}
				return false;
			});
		jQuery("a[id^='ocmx_layout_']").click(function(){
			jQuery(".selected").removeClass("selected");
			jQuery(this).parent().addClass("selected");
			
			latout_id = jQuery(this).attr("id");
			layout = jQuery(this).attr("id").replace("ocmx_layout_", "");
			layout_option = layout+"_home_options";
			
			jQuery("#ocmx_home_page_layout").attr("value", layout);
			
			loading = "<li><div class=\"form-wrap\"><a href=\"#\"><img src=\"images/loading.gif\" alt=\"\" /></a></div></li>";
			
			jQuery("#layout_options").html(loading);
			
			i = 1;
			jQuery(".layout-selector").children("li").each(function(){				
				li_id = jQuery(this).children("a").attr("id");
				if(li_id == latout_id && i == 3)
					{
						jQuery("#layout_options").removeClass("clear-left-corner").addClass("clear-right-corner");
					}
				else if(li_id == latout_id && i == 1)
					{
						jQuery("#layout_options").removeClass("clear-right-corner").addClass("clear-left-corner");
					}
				else if(li_id == latout_id)
					{
						jQuery("#layout_options").removeClass("clear-right-corner").removeClass("clear-left-corner");
					}
				i++;
			});
			
			jQuery.get(post_page,
				{action : 'ocmx_layout-refresh', layout_option: layout_option, layout: layout}, 
				function(data)
						{jQuery("#layout_options").html(data).fadeIn()}
					);
			return false;
		});
				//AJAX Upload & Logo Select
		jQuery("li a.remove").live("click", function(){
			sure_delete = confirm("Are you sure you want to remove this image?");
			if(sure_delete)
				{
					attachid = jQuery(this).parent().children("a.image").attr("id");
					jQuery.get(post_page,
						{action : 'ocmx_remove-image', attachid: attachid}, 
						function(data)
								{jQuery("#"+attachid).parent().fadeOut();}
							);
				}
			return false;
		});
		
		jQuery(".previous-logos ul li a.image").live("click", function(){
			parent = jQuery(this).parent();
			grandparent = jQuery(this).parent().parent();
			greatgrandparent = jQuery(this).parent().parent().parent();

			//Text Box for image
			selected_input = greatgrandparent.parent().children().children("input[type='text']");

			//Anchore which displays image
			selected_a = greatgrandparent.parent().children(".logo-display").children("a");
			
			//fadeOut the image
			jQuery(selected_a).stop().fadeOut();
			
			//Get the new image src
			image_value = jQuery(this).children("img").attr("src");
			
			//Change the BG and fade in the image
			setTimeout(function(){
				jQuery(selected_a).css({background: 'url("'+image_value+'") no-repeat center'}).fadeIn();
				jQuery(selected_input).attr("value", image_value);
			}, 500);
			return false;
		})
		
		jQuery("input[id^='clear_upload_']").click(function(){
			input_id = jQuery(this).attr("id").replace("clear_", "")+"_text";
			image_link_id = input_id.replace("_text", "_href");
			var clear_img = confirm("Are you sure you want to clear this image?");
			if(clear_img){
				jQuery("#"+image_link_id).css({background: 'url("") no-repeat center'}).fadeIn();
				jQuery("#"+input_id).attr("value", "")
			}
			return false;
		});
		
		jQuery("input[id^='upload_button_']").each(function(){
			//Get the button Id
			var input_id = "#"+jQuery(this).attr("id");			
			
			//Make sure we're only talking about the button, and not the text field, that'll get messy
			if(input_id.indexOf("_text") <= -1){
				meta = jQuery(this).attr("id").replace("upload_button_", "");
				// Set the approtpriate meta, links and input id's
				var meta = meta.replace("_href", "");
				var image_link_id = input_id+"_href";
				var image_input_id = input_id+"_text";
				
				//Beging the Ajax upload vibe
				new AjaxUpload(jQuery(this).attr("id"), {
				  action:	post_page,
				  name: 	jQuery(this).attr("name"), // File upload name
				  data: 	{action:  "oboxfb_ajax-upload",
							input_name: jQuery(this).attr("name"),
							type: 'upload',
							meta_key: meta,
							data: jQuery(this).id},
				  autoSubmit: true, // Submit file after selection
				  responseType: false,
				  onChange: function(file, extension){
					  new_li = "<img src=\"images/loading.gif\" alt=\"\" /></a>";
					  jQuery("#new-upload-"+meta+" a.image").html(new_li);
					  jQuery("#new-upload-"+meta).fadeIn();
					},
				  onSubmit: function(file, extension){},
				  onComplete: function(file, response) {
					// If there was an error
					if(response.search('Upload Error') > -1){
						jQuery("#new-upload-"+meta+" a:nth-child(1)").html(response);
						setTimeout(function(){jQuery("#new-upload-"+meta).remove();}, 2000);
					}
					else{
						new_image = "<img src=\""+response+"\" alt=\"\" />";
						jQuery(image_link_id).fadeOut();
							
						setTimeout(function(){
							jQuery("#new-upload-"+meta+" a.image").html(new_image);
							jQuery("#new-upload-"+meta).attr("id", "");
							listItem = "<li id=\"new-upload-"+meta+"\" style=\"display: none;\"><a href=\"#\" class=\"image\"></a></li>";
							jQuery(".previous-logos").append(listItem);
							jQuery(image_input_id).attr("value", response);
							jQuery(image_link_id).css({background: 'url("'+response+'") no-repeat center'}).fadeIn();
						}, 1500);						
					}
				  }
				});
			}
		});
				
		jQuery("[id^='theme-list-edit']").live("click", function(){
			if(jQuery(this).html() == "Cancel")
				{jQuery("[id^='theme-list-edit']").html("Edit List");}
			else 
				{jQuery("[id^='theme-list-edit']").html("Cancel");}
				
			jQuery(".theme-functions").toggleClass("no_display");
			jQuery("[id^='delete-theme-']").toggleClass("no_display");
			return false;
		});
		
		jQuery("[id^='delete-theme-'] a").live("click", function(){
			var theme_file = jQuery(this).parent().attr("id").replace("delete-theme-", "");
			var theme_name = jQuery(this).parent().parent().children("h4").text();
			var theme_div = jQuery(this).parent().parent();
			var confirm_delete = confirm("Are you sure you want to remove "+theme_name+"?")
			if(confirm_delete)
				{
					theme_div.addClass("loading").children("*").fadeOut();
					jQuery.get(post_page,
							{action : 'oboxfb_theme-remove', template: theme_file}, 
							function(data){
								if(data.indexOf("Success") !== -1)
									{
										theme_div.fadeOut()
										setTimeout(function(){theme_div.remove();}, 500);
									}
								else
									{alert("There was an error when removing this theme.");}
							}
					);
				}
			return false;
		});
		jQuery("#add-theme").each(function(){
			//Get the button Id
			var input_id = "#"+jQuery(this).attr("id");			
			
			meta = jQuery(this).attr("id").replace("upload_button_", "");
			
			// Set the approtpriate meta, links and input id's
			var meta = meta.replace("_href", "");
			
			//Beging the Ajax upload vibe
			new AjaxUpload(jQuery(this).attr("id"), {
			  action:	post_page,
			  name: 	"new_theme", // File upload name
			  data: 	{action:  "oboxfb_theme-upload", type: 'upload'},
			  autoSubmit: true, // Submit file after selection
			  responseType: false,
			  onChange: function(file, extension){
				  if(extension == "zip"){
					  jQuery("#add-theme").parent().unbind();
					  jQuery(".empty").removeClass("hover");
					  jQuery("#add-theme").parent().addClass("loading").children("*").fadeOut();
				  }
				},
			  onSubmit: function(file, extension){},
			  onComplete: function(file, response) {
				jQuery("#add-theme").parent().children("div:eq(0)").remove();
				jQuery("#add-theme").parent().html(response).removeClass("empty").removeClass("loading").children("div:eq(1)").fadeIn();
				
				// If there was an error
				if(response.search('Upload Error') > -1){
					jQuery("#new-theme").html(response);
					setTimeout(function(){jQuery("#new-theme").remove();}, 2000);
				}
				else{
					return false;
				}
			  }
			});
		});
	});