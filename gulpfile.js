
var gulp = require('gulp');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var ngAnnotate = require('gulp-ng-annotate');
var cssnano = require('gulp-cssnano');
var flatten = require('gulp-flatten');
var del = require('del');

// -------------------------------------------------------------
// Variables
// -------------------------------------------------------------
var dest = 'web/compiled';
var cssDir = 'web/css/**/*.css';
var jsDir = 'web/js/**/*.js';
var vendorDir = 'web/vendor';

// -------------------------------------------------------------
// Default task
// -------------------------------------------------------------
gulp.task('default', ['watch', 'build']);

// -------------------------------------------------------------
// Build task
// -------------------------------------------------------------
gulp.task('build', ['clean'], function() {

    // build asset files
    gulp.src(cssDir)
        // compiled
        .pipe(concat(`site.compiled.css`))
        .pipe(gulp.dest(dest))
        // compiled min
        .pipe(concat(`site.compiled.min.css`))
        .pipe(cssnano())
        .pipe(gulp.dest(dest));

    gulp.src(jsDir)
        // compiled
        .pipe(concat(`app.compiled.js`))
        .pipe(ngAnnotate())
        .pipe(gulp.dest(dest))
        // compiled.min
        .pipe(concat(`app.compiled.min.js`))
        .pipe(uglify())
        .pipe(gulp.dest(dest));

    // build vendor files
    gulp.src([`${vendorDir}/**/*.css`, `!${vendorDir}/**/*.min.css`])
        .pipe(concat(`vendor.compiled.css`))
        .pipe(gulp.dest(dest));
    gulp.src([`${vendorDir}/**/*.min.css`])
        .pipe(concat(`vendor.compiled.min.css`))
        .pipe(gulp.dest(dest));
    gulp.src([`${vendorDir}/**/angular.js`, `${vendorDir}/**/*.js`, `!${vendorDir}/**/*.min.js`])
        .pipe(concat(`vendor.compiled.js`))
        .pipe(gulp.dest(dest));
    gulp.src([`${vendorDir}/**/angular.min.js`, `${vendorDir}/**/*.min.js`])
        .pipe(concat(`vendor.compiled.min.js`))
        .pipe(gulp.dest(dest));

    // copy map files
    gulp.src(`${vendorDir}/**/*.map`)
        .pipe(flatten())
        .pipe(gulp.dest(dest));
});

gulp.task('clean', function() {
    del([`${dest}/*`]);
});

// -------------------------------------------------------------
// Watch task
// -------------------------------------------------------------
gulp.task('watch', function () {
    gulp.watch([cssDir, jsDir], ['build'])
});