const gulp = require('gulp');
const terser = require('gulp-terser');
const rename = require('gulp-rename');

// Source paths
const paths = {
  adminJs: {
    src: 'MpesaPaywallPro/admin/js/temp/*.js',
    dest: 'MpesaPaywallPro/admin/js/dist'
  },
  publicJs: {
    src: 'MpesaPaywallPro/public/js/temp/*.js',
    dest: 'MpesaPaywallPro/public/js/dist'
  }
};

// Minify admin JS
function minifyAdminJs() {
  return gulp.src(paths.adminJs.src)
    .pipe(terser())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.adminJs.dest));
}

// Minify public JS
function minifyPublicJs() {
  return gulp.src(paths.publicJs.src)
    .pipe(terser())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.publicJs.dest));
}

// Combined JS task
const minifyJs = gulp.parallel(minifyAdminJs, minifyPublicJs);

// Watch for changes
function watchJs() {
  gulp.watch(paths.adminJs.src, minifyAdminJs);
  gulp.watch(paths.publicJs.src, minifyPublicJs);
}

// Default task
exports.default = minifyJs;
exports.js = minifyJs;
exports.watch = gulp.series(minifyJs, watchJs);
exports.adminJs = minifyAdminJs;
exports.publicJs = minifyPublicJs;
