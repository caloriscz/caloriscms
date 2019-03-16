let gulp = require('gulp');
let concat = require('gulp-concat');

gulp.task("scripts-a", done => {
    gulp.src([
        './node_modules/jquery/dist/jquery.min.js',
        './node_modules/tether/dist/js/tether.min.js',
        './node_modules/popper.js/dist/umd/popper.min.js',
        './node_modules/bootstrap/dist/js/bootstrap.js',
        './node_modules/dropzone/dist/min/dropzone.min.js',
        './node_modules/summernote/dist/summernote.min.js',
        './node_modules/summernote/dist/summernote-bs4.min.js',
        './node_modules/summernote/lang/summernote-cs-CZ.js',
        './node_modules/jquery-ui-dist/jquery-ui.min.js',
        './vendor/studio-42/elfinder/js/elfinder.min.js',
        './vendor/studio-42/elfinder/js/i18n/elfinder.cs.js',
        './node_modules/jquery-colorbox/jquery.colorbox-min.js',
        './node_modules/nette-forms/src/assets/netteForms.js',
        './node_modules/nette.ajax.js/nette.ajax.js',
        './node_modules/flatpickr/dist/flatpickr.js',
        './node_modules/flatpickr/dist/l10n/cs.js',
        './node_modules/jstree/dist/jstree.min.js',
        './node_modules/moment/min/moment.min.js'
    ])
        .pipe(concat('all-back.js'))
        .pipe(gulp.dest('./www/js/'));

    done();
});

gulp.task("scripts-f", done => {
    gulp.src([
        './node_modules/jquery/dist/jquery.min.js',
        './node_modules/popper.js/dist/umd/popper.min.js',
        './node_modules/bootstrap/dist/js/bootstrap.min.js',
        './node_modules/jquery-ui-dist/jquery-ui.min.js',
        './node_modules/jquery-colorbox/jquery.colorbox-min.js',
        './node_modules/nette-forms/src/assets/netteForms.js',
        './node_modules/nette.ajax.js/nette.ajax.js'
    ])
        .pipe(concat('all-front.js'))
        .pipe(gulp.dest('./www/js/'));

    done();
});