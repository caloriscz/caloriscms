(function ($) {
    /* Colorbox: lightbox */
    $('a.gallery').colorbox({
        rel: 'gallery',
        maxWidth: '100%',
        maxHeight: '95%',
        scrolling: false
    });

    /* Laniakea: Alternative image on hover */
    $('.img-hovered').hover(sourceSwap, sourceSwap);

})(jQuery);

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



/* Functions */
var sourceSwap = function () {
    var $this = $(this);
    var newSource = $this.data('alt-src');
    $this.data('alt-src', $this.attr('src'));
    $this.attr('src', newSource);
}
