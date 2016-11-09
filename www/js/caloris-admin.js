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