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
	Route::post('insert-decease-profile', 'AccessController@insertDeceaseProfile');
	Route::post('insert-contract', 'AccessController@insertContract');
	Route::post('insert-inventory', 'AccessController@insertInventory');
	Route::post('insert-packagemodal', 'AccessController@insertPackagemodal');
	Route::post('insert-iteminclusions', 'AccessController@insertItemInclusions');
	Route::post('insert-itemreceiving', 'AccessController@insertItemReceiving');
	Route::post('insert-member-profile', 'AccessController@insertMemberProfile');
	Route::post('insert-RTD', 'AccessController@insertRTD');
	Route::post('post-contract', 'AccessController@postContract');

	Route::post('post-purchase', 'AccessController@postPurchase');
	
});


	//getBillingOfClient

//though not suggested, you are welcome to just put GET requests here, request here does not need authentication.
Route::get('get-signee', 'AccessController@getSignee')->middleware('cors');
Route::get('get-package-list', 'AccessController@getPackageList')->middleware('cors');
Route::get('get-decease-dropdowns', 'AccessController@getDeceaseDropdowns')->middleware('cors');
Route::get('get-deceased', 'AccessController@getDeceased')->middleware('cors');
Route::get('get-sc-locations', 'AccessController@getSCLocations')->middleware('cors');
Route::get('sample-pdf', 'AccessController@samplepdf')->middleware('cors');
Route::get('get-minimal-probabilities', 'AccessController@getMinimalProbabilities')->middleware('cors');
Route::get('get-items-services-for-merchandising', 'AccessController@getItemsServicesForMerchandising')->middleware('cors');
Route::get('get-package-item-inclusions', 'AccessController@getPackageItemInclusions')->middleware('cors');
Route::get('get-inventory-search', 'AccessController@getInventorySearch')->middleware('cors');
Route::get('get-RTD', 'AccessController@getRTD')->middleware('cors');
Route::get('get-RTDValue', 'AccessController@getRTDValue')->middleware('cors');
Route::get('get-billing-of-client', 'AccessController@getBillingOfClient')->middleware('cors');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
