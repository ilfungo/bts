jQuery.noConflict();
function slideFrame(thumbid, direction, type, match_height)
	{
		/* Set the new position & frame number */

		move_by = jQuery(thumbid).parent().width();
		jQuery(thumbid).children("li").animate({width: move_by+"px"});

		frame_left = jQuery(thumbid).css(type).replace("px", "");
		frame_no = (-(frame_left/move_by));
		maxsize = (jQuery(thumbid).children("li").size()-1);
		jQuery(".dot-selected").removeClass("dot-selected");

		if(direction == 0)
			{
				new_frame_no =  Math.round((frame_no/1)+1);
				if(maxsize <= frame_no)
					new_frame_no = 0;
				new_left = -(new_frame_no*move_by)+"px";

			}
		else
			{
				new_frame_no = Math.round((frame_no/1)-1);

				if(frame_no == 0)
					new_frame_no = maxsize;

				new_left = -(new_frame_no*move_by)+"px";
			}

		jQuery(".slider-dots a").eq(new_frame_no).addClass("dot-selected");
		setHeight(thumbid, new_frame_no);

		setTimeout(function(){jQuery.noslide = 0;}, 200);

		if(type == "left")
			{jQuery(thumbid).animate({"left": new_left}, {duration: 200});}
		else
			{jQuery(thumbid).animate({"top": new_left}, {duration: 300});}
	}

function setHeight(thumbid, new_frame_no){

	if(jQuery(thumbid).html() !== null) {
		framehtml = jQuery(thumbid).children("li").eq(new_frame_no).html();
		if(framehtml == undefined)
			return true;
		if(framehtml.toString().indexOf("iframe") > -1)
			{usechild = "iframe";}
		else if(framehtml.toString().indexOf("object") > -1)
			{usechild = "object";}
		else
			{usechild = "img";}

		theli = jQuery(thumbid).children("li").eq(new_frame_no);

		if(theli.html().toString().indexOf("<img") > -1){
			var useheight = theli.children(usechild).height();
			jQuery(".slider").animate({height: (useheight)}, 250);
		}
	}
}

function resize_slide(element){
	var width = jQuery(element).width();
	if(jQuery(element).children("ul").css("left") == undefined){
		return false;
	}
	var left = jQuery(element).children("ul").css("left").replace("px", "");
	var maxmove = -(jQuery(element).children("ul").children("li").size()*width);
	jQuery(element).children("ul").children("li").animate({width: width}, 150);

	if(jQuery(element).children("ul").children("li").length > 1){
		var frame = jQuery(".dot-selected").index();
		setTimeout(function(){
			jQuery(element).children("ul").animate({left: -(frame*width)}, 700);
			setHeight(jQuery(element).children("ul"), frame);
		}, 250);
		setTimeout(function(){jQuery.noslide = 0;}, 500);
	}
	setHeight(".slider ul.gallery-container", frame);
}

function clear_auto_slide(){
	jQuery("div[id^='slider-auto']").each(function(){
		if(!isNaN(jQuery(this).text()) && jQuery(this).text() !== "0" && jQuery(this).text() !== "")
			{clearInterval(SliderInterval);}
	});
}


jQuery(window).resizeend({
	onDragEnd: function(){
		jQuery.noslide = 1;
		resize_slide(".oboxfb-slider");
	}
});

jQuery(document).ready(function()
	{
		// jQuery(".gallery-container li, .post-image, .portfolio-image, #content-container").fitVids();
		if(jQuery.browser.msie || jQuery.browser.mozilla)
			{Screen = jQuery("html");}
		else
			{Screen = jQuery("body");}

		jQuery.noslide = 0;
		var thumbid = ".oboxfb-slider ul.gallery-container";
		var parentwidth = jQuery(thumbid).parent().width();
		jQuery(thumbid).children("li").animate({width: parentwidth+"px"});
		jQuery(thumbid).animate({"left": 0}, {duration: 500});

		setTimeout(function(){setHeight(thumbid, 0);}, 1000);

		jQuery("div[id^='slider-auto']").each(function(){
			if(!isNaN(jQuery(this).text()) && jQuery(this).text() !== "0" && jQuery(this).text() !== "")
				{
					SliderInterval = setInterval(function(){
						if(jQuery.noslide == 0)
							{
								jQuery.noslide = 1;
								slideFrame(thumbid, 0, "left");
							}
					}, (jQuery(this).text()*1000));
				}
		});

		jQuery("iframe, object").mouseover(function(){clear_auto_slide();});

		jQuery(".oboxfb-slider .next").click(function(){
			if(jQuery.noslide == 0)
				{
					jQuery.noslide = 1;
					slideFrame(thumbid, 0, "left");
				}
			return false;
		});

		jQuery(".oboxfb-slider .previous").click(function(){
			if(jQuery.noslide == 0)
				{
					jQuery.noslide = 1;
					slideFrame(thumbid, 1, "left");
				}
			return false;
		});

		jQuery(".oboxfb-slider .slider-dots a").click(function(){
			if(jQuery.noslide == 0)
				{
					jQuery.noslide = 1;
					clear_auto_slide();
					parentwidth = jQuery(thumbid).parent().width();
					new_left  = -(jQuery(this).index()*parentwidth);
					jQuery(".dot-selected").removeClass("dot-selected");
					jQuery(this).addClass("dot-selected");
					jQuery(thumbid).animate({"left": new_left}, {duration: 500});
					setHeight(thumbid, jQuery(this).index());

					frame_left = jQuery(thumbid).css("left").replace("px", "");
					frame_no = (-(frame_left/parentwidth));

					setTimeout(function(){jQuery.noslide = 0;}, 500);
				}
			return false;
		});

		var parentwidth = jQuery(".portfolio-slider").width();
		jQuery(".portfolio-slider ul").children("li").animate({width: parentwidth+"px"});

		jQuery(".portfolio-slider .next").click(function(){
			if(jQuery.noslide == 0)
				{
					jQuery.noslide = 1;
					slideFrame(".portfolio-slider ul", 0, "left");
				}
			return false;
		});

		jQuery(".portfolio-slider .previous").click(function(){
			if(jQuery.noslide == 0)
				{
					jQuery.noslide = 1;
					slideFrame(".portfolio-slider ul", 1, "left");
				}
			return false;
		});

		jQuery(".portfolio-slider .slider-dots a").click(function(){
			parentwidth = jQuery(".portfolio-slider").width();
			new_left  = -(jQuery(this).index()*parentwidth);
			jQuery(".dot-selected").removeClass("dot-selected");
			jQuery(this).addClass("dot-selected");
			jQuery(".portfolio-slider ul").animate({"left": new_left}, {duration: 500});

			frame_left = jQuery(".portfolio-slider ul").css("left").replace("px", "");
			frame_no = (-(frame_left/parentwidth));
			return false;
		});


	});