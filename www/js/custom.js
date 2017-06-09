/* Content editable parts of the site */
$('body').on('focus', '[contenteditable]', function (e) {

}).on('keypress', '[contenteditable]', function (e) {
    if (e.keyCode == 27) {
        $(this).blur();
    }
}).on('blur', '[contenteditable]', function (e) {
    if ($(this).data("editor") === 'page_title') {
        $.ajax({
            type: 'post',
            url: '/',
            data: 'do=pagetitle&editorId=' + $(this).data("editor-id") + '&text=' + $(this).html()
        });
    } else {
        $.ajax({
            type: 'post',
            url: '/',
            data: 'do=snippet&snippetId=' + $(this).data("snippet") + '&text=' + $(this).html()
        });
    }
});
