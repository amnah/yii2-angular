
var gulp = require('gulp');
var watch = require('gulp-watch');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var ngAnnotate = require('gulp-ng-annotate');
var minifyCss = require('gulp-minify-css');
var rev = require('gulp-rev');
var del = require('del');

var dest = 'web/compiled';

// -------------------------------------------------------------
// Default task
// -------------------------------------------------------------
gulp.task('default', ['watch']);

// -------------------------------------------------------------
// Build task
// Make sure revision happens after assets are built
// https://github.com/gulpjs/gulp/blob/master/docs/recipes/running-tasks-in-series.md
// -------------------------------------------------------------
gulp.task('build', ['buildAssets'], function() {

    // make revision
    gulp.src([`${dest}/*`])
        .pipe(rev())
        .pipe(gulp.dest(dest))
        .pipe(rev.manifest())
        .pipe(gulp.dest(dest));
});

gulp.task('buildAssets', function() {

    // delete existing files
    del([`${dest}/*`]);

    // build css files
    gulp.src(['web/css/**/*.css'])
        // compiled
        .pipe(concat(`site.compiled.css`))
        .pipe(gulp.dest(dest))
        // compiled min
        .pipe(concat(`site.compiled.min.css`))
        .pipe(minifyCss())
        .pipe(gulp.dest(dest));

    // build js files
    return gulp.src(['web/js/**/*.js'])
        // compiled
        .pipe(concat(`app.compiled.js`))
        .pipe(ngAnnotate())
        .pipe(gulp.dest(dest))
        // compiled.min
        .pipe(concat(`app.compiled.min.js`))
        .pipe(uglify())
        .pipe(gulp.dest(dest));
});

// -------------------------------------------------------------
// Watch task
// -------------------------------------------------------------
gulp.task('watch', ['build'], function () {
    gulp.watch('web/js/**/*.js', ['build'])
});