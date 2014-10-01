$(document).ready(function() { 
		$(".toggle").parent('li').find('ul').slideUp('slow');
		$(".toggle").click(function(){
			$(this).parent('li').find('ul').slideToggle('slow');
			return false;
		});	
		$(".side li a").click(function(){
			var href=$(this).attr("href");
			var pos=$(href).position();	
			 $('.side li a').removeClass("active");
			 $(this).addClass("active");					
			$('body,html').animate({ scrollTop: pos.top-10 });
			return false;
		});
		h=$(".side").height();
		pos=$(".side").position();
		$(window).scroll(function(){
			if($("body").width() > 960){
				if(window.pageYOffset>h){
					$(".side").css({
			            position: 'fixed',
			            top: 0,
			            left: pos.left,
					});
				}else{
					$(".side").css({
			            position: 'relative',
			            top: 0,
			            left: 0,
					});
				}	
			}
		});
});
