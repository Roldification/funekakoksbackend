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

/*
 * 
 * always be mindful that when putting POST requests via 3rd party application, always register
 * the routes within this group
*/
Route::group(['middleware' => ['apiauth', 'cors']], function() {

	Route::post('insert-user', 'AccessController@insertAccess');
	Route::post('login-user', 'AccessController@loginUser');
	Route::post('login-user', 'AccessController@loginUser');
	Route::post('insert-decease-profile', 'AccessController@insertDeceaseProfile');
});

	//getSCLocations
//though not suggested, you are welcome to just put GET requests here, request here does not need authentication.
Route::get('get-signee', 'AccessController@getSignee')->middleware('cors');
Route::get('get-package-list', 'AccessController@getPackageList')->middleware('cors');
Route::get('get-package-list', 'AccessController@getPackageList')->middleware('cors');
Route::get('get-decease-dropdowns', 'AccessController@getDeceaseDropdowns')->middleware('cors');
Route::get('get-deceased', 'AccessController@getDeceased')->middleware('cors');
Route::get('get-sc-locations', 'AccessController@getSCLocations')->middleware('cors');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
