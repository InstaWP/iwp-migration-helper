const gulp = require('gulp'),
    del = require('del'),
    zip = require('gulp-zip');
const {series, parallel} = require('gulp');
const readline = require('readline');
const replace = require('gulp-replace');

const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});
let originalValues = {};

function updateFile(cb) {
    let valuesToUpdate = [
        {
            key: 'INSTAWP_API_KEY',
            prompt: 'Enter the API key : ',
            placeholder: ''
        },
        {
            key: 'INSTAWP_ENVIRONMENT',
            prompt: 'Enter the InstaWP Environment (Default: app) : ',
            placeholder: 'app'
        },
        {
            key: 'INSTAWP_MIGRATE_ENDPOINT',
            prompt: 'Enter the migrate endpoint (Default: migrate): ',
            placeholder: 'migrate'
        }
    ];

    let currentIndex = 0;

    function updateValue() {
        let valueInfo = valuesToUpdate[currentIndex];
        rl.question(valueInfo.prompt, function (content) {
            content = content || valueInfo.placeholder;

            if (!originalValues[valueInfo.key]) {
                originalValues[valueInfo.key] = valueInfo.placeholder;
            }

            gulp.src('iwp-hosting-migration.php')
                .pipe(replace(new RegExp(`define\\(\\s*'${valueInfo.key}'\\s*,\\s*'[^']*'\\s*\\)`, 'g'), `define( '${valueInfo.key}', '${content}' )`))
                .pipe(gulp.dest('./'))
                .on('end', function () {
                    currentIndex++;
                    if (currentIndex < valuesToUpdate.length) {
                        updateValue();
                    } else {
                        rl.close();
                        cb();
                    }
                });
        });
    }

    updateValue();
}

function revertReplacements(cb) {
    let promises = [];

    for (let key in originalValues) {
        let originalValue = originalValues[key];
        promises.push(
            new Promise((resolve, reject) => {
                gulp.src('iwp-hosting-migration.php')
                    .pipe(replace(new RegExp(`define\\(\\s*'${key}'\\s*,\\s*'[^']*'\\s*\\)`), `define( '${key}', '${originalValue}' )`))
                    .pipe(gulp.dest('./'))
                    .on('end', resolve)
                    .on('error', reject);
            })
        );
    }

    Promise.all(promises)
        .then(() => {
            console.log('Reverted replacements successfully.');
            cb();
        })
        .catch((error) => {
            console.error('Error reverting replacements:', error);
            cb();
        });
}

var zipPath = [
    './',
    './**',
    './**',
    '!./.git/**',
    '!./**/.gitignore',
    '!./**/*.md',
    '!./**/*.scss',
    '!./**/tailwind-input.css',
    '!./**/composer.json',
    '!./**/auth.json',
    '!./**/.gitignore',
    '!./**/LICENSE',
    '!./**/phpunit*',
    '!./tests/**',
    '!./node_modules/**',
    '!./build/**',
    '!./gulpfile.js',
    '!./package.json',
    '!./package-lock.json',
    '!./composer.json',
    '!./composer.lock',
    '!./phpcs.xml',
    '!./LICENSE',
    '!./README.md',
    '!./vendor/bin/**',
    '!./vendor/**/*.txt',
    '!./includes/file-manager/instawp*.php',
    '!./includes/database-manager/instawp*.php',
];

function clean_files() {
    let cleanPath = ['../iwp-hosting-migration.zip'];
    return del(cleanPath, {force: true});
}

function create_zip() {
    return gulp.src(zipPath, {base: '../'})
        .pipe(zip('iwp-hosting-migration.zip'))
        .pipe(gulp.dest('../'))
}

exports.default = series(updateFile, clean_files, create_zip, revertReplacements);