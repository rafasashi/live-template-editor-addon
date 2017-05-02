;(function($){
	
	$(document).ready(function(){	
	
		$.fn.insertMedia = function(inputId){
			
			this.contents().find('.insert_media').off();
			
			this.contents().find('.insert_media').on('click', function(e){

				e.preventDefault();
				
				var imgUrl = $(this).attr('data-src');
				
				// set input change
				 
				$(inputId).val( imgUrl );

				// trigger input change
				
				$(inputId).trigger("change");
			
				// close current modal
				
				$('.modal').modal('toggle');
			});
			
			this.contents().find('.add_account').on('click', function(e){

				var modalIframe = $('.modal').find('iframe');
			
				// reset modal src
				
				//var iframeSrc = modalIframe.attr('src');
				//modalIframe.attr('src','').delay( 1000 ).attr('src',iframeSrc);
			
				modalIframe.attr('src','');
			
				// close current modal
				
				$('.modal').modal('toggle');				
			});
			
			return this;			
		}
		
		//modal always on top
		
		$('.modal').appendTo("body");
		
		$('.modal').on('shown.bs.modal', function(e) {
			
			var modalIframe = $(this).find('iframe');
			
			if(modalIframe.length > 0){
				
				var invokerId 	= $(e.relatedTarget);
				var inputId 	= '#' + invokerId.attr('data-id');
				var iframeSrc 	= modalIframe.attr("src");
				
				if(typeof iframeSrc == typeof undefined || iframeSrc == false){
					
					var iframeDataSrc = modalIframe.attr("data-src");
					
					if(typeof iframeDataSrc !== typeof undefined && iframeDataSrc !== false){

						modalIframe.attr("src", iframeDataSrc);

						modalIframe.on('load', function(){
							
							modalIframe.insertMedia(inputId);
						});						
					}
				}
				else{
					
					modalIframe.insertMedia(inputId);
				}				
			}
		});
		
		//set tooltips
		
		$('[data-toggle="tooltip"]').tooltip();
	});
	
})(jQuery);