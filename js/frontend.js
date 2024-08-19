




jQuery(document).ready(function ($) {

	

	var timer;

	$('li.menu-item.has-mega').on('mouseover',function(){
		var e = $(this);
		clearTimeout(timer);
		timer = setTimeout(function(){
			$('div.sub-menu.o').removeClass('o');
			$('div.sub-menu', e).addClass('o');
			$('.bg_overlay').addClass('active');
		},400);
	});
	$('.bg_overlay').on('mouseover',function(){
		clearTimeout(timer);
		timer = setTimeout(function(){
			$('div.sub-menu.o').removeClass('o');
			$('.bg_overlay.active').removeClass('active');
		},400);
	});



	// $('li.menu-item.has-mega').on("mouseover", function () {
	//     clearTimeout(timer);
	//     openSubmenu();

	// }).on("mouseleave", function () {
	//     timer = setTimeout(
	//         closeSubmenu()
	//         , 2000);
		
	// });


	// function openSubmenu() {
	//     $('div.sub-menu').css('visibility', 'visible');
	// }
	// function closeSubmenu() {
	//     $('div.sub-menu').css('visibility', 'hidden');
	// }


/*
	$('li.menu-item.has-mega').hover(
		function () {
			$('#categories-menu').addClass('active-menu');
		},
		function () {
			$('#categories-menu').removeClass('active-menu');
		}, 1000);

	$('li.menu-item.has-mega').hover(
		function () {
			$('.bg_overlay').addClass('active');
		},
		function () {
			$('.bg_overlay').removeClass('active');
		});

	if ($.trim($('.sub-menu.mega-menu').text()).length == 0) {
		$(".sub-menu.mega-menu").prependTo("#foo");
	}

*/    



	/*const mmenu = new MmenuLight(document.querySelector('#categories-menu'));

	$('a[href="#categories-menu"]').on('click',function(e){
		mmenu.open();
		e.preventDefault();
		e.stopPropagation();
	});*/

	$("#categories-menu").mmenu({
		extensions: ['pageshadow'],
		navbar: {
			title: 'Каталог товаров'
		},
	 
		scrollBugFix: {
			fix: false
		},

	//	openingInterval: 0,

		navbars: [{
			position: 'top',
			content: [
				'prev',
				'title',
				'close'
			]
		}]
	}, {
		clone: true
	});


});