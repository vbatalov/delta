


(function($){

$(document).ready(function(){
	moveElem(moveElem_items);

//---------------------------------------
	// $('textarea.autosize').textareaAutoSize();

//---------------------------------------
	myform_init();

	header_height();

//---------------------------------------
	// if ($('.masking').length) {
	// 	$('.masking').each(function(){
	// 		let el = $(this);
	// 		let tp = el.data('masking');
	// 		if (tp == 'phone') {
	// 			el.mask('+7 000 000-0000');
	// 		}
	// 	});
	// }


//---------------------------------------
	$('.popupbox .close').on('click',function(){
		if ($('body').hasClass('popup_open')) {
			$('body').removeClass('popup_open');
			popup_act(0, 'close');
		}
	});

	$('.features_button').on('click',function(){
		if ($('body').hasClass('popup_open')) {
			$('body').removeClass('popup_open');
			popup_act(1, 'close');
		} else {
			$('body').addClass('popup_open');
			popup_act(1, 'open');
		}
	});
	
	$('.request_demo button, .request_demo_a').on('click',function(){
		if ($('body').hasClass('popup_open')) {
			$('body').removeClass('popup_open');
			popup_act(2, 'close');
		} else {
			$('body').addClass('popup_open');
			popup_act(2, 'open');
		}
	});
	
	$('.mobilemenu_button').on('click',function(){
		if (window.innerWidth > 800) return;
		if ($('body').hasClass('popup_open')) {
			$('body').removeClass('popup_open');
			popup_act(4, 'close');
		} else {
			$('body').addClass('popup_open');
			popup_act(4, 'open');
		}
	});
	
	$('.callbackorder').on('click',function(){
		if ($('body').hasClass('popup_open')) {
			$('body').removeClass('popup_open');
			popup_act(3, 'close');
		} else {
			$('body').addClass('popup_open');
			popup_act(3, 'open');
		}
	});

	$(window).on('keyup',function(e){
		var code = e.keyCode || e.which;
		if (
			(code == 27)
			&& $('body').hasClass('popup_open')
		) {
			$('body').removeClass('popup_open');
			popup_act(0, 'close');
		}
	});

	window.onpopstate = function(e){
		if ($('body').hasClass('popup_open')) {
			$('body').removeClass('popup_open');
			popup_act(0, 'close');
		}
	};
//---------------------------------------
});

	
$(window).on('load',function(){
	$('body').addClass('pageloaded');

	//---------------------------------------
	header_height();

	//---------------------------------------
	$(window).trigger('lazyload');

	//---------------------------------------
});


$(window).scroll(function(){
});


$(window).resize(function(){
	moveElem(moveElem_items);
	
//---------------------------------------
	header_height();
});


$(window).on('swipeleft',function(e){
	$('.mobilemenu_button').trigger('click');
});
$(window).on('swiperight',function(e){
	if ($('body').hasClass('popup_open')) {
		$('body').removeClass('popup_open');
		popup_act(0, 'close');
	}
});


$(window).on('lazyload',function(){
	$('.lazyimg[data-src]').each(function(){
		var elem = $(this);
		elem.removeClass('lazyimg');
		var img = elem.data('src');
		if ( ! img) img = elem.data('src');
		if ( ! img) return;
		elem.attr('src', img);
		elem.on('load',function(){
			$(this).addClass('lazy_loaded');
		});
		// elem.removeAttr('data-src');
	});
	$('.lazybackgr[data-backgr]').each(function(){
		var elem = $(this);
		elem.removeClass('lazybackgr');
		var img = elem.data('backgr');
		if ( ! img) img = elem.data('backgr');
		if ( ! img) return;
		if (img.indexOf('crop/')===0) {
			img += '&w='+ $(window).width();
			img += '&h='+ $(window).height();
		}
		elem.css({
			backgroundImage: "url('"+img+"')"
		});
		var loader = new Image();
		loader.src = img;
		loader.onload= function(){
			elem.addClass('lazy_loaded');
		};
		// elem.removeAttr('data-backgr');
	});
});


function moveElem(items)
{
	/**
	 * moveElem
	 * @version 2.0
	 * 01.10.2018
	 */
	var ww = window.innerWidth;
	items.forEach(function (itm, index) {
		if (
			(
				(
					(itm['max'] && ww <= itm['max'])
					|| (itm['min'] && itm['min'] < ww)
				) && itm['if'] !== false
			) || (
				itm['onlyif']
				&& itm['onlyif']() !== false
			)
		) {
			var foreach = itm['each'] ? itm['each'] : 'body';
			$(foreach).each(function(){
				var fromelem = itm['from']==='this' ? $(itm['elem'], this) : $(itm['from']+' '+itm['elem'], this);
				if (itm['act'] == 'after' || itm['act'] == 'before') {
					var to = itm['to']==='this' ? $(this) : $(itm['to']+' '+itm['elem2'], this);
				} else {
					var to = itm['to']==='this' ? $(this) : $(itm['to'], this);
				}

				if ( ! fromelem.length && to.length) return;

				if (itm['act'] == 'append') {
					to.append(fromelem);
				} else if (itm['act'] == 'prepend') {
					to.prepend(fromelem);
				} else if (itm['act'] == 'after') {
					to.after(fromelem);
				} else if (itm['act'] == 'before') {
					to.before(fromelem);
				}

				if (itm['done']) itm['done']();
			});
		}
	});
}


function myform_init()
{
	$('.myform input').each(function(){
		var input = $(this);
		var box = input.parent();

		if (input.val() == '') {
			box.removeClass('uservalue');
		} else {
			box.addClass('uservalue');
		}

		input.focus(function(){
			box.addClass('focus');
		});

		input.focusout(function(){
			box.removeClass('focus');
			if (input.val() == '') {
				box.removeClass('uservalue');
			} else {
				box.addClass('uservalue');
			}
		});

		input.keyup(function(){
			if (input.val() == '') {
				box.removeClass('uservalue');
			} else {
				box.addClass('uservalue');
			}
		});
	});
}

function header_height()
{
	if (window.innerWidth > 1100) {
		$('.height100vh').addClass('height100vh_a');
	} else {
		$('.height100vh').removeClass('height100vh_a');
	}
	if ($('.backgr_video').length) {
		$('.iblock_1').height($('.backgr_video').height());
	}
}


})(jQuery);


function popup_act(id, act, done)
{
	var box= $('.popupbox');
	if (act == 'open') {
		box.addClass('popup_open');
		$('.popupbox .popup').hide();
		$('.popupbox .popup_'+id).show();
		box.stop()
			.css({ opacity:0 })
			.show()
			.animate({ opacity:1 }, 300, function(){
				box.addClass('popup_open_done');
				$('.popupbox .popup_'+id+' .focus').focus();
				if(done != undefined)
					done();
			});

	} else {
		box.removeClass('popup_open');
		box.stop()
			.animate({ opacity:0 }, 300, function(){
				$(this).hide();
				box.removeClass('popup_open_done');
				if(done != undefined)
					done();
			});
	}
}
