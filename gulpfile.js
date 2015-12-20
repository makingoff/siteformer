var gulp = require('gulp');
var cssi = require('gulp-cssi');
var livereload = require('gulp-livereload');
var browserify = require('browserify');
var coffeeify = require('coffeeify');
var imagemin = require('gulp-imagemin');
var pngquant = require('imagemin-pngquant');
var rename = require('gulp-rename');
var plumber = require('gulp-plumber');
var gutil = require('gulp-util');
var path = require('path');
var uglify = require('gulp-uglify');
var through2 = require('through2');
var concat = require('gulp-concat');

var requirePaths = [
  "node_modules",
  "modules/GUI/libs",
  "modules/GUI/types",
  "temp/modules/GUI/.compile_templates"
];

gulp.task('watch', ['prepare-css', 'prepare-js', 'prepare-js-lib', 'prepare-images'], function ()
{
  livereload.listen();
  gulp.watch('modules/GUI/**/*.html', ['prepare-js']);
  gulp.watch('modules/GUI/components/**/*.css', ['prepare-css']);
  gulp.watch('modules/GUI/sections/**/*.css', ['prepare-css']);
  gulp.watch('modules/GUI/components/**/*.{js,coffee}', ['prepare-js']);
  gulp.watch('modules/GUI/sections/**/*.{js,coffee}', ['prepare-js']);
  gulp.watch('modules/GUI/types/**/*.{js,coffee}', ['prepare-js']);
  gulp.watch('modules/GUI/libs/**/*.{js,coffee}', ['prepare-js-lib']);
});

gulp.task('prepare-css', function ()
{
  gulp.src([
    '!modules/GUI/components/main/main.css',
    'modules/GUI/components/common/reset.css',
    'modules/GUI/components/common/default.css',
    'modules/GUI/components/**/*.css'
  ])
  .pipe(cssi('main.css', {prefix: '../', saveEnclosure: 2}))
  .pipe(gulp.dest('modules/GUI/dist'))
  .pipe(livereload());
});

process.on('uncaughtException', function (er) {
  console.log(er.toString());
  this.emit('end');
});

var commonBundle = [
  'node_modules/q/q.js',
  'modules/GUI/libs/jquery-plugins.coffee',
  'modules/GUI/libs/view.coffee',
  'modules/GUI/libs/model.coffee',
  'modules/GUI/libs/render.js',
  'modules/GUI/libs/ajax.coffee',
  'modules/GUI/libs/popup.js'
];
var exposeCommonBundle = {
  'node_modules/q/q.js': 'q',
  'modules/GUI/libs/jquery-plugins.coffee': 'jquery-plugins.coffee',
  'modules/GUI/libs/view.coffee': 'view.coffee',
  'modules/GUI/libs/model.coffee': 'model.coffee',
  'modules/GUI/libs/render.js': 'render',
  'modules/GUI/libs/ajax.coffee': 'ajax.coffee',
  'modules/GUI/libs/popup.js': 'popup'
};

function browserified (params)
{
  params || (params = {});
  params.options || (params.options = {});
  params.require || (params.require = {});
  params.external || (params.external = {});
  return through2.obj(function (file, enc, next) {
    var expose = path.basename(file.path);
    if (path.extname(file.path) === '.js') {
      expose = expose.substr(0, expose.length - path.extname(file.path).length);
    }
    var brwsrf = browserify(file.path, params.options);

    var i;
    for (i in params.require) {
      if (params.require.hasOwnProperty.call(params.require, i)) {
        if (path.normalize(__dirname + '/' + i) === file.path) {
          brwsrf.require(file.path, {expose: params.require[i]});
        }
      }
    }

    for (i in params.external) {
      if (params.external.hasOwnProperty.call(params.external, i)) {
        if (path.normalize(__dirname + '/' + i) !== file.path) {
          brwsrf.external(params.external[i]);
        }
      }
    }

    brwsrf.bundle(function (err, res)
    {
      if (err) {
        file.contents = null;
        next(err, file);
      }
      else {
        file.contents = res;
        next(err, file);
      }
    });
  });
}

gulp.task('prepare-js-lib', function ()
{
  return gulp.src(commonBundle)
  .pipe(browserified({
    require: exposeCommonBundle,
    external: exposeCommonBundle,
    options: {
      transform: [
        'coffeeify'
      ],
      debug: true,
      paths: requirePaths
    }
  }))
  .on('error', function (err)
  {
    console.log(err.toString());
    gutil.beep();
    this.emit('end');
  })
  .pipe(rename(function (file)
  {
    file.extname = '.js';
    return file;
  }))
  .pipe(concat('common-bundle.js'))
  // .pipe(uglify())
  .pipe(gulp.dest('modules/GUI/dist'))
  .pipe(livereload());
});

gulp.task('prepare-js', function ()
{
  return gulp.src([
    '!modules/GUI/sections/**/*Model.coffee',
    '!modules/GUI/sections/**/*View.coffee',
    'modules/GUI/sections/**/*.coffee'
  ])
  .pipe(browserified({
    options: {
      transform: [
        'coffeeify'
      ],
      debug: true,
      paths: requirePaths
    },
    external: exposeCommonBundle
  }))
  .on('error', function (err)
  {
    console.log(err.toString());
    gutil.beep();
    this.emit('end');
  })
  .pipe(rename(function (file)
  {
    file.extname = '.js';
    return file;
  }))
  // .pipe(uglify())
  // .external('./a.coffee')
  .pipe(gulp.dest('modules/GUI/dist'))
  .pipe(livereload());
});

gulp.task('prepare-images', function ()
{
  gulp.src([
    'modules/GUI/components/**/*.{png,svg,jpg,jpeg}'
  ])
  .pipe(imagemin({
    progressive: true,
    svgoPlugins: [{removeViewBox: false}],
    use: [pngquant()]
  }))
  .pipe(rename(function (path)
  {
    path.dirname = '';
    return path;
  }))
  .pipe(gulp.dest('modules/GUI/dist'))
  .pipe(livereload());
});
