jQuery(document).ready(function()
	{
		setTimeout(function(){
			jQuery("a").each(function(){
				if(jQuery(this).attr("href") == undefined){
					return;
				}

				var href = jQuery(this).attr("href").toString();
				if(href.indexOf(oboxfb.path) > -1) {
					if(href.indexOf("?") > -1){
						var sep = "&";
					} else {
						var sep = "?";
					}
					var href = href+sep+"obox-fb=1";
					jQuery(this).attr("href", href);
				}

				if(href.indexOf('wp-login') > -1 || href.indexOf('wp-admin') > -1){
					jQuery(this).attr("target", "_blank");
				}

				if(href.indexOf('jpeg') > -1 || href.indexOf('jpg') > -1 || href.indexOf('png') > -1 || href.indexOf('gif') > -1){
					jQuery(this).click(function(){return false;})
				}


			});
		}, 1);
		jQuery("form[name='checkout']").attr("target", "_blank");

		jQuery("form").each(function(){
			if(jQuery(this).attr("action")){
				var action = jQuery(this).attr("action").toString();
				if(action.indexOf(oboxfb.path) > -1) {
					if(action.indexOf("?") > -1){
						var sep = "&";
					} else {
						var sep = "?";
					}
					var action = action+sep+"obox-fb=1";
					jQuery(this).attr("action", action);
				}
			}
		});

	});