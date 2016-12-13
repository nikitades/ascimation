<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
 |-------------------------------------------------------------------------
 | Public routes:
 |-------------------------------------------------------------------------
 */

Route::get('/', 'PagesController@home');

/*
 * Auth routes
 */

Route::get('login', 'Auth\LoginController@showLoginForm');
Route::post('login', 'Auth\LoginController@login');
Route::get('logout', 'Auth\LoginController@logout');
Route::get('register', 'Auth\RegisterController@showRegistrationForm');
Route::post('register', 'Auth\RegisterController@register');

/*
 * News:
 */

Route::get('news/{url}', 'NewsController@show');

/*
 |-------------------------------------------------------------------------
 | Ajax routes:
 |-------------------------------------------------------------------------
 */

Route::group(['middleware' => ['ajax', 'admin'], 'prefix' => 'ajax/admin'], function () {
    Route::match(['get', 'post'], '{controller}/{method}', 'AjaxController@run');
});

Route::group(['middleware' => ['ajax'], 'prefix' => 'ajax'], function () {
    Route::match(['get', 'post'], '{controller}/{method}', 'AjaxController@run');
});

/*
 |-------------------------------------------------------------------------
 | Admin routes:
 |-------------------------------------------------------------------------
 */

Route::group(['middleware' => ['auth', 'admin']], function () {

    /*
     * Version route:
     */

    Route::get('/version', function () {
        return view('admin.version');
    });

    /*
     * General admin routes:
     */

    Route::get('/admin', 'AdminController@index');
    Route::get('/admin/{entity}', 'AdminController@entity');
    Route::get('/admin/{entity}/new', 'AdminController@create');
    Route::get('/admin/{entity}/{id}/edit', 'AdminController@edit');

    /*
    // Password Reset Routes...
    $this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
    $this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    $this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
    $this->post('password/reset', 'Auth\ResetPasswordController@reset');
     */

    /*
     * Pages:
     */

    Route::delete('pages/{id}', 'PagesController@destroy');
    Route::put('pages/{id}', 'PagesController@update');
    Route::post('pages', 'PagesController@store');
    Route::post('pages/{id}/{direction}', 'PagesController@move');
    Route::delete('pages/{id}/{direction}', 'PagesController@move');

    /*
     * Views:
     */

    Route::delete('views/{id}', 'ViewsController@destroy');
    Route::put('views/{id}', 'ViewsController@update');
    Route::post('views', 'ViewsController@store');

    /*
     * FAQs:
     */

    Route::delete('faq/{id}', 'FaqController@destroy');
    Route::put('faq/{id}', 'FaqController@update');
    Route::post('faq', 'FaqController@store');

    /*
     * News:
     */

    Route::put('news/{id}', 'NewsController@update');
    Route::post('news', 'NewsController@store');
    Route::get('news/{id}/delete', 'NewsController@destroy');
    Route::get('news/{id}/hot', 'NewsController@makeHot');

    /*
     * Ascii:
     */

    Route::get('ascii/{id}/delete', 'AsciiController@destroy');

    /*
     * Texts:
     */

    Route::delete('texts/{id}', 'TextsController@destroy');
    Route::put('texts/{id}', 'TextsController@update');
    Route::post('texts', 'TextsController@store');

    /*
     * Settings:
     */

    Route::delete('settings/{id}', 'SettingsController@destroy');
    Route::put('settings/{id}', 'SettingsController@update');
    Route::post('settings', 'SettingsController@store');

    /*
     * Services:
     */

    Route::delete('services/{id}', 'ServicesController@destroy');
    Route::put('services/{id}', 'ServicesController@update');
    Route::post('services', 'ServicesController@store');

    /*
     * Service categories:
     */

    Route::delete('service_categories/{id}', 'ServiceCategoriesController@destroy');
    Route::put('service_categories/{id}', 'ServiceCategoriesController@update');
    Route::post('service_categories', 'ServiceCategoriesController@store');

    /*
     * Backups:
     */

    Route::get('backups/all', 'BackupsController@all');
    Route::get('backups/base', 'BackupsController@base');
    Route::get('backups/files', 'BackupsController@files');
    Route::get('backups/{id}/restore', 'BackupsController@restore');
    Route::get('backups/{id}/delete', 'BackupsController@destroy');

    /*
     * Moving:
     */

    Route::get('admin/move/{entity}/{id}/{direction}', 'AdminController@move');

    /*
     * Deleting images:
     */

    Route::get('images/delete/{id}', 'ImagesController@destroy');

    /*
     * Deleting files:
     */

    Route::get('files/delete/{name}', 'FilesController@destroy');

});

/*
 * App routes:
 */

Route::get('/latest', 'AsciiController@enlist');
Route::get('/latest/{page}', 'AsciiController@enlist')->where('page', '[0-9]+');
Route::get('/embed/{ascii_uuid}', 'AsciiController@embed');
Route::get('/cleanse', 'AsciiController@cleanse');
Route::get('/{ascii_uuid}', 'AsciiController@show');

/*
 * Pages:
 */

Route::get('/pages/{whole_url}', 'PagesController@show')->where('whole_url', '(.*)');