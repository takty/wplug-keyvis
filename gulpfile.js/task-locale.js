/**
 *
 * Function for gulp (Locale)
 *
 * @author Takuto Yanagida
 * @version 2021-10-14
 *
 */

'use strict';

const gulp = require('gulp');
const $    = require('gulp-load-plugins')({ pattern: ['gulp-*', '!gulp-sass'] });

const gp = require('gettext-parser');

function makeLocaleTask(src, dest = '/dest', base = null) {
	const localeTask = () => gulp.src(src, { base: base })
		.pipe($.plumber())
		.on('data', f => {
			f.path = f.path.replace(/\.po$/, '.mo');
			return f.contents = gp.mo.compile(gp.po.parse(f.contents));
		})
		.pipe($.changed(dest, { hasChanged: $.changed.compareContents, extension: '.mo' }))
		.pipe(gulp.dest(dest));
	return localeTask;
}

exports.makeLocaleTask = makeLocaleTask;
