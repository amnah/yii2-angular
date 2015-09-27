
var gulp = require('gulp');
var watch = require('gulp-watch');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var ngAnnotate = require('gulp-ng-annotate');

// -------------------------------------------------------------
// Default task
// -------------------------------------------------------------
gulp.task('default', ['build', 'watch']);

// -------------------------------------------------------------
// Build task
// -------------------------------------------------------------
gulp.task('build', build);

// -------------------------------------------------------------
// Watch task
// -------------------------------------------------------------
gulp.task('watch', function () {
    watch('web/js/**/*.js', {verbose: true, usePolling: true, interval: 2000}, build);
});




function build() {
    var dest = 'web/compiled';
    var date = new Date();
    var processTime = date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds();
    //var timestamp = '';//date.getTime()/1000;

    // build compiled js files
    console.log(`[${processTime}] Building js files`);
    gulp.src(['web/js/**/*.js', '!web/js/vendor/**/*.js'])
        //.pipe(concat(`app-${timestamp}.compiled.js`))
        .pipe(concat(`app.compiled.js`))
        .pipe(ngAnnotate())
        .pipe(gulp.dest(dest))
        .pipe(concat(`app.compiled.min.js`))
        .pipe(uglify())
        .pipe(gulp.dest(dest));
}