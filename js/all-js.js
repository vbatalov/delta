(function($){
    
    //search popup

    $(document).ready(function () {
        $(".search_button").on("click", function (e) {
            e.preventDefault();
            $('html,body').animate({
                
            },);
            $(".search_pop-up").slideToggle();
        });
    });

//<!-- /search popup  -->
    
    
//    <!--    fix-menu-->

    $(document).ready(function () {
        $(window).scroll(function () {
            if ($(window).scrollTop() > 400 && window.innerWidth >= 1200) {
                //$('.top-menu').addClass('active');
                $('.header-scroll ').fadeIn();
                //$('.userpanel.OnBottomLine').removeClass('hide'); 

            } else {
                //$('.top-menu').removeClass('active');
                $('.header-scroll').fadeOut();
                //$('.userpanel.OnBottomLine').addClass('hide');
            }
        });
    });

$(window).resize(function () {
    if (window.innerWidth >= 1200) {
        $('.header-scroll').fadeIn();
    } else {
        $('.header-scroll').fadeOut();
    }
}); 
//<!--    /fix-menu-->

//<!--  scrollup  -->
    $(document).ready(function () {

        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('.scrollup').fadeIn();
            } else {
                $('.scrollup').fadeOut();
            }
        });

        $('.scrollup').click(function () {
            $("html, body").animate({
                scrollTop: 0
            }, 600);
            return false;
        });

    }); 
//<!--  /scrollup  -->


//<!--  form input  -->

    $('.setdefaultvalue').each(function () {
        var elem = $(this);
        var input = $('input,textarea', this);
        if (input.val() == '')
            elem
            .addClass('defaultvalue')
            .removeClass('uservalue');
        else
            elem
            .removeClass('defaultvalue')
            .addClass('uservalue');
        input.focus(function () {
            elem.addClass('focus');
        });
        input.focusout(function () {
            elem.removeClass('focus');
            if (input.val() == '')
                elem
                .addClass('defaultvalue')
                .removeClass('uservalue');
        });
        input.keyup(function () {
            if (input.val() == '')
                elem
                .addClass('defaultvalue')
                .removeClass('uservalue');
            else
                elem.removeClass('defaultvalue').addClass('uservalue');
        });
    }); 
//<!--  /form input  -->


    
    })(jQuery);