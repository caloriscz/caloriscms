var gulp = require('gulp');
var concat = require('gulp-concat');

gulp.task("scripts-a", function () {
    gulp.src([
        './bower_components/jquery/dist/jquery.min.js',
        './bower_components/jquery-ui/jquery-ui.min.js',
        './bower_components/tether/dist/js/tether.min.js',
        './bower_components/popper.js/dist/umd/popper.min.js',
        './bower_components/bootstrap/dist/js/bootstrap.js',
        './bower_components/pace/pace.min.js',
        './bower_components/dropzone/dist/min/dropzone.min.js',
        './node_modules/summernote/dist/summernote.min.js',
        './node_modules/summernote/lang/summernote-cs-CZ.js',
        './bower_components/elfinder/js/elfinder.min.js',
        './bower_components/elfinder/i18n/elfinder.cs.js',
        './bower_components/jquery-colorbox/jquery.colorbox-min.js',
        './bower_components/nette-forms/src/assets/netteForms.js',
        './bower_components/nette.ajax.js/nette.ajax.js',
        './bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js',
        './bower_components/bootstrap-datepicker/js/locales/bootstrap-datepicker.cs.js',
        './bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
        './bower_components/ublaboo-datagrid/assets/dist/datagrid.min.js',
        './bower_components/ublaboo-datagrid/assets/dist/datagrid-spinners.min.js',
        './bower_components/jquery-autocomplete/jquery.autocomplete.js',
    ])
        .pipe(concat('all-back.js'))
        .pipe(gulp.dest('./www/js/'));
});

gulp.task("scripts-f", function () {
    gulp.src([
        './bower_components/jquery/dist/jquery.min.js',
        './bower_components/popper.js/dist/umd/popper.min.js',
        './bower_components/bootstrap/dist/js/bootstrap.min.js',
        './bower_components/jquery-ui/jquery-ui.min.js',
        './bower_components/jquery-colorbox/jquery.colorbox-min.js',
        './bower_components/nette-forms/src/assets/netteForms.js',
        './bower_components/nette.ajax.js/nette.ajax.js'
    ])
        .pipe(concat('all-front.js'))
        .pipe(gulp.dest('./www/js/'));
});