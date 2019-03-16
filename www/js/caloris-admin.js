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
        altFormat: "j. n. Y H:i",
        altInput: true,
        enableTime: true,
        locale: 'cs',
        time_24hr: true
    });

    $(".datepicker-date").flatpickr({
        altFormat: "j. n. Y",
        altInput: true,
        enableTime: false,
        locale: 'cs',
        time_24hr: true
    });
});

/* Focus on modal ----- */
$(document).ready(function () {
    $('#myModal').on('shown.bs.modal', function () {
        $('#frm-insertForm-title').focus();
    })

});

/* JSTree  Menus ----- */
$(document).ready(function () {
    $('.tree').jstree({
        "icons": true,
        "core": {
            "check_callback": true
        },
        "contextmenu": {
            "items": function () {
                return {
                    "Create": {
                        "label": "Vytvořit",
                        "action": function (data) {
                            var ref = $.jstree.reference(data.reference);
                            sel = ref.get_selected();
                            if (!sel.length) {
                                return false;
                            }
                            sel = sel[0];
                            sel = ref.create_node(sel, {"type": "file"});
                            if (sel) {
                                ref.edit(sel);
                            }

                        }
                    },
                    "Rename": {
                        "label": "Přejmenovat",
                        "action": function (data) {
                            var inst = $.jstree.reference(data.reference);
                            obj = inst.get_node(data.reference);
                            inst.edit(obj);
                        }
                    },
                    "Delete": {
                        "label": "Smazat",
                        "action": function (data) {
                            var ref = $.jstree.reference(data.reference),
                                sel = ref.get_selected();
                            if (!sel.length) {
                                return false;
                            }
                            ref.delete_node(sel);

                        }
                    }
                };
            }
        }
        ,
        "plugins": ["dnd", "crrm", "contextmenu"]
    }).bind("move_node.jstree", function (e, data) {
        $.ajax({
            data: 'do=menuEditor-sort&id_from=' + data.node.id.substring(3) + '&id_to=' + data.parent.substring(3) + '&id=' +
            data.node.id.substring(3) + '&position_old=' + data.old_position + '&position=' + data.position,
            type: 'GET',
            url: '/admin/menu/default'
        });
    }).on('delete_node.jstree', function (e, data) {
        $.ajax({
            data: 'do=menuEditor-delete&node_id=' + data.node.id.substring(3),
            type: 'GET',
            url: '/admin/menu/default'
        });
    }).on('create_node.jstree', function (e, data) {
        $.ajax({
            data: 'do=menuEditor-create&node_id=' + data.node.parent.substring(3) + '&menu=' + getParameterByName('menu') + '&text=' + data.text,
            datatype: 'json',
            type: 'GET',
            url: '/admin/menu/default',
            success: function (output) {

                var output = JSON.parse(output);

                data.instance.set_id(data.node.id, 'j1_' + output.id);
                data.node.a_attr.id = 'j1_' + output.id + '_anchor';
            }
        });
    }).on('rename_node.jstree', function (e, data) {
        $.ajax({
            data: 'do=menuEditor-rename&node_id=' + data.node.id.substring(3) + '&menu=' + getParameterByName('menu') + '&text=' + data.text,
            type: 'GET',
            url: '/admin/menu/default'
        });
    });

    $('.tree').on('ready.jstree', function () {
        $(".tree").jstree("open_all");
    });


    $(".tree li").on("click", "a",
        function () {
            document.location.href = this;
        }
    );
});

/* JSTree  Pricelist categories ----- */
$(document).ready(function () {
    $('.tree-pricelist').jstree({
        "icons": true,
        "core": {
            "check_callback": true
        },
        "contextmenu": {
            "items": function () {
                return {
                    "Create": {
                        "label": "Vytvořit",
                        "action": function (data) {
                            var ref = $.jstree.reference(data.reference);
                            sel = ref.get_selected();
                            if (!sel.length) {
                                return false;
                            }
                            sel = sel[0];
                            sel = ref.create_node(sel, {"type": "file"});
                            if (sel) {
                                ref.edit(sel);
                            }

                        }
                    },
                    "Rename": {
                        "label": "Přejmenovat",
                        "action": function (data) {
                            var inst = $.jstree.reference(data.reference);
                            obj = inst.get_node(data.reference);
                            inst.edit(obj);
                        }
                    },
                    "Delete": {
                        "label": "Smazat",
                        "action": function (data) {
                            var ref = $.jstree.reference(data.reference),
                                sel = ref.get_selected();
                            if (!sel.length) {
                                return false;
                            }
                            ref.delete_node(sel);

                        }
                    }
                };
            }
        }
        ,
        "plugins": ["dnd", "crrm", "contextmenu"]
    }).bind("move_node.jstree", function (e, data) {
        $.ajax({
            data: 'do=pricelistCategoryEdit-sort&id_from=' + data.node.id.substring(3) + '&id_to=' + data.parent.substring(3) + '&id=' +
            data.node.id.substring(3) + '&position_old=' + data.old_position + '&position=' + data.position,
            type: 'GET',
            url: '/admin/pricelist/menu'
        });
    }).on('delete_node.jstree', function (e, data) {
        $.ajax({
            data: 'do=pricelistCategoryEdit-delete&node_id=' + data.node.id.substring(3),
            type: 'GET',
            url: '/admin/pricelist/menu'
        });
    }).on('create_node.jstree', function (e, data) {
        console.log('renaming');
        $.ajax({
            data: 'do=pricelistCategoryEdit-create&node_id=' + data.node.parent.substring(3) + '&text=' + data.text,
            datatype: 'json',
            type: 'GET',
            url: '/admin/pricelist/menu',
            success: function (output) {
                var output = JSON.parse(output);

                data.instance.set_id(data.node.id, 'j1_' + output.id);
                data.node.a_attr.id = 'j1_' + output.id + '_anchor';
            }
        });
    }).on('rename_node.jstree', function (e, data) {
        var pricelist = getParameterByName('pricelist');

        $.ajax({
            data: 'do=pricelistCategoryEdit-rename&node_id=' + data.node.id.substring(3) + '&text=' + data.text + '&pricelist=' + pricelist,
            type: 'GET',
            url: '/admin/pricelist/menu'
        });
    });

    $('.tree-pricelist').on('ready.jstree', function () {
        $(".tree-pricelist").jstree("open_all");
    });


    $(".tree-pricelist li").on("click", "a",
        function () {
            document.location.href = this;
        }
    );
});

/* JSTree  Link categories ----- */
$(document).ready(function () {
    $('.tree-links').jstree({
        "icons": true,
        "core": {
            "check_callback": true
        },
        "contextmenu": {
            "items": function () {
                return {
                    "Create": {
                        "label": "Vytvořit",
                        "action": function (data) {
                            var ref = $.jstree.reference(data.reference);
                            sel = ref.get_selected();
                            if (!sel.length) {
                                return false;
                            }
                            sel = sel[0];
                            sel = ref.create_node(sel, {"type": "file"});
                            if (sel) {
                                ref.edit(sel);
                            }

                        }
                    },
                    "Rename": {
                        "label": "Přejmenovat",
                        "action": function (data) {
                            var inst = $.jstree.reference(data.reference);
                            obj = inst.get_node(data.reference);
                            inst.edit(obj);
                        }
                    },
                    "Delete": {
                        "label": "Smazat",
                        "action": function (data) {
                            var ref = $.jstree.reference(data.reference),
                                sel = ref.get_selected();
                            if (!sel.length) {
                                return false;
                            }
                            ref.delete_node(sel);

                        }
                    }
                };
            }
        }
        ,
        "plugins": ["dnd", "crrm", "contextmenu"]
    }).bind("move_node.jstree", function (e, data) {
        $.ajax({
            data: 'do=categoryPanel-sort&id_from=' + data.node.id.substring(3) + '&id_to=' + data.parent.substring(3) + '&id=' +
            data.node.id.substring(3) + '&position_old=' + data.old_position + '&position=' + data.position,
            type: 'GET',
            url: '/admin/links/default'
        });
    }).on('delete_node.jstree', function (e, data) {
        $.ajax({
            data: 'do=categoryPanel-delete&node_id=' + data.node.id.substring(3),
            type: 'GET',
            url: '/admin/links/default'
        });
    }).on('create_node.jstree', function (e, data) {
        console.log('renaming');
        $.ajax({
            data: 'do=categoryPanel-create&node_id=' + data.node.parent.substring(3) + '&text=' + data.text,
            datatype: 'json',
            type: 'GET',
            url: '/admin/links/default',
            success: function (output) {
                var output = JSON.parse(output);

                data.instance.set_id(data.node.id, 'j1_' + output.id);
                data.node.a_attr.id = 'j1_' + output.id + '_anchor';
            }
        });
    }).on('rename_node.jstree', function (e, data) {
        $.ajax({
            data: 'do=categoryPanel-rename&node_id=' + data.node.id.substring(3) + '&text=' + data.text,
            type: 'GET',
            url: '/admin/links/default'
        });
    });

    $('.tree-links').on('ready.jstree', function () {
        $(".tree-links").jstree("open_all");
    });


    $(".tree-links li").on("click", "a",
        function () {
            document.location.href = this;
        }
    );
});

/* Sortable ----- */
$(function () {
    $("#sortable").sortable({
        update: function (event, ui) {
            var data = $(this).sortable('serialize');
            var newOrder = $(this).sortable('toArray').toString();
            var Ids = document.querySelector('div#sorter-ids');

            $.ajax({
                data: 'do=carouselManager-images&sortable=' + newOrder + '&ids=' + Ids.dataset.images,
                type: 'GET',
                url: '/admin/appearance/carousel'
            });
        }
    });
    $("#sortable").disableSelection();
});

/* Login field preselect ----- */
$(function () {
    $(".login-panel input[type='text']").focus();
});

/* Ace plugin with Emmett support ---- */
$(function () {
    if ($('pre#document').length > 0) {
        var editor = ace.edit("document");
        var textarea = $('textarea[name="document"]').hide();

        editor.maxLines = Infinity;
        editor.session.setMode("ace/mode/html");
        editor.setTheme("ace/theme/terminal");
        // enable emmet on the current editor
        editor.setOption("enableEmmet", true);
        editor.on('change', function () {
            textarea.val(editor.getSession().getValue());
        });
    }
});

/* Summernote ----- */
$(function () {
    $('.summernote').summernote({
        width: "99%",
        height: 600,
        tabsize: 2,
        toolbar: [
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['code', ['codeview']],
            ['insert', ['picture', 'link', 'elfinder']],
            ['myplugin', ['aceCodeEditor']],
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']]
        ],
        onImageUpload: function (files, editor, welEditable) {
            sendFile(files[0], editor, welEditable);
        }
    });
});


// Get querystring value
function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}