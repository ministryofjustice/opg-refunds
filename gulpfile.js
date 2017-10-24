// Utils
var gulp = require('gulp');

// JavaScript
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');

// CSS
var cleanCSS = require('gulp-clean-css');
var sass = require('gulp-sass');

// Misc
const sourcemaps = require('gulp-sourcemaps');

// --------------------------------------------------------
// Configuration
// --------------------------------------------------------

// Paths
const paths = {
  build: `${__dirname}/public`,
  src: `${__dirname}/src`,
  modules: `${__dirname}/node_modules`
};

// GOVUK assets
const govUKTemplateJinjaModule = `${paths.modules}/govuk_template_jinja/assets/**/*`;
const govukTemplateImages = `${paths.modules}/govuk_frontend_toolkit/images/**/*`;

// Vendor JavaScript files
const vendorModules = [
  `${paths.modules}/govuk_template_jinja/assets/javascripts/govuk-template.js`,
  `${paths.modules}/jquery/dist/jquery.min.js`,
  `${paths.modules}/govuk_frontend_toolkit/javascripts/govuk/show-hide-content.js`
];

// Application JavaScript files
const applicationModules = [
  `${paths.src}/js/vendor/govuk_elements/details.polyfill.js`,
  `${paths.src}/js/app/back-link.js`,
  `${paths.src}/js/app/scroll-to-hash.js`,
  `${paths.src}/js/app/application.js`
];

const stylesPath = `${paths.src}/scss/**/*.scss`;

// --------------------------------------------------------
// Tasks
// --------------------------------------------------------

// Clean
function clean() {
  return del(`${paths.build}`);
}

// GOVUK Template
function govuk_template() {
	return gulp.src(govUKTemplateJinjaModule)
		.pipe(gulp.dest(`${paths.build}/assets/govuk_template`));
}

// GOVUK Frontent Toolkit images
function govuk_frontend_toolkit_images() {
	return gulp.src(govukTemplateImages)
		.pipe(gulp.dest(`${paths.build}/assets/images`));
}

// Scripts
function vendorScripts() {
  return gulp.src(vendorModules)
    .pipe(sourcemaps.init())
    .pipe(concat('vendor.js'))
    .pipe(uglify({
        compress: { ie8: true },
        mangle: { ie8: true },
        output: { ie8: true }
      }))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(`${paths.build}/assets/javascripts`));
}

function appScripts() {
  return gulp.src(applicationModules)
    .pipe(sourcemaps.init())
    .pipe(concat('app.js'))
    .pipe(uglify({
      compress: { ie8: true },
      mangle: { ie8: true },
      output: { ie8: true }
    }))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(`${paths.build}/assets/javascripts`));
}

// Styles
function styles() {
  return gulp.src(stylesPath)
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(cleanCSS())
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(`${paths.build}/assets/styles`));
}

// Watch
function watch() {
  gulp.watch(applicationModules, appScripts);
  gulp.watch(vendorScripts, vendorModules);
}

// Task sets
const compile = gulp.series(clean, 
  gulp.parallel(vendorScripts,appScripts,govuk_template,govuk_frontend_toolkit_images)
);

gulp.task('build', gulp.series(compile));
gulp.task('dev', gulp.series(compile, watch));