"use strict";

/************************
 * SETUP
 ************************/
const gulp = require('gulp');
const sourcemaps = require('gulp-sourcemaps');
const notify = require("gulp-notify");
const watch = require('gulp-watch');
const concat = require('gulp-concat');
const rename = require('gulp-rename');
// SASS
const sass = require('gulp-sass');
const sassLint = require('gulp-sass-lint');
const icomoonBuilder = require('gulp-icomoon-builder');
const cleanCss = require('gulp-clean-css');
const autoprefixer = require('gulp-autoprefixer');
// JS
const babel = require('gulp-babel');
const uglify = require('gulp-uglify');
const jshint = require('gulp-jshint'); // doc - http://jshint.com/docs/options/
const jshintStylish = require('jshint-stylish');

/************************
 * CONFIGURATION
 ************************/

let paths = {
  bowerDir: './bower_components',
  npmDir: './node_modules',
};

let includePaths = [
  // Add paths to any sass @imports that you will use from bower_components here
  // Adding paths.bowerDir will allow you to target any bower package folder as an include path
  // for generically named assets
  paths.npmDir + '/foundation-sites/scss',
];

let sassdocSrc = [
  './scss/**/*.scss',
];

let scriptsSrc = [
  // add npm components scripts here
  paths.npmDir + '/svg-injector/svg-injector.js',
  paths.npmDir + '/foundation-sites/js/foundation.core.js',
  paths.npmDir + '/foundation-sites/js/foundation.util.mediaQuery.js',

  './js/src/*.js'
];

let autoprefixerVersions = ['last 2 versions', '> 1%', 'ie 11'];



/************************
 * TASKS
 ************************/

// SCSS tasks
gulp.task('scss-lint', () => {
  gulp.src(sassdocSrc)
    .pipe(sassLint())
    .pipe(sassLint.format(notify()))
});
gulp.task('styles', () => {
  gulp.src(sassdocSrc)
    .pipe(sourcemaps.init())
    .pipe(
      sass({
        includePaths: includePaths
      })
      // Catch any SCSS errors and prevent them from crashing gulp
      .on('error', function (error) {
        console.error('>>> ERROR', error);
        notify().write(error);
        this.emit('end');
      })
    )
    .pipe(autoprefixer(autoprefixerVersions))
    .pipe(sourcemaps.write())
    .pipe(concat('demo.css'))
    .pipe(gulp.dest('./css/'))
});

// JS tasks
gulp.task('js-lint', () => {
  return gulp.src(scriptsSrc)
    .pipe(jshint())
    .pipe(jshint.reporter(jshintStylish))
    // Use gulp-notify as jshint reporter
    .pipe(notify(function (file) {
      if (!file.jshint) return false;
      // Don't show something if success
      if (file.jshint.success) return false;

      var errors = file.jshint.results.map(function (data) {
        if (data.error) {
          return "(" + data.error.line + ':' + data.error.character + ') ' + data.error.reason;
        }
      }).join("\n");
      return file.relative + " (" + file.jshint.results.length + " errors)\n" + errors;
    }));
});

gulp.task('scripts', () => {
  gulp.src(scriptsSrc)
    .pipe(sourcemaps.init())
    .pipe(babel({
      presets: ["es2015", "es2016", "es2017"]
    }))
    .on('error', function(error) {
      console.log('>>> ERROR', error);
      notify().write(error);
      this.emit('end');
    })
    .pipe(concat('draft.js'))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest('./js/dist/'))
});

gulp.task('build-fonts', () => {
  gulp.src('./fonts/icomoon/selection.json')
    .pipe(icomoonBuilder({
      templateType: 'map',
    }))
    .on('error', function (error) {
      console.log(error);
      notify().write(error);
    })

    .pipe(gulp.dest('scss/base/'))
    .on('error', function (error) {
      console.log(error);
      notify().write(error);
    });
});


// Watcher
gulp.task('watch', () => {
  watch(sassdocSrc, () => {
    gulp.start('scss-lint');
    gulp.start('styles');
  });

  watch(scriptsSrc, () => {
    gulp.start('js-lint');
    gulp.start('scripts');
  });

  watch(['./fonts/icomoon/selection.json'], () => {
    gulp.start('build-fonts');
  });
});

gulp.task('default', ['watch']);
