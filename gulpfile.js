var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {

    /*
     Admin styles & scripts:
     */

    mix.sass('admin.scss', 'resources/assets/css/admin.css');
    mix.sass('sunny.scss', 'resources/assets/css/sunny.css');
    mix.sass('modal.scss', 'resources/assets/css/modal.css');

    mix.styles([
        'libs/bootstrap.min.css',
        'libs/bootstrap-theme.min.css',
        'libs/select2.min.css',
        'libs/jquery-ui.structure.min.css',
        'libs/jquery-ui.min.css',
        'libs/jquery-ui.theme.min.css',
        'sunny.css',
        'modal.css',
        'admin.css',
    ], 'public/css/admin.css', 'resources/assets/css');

    mix.scripts([
        'libs/jquery.min.js',
        'libs/bootstrap.min.js',
        'libs/select2.full.min.js',
        'libs/jquery-ui.min.js',
        'libs/simple-popup.min.js',
        'libs/jquery.mjs.nestedSortable.js',
        'engine.js',
        'admin.js'
    ], 'public/js/admin.js', 'resources/assets/js');

    /*
     Global app styles & scripts:
     */

    mix.sass('app.scss', 'resources/assets/css/app.css');
    mix.sass('regularPage.scss', 'public/css/regularPage.css');
    mix.sass('homePage.scss', 'public/css/homePage.css');
    mix.sass('asciiPage.scss', 'public/css/asciiPage.css');
    mix.sass('login.scss', 'public/css/login.css');
    mix.sass('admin-pages-list.scss', 'public/css/admin-pages-list.css');

    mix.styles([
        'libs/bootstrap.min.css',
        'libs/bootstrap-theme.min.css',
        'libs/jquery-ui.structure.min.css',
        'libs/jquery-ui.min.css',
        'libs/jquery-ui.theme.min.css',
        'sunny.css',
        'modal.css',
        'app.css'
    ], 'public/css/app.css', 'resources/assets/css');

    mix.scripts([
        'libs/jquery.min.js',
        'libs/jquery-ui.min.js',
        'libs/bootstrap.min.js',
        'libs/simple-popup.min.js',
        'libs/jquery.mjs.nestedSortable.js',
        'engine.js',
        'app.js'
    ], 'public/js/app.js', 'resources/assets/js');

    /*
     *  Version all the files:
     */

    mix.version([
        'public/css/admin.css',
        'public/js/admin.js',
        'public/css/app.css',
        'public/css/regularPage.css',
        'public/css/login.css',
        'public/css/admin-pages-list.css',
        'public/js/app.js'
    ]);
});