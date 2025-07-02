<?php

use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'Api\V1', 'prefix' => 'v1', 'middleware' => 'cors'], function () {
    Route::post('login', 'GuestController@login');
    Route::post('signup', 'GuestController@signup');
    Route::post('forgot_password', 'GuestController@forgot_password');
    Route::get('content/{type}', 'GuestController@content');
    Route::post('forgot_password', 'GuestController@forgot_password');
    Route::post('check_ability', 'GuestController@check_ability');
    Route::post('version_checker', 'GuestController@version_checker');

    //            Country Selection apis here
    Route::group(['middleware' => 'ApiTokenChecker'], function () {
        Route::group(['prefix' => 'user'], function () {
            Route::get('getProfile', 'UserController@getProfile');
            Route::get('logout', 'UserController@logout');
        });

    });
    // Master List API
    Route::get('classesmaster', 'MasterController@classmasterList'); // Get all class masters
    Route::get('section', 'MasterController@sectionList'); // Get all sections (A-Z)

     // Role API
    Route::get('role', 'RoleController@index');
    Route::get('role/{id}', 'RoleController@show');

    // Classes API
    Route::get('classes', 'ClassController@index'); // Get all classes
    Route::get('classes/{id}', 'ClassController@show'); // Get single class by ID

    // branch API
    Route::get('branch', 'BranchController@index'); // Get all section
    Route::get('branch/{id}', 'BranchController@show'); // Get single section by ID

});


