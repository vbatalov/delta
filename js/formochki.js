(function($){

$(window).on('load',function(){
	$('.formochki .frm_submit button').each(function(){
		if ($(this).hasClass('frm_submit_load')) return;
		$(this).addClass('frm_submit_load');
		var elem = $(this).parents('.formochki');
		$('.frm_result', elem).hide();
		$('form',elem).submit(function(){ return false; });
		$(this).click(function(){
			$.post($('form',elem).attr('action')+"?formochki", $('form',elem).serialize()).done(function(data){
					var result = $.parseJSON(data);
					var res = $('.frm_result_'+result.result, elem);
					res.show();
					if (result.text) res.html(result.text);
					if (result.hideform == 'true') {
						$('form',elem).remove();
					}
				});
			return;
		});
	});
});

})(jQuery);
