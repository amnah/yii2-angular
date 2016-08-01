
'use strict';

var path = require('path');
var gulp = require('gulp');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var cssnano = require('gulp-cssnano');
var flatten = require('gulp-flatten');
var del = require('del');
var browserify = require('browserify');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var watchify = require('watchify');
var gutil = require('gulp-util');
var sourcemaps = require('gulp-sourcemaps');
var assign = require('lodash.assign');


// -------------------------------------------------------------
// Browserify + watchify setup - this caches the object for faster compilation
// https://github.com/gulpjs/gulp/blob/master/docs/recipes/fast-browserify-builds-with-watchify.md
// https://github.com/substack/watchify
// -------------------------------------------------------------
var pollInterval = 250;
var customOpts = {
    entries: ['./web/js/main.js'],
    debug: true
};
var browserifyOpts = assign({}, watchify.args, customOpts);
var watchifyOpts = {poll: pollInterval, delay: pollInterval};
var b = watchify(browserify(browserifyOpts), watchifyOpts);
b.transform("babelify", {presets: ["es2015"]});
b.transform("vueify");

// "Make sure to have the NODE_ENV environment variable set to "production" when building for production! This strips away unnecessary code (e.g. hot-reload) for smaller bundle size"
// @link https://github.com/vuejs/vueify#building-for-production
process.env.NODE_ENV = 'production';


// -------------------------------------------------------------
// Tasks and events
// -------------------------------------------------------------

// Build task
// create bundle directly for build (not the cached object above)
gulp.task('build', function() {
    var bundle = browserify(browserifyOpts)
        .transform("babelify", {presets: ["es2015"]})
        .transform("vueify")
        .bundle();
    buildAll(bundle);
});

// Watch task and file updates
gulp.task('default', ['watch']);
gulp.task('watch', updateBundle);
b.on('update', updateBundle);
function updateBundle(files) {
    // get basename of files
    if (files && files.join) {
        for (var i=0; i<files.length; i++) {
            files[i] = path.basename(files[i]);
        }
        theFiles = files.join();
    }
    buildAll(b.bundle());
}

// File change notification
var theFiles;
b.on('log', function(msg) {
    if (!theFiles) {
        theFiles = 'First run';
    } else {
        theFiles = `${theFiles} changed`;
    }
    gutil.log(gutil.colors.cyan(theFiles), msg);
});


// -------------------------------------------------------------
// Functions
// -------------------------------------------------------------

function buildAll(bundle) {

    var dest = 'web/compiled';
    var vendorDir = 'web/vendor';

    // clean files first
    del([`${dest}/*`]).then(function() {

       // build app js files
        var stream = bundle
            //.on('error', gutil.log.bind(gutil, 'Browserify Error'))
            .on('error', function(err) {
                gutil.log(gutil.colors.red("Browserify error:"), err.message);
            })
            .pipe(source('app.js'))
            .pipe(buffer());
        stream
            .pipe(sourcemaps.init({loadMaps: true})) // loads map from browserify file
            .pipe(sourcemaps.write('./')) // writes .map file
            .pipe(gulp.dest(dest));
        stream
            .pipe(concat(`app.min.js`))
            .pipe(sourcemaps.init({loadMaps: true})) // loads map from browserify file
            .pipe(uglify())
            .pipe(sourcemaps.write('./')) // writes .map file
            .pipe(gulp.dest(dest));

        // build vendor js files
        gulp.src([`${vendorDir}/**/jquery-*.js`, `${vendorDir}/**/*.js`, `!${vendorDir}/**/*.min.js`])
            .pipe(concat(`vendor.js`))
            .pipe(gulp.dest(dest));
        gulp.src([`${vendorDir}/**/jquery-*.min.js`, `${vendorDir}/**/*.min.js`])
            .pipe(concat(`vendor.min.js`))
            .pipe(gulp.dest(dest));

        // copy map files
        gulp.src(`${vendorDir}/**/*.map`)
            .pipe(flatten())
            .pipe(gulp.dest(dest)); 

        // build app css files
        gulp.src('web/css/**/*.css')
            .pipe(concat(`app.css`))
            .pipe(gulp.dest(dest))
            .pipe(concat(`app.min.css`))
            .pipe(cssnano())
            .pipe(gulp.dest(dest));

        // build vendor css files
        gulp.src([`${vendorDir}/**/*.css`, `!${vendorDir}/**/*.min.css`])
            .pipe(concat(`vendor.css`))
            .pipe(gulp.dest(dest));
        gulp.src([`${vendorDir}/**/*.min.css`])
            .pipe(concat(`vendor.min.css`))
            .pipe(gulp.dest(dest));
    });
}
