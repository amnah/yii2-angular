
'use strict';

const importStart = Date.now()
const gutil = require('gulp-util');
gutil.log(`Importing packages ...`);

const path = require('path');
const browserify = require('browserify');
const del = require('del');
const gulp = require('gulp');
const cleanCSS = require('gulp-clean-css');
const concat = require('gulp-concat');
const flatten = require('gulp-flatten');
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const uglify = require('gulp-uglify');
const assign = require('lodash.assign');
const buffer = require('vinyl-buffer');
const source = require('vinyl-source-stream');
const watchify = require('watchify');

const importTime = (Date.now() - importStart) /  1000;
gutil.log(`Done importing (${importTime} seconds)`);


// -------------------------------------------------------------
// File paths
// -------------------------------------------------------------
const assetPath = './assets';
const vendorPath = assetPath + '/vendor';
const distPath = './web/compiled';


// -------------------------------------------------------------
// Browserify + watchify setup - this caches the object for faster compilation
// https://github.com/gulpjs/gulp/blob/master/docs/recipes/fast-browserify-builds-with-watchify.md
// https://github.com/substack/watchify
// -------------------------------------------------------------
const pollInterval = 350;
const customOpts = {
    entries: [`${assetPath}/js/main.js`],
    debug: true
};
const browserifyOpts = assign({}, watchify.args, customOpts);
const watchifyOpts = {poll: pollInterval, delay: pollInterval, ignoreWatch: ['**/node_modules/**', 'vendor/**', '**/*.php']};
const b = browserify(browserifyOpts)
    .transform("babelify", {presets: ['latest']})
    .transform("vueify");

// Make sure to have the NODE_ENV environment variable set to "production" when building for production!
// This strips away unnecessary code (e.g. hot-reload) for smaller bundle size.
// @link https://github.com/vuejs/vueify#building-for-production
process.env.NODE_ENV = 'production';


// -------------------------------------------------------------
// Tasks and events
// -------------------------------------------------------------

// Build task
let buildStart;
gulp.task('default', ['build']);
gulp.task('build', function() {
    buildStart = Date.now();
    buildAll(b.bundle());
});

// Watch task
// the b.on('update') is for js/vue files
// the gulp.watch() is for sass files
let changedFiles;
b.on('update', function(files) {
    changedFiles = files;
    buildAll(b.bundle());
});
gulp.task('watch', ['build'], function() {
    // add watchify to browserify in this task
    watchify(b, watchifyOpts);
    gulp.watch(`${assetPath}/sass/*`, function(e) {
        changedFiles = [e.path];
        buildAll(b.bundle());
    });
});

// File change notification
// (prep message of changed files)
b.on('log', function(msg) {
    // get basename of files
    if (changedFiles && changedFiles.join) {
        for (let i=0; i<changedFiles.length; i++) {
            changedFiles[i] = path.basename(changedFiles[i]);
        }
        changedFiles = changedFiles.join(' / ');
    }
    changedFiles = changedFiles ? `${changedFiles}` : `First run`;
    changedFiles = gutil.colors.cyan(changedFiles) + ' ' + msg;
});

// -------------------------------------------------------------
// Build
// -------------------------------------------------------------

function buildAll(bundle) {

    // clean files first
    del([`${distPath}/*`]).then(function() {

        // -----------------------------------------------------
        // build compiled js files
        const jsStream = bundle
            .on('error', function(err) {
                gutil.log(gutil.colors.red('Browserify error:'), err.message);
            })
            .on('end', function() {
                if (changedFiles) {
                    gutil.log(changedFiles);
                } else if (buildStart) {
                    const buildTime = (Date.now() - buildStart) / 1000;
                    const totalTime = (Date.now() - importStart) / 1000;
                    gutil.log(`Bundle time (${buildTime} seconds)`);
                    gutil.log(`Total time  (${totalTime} seconds)`);
                }
            })
            .pipe(source('app.js'))
            .pipe(buffer());
        jsStream
            .pipe(sourcemaps.init({loadMaps: true})) // loads map from browserify file
            .pipe(sourcemaps.write('./'))
            .pipe(gulp.dest(distPath));
        jsStream
            .pipe(concat(`app.min.js`))
            .pipe(uglify())
            .pipe(sourcemaps.write('./'))
            .pipe(gulp.dest(distPath));

        // -----------------------------------------------------
        // build vendor js files
        gulp.src([`${vendorPath}/**/jquery-*.js`, `${vendorPath}/**/*.js`, `!${vendorPath}/**/*.min.js`])
            .pipe(concat(`vendor.js`))
            .pipe(gulp.dest(distPath));
        gulp.src([`${vendorPath}/**/jquery-*.min.js`, `${vendorPath}/**/*.min.js`])
            .pipe(concat(`vendor.min.js`))
            .pipe(gulp.dest(distPath));

        // -----------------------------------------------------
        // build compiled css files
        const cssStream = gulp.src(`${assetPath}/sass/main.scss`)
            .pipe(sourcemaps.init())
            .pipe(sass().on('error', function(err) {
                gutil.log(gutil.colors.red("Sass error:"), err.message);
            }));
        cssStream
            .pipe(concat(`app.css`))
            .pipe(sourcemaps.write('./'))
            .pipe(gulp.dest(distPath));
        cssStream
            .pipe(concat(`app.min.css`))
            .pipe(cleanCSS(/*{compatibility: 'ie8'}*/))
            .pipe(sourcemaps.write('./'))
            .pipe(gulp.dest(distPath));

        // -----------------------------------------------------
        // build vendor css files
        gulp.src([`${vendorPath}/**/*.css`, `!${vendorPath}/**/*.min.css`])
            .pipe(concat(`vendor.css`))
            .pipe(gulp.dest(distPath));
        gulp.src([`${vendorPath}/**/*.min.css`])
            .pipe(concat(`vendor.min.css`))
            .pipe(gulp.dest(distPath));

        // -----------------------------------------------------
        // copy map files
        gulp.src(`${vendorPath}/**/*.map`)
            .pipe(flatten())
            .pipe(gulp.dest(distPath));
    });
}
