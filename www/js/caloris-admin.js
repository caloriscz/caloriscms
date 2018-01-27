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
        $('.wysiwyg-disabled').summernote({
            toolbar: [
                // [groupName, [list of button]]
                ['fullscreen', ['fullscreen']],
            ],
            height: 500
        });

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

    flatpickr.prototype.getMinutes = function (givenDate) {
        return '01';
    }


    // Flatpickr
    $(".datepicker").flatpickr({
        altInoput: "Y-m-d H:i",
        enableTime: true,
        locale: 'cs',
        time_24hr: true
    });
});

/* Focus on modal */
$(document).ready(function () {
    $('#myModal').on('shown.bs.modal', function () {
        $('#frm-insertForm-title').focus();
    })

});