
var gulp = require('gulp');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var ngAnnotate = require('gulp-ng-annotate');
var minifyCss = require('gulp-minify-css');
var rev = require('gulp-rev');
var merge = require('merge-stream');
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
// Make sure revision happens after assets are built
// https://github.com/gulpjs/gulp/blob/master/docs/recipes/running-tasks-in-series.md
// -------------------------------------------------------------
gulp.task('build', ['buildAssets', 'buildVendor'], function() {

    // make revision
    gulp.src([`${dest}/*`])
        .pipe(rev())
        .pipe(gulp.dest(dest))
        .pipe(rev.manifest())
        .pipe(gulp.dest(dest));
});

gulp.task('buildAssets', ['clean'], function() {

    var css = gulp.src(cssDir)
        // compiled
        .pipe(concat(`site.compiled.css`))
        .pipe(gulp.dest(dest))
        // compiled min
        .pipe(concat(`site.compiled.min.css`))
        .pipe(minifyCss())
        .pipe(gulp.dest(dest));

    var js = gulp.src(jsDir)
        // compiled
        .pipe(concat(`app.compiled.js`))
        .pipe(ngAnnotate())
        .pipe(gulp.dest(dest))
        // compiled.min
        .pipe(concat(`app.compiled.min.js`))
        .pipe(uglify())
        .pipe(gulp.dest(dest));

    return merge(css, js);
});

gulp.task('buildVendor', ['clean'], function() {
    var cssVendor = gulp.src([`${vendorDir}/**/*.css`, `!${vendorDir}/**/*.min.css`])
        .pipe(concat(`vendor.compiled.css`))
        .pipe(gulp.dest(dest));
    var cssVendorMin = gulp.src([`${vendorDir}/**/*.min.css`])
        .pipe(concat(`vendor.compiled.min.css`))
        .pipe(gulp.dest(dest));
    var jsVendor = gulp.src([`${vendorDir}/**/angular.js`, `${vendorDir}/**/*.js`, `!${vendorDir}/**/*.min.js`])
        .pipe(concat(`vendor.compiled.js`))
        .pipe(gulp.dest(dest));
    var jsVendorMin = gulp.src([`${vendorDir}/**/angular.min.js`, `${vendorDir}/**/*.min.js`])
        .pipe(concat(`vendor.compiled.min.js`))
        .pipe(gulp.dest(dest));

    return merge(cssVendor, cssVendorMin, jsVendor, jsVendorMin);
});

gulp.task('clean', function() {
    del([`${dest}/*`]);
});

// -------------------------------------------------------------
// Watch task
// -------------------------------------------------------------
gulp.task('watch', function () {
    gulp.watch('web/js/**/*.js', ['build'])
});