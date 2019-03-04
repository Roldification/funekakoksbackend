<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['middleware' => ['apiauth', 'cors']], function() {
	// uses 'auth' middleware plus all middleware from $middlewareGroups['web']
	Route::post('insert-user', 'AccessController@insertAccess');
	Route::post('login-user', 'AccessController@loginUser');
});


Route::get('get-user', 'AccessController@getUser')->middleware('apiauth');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
