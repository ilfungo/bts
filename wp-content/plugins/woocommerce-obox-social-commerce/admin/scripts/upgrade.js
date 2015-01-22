post_page = oboxfb.ajaxurl;
jQuery(document).ready(function()
	{
		jQuery("#oboxfb_license_button").live("click", function()
			{
				var d = new Date();
				var curr_date = d.getDate();
				var curr_month = d.getMonth();
				var curr_year = d.getFullYear();
				myDate = curr_date + "-" + curr_month + "-" + curr_year;
				
				jQuery("#result").fadeIn();
				jQuery("#result .admin-content ul").html("<li>Validating...</li>");
				
				jQuery.get(post_page,
					{action : "validate_key", hashkey: jQuery("#oboxfb_license_key").attr("value"), timestamp: myDate},
					
					function(data){
						jQuery("#result .admin-content ul").slideUp();
						html_construct = jQuery("#result .admin-content ul").html()+data;
						setTimeout(function(){
							jQuery("#result .admin-content ul").html(html_construct).slideDown();
						}, 300);
						
						if(data.toString().indexOf("is Valid") !== -1){
							jQuery("#result .admin-content ul").slideUp();
							html_construct = jQuery("#result .admin-content ul").html()+"<li>Downloading Package</li>";
							setTimeout(function(){
								jQuery("#result .admin-content ul").html(html_construct).slideDown();
							}, 300);
							
							jQuery.get(post_page,
								{action : "do_oboxfb_upgrade", hashkey: jQuery("#oboxfb_license_key").attr("value"), timestamp: myDate},
								function(data)
									{
										html_construct = jQuery("#result .admin-content ul").html()+data;
										if(html_construct.toString().indexOf("success") !== -1)
											html_construct = html_construct = jQuery("#result .admin-content ul").html()+"<li>Well done! The upgrade was successful.</li>";
										jQuery("#result .admin-content ul").slideUp()
										
										setTimeout(function(){jQuery("#result .admin-content ul").html(html_construct).slideDown();}, 300);
									}
								);	
						};
					});
						
			});
			
		jQuery("a[id^='upgrade-button-']").live("click", function()
			{
				zipfile = jQuery(this).attr("rel");
				version = jQuery(this).attr("id").replace("upgrade-button-", "");
				this_href = this;
				status = version.replace(".", "-");
				status = "#upgrade-status-"+status.replace(".", "-");
				sure_upgrade = confirm("Are you sure you'd like to replace the old files?");
				if(sure_upgrade)
					{
						jQuery(status).fadeIn();
						jQuery.get(post_page,
							{action : 'do_ocmx_upgrade', zipfile: zipfile, version: version}, 
							function(data)
									{
										
										setTimeout(function(){jQuery(this_href).parent().parent().fadeOut();}, 200);
										jQuery(this_href).parent().html("<p>Successfully Updated!</p>");
										jQuery(status).html(data);
									}
								);
					}
				return false;
			});
	});