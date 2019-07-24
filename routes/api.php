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
	Route::post('get-accounts-of-client', 'ServiceContractController@getAccountsOfClient');
	Route::post('process-transfer', 'ServiceContractController@processTransfer');
	Route::post('update-contract', 'ServiceContractController@updateContract');
	Route::post('login-user', 'AccessController@loginUser');
	Route::post('insert-contract', 'AccessController@insertContract');
	Route::post('insert-decease-profile', 'AccessController@insertDeceaseProfile');
	Route::post('insert-member-profile', 'AccessController@insertMemberProfile');
	Route::post('cancel-payment', 'ServiceContractController@cancelPayment');
	Route::post('get-member-branch', 'ServiceContractController@getMemberBranch');
	Route::post('remove-charging', 'ServiceContractController@removeCharging');
	Route::post('cancel-purchase-payment', 'ServiceContractController@cancelPurchasePayment');
	Route::post('insert-branch', 'AccessController@insertBranch');
	Route::post('insert-relation', 'AccessController@insertRelation');
	Route::post('insert-location', 'AccessController@insertLocation');

	// Inventory
	Route::post('insert-rr', 'InventoryController@insertRR');
	Route::post('insert-inventory', 'InventoryController@insertInventory');
	Route::post('insert-service', 'InventoryController@insertService');
	Route::post('insert-inclusions', 'InventoryController@insertInclusions');
	Route::post('insert-package', 'InventoryController@insertPackage');
	Route::post('insert-supplier', 'InventoryController@insertSupplier');
	Route::post('get-inclusion-list', 'InventoryController@getInclusionList');
	Route::post('get-items', 'InventoryController@getItemList');
	Route::post('get-service', 'InventoryController@getServiceList');
	Route::post('get-package', 'InventoryController@getPackageListEdit');
	Route::post('get-product-list', 'InventoryController@getProductList');
	Route::post('get-rr-list', 'InventoryController@getRRList');

	Route::post('update-items', 'InventoryController@updateItems');
	Route::post('update-service', 'InventoryController@updateService');
	Route::post('update-supplier', 'InventoryController@updateSupplier');
	Route::post('update-package', 'InventoryController@updatePackage');

	Route::post('delete-supplier', 'InventoryController@deleteSupplier');
	Route::post('delete-inventory', 'InventoryController@deleteInventory');
	Route::post('delete-inc', 'InventoryController@deleteInc');

	// TC Cares
	Route::post('insert-plan-profile', 'CaresController@insertPlanProfile');
	Route::post('update-plan-info', 'CaresController@updatePlanInfo');

	Route::post('insert-plan-package', 'CaresController@insertPlanPackage');
	Route::post('insert-plan-inclusions', 'CaresController@insertPlanInclusions');
	Route::post('get-inclusion-cares', 'CaresController@getInclusionCares');
	Route::post('delete-cares-inc', 'CaresController@deleteCaresInc');
	Route::post('plan-activation', 'CaresController@planActivation');
	
	//
	Route::post('update-password', 'AccessController@updatePassword');
	Route::post('update-relation', 'AccessController@updateRelation');
	Route::post('update-info', 'AccessController@updateInfo');
	Route::post('update-branch', 'AccessController@updateBranch');
	Route::post('update-location', 'AccessController@updateLocation');
	
	Route::post('update-incentives', 'AccessController@updateIncentives');

	Route::post('delete-relation', 'AccessController@deleteRelation');
	Route::post('delete-branch', 'AccessController@deleteBranch');
	Route::post('delete-location', 'AccessController@deleteLocation');

	
	Route::post('post-purchase', 'AccessController@postPurchase');
	Route::post('post-contract', 'AccessController@postContract');	

	Route::post('post-contract', 'AccessController@postContract');
	Route::post('post-billing-payment', 'AccessController@postBillingPayment');
	Route::post('update-relation', 'AccessController@updateRelation');
	Route::post('update-deceased', 'AccessController@updateDeceased');
	Route::post('update-signee', 'AccessController@updateSignee');
	Route::post('update-informant', 'AccessController@updateInformant');
	Route::post('delete-relation', 'AccessController@deleteRelation');;
	Route::post('unpost-contract', 'ServiceContractController@unpostContract');
	Route::post('unpost-sales', 'ServiceContractController@unpostSales');
	Route::post('get-minimal-probabilities', 'AccessController@getMinimalProbabilities');
	Route::post('insert-charging', 'ServiceContractController@insertCharging');
});


	//getPurchaseDetails


//though not suggested, you are welcome to just put GET requests here, request here does not need authentication.
Route::get('get-signee', 'AccessController@getSignee')->middleware('cors');
Route::get('get-informant', 'AccessController@getInformant')->middleware('cors');
Route::get('transfer-item-details', 'ServiceContractController@transferItemDetails')->middleware('cors');
Route::get('get-the-items', 'ServiceContractController@getTheItems')->middleware('cors');
Route::get('get-contract-info', 'ServiceContractController@getContractInfo')->middleware('cors');
Route::get('get-purchase-details', 'ServiceContractController@getPurchaseDetails')->middleware('cors');
Route::get('get-decease-dropdowns', 'AccessController@getDeceaseDropdowns')->middleware('cors');
Route::get('get-deceased', 'AccessController@getDeceased')->middleware('cors');
Route::get('get-sc-locations', 'AccessController@getSCLocations')->middleware('cors');
Route::get('service-contract', 'AccessController@samplepdf')->middleware('cors');
Route::get('get-charging', 'ServiceContractController@getCharging')->middleware('cors');
Route::get('get-minimal-probabilities', 'AccessController@getMinimalProbabilities')->middleware('cors');
Route::get('get-cc-locations', 'AccessController@getCCLocations')->middleware('cors');
Route::get('statement-print', 'AccessController@statementPrint');

Route::get('get-items-services-for-merchandising', 'AccessController@getItemsServicesForMerchandising')->middleware('cors');
Route::get('get-package-item-inclusions', 'AccessController@getPackageItemInclusions')->middleware('cors');
Route::get('get-slcode', 'AccessController@getSLCode')->middleware('cors');
Route::get('get-package-item-inclusions', 'AccessController@getPackageItemInclusions')->middleware('cors');
Route::get('get-inventory-search', 'AccessController@getInventorySearch')->middleware('cors');
Route::get('get-contract-list', 'AccessController@getContractList')->middleware('cors');
Route::get('get-relation', 'AccessController@getRelation')->middleware('cors');
Route::get('get-relation-value', 'AccessController@getRelationValue')->middleware('cors');
Route::get('get-member-info', 'AccessController@getMemberInfo')->middleware('cors');
Route::get('get-branch', 'AccessController@getBranch')->middleware('cors');
Route::get('get-branch-value', 'AccessController@getBranchValue')->middleware('cors');
Route::get('get-driver', 'AccessController@getDriver')->middleware('cors');
Route::get('get-driver-value', 'AccessController@getDriverValue')->middleware('cors');
Route::get('get-billing-of-client', 'AccessController@getBillingOfClient')->middleware('cors');
Route::get('get-accounts', 'AccessController@getAccounts')->middleware('cors');
Route::get('get-details-of-contract', 'ServiceContractController@getDetailsOfContract')->middleware('cors');

// TC Cares
Route::get('get-member-plan-info', 'CaresController@getMemberPlanInfo')->middleware('cors');
Route::get('get-plan-package', 'CaresController@getPlanPackage')->middleware('cors');
Route::get('get-cares-package', 'CaresController@getCaresPackage')->middleware('cors');

// Incentives
Route::get('get-incentives', 'AccessController@getIncentives')->middleware('cors');

// Inventory
Route::get('get-inventory-list', 'InventoryController@getInventoryList')->middleware('cors');
Route::get('get-item-package', 'InventoryController@getItemPackage')->middleware('cors');
Route::get('get-service-package', 'InventoryController@getServicePackage')->middleware('cors');
Route::get('get-supplier', 'InventoryController@getSupplierList')->middleware('cors');
Route::get('get-package-list', 'InventoryController@getPackageList')->middleware('cors');
Route::get('get-add-package-list', 'InventoryController@getAddPackageList')->middleware('cors');
Route::get('get-package-inclusions', 'InventoryController@getPackageInclusions')->middleware('cors');
Route::get('get-supplier-value', 'InventoryController@getSupplierValue')->middleware('cors');
Route::get('get-fun-branch', 'InventoryController@getFunBranch')->middleware('cors');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
