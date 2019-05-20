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
	Route::post('insert-member-profile', 'AccessController@insertMemberProfile');
	Route::post('insert-relation', 'AccessController@insertRelation');

	Route::post('insert-inventory', 'AccessController@insertInventory');
	Route::post('insert-service', 'AccessController@insertService');

	Route::post('insert-branch', 'AccessController@insertBranch');
	Route::post('insert-driver', 'AccessController@insertDriver');
	Route::post('insert-embalmer', 'AccessController@insertEmbalmer');
	Route::post('insert-planprofile', 'AccessController@insertPlanProfile');
	Route::post('insert-inclusions', 'AccessController@insertInclusions');

	Route::post('insert-supplier', 'AccessController@insertSupplier');

	Route::post('update-relation', 'AccessController@updateRelation');
	Route::post('update-info', 'AccessController@updateInfo');
	Route::post('update-branch', 'AccessController@updateBranch');
	Route::post('update-driver', 'AccessController@updateDriver');
	Route::post('update-embalmer', 'AccessController@updateEmbalmer');
	
	Route::post('update-items', 'AccessController@updateItems');
	Route::post('update-service', 'AccessController@updateService');
	Route::post('update-supplier', 'AccessController@updateSupplier');

	Route::post('delete-relation', 'AccessController@deleteRelation');
	Route::post('delete-branch', 'AccessController@deleteBranch');
	Route::post('delete-driver', 'AccessController@deleteDriver');
	Route::post('delete-embalmer', 'AccessController@deleteEmbalmer');
	Route::post('delete-inventory', 'AccessController@deleteInventory');
	Route::post('delete-supplier', 'AccessController@deleteSupplier');
	
	Route::post('get-product-list', 'AccessController@getProductList');
	Route::post('get-items', 'AccessController@getItemList');
	Route::post('get-service', 'AccessController@getServiceList');

	Route::post('post-purchase', 'AccessController@postPurchase');
	Route::post('post-contract', 'AccessController@postContract');	
});


//though not suggested, you are welcome to just put GET requests here, request here does not need authentication.
Route::get('get-signee', 'AccessController@getSignee')->middleware('cors');
Route::get('get-informant', 'AccessController@getInformant')->middleware('cors');
Route::get('get-package-list', 'AccessController@getPackageList')->middleware('cors');
Route::get('get-decease-dropdowns', 'AccessController@getDeceaseDropdowns')->middleware('cors');
Route::get('get-deceased', 'AccessController@getDeceased')->middleware('cors');
Route::get('get-sc-locations', 'AccessController@getSCLocations')->middleware('cors');
Route::get('sample-pdf', 'AccessController@samplepdf')->middleware('cors');
Route::get('get-minimal-probabilities', 'AccessController@getMinimalProbabilities')->middleware('cors');

Route::get('get-items-services-for-merchandising', 'AccessController@getItemsServicesForMerchandising')->middleware('cors');
Route::get('get-package-item-inclusions', 'AccessController@getPackageItemInclusions')->middleware('cors');
Route::get('get-inventory-list', 'AccessController@getInventoryList')->middleware('cors');
Route::get('get-item-package', 'AccessController@getItemPackage')->middleware('cors');
Route::get('get-service-package', 'AccessController@getServicePackage')->middleware('cors');
Route::get('get-package-inclusions', 'AccessController@getPackageInclusions')->middleware('cors');
Route::get('get-supplier', 'AccessController@getSupplierList')->middleware('cors');
Route::get('get-supplier-value', 'AccessController@getSupplierValue')->middleware('cors');

Route::get('get-slcode', 'AccessController@getSLCode')->middleware('cors');

Route::get('get-relation', 'AccessController@getRelation')->middleware('cors');
Route::get('get-relation-value', 'AccessController@getRelationValue')->middleware('cors');
Route::get('get-member-info', 'AccessController@getMemberInfo')->middleware('cors');
Route::get('get-branch', 'AccessController@getBranch')->middleware('cors');
Route::get('get-branch-value', 'AccessController@getBranchValue')->middleware('cors');
Route::get('get-driver', 'AccessController@getDriver')->middleware('cors');
Route::get('get-driver-value', 'AccessController@getDriverValue')->middleware('cors');
Route::get('get-embalmer', 'AccessController@getEmbalmer')->middleware('cors');
Route::get('get-embalmer-value', 'AccessController@getEmbalmerValue')->middleware('cors');


Route::get('get-billing-of-client', 'AccessController@getBillingOfClient')->middleware('cors');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
