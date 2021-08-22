/**
 *
 * Gulpfile
 *
 * @author Takuto Yanagida
 * @version 2021-07-28
 *
 */


'use strict';

const gulp = require('gulp');

const { makeJsTask, makeCssTask, makeSassTask, makeCopyTask, makeModuleTask, makeLocaleTask } = require('./common');

const update = makeModuleTask('gida-slider/dist/**/*', './src/assets', 'gida-slider/dist');

const js_raw   = makeJsTask(['src/**/*.js', '!src/**/*.min.js'], './dist', 'src');
const js_copy  = makeCopyTask('src/**/*.min.js*(.map)', './dist');
const js       = gulp.parallel(js_raw, js_copy);

const css_raw  = makeCssTask(['src/**/*.css', '!src/**/*.min.css'], './dist', 'src');
const css_copy = makeCopyTask('src/**/*.min.css*(.map)', './dist');
const css      = gulp.parallel(css_raw, css_copy);

const sass     = makeSassTask('src/**/*.scss', './dist');
const php      = makeCopyTask('src/**/*.php', './dist');
const locale   = makeLocaleTask('src/languages/**/*.po', './dist', 'src');

const watch = (done) => {
	gulp.watch('src/**/*.js', js);
	gulp.watch('src/**/*.css', css);
	gulp.watch('src/**/*.scss', sass);
	gulp.watch('src/**/*.php', php);
	gulp.watch('src/**/*.po', locale);
	done();
};

exports.update  = update;
exports.build   = gulp.parallel(js, css, sass, php, locale);
exports.default = gulp.series(exports.build, watch);
