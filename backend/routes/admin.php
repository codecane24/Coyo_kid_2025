<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\LogsController;
use App\Http\Controllers\Admin\CompanyController;


Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
});

Route::group(['middleware' => 'guest', 'namespace' => 'General'], function () {
    Route::post('login', 'GeneralController@login')->name('login_post');
    Route::get('login', 'GeneralController@Panel_Login')->name('login');
    Route::get('forgot_password', 'GeneralController@Panel_Pass_Forget')->name('forgot_password');
    Route::post('forgot_password', 'GeneralController@ForgetPassword')->name('forgot_password_post');
    Route::get('select-branch', 'GeneralController@SelectBranch')->name('select_branch');
    Route::post('update-password/{id}', 'GeneralController@UpdatePassword')->name('update-password')->name('user.update_password');
    Route::get('logout', 'GeneralController@logout')->name('logout');
});

Route::group(['middleware' => 'Is_Admin'], function () {
    Route::get('/', 'General\GeneralController@Admin_dashboard')->name('dashboard');
    
  
    Route::get('/totalusers', 'General\GeneralController@totalusers')->name('totalusers');
    Route::get('/profile', 'General\GeneralController@get_profile')->name('profile');
    Route::post('/profile', 'General\GeneralController@post_profile')->name('post_profile');
    Route::get('/update_password', 'General\GeneralController@get_update_password')->name('get_update_password');
    Route::post('/update_password', 'General\GeneralController@update_password')->name('update_password');
    Route::get('/site_settings', 'General\GeneralController@get_site_settings')->name('get_site_settings');
    Route::post('/site_settings', 'General\GeneralController@site_settings')->name('site_settings');
    Route::get('/get-role-permissions/{roleId}', [RoleController::class, 'getPermissionsForRole'])
        ->name('get-role-permissions');

    Route::group(['namespace' => 'Admin'], function () {
       
            });
});
