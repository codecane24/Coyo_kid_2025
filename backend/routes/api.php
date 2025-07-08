<?php

use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'Api\V1', 'prefix' => 'v1', 'middleware' => 'cors'], function () {
    Route::post('login', 'UserController@login');
    Route::post('signup', 'GuestController@signup');
    Route::get('content/{type}', 'GuestController@content');
    Route::post('forgot_password', 'GuestController@forgot_password');
    Route::post('check_ability', 'GuestController@check_ability');
    Route::post('version_checker', 'GuestController@version_checker');

    //            Country Selection apis here
    Route::group(['middleware' => 'ApiTokenChecker'], function () {

       // Route::resource('user', 'UserApiController');

        Route::group(['prefix' => 'user'], function () {
            Route::get('getProfile', 'UserController@getProfile');
            Route::get('logout', 'UserController@logout');
        });

    });
    // Master List API
    Route::get('company/{companyEncruptedId}', 'MasterController@companydata'); // Get company data by encrypted ID
    Route::get('branch', 'MasterController@branchList'); // Get all branches
    Route::get('branch/{id}', 'MasterController@showBranch'); // Get single branch by ID
    Route::get('classesmaster', 'MasterController@classmasterList'); // Get all class masters
    Route::get('section', 'MasterController@sectionList'); // Get all sections (A-Z)

    // ecnrypted ID API
    Route::get('encrypt/{id}', 'MasterController@encryptId'); // Encrypt provided ID
    Route::get('serialNo/{type}', 'MasterController@getSerialNo'); // Decrypt provided ID

     // Role API
    Route::get('role', 'RoleController@index');

    // Permission  API
    Route::resource('permission', 'PermissionApiController');

    // Permission  API
    Route::resource('user', 'UserApiController');

    // Classes API
    Route::resource('classes', 'ClassController'); 
    Route::get('classes/{id}', 'ClassController@show'); // Get single class by ID

    // branch API
    Route::resource('branch', 'BranchApiController');

});


