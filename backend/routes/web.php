<?php

use App\Http\Controllers\frontend\UserDashboardController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\frontend\UserOrderController;
use App\Http\Controllers\frontend\ProductController;
use App\Http\Controllers\General\GeneralController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get("clear-cache","General\GeneralController@ClearCache");


    Route::get('/', function () {
            return redirect()->route('home');
    });
    Route::get('/home', 'frontend\HomeNewController@newtheme')->name('home');
    Route::get('login', 'General\GeneralController@Panel_Login')->name('login');

  Route::group(['as'=>'user.', 'prefix' => 'user'], function() {
    // Bill routes
  });
  