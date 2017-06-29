$(function () {
    $.nette.init();
});

//Loads the correct sidebar on window load,
//collapses the sidebar on window resize.
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
    /* Lightbox gallery */
    $('a.gallery-lightbox').colorbox({
        rel: 'gallery',
        maxWidth: '100%',
        maxHeight: '95%',
        scrolling: false
    });

    $(function () {
        $('.tool-tip').tooltip()
    })

    /* AJAX form on submit*/
    $("form.ajax").on("submit", function () {
        $(this).ajaxSubmit();
        return false;
    });

    $(function () {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss'
        });
    });

    /* Summernote */
    $(function () {
        $("#wysiwyg").summernote({
            width: "95%",
            height: 500
        });

        $("#wysiwyg-sm").summernote({
            width: "95%",
            height: 120,
            toolbar: [
                ['style', ['bold', 'clear']],
            ],
        });
    });

    //Tree View
    $(function () {
        $('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');
        $('.tree li.parent_li > span').on('click', function (e) {
            var children = $(this).parent('li.parent_li').find(' > ul > li');
            if (children.is(":visible")) {
                children.hide('fast');
                $(this).attr('title', 'Expand this branch').find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
            } else {
                children.show('fast');
                $(this).attr('title', 'Collapse this branch').find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
            }
            e.stopPropagation();
        });
    });

    $(document).ready(function () {
        if ($('input[name="allday"]').attr('checked')) {
            $('#event_hour, #event_minute, #event_end_hour, #event_end_minute').hide();
        } else {
            $('#event_hour, #event_minute, #event_end_hour, #event_end_minute').show();
        }

        $('input[name="allday"]').change(function () {
            if (this.checked) {
                $('#event_hour, #event_minute, #event_end_hour, #event_end_minute').hide();
            } else {
                $('#event_hour, #event_minute, #event_end_hour, #event_end_minute').show();
            }
        });

        $('.wysiwyg-disabled').summernote({
            toolbar: [
                // [groupName, [list of button]]
                ['fullscreen', ['fullscreen']],
            ],
            height: 500
        });

        $('.wysiwyg-disabled').summernote('disabled');

        $('#wysiwyg-page').summernote({
            width: "99%",
            height: 500,
            onImageUpload: function (files, editor, welEditable) {
                //welEditable.focus();
                sendFile(files[0], editor, welEditable);
            }
        });

        function sendFile(file, editor, welEditable) {
            data = new FormData();
            data.append("file", file);
            data.append("do", "documentEditor-imageAddForm-submit");
            data.append("page_id", $(".data-wysiwyg").data("ids"));
            $.ajax({
                data: data,
                type: "POST",
                url: "/admin",
                cache: false,
                contentType: false,
                processData: false,
                success: function (url) {
                    console.log(file.name);
                    console.log($(".data-wysiwyg").data("ids"));
                    $('#wysiwyg-page').summernote("insertImage", '/media/' + $(".data-wysiwyg").data("ids") + '/' + file.name);
                }
            });
        }
    });


    /*Events - full calendar */
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        events: '/api/events/default',
        lang: 'cs',
        weekends: true,

    })

    /* on message key up */
    $('.message').keyup(function () {
        var maxLength = $(this).attr("maxlength");
        var length = $(this).val().length;
        var length = maxLength - length;
        $('#chars').text(length);
    });

});
/* Autocomplete for parametres */
$(document).ready(function () {
    // TypeError: $.browser is undefined solution for autocomplete
    var matched, browser;

    jQuery.uaMatch = function (ua) {
        ua = ua.toLowerCase();

        var match = /(chrome)[ \/]([\w.]+)/.exec(ua) ||
            /(webkit)[ \/]([\w.]+)/.exec(ua) ||
            /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) ||
            /(msie) ([\w.]+)/.exec(ua) ||
            ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) ||
            [];

        return {
            browser: match[1] || "",
            version: match[2] || "0"
        };
    };
    matched = jQuery.uaMatch(navigator.userAgent);
    browser = {};

    if (matched.browser) {
        browser[matched.browser] = true;
        browser.version = matched.version;
    }

    // Chrome is Webkit, but Webkit is also Safari.
    if (browser.chrome) {
        browser.webkit = true;
    } else if (browser.webkit) {
        browser.safari = true;
    }

    jQuery.browser = browser;
});

/* Focus on modal */
$(document).ready(function () {
    $('#myModal').on('shown.bs.modal', function () {
        $('#frm-insertForm-title').focus();
    })

});

/*Parametres autocomplete */
if ($("input[name='paramkey']").length > 0) {
    var groupkey = $("input[name='paramkey']").attr("data-params").split(";");
    $("input[name='paramkey']").autocomplete({source: groupkey});
}

$('.datepicker').datepicker({
    language: document.documentElement.lang,
    format: 'd. m. yyyy',
    startDate: '0d',
    endDate: '+2m',
    autoclose: true
});

$('#event_start').datepicker({
    language: document.documentElement.lang,
    format: 'd. m. yyyy',
    startDate: '0d',
    endDate: '+2m',
    autoclose: true
}).on('changeDate', function (ev) {
    $('input[name="date_event_end"]').val($('#event_start').val());
});