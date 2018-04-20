var gulp = require('gulp');
var concat = require('gulp-concat');

gulp.task("scripts-a", function () {
    gulp.src([
        './node_modules/jquery/dist/jquery.min.js',
        './node_modules/tether/dist/js/tether.min.js',
        './node_modules/popper.js/dist/umd/popper.min.js',
        './node_modules/bootstrap/dist/js/bootstrap.js',
        './bower_components/dropzone/dist/min/dropzone.min.js',
        './node_modules/summernote/dist/summernote.min.js',
        './node_modules/summernote/lang/summernote-cs-CZ.js',
        './node_modules/jquery-ui-dist/jquery-ui.min.js',
        './bower_components/elfinder/js/elfinder.min.js',
        './bower_components/elfinder/i18n/elfinder.cs.js',
        './bower_components/jquery-colorbox/jquery.colorbox-min.js',
        './bower_components/nette-forms/src/assets/netteForms.js',
        './bower_components/nette.ajax.js/nette.ajax.js',
        './bower_components/ublaboo-datagrid/assets/dist/datagrid.min.js',
        './bower_components/ublaboo-datagrid/assets/dist/datagrid-spinners.min.js',
        './node_modules/moment/moment.min.js',
        './node_modules/flatpickr/dist/flatpickr.js',
        './node_modules/flatpickr/dist/l10n/cs.js',
        './node_modules/jstree/dist/jstree.min.js',
        './node_modules/moment/min/moment.min.js',
        './bower_components/fullcalendar/dist/fullcalendar.min.js',
        './bower_components/fullcalendar/dist/lang/YOUR_LANGUAGE.js'
    ])
        .pipe(concat('all-back.js'))
        .pipe(gulp.dest('./www/js/'));
});

gulp.task("scripts-f", function () {
    gulp.src([
        './node_modules/jquery/dist/jquery.min.js',
        './node_modules/popper.js/dist/umd/popper.min.js',
        './node_modules/bootstrap/dist/js/bootstrap.min.js',
        './node_modules/jquery-ui-dist/jquery-ui.min.js',
        './bower_components/jquery-colorbox/jquery.colorbox-min.js',
        './bower_components/nette-forms/src/assets/netteForms.js',
        './bower_components/nette.ajax.js/nette.ajax.js'
    ])
        .pipe(concat('all-front.js'))
        .pipe(gulp.dest('./www/js/'));
});