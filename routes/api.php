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
	Route::post('reset-charging', 'ServiceContractController@resetCharging');
	Route::post('process-deduction', 'ServiceContractController@processDeduction');
	Route::post('update-contract', 'ServiceContractController@updateContract');
	Route::post('login-user', 'AccessController@loginUser');
	Route::post('insert-contract', 'AccessController@insertContract');
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

	Route::post('get-chapel-inclusion-list', 'InventoryController@getChapelInclusionList');


	Route::post('insert-inclusions-inv', 'InventoryController@insertInclusionsInv');
	Route::post('insert-inclusions-serv', 'InventoryController@insertInclusionsServ');
	
	Route::post('insert-chapel-package', 'InventoryController@insertChapelPackage');
	Route::post('insert-chapel-inc-inv', 'InventoryController@insertChapelIncInv');
	Route::post('insert-chapel-inc-serv', 'InventoryController@insertChapelIncServ');
	Route::post('insert-chapel-inclusions', 'InventoryController@insertChapelInclusions');

	Route::post('get-chapel-list-edit', 'InventoryController@getChapelListEdit');

	Route::post('update-items', 'InventoryController@updateItems');
	Route::post('update-service', 'InventoryController@updateService');
	Route::post('update-supplier', 'InventoryController@updateSupplier');
	Route::post('update-package', 'InventoryController@updatePackage');

	Route::post('update-chapel-package', 'InventoryController@updateChapelPackage');

	Route::post('delete-supplier', 'InventoryController@deleteSupplier');
	Route::post('delete-inventory', 'InventoryController@deleteInventory');
	Route::post('delete-inc', 'InventoryController@deleteInc');

	Route::post('delete-chapel-inc', 'InventoryController@deleteChapelInc');

	// TC Cares
	Route::post('insert-plan-contract', 'CaresController@insertPlanContract');
	Route::post('insert-plan-profile', 'CaresController@insertPlanProfile');
	Route::post('update-plan', 'CaresController@updatePlan');
	Route::post('insert-plan-package', 'CaresController@insertPlanPackage');
	Route::post('insert-plan-inclusions', 'CaresController@insertPlanInclusions');
	Route::post('get-inclusion-cares', 'CaresController@getInclusionCares');
	Route::post('delete-cares-inc', 'CaresController@deleteCaresInc');
	Route::post('plan-activation', 'CaresController@planActivation');
	Route::post('get-packages', 'CaresController@getPackages');
	Route::post('get-plan-inclusions', 'CaresController@getPlanInclusions');
	Route::post('get-active-package-data', 'CaresController@getActivePackageData');
	Route::post('update-cares-info', 'CaresController@updateCaresInfo');
	Route::post('get-plan-transactions', 'CaresController@getPlanTransactions');
	Route::post('get-plan-transactions-two', 'CaresController@getPlanTransactionsTwo');
	Route::post('get-plan-transactions-three', 'CaresController@getPlanTransactionsThree');
	Route::post('get-plan-inclusions-details', 'CaresController@getPlanInclusionsDetails');
	Route::post('update-cares-package', 'CaresController@updateCaresPackage');
	Route::post('update-pay', 'CaresController@updatePay');
	Route::post('withdraw-account', 'CaresController@withdrawAccount');
	Route::post('insert-plan-inclusions-items', 'CaresController@insertPlanInclusionsItems');
	Route::post('get-plan-ledger', 'CaresController@getPlanLedger');

	Route::post('update-password', 'AccessController@updatePassword');
	Route::post('update-relation', 'AccessController@updateRelation');
	Route::post('update-info', 'AccessController@updateInfo');
	Route::post('update-branch', 'AccessController@updateBranch');
	Route::post('update-location', 'AccessController@updateLocation');
	Route::post('update-incentives', 'AccessController@updateIncentives');
	Route::post('update-incentives-details', 'AccessController@updateIncDetails');

	Route::post('insert-mci', 'AccessController@insertMCI');

	Route::post('update-user-detail-info', 'AccessController@updateUserDetailInfo');
	Route::post('update-account-role', 'AccessController@updateAccountRole');
	Route::post('deactivate-user', 'AccessController@deactivateUser');
	Route::post('activate-user', 'AccessController@activateUser');

	Route::post('delete-relation', 'AccessController@deleteRelation');
	Route::post('delete-branch', 'AccessController@deleteBranch');
	Route::post('delete-location', 'AccessController@deleteLocation');

	Route::post('post-purchase', 'AccessController@postPurchase');
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

	Route::post('get-user-details', 'AccessController@getUserDetails');
	Route::post('get-incentives-id', 'AccessController@getIncentivesId');
	Route::post('add-incentives', 'AccessController@AddIncentives');
	Route::post('insert-profile', 'AccessController@insertProfile');
	Route::post('get-ledger-data', 'AccessController@getLedgerData');

	Route::post('deactivate-profile', 'AccessController@deactivateProfile');
	Route::post('activate-profile', 'AccessController@activateProfile');

	Route::post('adamin-update-inc', 'AccessController@adminUpdateInc');
	Route::post('idle-password', 'AccessController@idlePassword');

	Route::post('get-monthly', 'AccessController@getMonthly');
	Route::post('get-yearly', 'AccessController@getYearly');

	Route::post('pending-contract', 'ServiceContractController@pendingContract');
	Route::post('disapprove-contract', 'ServiceContractController@disapproveContract');

	Route::post('insert-cancel-sales', 'ServiceContractController@insertCancelSales');

	Route::post('disapprove-merchandise', 'ServiceContractController@disapproveMerchandise');
	Route::post('save-remittance', 'UtilityController@saveRemittance');
	Route::post('approve-remittance', 'UtilityController@approveRemittance');
});


	//getPurchaseDetails


//though not suggested, you are welcome to just put GET requests here, request here does not need authentication.
Route::get('get-signee', 'AccessController@getSignee')->middleware('cors');
Route::get('get-contract-decease', 'AccessController@getContractDecease')->middleware('cors');
Route::get('get-informant', 'AccessController@getInformant')->middleware('cors');
Route::get('transfer-item-details', 'ServiceContractController@transferItemDetails')->middleware('cors');
Route::get('get-the-items', 'ServiceContractController@getTheItems')->middleware('cors');
Route::get('get-contract-info', 'ServiceContractController@getContractInfo')->middleware('cors');
Route::get('get-purchase-details', 'ServiceContractController@getPurchaseDetails')->middleware('cors');
Route::get('get-decease-dropdowns', 'AccessController@getDeceaseDropdowns')->middleware('cors');
Route::get('get-deceased', 'AccessController@getDeceased')->middleware('cors');
Route::get('get-sc-locations', 'AccessController@getSCLocations')->middleware('cors');
Route::get('service-contract', 'AccessController@samplepdf')->middleware('cors');
Route::get('service-contract-dswd', 'AccessController@printDSWD')->middleware('cors');
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

Route::get('get-branch', 'AccessController@getBranch')->middleware('cors');
Route::get('get-branch-value', 'AccessController@getBranchValue')->middleware('cors');
Route::get('get-driver', 'AccessController@getDriver')->middleware('cors');
Route::get('get-driver-value', 'AccessController@getDriverValue')->middleware('cors');
Route::get('get-billing-of-client', 'AccessController@getBillingOfClient')->middleware('cors');
Route::get('get-accounts', 'AccessController@getAccounts')->middleware('cors');
Route::get('get-details-of-contract', 'ServiceContractController@getDetailsOfContract')->middleware('cors');

// TC Cares
Route::get('get-plan-contract', 'CaresController@getPlanContract')->middleware('cors');
Route::get('get-member-plan-info', 'CaresController@getMemberPlanInfo')->middleware('cors');
Route::get('get-plan-package', 'CaresController@getPlanPackage')->middleware('cors');
Route::get('get-cares-package', 'CaresController@getCaresPackage')->middleware('cors');
Route::get('get-plan-package-active', 'CaresController@getPlanPackageActive')->middleware('cors');
Route::get('get-plan-contract-details', 'CaresController@getPlanContractDetails')->middleware('cors');
Route::get('get-prof-member', 'CaresController@getProfMember')->middleware('cors');
Route::get('cares-agreement', 'CaresController@caresAgreement')->middleware('cors');


// Incentives
Route::get('get-incentives', 'AccessController@getIncentives')->middleware('cors');
Route::get('generate-incentives', 'AccessController@generateIncentives')->middleware('cors');

// Inventory
Route::get('get-item-package', 'InventoryController@getItemPackage')->middleware('cors');
Route::get('get-service-package', 'InventoryController@getServicePackage')->middleware('cors');
Route::get('get-supplier', 'InventoryController@getSupplierList')->middleware('cors');
Route::get('get-package-list', 'InventoryController@getPackageList')->middleware('cors');
Route::get('get-add-package-list', 'InventoryController@getAddPackageList')->middleware('cors');
Route::get('get-package-inclusions', 'InventoryController@getPackageInclusions')->middleware('cors');
Route::get('get-supplier-value', 'InventoryController@getSupplierValue')->middleware('cors');
Route::get('get-fun-branch', 'InventoryController@getFunBranch')->middleware('cors');
Route::get('get-chapel-inclusions', 'ServiceContractController@getChapelInclusions')->middleware('cors');
Route::get('get-chapel-list', 'InventoryController@getChapelList')->middleware('cors');
Route::get('casket-packages', 'InventoryController@casketPackages')->middleware('cors');
Route::get('get-chapel-item', 'InventoryController@getChapelItem')->middleware('cors');

Route::get('get-all-items', 'InventoryController@getAllItems')->middleware('cors');
Route::get('get-all-services', 'InventoryController@getAllServices')->middleware('cors');
Route::get('get-all-casket-packages', 'InventoryController@getAllCasketPackages')->middleware('cors');
Route::get('get-all-chapel-packages', 'InventoryController@getAllChapelPackages')->middleware('cors');

Route::get('get-signee-details', 'AccessController@getSigneeDetails')->middleware('cors');
Route::get('get-deceased-details', 'AccessController@getDeceasedDetails')->middleware('cors');
Route::get('get-informant-details', 'AccessController@getInformantDetails')->middleware('cors');
Route::get('get-walkin-details', 'AccessController@getWalkinDetails')->middleware('cors');
// accounts
Route::get('get-active-list', 'AccessController@getActiveAccount')->middleware('cors');
Route::get('get-all-item-services', 'CaresController@getAllItemServ')->middleware('cors');
Route::get('get-plan-profile', 'CaresController@getPlanProfile')->middleware('cors');
Route::get('get-walkin', 'AccessController@getWalkin')->middleware('cors');

Route::get('get-pending-contract', 'ServiceContractController@getPendingContract')->middleware('cors');
Route::get('get-pending-merchandise', 'ServiceContractController@getPendingMerchandise')->middleware('cors');

<<<<<<< HEAD
Route::get('get-cash-transaction-of-user', 'UtilityController@getCashTransactionOfUser')->middleware('cors');
Route::get('get-remittance-for-approval-header', 'UtilityController@getRemittanceForApprovalHeader')->middleware('cors');
Route::get('get-cash-transaction-request-of-user', 'UtilityController@getCashTransactionRequestOfUser')->middleware('cors');
=======

Route::get('get-pending-contract-count', 'ServiceContractController@getPendingContractCount')->middleware('cors');
Route::get('get-pending-service-contract-count', 'ServiceContractController@getPendingSContractCount')->middleware('cors');
Route::get('get-pending-merchandise-contract-count', 'ServiceContractController@getPendingMContractCount')->middleware('cors');
>>>>>>> e85143ecc976f64dd7ff44aff213b78dfdd14c1b

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
