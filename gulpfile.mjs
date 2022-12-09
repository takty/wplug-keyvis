/**
 * Gulpfile
 *
 * @author Takuto Yanagida
 * @version 2022-12-09
 */

import gulp from 'gulp';

import { makeJsTask } from './gulp/task-js.mjs';
import { makeSassTask } from './gulp/task-sass.mjs';
import { makeCopyTask } from './gulp/task-copy.mjs';
import { makeModuleTask } from './gulp/task-module.mjs';
import { makeLocaleTask }  from './gulp/task-locale.mjs';

const js_raw  = makeJsTask(['src/**/*.js', '!src/**/*.min.js'], './dist', 'src');
const js_copy = makeCopyTask('src/**/*.min.js*(.map)', './dist', 'src');
const js      = gulp.parallel(js_raw, js_copy);

const css_copy = makeCopyTask('src/**/*.min.css*(.map)', './dist', 'src');
const css      = gulp.parallel(css_copy);

const sass   = makeSassTask('src/**/*.scss', './dist', 'src');
const php    = makeCopyTask('src/**/*.php', './dist', 'src');
const locale = makeLocaleTask('src/languages/**/*.po', './dist', 'src');

const watch = done => {
	gulp.watch('src/**/*.js', js);
	gulp.watch('src/**/*.css', css);
	gulp.watch('src/**/*.scss', sass);
	gulp.watch('src/**/*.php', php);
	gulp.watch('src/**/*.po', locale);
	done();
};

export const update = makeModuleTask('gida-slider/dist/**/*', './src/assets', 'gida-slider/dist');
export const build  = gulp.parallel(js, css, sass, php, locale);
export default gulp.series(build, watch);
