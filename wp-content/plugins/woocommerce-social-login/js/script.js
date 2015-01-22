(function($){
	$(document).ready(function() {
		$(function(){
			$(".codenegar_show_social_login").click(function(e){
				$(".codenegar_social_login").slideToggle();
				e.preventDefault();
			});
		});
	});
})(jQuery);