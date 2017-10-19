// Utils
var gulp = require('gulp');

// JavaScript
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');

// Misc
const sourcemaps = require('gulp-sourcemaps');

// --------------------------------------------------------
// Configuration
// --------------------------------------------------------

// Paths
const paths = {
  build: `${__dirname}/public`,
  src: `${__dirname}/src/js`,
  modules: `${__dirname}/node_modules`
};

// Vendor JavaScript files
const vendorModules = [
  `${paths.modules}/govuk_template_jinja/assets/javascripts/govuk-template.js`,
  `${paths.modules}/jquery/dist/jquery.min.js`,
  `${paths.modules}/govuk_frontend_toolkit/javascripts/govuk/show-hide-content.js`
];

// Application JavaScript files
const applicationModules = [
  `${paths.src}/vendor/govuk_elements/details.polyfill.js`,
  `${paths.src}/app/back-link.js`,
  `${paths.src}/app/scroll-to-hash.js`,
  `${paths.src}/app/application.js`
];

// --------------------------------------------------------
// Tasks
// --------------------------------------------------------

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

// Watch
function watch() {
  gulp.watch(applicationModules, appScripts);
}

// Task sets
const compile = gulp.series(gulp.parallel(vendorScripts,appScripts));

gulp.task('build', gulp.series(compile));
gulp.task('dev', gulp.series(compile, watch));
