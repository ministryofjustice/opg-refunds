var UglifyJS = require("uglify-js");
var fs = require('fs');

var appFiles = [
  "vendor/govuk_elements/details.polyfill.js",
  "application.js",
];
var appFilesRoot = __dirname + "/app/";
appFiles = appFiles.map(function(file) { return appFilesRoot + file });

var vendorFiles = [
  "jquery/dist/jquery.min.js",
  "govuk_template_jinja/assets/javascripts/govuk-template.js",
  "govuk_frontend_toolkit/javascripts/govuk/show-hide-content.js",
];
var vendorFilesRoot = __dirname + '/../../node_modules/';
vendorFiles = vendorFiles.map(function(file) { return vendorFilesRoot + file });

// Write
var vendorResult = UglifyJS.minify(vendorFiles);
fs.writeFileSync(__dirname + '/../../public/assets/main.js', vendorResult.code);

var appResult = UglifyJS.minify(appFiles);
fs.writeFileSync(__dirname + '/../../public/assets/application.js', appResult.code);