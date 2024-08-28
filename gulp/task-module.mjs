/**
 * Function for gulp (Module)
 *
 * @author Takuto Yanagida
 * @version 2024-06-19
 */

import gulp from 'gulp';
import plumber from 'gulp-plumber';
import ignore from 'gulp-ignore';
import changed, { compareContents } from 'gulp-changed';

export function makeModuleTask(module, dest = '/src', base) {
	const moduleTask = () => gulp.src(`node_modules/${module}`, { base: `node_modules/${base}`, encoding: false })
		.pipe(plumber())
		.pipe(ignore.include({ isFile: true }))
		.pipe(changed(dest, { hasChanged: compareContents }))
		.pipe(gulp.dest(dest));
	return moduleTask;
}
