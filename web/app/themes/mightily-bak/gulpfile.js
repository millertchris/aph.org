var gulp = require('gulp'),
	sass = require('gulp-sass'),
	browserSync = require('browser-sync'),
	autoprefixer = require('gulp-autoprefixer'),
	uglify = require('gulp-uglify'),
	header = require('gulp-header'),
	rename = require('gulp-rename'),
	cssnano = require('gulp-cssnano'),
	concat = require('gulp-concat'),
	sourcemaps = require('gulp-sourcemaps'),
	package = require('./package.json'),
	fileinclude = require('gulp-file-include'),
	log = require('fancy-log');

var banner = [
	'/*!\n' +
	' * <%= package.name %>\n' +
	' * <%= package.title %>\n' +
	' * <%= package.url %>\n' +
	' * @author <%= package.author %>\n' +
	' * @version <%= package.version %>\n' +
	' * Copyright ' + new Date().getFullYear() + '. <%= package.license %> licensed.\n' +
	' */',
	'\n'
].join('');

var cssFiles = ['src/scss/style.scss'],
	cssDest = 'app/assets/css';

var jsFiles = ['src/js/scripts.js', 'src/js/admin-scripts.js', 'src/js/validatePo.js', 'src/js/inviteShopper.js', 'src/js/manageAddresses.js', 'src/js/orderStatus.js'],
	jsDest = 'app/assets/js';

gulp.task('css', function (done) {
	gulp.src(cssFiles)
		.pipe(sourcemaps.init())
		.pipe(sass({
			errLogToConsole: true
		}))
		.pipe(autoprefixer('last 4 version'))
		.pipe(gulp.dest(cssDest))
		.pipe(cssnano())
		.pipe(rename({
			suffix: '.min'
		}))
		.pipe(header(banner, {
			package: package
		}))
		.pipe(sourcemaps.write(''))
		.pipe(gulp.dest(cssDest))
		.pipe(browserSync.reload({
			stream: true
		}));
		done();
});

gulp.task('js', function (done) {
	gulp.src(jsFiles)
		.pipe(sourcemaps.init())
		// .pipe(concat('scripts.js'))
		.pipe(fileinclude({
			prefix: '@@',
			basepath: 'src/'
		}))
		.pipe(header(banner, {
			package: package
		}))
		.pipe(gulp.dest(jsDest))
		.pipe(uglify())
		.pipe(header(banner, {
			package: package
		}))
		.pipe(rename({
			suffix: '.min'
		}))
		.pipe(sourcemaps.write(''))
		.pipe(gulp.dest(jsDest))
		.pipe(browserSync.reload({
			stream: true,
			once: true
		}));
		done();
});

gulp.task('html', function (done) {
	gulp.src(['src/pages/*.html'])
		.pipe(fileinclude({
			prefix: '@@',
			basepath: 'src/'
		}))
		.pipe(sourcemaps.write(''))
		.pipe(gulp.dest('app/'))
		.pipe(browserSync.reload({ stream: true }));
		done();
});

gulp.task('browser-sync', function (done) {
	browserSync.init(null, {
		server: {
			baseDir: "app"
		}
	});
	gulp.watch("src/scss/**/*.scss", gulp.series('css'));
	gulp.watch("src/js/**/*.js", gulp.series('js'));
	gulp.watch("src/**/*.html", gulp.series('html'));
	gulp.watch("app/*.html", gulp.series('bs-reload'));
	done();
});

gulp.task('bs-reload', function () {
	browserSync.reload();
});

gulp.task('default', gulp.series('css', 'js', 'html', 'browser-sync'), function () {

});

// gulp.task('default', ['css', 'js', 'html', 'browser-sync'], function () {
// 	gulp.watch("src/scss/**/*.scss", ['css']);
// 	gulp.watch("src/js/**/*.js", ['js']);
// 	gulp.watch("src/**/*.html", ['html']);
// 	gulp.watch("app/*.html", ['bs-reload']);
// });
// Deploy test