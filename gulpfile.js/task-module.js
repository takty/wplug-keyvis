/**
 *
 * Function for gulp (Module)
 *
 * @author Takuto Yanagida
 * @version 2021-10-14
 *
 */

'use strict';

const gulp = require('gulp');
const $    = require('gulp-load-plugins')({ pattern: ['gulp-*', '!gulp-sass'] });

function makeModuleTask(module, dest = '/src', base = null) {
	const moduleTask = () => gulp.src(`node_modules/${module}`, { base: `node_modules/${base}` })
		.pipe($.plumber())
		.pipe($.ignore.include({ isFile: true }))
		.pipe($.changed(dest, { hasChanged: $.changed.compareContents }))
		.pipe(gulp.dest(dest));
	return moduleTask;
}

exports.makeModuleTask = makeModuleTask;
