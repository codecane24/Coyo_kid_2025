<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ClassFeesController;

Route::group(['namespace' => 'Api\V1', 'prefix' => 'v1', 'middleware' => 'cors'], function () {
    Route::post('login', 'UserController@login');
    Route::post('signup', 'GuestController@signup');
    Route::get('content/{type}', 'GuestController@content');
    Route::post('forgot_password', 'GuestController@forgot_password');
    Route::post('check_ability', 'GuestController@check_ability');
    Route::post('version_checker', 'GuestController@version_checker');

     //Route::put('user/{token}', 'UserApiController@update');  
    Route::resource('user', 'UserApiController')->except(['update', 'show']);
    Route::get('user/{encryptedID}', 'UserApiController@show');    // encryptedID for show
    Route::put('user/{encryptedID}', 'UserApiController@update');  

    //  Country Selection apis here
    Route::group(['middleware' => 'ApiTokenChecker'], function () {
        Route::resource('mytest','MytestController');
        Route::resource('user1', 'UserApiController');
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
    Route::get('module-group', 'MasterController@ModuleGroupList'); // Get all permission groups
    Route::get('house', 'MasterController@HouseList'); // Get all houses
    Route::get('board', 'MasterController@BoardList'); // Get all boards
    Route::get('medium', 'MasterController@MediumList'); // Get all mediums
    Route::get('subject', 'MasterController@SubjectList'); // Get all subjects
    
    // ecnrypted ID API
    Route::get('encrypt/{id}', 'MasterController@encryptId'); // Encrypt provided ID
    Route::get('serialnumber/{type}', 'MasterController@getSerialNo'); // Decrypt provided ID

     // Role API
    Route::get('role', 'RoleController@index');

    // Permission  API
    Route::resource('permission', 'PermissionApiController');

    // Permission  API
    //Route::resource('user', 'UserApiController');

    // Classes API
    Route::resource('classes', 'ClassController'); 
    Route::get('classes/{id}', 'ClassController@show'); // Get single class by ID

    // branch API
    Route::resource('branch', 'BranchApiController');

    // Students API
    
    Route::get('student/{id}/data', 'StudentController@getStudentData')
        ->where('id', '[0-9]+')
        ->name('students.data');
    Route::apiResource('student', 'StudentController');

    // Teacher API
    Route::apiResource('teacher', 'TeacherController');

    //=== Fees API 
    Route::apiResource('fees-group','FeesGroupControler');
    Route::apiResource('fees-type','FeesTypeController');
    Route::apiResource('fees-master','FeesMasterController');
    Route::apiResource('assign-fees','FeesAssignController');
    Route::apiResource('student-fees', 'StudentFeesController');
    Route::apiResource('academic-year', 'AcademicYearController');

    //=== Admission Inquiry API
    Route::apiResource('admission-inquiry', 'AdmissionInquiryController');

    //=== Class Fees API
    Route::apiResource('class-fees', ClassFeesController::class);
    Route::get('class-fees/class/{classid}', [ClassFeesController::class, 'showClassFees'])
        ->where('classid', '[0-9]+')
        ->name('class-fees.class');
    Route::match(['get', 'post'], 'class-fees/classwise', [ClassFeesController::class, 'classwiseFees']);
});


