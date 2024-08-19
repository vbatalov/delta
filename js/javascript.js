var moveElem_items = [
	{
		max:  1199,
		act:  'append',
		from: '.left',
		to:   '.header__wrap',
		elem: '.left__logo-box'
	},
	{
		min:  1199,
		act:  'append',
		from: '.header__wrap',
		to:   '.left',
		elem: '.left__logo-box'
	},
    
//    {
//		max:  1199,
//		act:  'append',
//		from: '.header__wrap',
//		to:   '.adaptive-menu__buttons ',
//		elem: '.header__btn-container'
//	},
//	{
//		min:  1199,
//		act:  'append',
//		from: '.adaptive-menu__buttons',
//		to:   '.header__wrap',
//		elem: '.header__btn-container'
//	},
    
//    {
//		max:  1025,
//		act:  'append',
//		from: '.header__right-box',
//		to:   '.adaptive-menu__wrap ',
//		elem: '.search'
//	},
//	{
//		min:  1025,
//		act:  'append',
//		from: '.adaptive-menu__wrap',
//		to:   '.header__right-box',
//		elem: '.search'
//	},
//    
];


(function($){

$(document).ready(function(){
	moveElem(moveElem_items);
});

$(window).resize(function(){
	moveElem(moveElem_items);
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


})(jQuery);

