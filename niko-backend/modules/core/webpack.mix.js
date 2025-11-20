let mix = require('laravel-mix');
let fs = require('fs');

let deleteFolderRecursive = function (path) {
    if (fs.existsSync(path)) {
        fs.readdirSync(path).forEach(function (file, index) {
            let curPath = path + "/" + file;
            if (fs.lstatSync(curPath).isDirectory()) { // recurse
                deleteFolderRecursive(curPath);
            } else { // delete file
                fs.unlinkSync(curPath);
            }
        });
        fs.rmdirSync(path);
    }
};

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
mix.setPublicPath('./');
mix
    .js('resources/assets/src/js/entry.js', 'resources/assets/dist/js/entry.js')
    .sass('resources/assets/src/plugins/bootstrap/css/bootstrap.scss', 'resources/assets/src/css/temp/bootstrap.css')
    .sass('resources/assets/src/plugins/select2/css/select2.scss', 'resources/assets/src/css/temp/select2.css')
    .less('resources/assets/src/css/less/style.less', 'resources/assets/src/css/temp/less.css')
    .combine(
        [
            // temp
            'resources/assets/src/css/temp/bootstrap.css',
            'resources/assets/src/css/temp/select2.css',
            'resources/assets/src/css/temp/less.css',

            // plugins
            'resources/assets/src/plugins/bootstrap-toggle/bootstrap-toggle.css',
            'resources/assets/src/plugins/daterangepicker/daterangepicker.css',
            'resources/assets/src/plugins/dropzone/dropzone.css',
            'resources/assets/src/plugins/fancybox/jquery.fancybox.css',
            'resources/assets/src/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css',
            'resources/assets/src/plugins/jquery-datetimepicker/jquery.datetimepicker.css',
            'resources/assets/src/plugins/jquery-ui-1.12.1/jquery-ui.css',
            'resources/assets/src/plugins/multi-select/css/multi-select.css',
            'resources/assets/src/plugins/sweetalert2/sweetalert2.css',
            'resources/assets/src/plugins/jquery-minicolors/jquery.minicolors.css'
        ],
        'resources/assets/dist/css/style.css'
    )
    .copy('resources/assets/src/css/icons', 'resources/assets/dist/css/icons')
    .copy('resources/assets/src/plugins', 'resources/assets/dist/plugins')
    .copy('resources/assets/src/static', 'resources/assets/dist/static')
    .copy('resources/assets/src/plugins/jquery-ui-1.12.1/images', 'resources/assets/dist/css/images')
    .then(() => {
        try {
            deleteFolderRecursive('./resources/assets/src/css/temp');
            fs.unlinkSync('./mix-manifest.json');
        } catch (e) {
        }
    });
