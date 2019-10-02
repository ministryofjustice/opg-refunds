// Utils
const gulp = require('gulp');
const del = require('del');
const jasmineBrowser = require('gulp-jasmine-browser');

// JavaScript
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');

// CSS
const cleanCSS = require('gulp-clean-css');
const sass = require('gulp-sass');

// Misc
const sourcemaps = require('gulp-sourcemaps');

// --------------------------------------------------------
// Configuration
// --------------------------------------------------------

// Paths
const paths = {
  build: `${__dirname}/public/assets`,
  src: `${__dirname}/src`,
  modules: `${__dirname}/node_modules`,
  specs: `${__dirname}/test/js`,
};

// GOVUK assets
const govUKTemplateJinjaModule = `${paths.modules}/govuk_template_jinja/assets/**/*`;
const govukTemplateImages = `${paths.modules}/govuk_frontend_toolkit/images/**/*`;

// Vendor JavaScript files
const vendorModules = [
  `${paths.modules}/govuk_template_jinja/assets/javascripts/govuk-template.js`,
  `${paths.modules}/jquery/dist/jquery.min.js`,
  `${paths.modules}/govuk_frontend_toolkit/javascripts/govuk/show-hide-content.js`,
  `${paths.modules}/govuk_frontend_toolkit/javascripts/govuk/details.polyfill.js`
];

// Application JavaScript files
const applicationModules = [
  `${paths.src}/js/app/back-link.js`,
  `${paths.src}/js/app/scroll-to-hash.js`,
  `${paths.src}/js/app/print-link.js`,
  `${paths.src}/js/app/single-use.js`,
  `${paths.src}/js/app/cookie-functions.js`,
  `${paths.src}/js/app/application.js`,
];

// Analytics JavaScript files
const analyticsModules = [
  `${paths.src}/js/app/analytics/moj.ga-tracker.js`,
  `${paths.modules}/govuk_frontend_toolkit/javascripts/govuk/analytics/govuk-tracker.js`,
  `${paths.modules}/govuk_frontend_toolkit/javascripts/govuk/analytics/analytics.js`,
  `${paths.modules}/govuk_frontend_toolkit/javascripts/govuk/analytics/external-link-tracker.js`,
  `${paths.src}/js/app/analytics/form-error-tracker.js`,
  `${paths.src}/js/app/analytics/analytics.js`
];

const specsModules = [
  `${paths.build}/javascripts/vendor.js`,
  `${paths.build}/javascripts/custom-analytics.js`,
  `${paths.specs}/app/**/*.spec.js`
];

const imagesPaths = [
  `${paths.src}/images/**/*.png`,
  `${paths.src}/images/**/*.svg`
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
		.pipe(gulp.dest(`${paths.build}/govuk_template`));
}

// GOVUK Frontent Toolkit images
function govuk_frontend_toolkit_images() {
	return gulp.src(govukTemplateImages)
		.pipe(gulp.dest(`${paths.build}/images`));
}

// Scripts
function vendorScripts() {
  return gulp.src(vendorModules)
    .pipe(sourcemaps.init())
    .pipe(concat('vendor.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(`${paths.build}/javascripts`));
}

function appScripts() {
  return gulp.src(applicationModules)
    .pipe(sourcemaps.init())
    .pipe(concat('app.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(`${paths.build}/javascripts`));
}

function analyticScripts() {
  return gulp.src(analyticsModules)
    .pipe(sourcemaps.init())
    .pipe(concat('custom-analytics.js'))
    .pipe(uglify())
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(`${paths.build}/javascripts`));
}

// Images
function images() {
  return gulp.src(imagesPaths)
    .pipe(gulp.dest(`${paths.build}/images`));
}

// Styles
function styles() {
  return gulp.src(stylesPath)
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(cleanCSS())
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(`${paths.build}/styles`));
}

function test() {
  return gulp.src(specsModules)
    .pipe(jasmineBrowser.specRunner())
    .pipe(jasmineBrowser.server({port: 8888}));
}

// Watch
function watch() {
  gulp.watch(vendorModules, vendorScripts);
  gulp.watch(applicationModules, appScripts);
  gulp.watch(analyticsModules, analyticScripts);
}

// Task sets
const compile = gulp.series(clean,
  gulp.parallel(vendorScripts,appScripts,analyticScripts,govuk_template,images,styles,govuk_frontend_toolkit_images)
);

gulp.task('build', gulp.series(compile));
gulp.task('dev', gulp.series(compile, watch));
gulp.task('test', gulp.series(analyticScripts, test))