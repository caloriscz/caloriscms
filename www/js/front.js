$(function () {
    $('#side-menu').metisMenu();
});

// Sets the min-height of #page-wrapper to window size
$(function () {
    $(window).bind("load resize", function () {
        topOffset = 50;
        width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse');
            topOffset = 100; // 2-row-menu
        } else {
            $('div.navbar-collapse').removeClass('collapse');
        }

        height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1)
            height = 1;
        if (height > topOffset) {
            $("#page-wrapper").css("min-height", (height) + "px");
        }
    });

    var url = window.location;
    var element = $('ul.nav a').filter(function () {
        return this.href == url || url.href.indexOf(this.href) == 0;
    }).addClass('active').parent().parent().addClass('in').parent();
    if (element.is('li')) {
        element.addClass('active');
    }
});


$(document).ready(function () {
    $('a.gallery').colorbox({
        rel: 'gallery',
        maxWidth: '100%',
        maxHeight: '95%',
        scrolling: false
    });

    //Check to see if the window is top if not then display button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.scrollToTop').fadeIn();
        } else {
            $('.scrollToTop').fadeOut();
        }
    });

    //Click event to scroll to top
    $('.scrollToTop').click(function () {
        $('html, body').animate({scrollTop: 0}, 800);
        return false;
    });

    // Pickup type    
    var selected = $("input[type='radio'][name='shipping']:checked").val();
    if (selected == 6) {
        $('#pickups').show();
    } else {
        $('#pickups').hide();
    }

    $("input[type='radio'][name='shipping']").click(function () {
        var selected = $("input[type='radio'][name='shipping']:checked").val();

        if (selected == 6) {
            $('#pickups').show();
        } else {
            $('#pickups').hide();
        }
    });

    $('.delivery').hide();
    $('#delivery_show').click(function (event) {
        event.preventDefault();
        $('.delivery').show(200);
        return false;
    });

    $('#address-form').hide();
    $('#insert-address').click(function (event) {
        event.preventDefault();
        $('#address-form').show(200);
        return false;
    });
});