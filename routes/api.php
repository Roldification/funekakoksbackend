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
	Route::post('insert-contract', 'AccessController@insertContract');
	Route::post('insert-inventory', 'AccessController@insertInventory');
	Route::post('insert-packagemodal', 'AccessController@insertPackagemodal');
	Route::post('insert-iteminclusions', 'AccessController@insertItemInclusions');
	Route::post('insert-decease-profile', 'AccessController@insertDeceaseProfile');
	Route::post('insert-signee-profile', 'AccessController@insertSigneeProfile');
	Route::post('insert-informant-profile', 'AccessController@insertInformantProfile');
	Route::post('insert-relation', 'AccessController@insertRelation');
	Route::post('post-contract', 'AccessController@postContract');
	Route::post('post-billing-payment', 'AccessController@postBillingPayment');
	Route::post('update-relation', 'AccessController@updateRelation');
	Route::post('update-deceased', 'AccessController@updateDeceased');
	Route::post('update-signee', 'AccessController@updateSignee');
	Route::post('update-informant', 'AccessController@updateInformant');
	Route::post('delete-relation', 'AccessController@deleteRelation');
	Route::post('post-purchase', 'AccessController@postPurchase');
	Route::post('unpost-contract', 'ServiceContractController@unpostContract');
	Route::post('get-minimal-probabilities', 'AccessController@getMinimalProbabilities');
});


	//unpostContract

//though not suggested, you are welcome to just put GET requests here, request here does not need authentication.
Route::get('get-signee', 'AccessController@getSignee')->middleware('cors');
Route::get('get-informant', 'AccessController@getInformant')->middleware('cors');
Route::get('get-package-list', 'AccessController@getPackageList')->middleware('cors');
Route::get('get-decease-dropdowns', 'AccessController@getDeceaseDropdowns')->middleware('cors');
Route::get('get-deceased', 'AccessController@getDeceased')->middleware('cors');
Route::get('get-sc-locations', 'AccessController@getSCLocations')->middleware('cors');
Route::get('sample-pdf', 'AccessController@samplepdf')->middleware('cors');

Route::get('get-items-services-for-merchandising', 'AccessController@getItemsServicesForMerchandising')->middleware('cors');
Route::get('get-package-item-inclusions', 'AccessController@getPackageItemInclusions')->middleware('cors');
Route::get('get-inventory-search', 'AccessController@getInventorySearch')->middleware('cors');
Route::get('get-contract-list', 'AccessController@getContractList')->middleware('cors');
Route::get('get-relation', 'AccessController@getRelation')->middleware('cors');
Route::get('get-relation-value', 'AccessController@getRelationValue')->middleware('cors');
Route::get('get-member-signee', 'AccessController@getMemberSignee')->middleware('cors');
Route::get('get-member-deceased', 'AccessController@getMemberDeceased')->middleware('cors');
Route::get('get-member-informant', 'AccessController@getMemberInformant')->middleware('cors');


Route::get('get-billing-of-client', 'AccessController@getBillingOfClient')->middleware('cors');
Route::get('get-accounts', 'AccessController@getAccounts')->middleware('cors');

Route::get('get-details-of-contract', 'ServiceContractController@getDetailsOfContract')->middleware('cors');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
