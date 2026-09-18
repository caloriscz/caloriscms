(function ($) {
    /* Colorbox: lightbox */
    $('a.gallery').colorbox({
        rel: 'gallery',
        maxWidth: '100%',
        maxHeight: '95%',
        scrolling: false
    });

    /* Alternative image on hover */
    $('.img-hovered').hover(function () {
            var source = $(this).attr('src');
            $(this).attr('src', $(this).data('alt-src'));
            $(this).data('alt-src', source);
            return sourceSwap;
        }, function () {
            var source = $(this).data('alt-src');

            $(this).data('alt-src', $(this).attr('src'));
            $(this).attr('src', source);
            return sourceSwap;
        }
    );

})(jQuery);

/* Hand-authored frontend behavior; this file is not a Gulp concatenation target. */
(function ($) {
    var selector = '[contenteditable="true"][data-editor-id], [contenteditable="true"][data-snippet]';
    $('body').on('focus', selector, function () {
        $(this).data('before-edit', $(this).html());
    }).on('keydown', selector, function (event) {
        if (event.key === 'Escape') {
            $(this).html($(this).data('before-edit'));
            $(this).blur();
        } else if (event.key === 'Enter' && $(this).data('editor') === 'page_title') {
            event.preventDefault();
            $(this).blur();
        }
    }).on('blur', selector, function () {
        var element = $(this);
        if (element.html() === element.data('before-edit')) return;
        var title = element.data('editor') === 'page_title';
        var data = {
            _do: title ? 'pagetitle' : 'snippet',
            _csrf: $('meta[name="inline-csrf"]').attr('content'),
            text: title ? element.text() : element.html()
        };
        data[title ? 'editorId' : 'snippetId'] = title ? element.data('editor-id') : element.data('snippet');
        var status = element.next('[data-inline-status]');
        if (!status.length) status = $('<span data-inline-status role="status"></span>').insertAfter(element);
        status.text('Saving…');
        element.attr('contenteditable', 'false');
        $.ajax({
            type: 'POST', url: window.location.pathname, data: data, dataType: 'json'
        }).done(function (response) {
            if (response.saved !== true || typeof response.content !== 'string') {
                status.text('Not saved. Reload the page and try again.');
                return;
            }
            if (title) element.text(response.content); else element.html(response.content);
            element.data('before-edit', element.html());
            status.text('Saved');
        }).fail(function () {
            status.text('Not saved. Check your access and content, then try again.');
        }).always(function () {
            element.attr('contenteditable', 'true');
        });
    });
})(jQuery);
