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