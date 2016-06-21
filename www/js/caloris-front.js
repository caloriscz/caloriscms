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

    //Click event to scroll to top
    /*$('.scrollToTop').click(function () {
     $('html, body').animate({scrollTop: 0}, 800);
     return false;
     });*/

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

    $('.address-form').hide();
    $('input[type=radio][name=insertaddress]').change(function (event) {
        event.preventDefault();
        var addressState = $(this).val();

        if (addressState == 2) {
            $('.address-form').show(200);
        } else {
            $('.address-form').hide();
        }
        return false;
    });

// Cart transitioin on click
    $('.add-to-cart').submit(function (e) {
        $('.cart-box').addClass("cart-box-animate");
    });
});

// Fill addresses
$("#addresses-selector").change(function () {
    var addressB = $("#addressarr").val();
    var addressBArr = $.parseJSON(addressB);

    $("input[name='name']").val(addressBArr[$("#addresses-selector-id").val()]['name']);
    $("input[name='street']").val(addressBArr[$("#addresses-selector-id").val()]['street']);
    $("input[name='city']").val(addressBArr[$("#addresses-selector-id").val()]['city']);
    $("input[name='zip']").val(addressBArr[$("#addresses-selector-id").val()]['zip']);
    $("input[name='phone']").val(addressBArr[$("#addresses-selector-id").val()]['phone']);
    $("input[name='company']").val(addressBArr[$("#addresses-selector-id").val()]['company']);
    $("input[name='vatin']").val(addressBArr[$("#addresses-selector-id").val()]['vatin']);
    $("input[name='vatid']").val(addressBArr[$("#addresses-selector-id").val()]['vatid']);
});

// Fill delivery address test
$("#del-addresses-selector").change(function () {
    var addressD = $("#addressarr").val();
    var addressBArr = $.parseJSON(addressD);

    $("input[name='del_name']").val(addressBArr[$("#del-addresses-selector-id").val()]['name']);
    $("input[name='del_street']").val(addressBArr[$("#del-addresses-selector-id").val()]['street']);
    $("input[name='del_city']").val(addressBArr[$("#del-addresses-selector-id").val()]['city']);
    $("input[name='del_zip']").val(addressBArr[$("#del-addresses-selector-id").val()]['zip']);
    $("input[name='del_phone']").val(addressBArr[$("#del-addresses-selector-id").val()]['phone']);
    $("input[name='del_company']").val(addressBArr[$("#del-addresses-selector-id").val()]['company']);
});