/**
 *
 * Function for gulp (Module)
 *
 * @author Takuto Yanagida
 * @version 2022-03-23
 *
 */

'use strict';

const gulp = require('gulp');
const $    = require('gulp-load-plugins')({ pattern: ['gulp-plumber', 'gulp-ignore', 'gulp-changed'] });

function makeModuleTask(module, dest = '/src', base) {
	const moduleTask = () => gulp.src(`node_modules/${module}`, { base: `node_modules/${base}` })
		.pipe($.plumber())
		.pipe($.ignore.include({ isFile: true }))
		.pipe($.changed(dest, { hasChanged: $.changed.compareContents }))
		.pipe(gulp.dest(dest));
	return moduleTask;
}

exports.makeModuleTask = makeModuleTask;
