/**
 * Function for gulp (Module)
 *
 * @author Takuto Yanagida
 * @version 2022-09-16
 */

import gulp from 'gulp';
import plumber from 'gulp-plumber';
import ignore from 'gulp-ignore';
import changed from 'gulp-changed';

export function makeModuleTask(module, dest = '/src', base) {
	const moduleTask = () => gulp.src(`node_modules/${module}`, { base: `node_modules/${base}` })
		.pipe(plumber())
		.pipe(ignore.include({ isFile: true }))
		.pipe(changed(dest, { hasChanged: changed.compareContents }))
		.pipe(gulp.dest(dest));
	return moduleTask;
}
