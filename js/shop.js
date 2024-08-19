(function ($) {


	var shop_basket_timeout;


	$(document).ready(function () {
		$('.shop_basket_form .chbf_inp').each(function () {
			//if($('textarea',this).length) $('textarea',this).textareaAutoSize();
			var elem = $(this);
			var input = $('input,textarea', this);
			if (input.val() != '')
				elem.addClass('chbf_inp_s');
			input.focus(function () {
				if (!elem.hasClass('chbf_inp_s'))
					elem.addClass('chbf_inp_s');
				elem.addClass('chbf_inp_f');
			});
			input.focusout(function () {
				if (input.val() == '')
					elem.removeClass('chbf_inp_s');
				elem.removeClass('chbf_inp_f');
			});
		});
		$('.shop_basket_form .chbf_inp .chbf_lab').on('click', function () {
			$('input,textarea', $(this).parent()).focus();
		});

		$('.shop_basket_form input, .shop_basket_form textarea').on('change', function () {
			var nm = $(this).attr('name');
			var vl = $(this).val();
			$.ajax("/ajax/?a1=shop&a2=data&nm=" + nm + "&vl=" + vl);
		});


	});



	$(document).on('click','.shopbtns .v1klik',function(){
		var e = $(this);
		var p = e.parents('.catalogitemid');
		docid = p.data('docid');
		price = p.data('price');

		$('.shop_basket_form .v1klik_f').val('y');
		$('.shop_basket_form .v1klik_itemid').val(docid);
		$('.shop_basket_itogo .shop_basket_checkout').data('itogo',price);

		if ($('body').hasClass('popup_open')) {
			$('body').removeClass('popup_open');
			popup_act(5, 'close');
		} else {
			$('body').addClass('popup_open');
			popup_act(5, 'open');
		}
	});


	$(document).on('click','.shopbasketbtn',function(){
		var e = $(this);
		var p = e.parent();
		if (e.hasClass('pr')) return;
		e.addClass('pr');
		var docid = e.parents('.catalogitemid');
		docid = docid.data('docid');
		$.ajax({
			url: '/ajax/?ajax&a1=shop&a2=add&id='+docid+'&count=1',
			dataType: 'json',
			cache: false
		})
		.done(function(data){
			e.removeClass('pr');
			if (data.res == 'ok') {
				p
					.removeClass('shopbasketstat_n')
					.addClass('shopbasketstat_y');
				$('.shb_cc').text(data.cc);
			} else {
			}
		});
	});



	$(document).on('click', '.more .plus, .more .minus', function () {
		let number = $(this).parent().parent().find(".number").text();
		if ($(this).hasClass('minus')) {
			number--;
			if (number <= 0)
				number = 1;
		} else {
			number++;
		}
		$(this).parent().parent().find(".number").text(number);
	});


	/*$(document).on('click', '.catalog__btn_false .btn-basket, .catalog__btn_false .btnBuy', function (e) {
		e.preventDefault();
		var box = $(this).parent();
		//$('.svgloading', box).show();
		var itemid = $(this).data('itemid');
		let number = +$('#quantity').text();
		if (number == 0) {
			number = 1;
		}
		//let number = $(this).parent().find( ".number" ).text();
		console.log(2);
		$.ajax('/ajax/?ajax&a1=shop&a2=add&id=' + itemid + '&count=' + number)
				.done(function (data) {
					$.ajax('/ajax/?ajax&a1=shop&a2=delete&id=' + itemid).done(function (data) {
						$('.shop_basket_items').html(data);
						$.ajax('/ajax/?a1=shop&a2=itogo')
								.done(function (data) {
									$('.shop_basket_itogo').html(data);
								});

						$.ajax('/ajax/?a1=shop&a2=items_count')
								.done(function (data) {
									$('.header .cart .shb_cc').html(data);
								});
					});

					$('.btn-basket', box).hide();
					$('.cib_ok', box).fadeIn('slow');
					$(this).parent().removeClass('catalog__btn_false');
					$(this).parent().addClass('catalog__btn_true');
					console.log(1);
					var res = $.parseJSON(data);
					//	var res = JSON.parse(data);
					console.log(res);
					console.log(res.cc);
					$('.shb_cc').text(res.cc);
					//$('.shb_ss b').show().text(res.ss);
					console.log(3);
				});
	});*/


	$(document).on('click', '.shop_basket_items .shbi_prms_but', function () {
		var elem = $('.shbi_prms_box', $(this).parent());
		$(this).remove();
		elem.stop().animate({opacity: 1, height: $('.shbi_prms', elem).height()}, 500);
	});


	$(document).on('click', '.shop_basket_items .shbi_cc .shbi_plus_minus, .shop_basket_items .shbi_del', function () {
		clearTimeout(shop_basket_timeout);
		$('.shop_basket_checkout').addClass('chbch_disabled');

		var elem = $(this).parent();
		while (! elem.hasClass('item'))
			elem = elem.parent();
		var u = elem.data('u');

		//$('.shbi_itogosum .svgloading').show();

		var a2 = 'count';
		if ($(this).hasClass('shbi_del'))
			a2 = 'delete';

		if (a2 == 'count')
		{
			//$('.shbi_sum .svgloading',elem).show();

			var count = parseInt($('.shbi_ccval', elem).text());
			if ($(this).hasClass('shbi_plus'))
				count++;
			else
				count--;
			if (count <= 0)
				count = 1;
			$('.shbi_ccval', elem).text(count);

		} else if (a2 == 'delete') {
			//$('>.svgloading',elem.parent()).show();
		}

		shop_basket_timeout = setTimeout(function () {
			$.ajax('/ajax/?a1=shop&a2=' + a2 + '&u=' + u + '&count=' + count)
					.done(function (data) {
						$('.shop_basket_items').html(data);

						$.ajax('/ajax/?a1=shop&a2=itogo')
								.done(function (data) {
									$('.shop_basket_itogo').html(data);
								});

						$.ajax('/ajax/?a1=shop&a2=items_count')
								.done(function (data) {
									$('.header .cart .shb_cc').html(data);
								});
					});
		}, (a2 == 'count' ? 700 : 0));
	});


	$(document).on('click', '.shop_basket_itogo .shbi_checkout .shop_basket_checkout', function () {
		if ($('.shop_basket_checkout').hasClass('chbch_disabled'))
			return;
		$('.shop_basket_checkout').addClass('chbch_disabled');
		//$('.shbi_checkout .svgloading').show();

		var code = $(this).data('code');
		var itogo = $(this).data('itogo');

		$.post('/ajax/?a1=shop&a2=checkout&itogo=' + itogo, $('.shop_basket_box .shop_basket_form form').serialize())
				.done(function (data) {
					data = $.parseJSON(data);
					if (data.result == 'ok')
					{
						$('.shbi_errors').hide();
						window.location = $('.shop_basket_form form').attr('action')+'?c='+data.c+'&s='+data.s;
					} else {
						$('.shop_basket_checkout').removeClass('chbch_disabled');
						//	$('.shbi_checkout .svgloading').hide();
						$('.shbi_errors').show().html(data.errors);
					}
				});
	});

	$(document).on('click', '#plus_value', function () {
		var plus_val = +$('#quantity').text();
		$('#quantity').text(plus_val + 1);
		//$('#summ').text('');
		plus_val++;
		$('#summ').text(plus_val * +($('.price__dec').eq(0).text()));
	});
	$(document).on('click', '#min_value', function () {
		if ($('#quantity').text() > 1) {
			var min_val = +$('#quantity').text();
			$('#quantity').text(min_val - 1);
			//$('#summ').text('');
			min_val--;
			$('#summ').text(min_val * +($('.price__dec').eq(0).text()));
		}
	}
	);
})(jQuery);