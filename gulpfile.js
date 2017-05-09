'use strict';

const gulp = require('gulp');
const rename = require('gulp-rename');
const cleanCSS = require('gulp-clean-css');
const pjson = require('./package.json');
const config = pjson.config || {};

gulp.task('styles', function () {
    return gulp.src(config.styles)
        .pipe(cleanCSS({restructuring: false}))
        .pipe(rename(function(path) {
            path.extname = '.min' + path.extname;
        }))
        .pipe(gulp.dest(config.targetFolder));
});

gulp.task('default', ['styles']);
