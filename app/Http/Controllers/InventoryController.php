<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\FisRReport;
use App\FisDeceased;
use App\FisItems;
use App\ServiceContract;
use App\ReceivingItems;
use App\FisSignee;
use App\FisInformant;
use App\FisRelation;
use App\FisItemSales;
use App\FisItemInventory;
use App\FisProductList;
use App\FisServiceSales;
use App\FisItemsalesHeader;
use App\FisBranches;
use App\FisDriver;
use App\FisEmbalmer;
use App\FisMemberData;
use App\FisServices;
use App\FisSupplier;
use App\FisInclusions;
use App\FisTransactionHeader;
use App\AccountingHelper;
use App\FisBranch;
use Mpdf\Mpdf;
use App\FisSalesTransaction;
use App\FisPaymentType;
use App\FisSCPayments;
use App\FisPackage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\FisCharging;

class InventoryController extends Controller
{
   public function insertRR(Request $request) {
		try {
			$value = (array)json_decode($request->post()['rrData']);	
			
			$value['date_received'] = date('Y-m-d', strtotime($value['date_received']));
					
			foreach ($value['rr_items'] as $row){
			try {
					$rr = FisRReport::create([
					'supplier_id'	=> $value['supplier_id'],
						'rr_no'	=> $value['rr_no'],
		        		'po_no'	=> $value['po_no'],
				        'dr_no'	=> $value['dr_no'],
				        'serialNo'	=> $value['serialNo'],
				       	'date_received' => $value['date_received'],
				        'remarks'	=> $value['remarks'],
				        'quantity' => $value['quantity'],
						'transactedBy' => 'hcalio',
					'item_id'=> $row->item_code,
					'item_name'=> $row->name,
					'cost'=> $row->cost
					]);		
			} catch (\Exception $e) {
			return [
				'message'=>$e->getMessage()
			]; }
			}

			return [
				'status'=>'saved',
				'message'=>$rr
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function insertInventory(Request $request) {
		try {
			$value = (array)json_decode($request->post()['inventorydata']);
			$inventoryItems = FisItems::create([
				  'item_code' => $value['code'].'-'.$value['item_code'],
			      'item_name' => $value['item_name'],
			      'unit_type' => $value['unit_type'],
			      'BatchNo' => $value['BatchNo'],
			      'SLCode' => $value['SLCode'],
			      'income_SLCode' => $value['income_SLCode'],
			      'isActive' => $value['isActive'],
			      'isInventoriable' => $value['isInventoriable'],
			      'rr_no' => $value['rr_no'],
			      'selling_price' => $value['selling_price'],
			      'date_entry' => date('Y-m-d')
				]);
			/*
			foreach ($value as $row)
			{
				$selection = DB::select(DB::raw("select fk_item_id, id as value, serialno as label, price as sublabel from
						_fis_productlist where isEncumbered=1 and branch='".$branch."'
						and fk_item_id='".$row->item_code."'"));
				
				array_push($itemSelection, $selection);
				
				$presentation = DB::select(DB::raw("select top ".$row->quantity." item_code, item_name, pl.id, pl.serialno, ".$row->price." as sell_price from _fis_productlist pl
					inner join _fis_items i on pl.fk_item_id = i.item_code
					where isEncumbered=1 and branch='".$branch."'and fk_item_id='".$row->item_code."'
					order by id"));
				
				array_push($itemPresentation, $presentation);
			} */
			
			return [
					'status'=>'saved',
					'message'=>$inventoryItems
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function insertService(Request $request) {
		try {
			$value = (array)json_decode($request->post()['servicedata']);
			$service = FisServices::create($value);
			return [
				'status'=>'saved',
				'message'=>$service
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function insertInclusions(Request $request) {
		try {
			$value = (array)json_decode($request->post()['inclusionsData']);

			$value['package_code'] = $value['package_id'];
			$packagePrice = FisPackage::find($value['package_code']);
	   		$packagePrice->update([
	   					'discount'=>$value['discount'],
	   					'standardPrice'=>$value['standardPrice'],
	   					'salesPrice'=>$value['salesPrice']
	   				]);

			foreach ($value['inclusions'] as $row){
			try {
					$inclusions = FisInclusions::create([
					'fk_package_id'=> $row->package_id,
					'inclusion_id'=> $row->inventory_id,
					'inclusion_name'=> $row ->name,
					'quantity'=> $row->quantity,
					'service_type'=> $row->service_type,
					'inventory_type'=> $row->inventory_type,
					'selling_price'=> $row->inventory_price,
					'total_amount'=> $row->total_price,
					'transactedBy'=> 'hcalio',
					'dateEncoded'=> date('Y-m-d')
					]);		
			} catch (\Exception $e) {
			return [
				'message'=>$e->getMessage()
			]; }
			}

			return [
				'status'=>'saved',
				'message'=>$inclusions, $packagePrice
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function insertPackage(Request $request) {
		try {
	
			$value = (array)json_decode($request->post()['packagetitledata']);
			$value['dateExpired'] = date('Y-m-d H:i:s', strtotime($value['dateExpired']));
			$packageData = FisPackage::create([
			      'package_code' => $value['package_code'],
			      'package_name' => $value['package_name'],
			      'isActive' => $value['isActive'],
			      'date_expired' => $value['dateExpired'],
			      'date_created' => date('Y-m-d')
				]);
			return [
				'status'=>'saved',
				'message'=>$packageData
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function insertSupplier(Request $request) {
		try {
			$value = (array)json_decode($request->post()['supplierdata']);
			$supplier = FisSupplier::create($value);
			return [
				'status'=>'saved',
				'message'=>$supplier
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function getInventoryList(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT item_name, selling_price, item_code,  isActive, 'Item' as type FROM _fis_items
			UNION ALL
			SELECT service_name, selling_price, cast(id as varchar(10))id, isActive, 'Service' as type FROM _fis_services
			UNION ALL
			SELECT package_name, salesPrice, cast(package_code as varchar(10))package_code, isActive, 'Package' as type FROM _fis_package
			"));

			if($user_check)
			return	$user_check;
			else return [];
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getItemPackage(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT item_code as value, item_name as label, *   FROM _fis_items "));

			if($user_check)
			return	$user_check;
			else return [];
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getServicePackage(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT service_code as value, service_name as label, * FROM _fis_services"));

			if($user_check)
			return	$user_check;
			else return [];
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getProductList(Request $request) {
		$value = (array)json_decode($request->post()['prodList']);
		try {
		$user_check = DB::select(DB::raw("SELECT I.item_name, P.fk_item_id, P.batch_no, P.branch, P.serialNo, P.rr_no, P.dr_no, P.isEncumbered, P.price
			FROM _fis_productList as P
			FULL OUTER JOIN _fis_items AS I on P.fk_item_id = I.item_code 
			WHERE p.fk_item_id = '".$value['item_code']."'"));
			if($user_check)

			return	$user_check;
			else return [];
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getInclusionList(Request $request) {
		$value = (array)json_decode($request->post()['incList']);
		try {
		$inclusions = DB::select(DB::raw("SELECT PN.fk_package_id, PN.inclusion_name, PN.quantity, PN.selling_price, PN.total_amount,
			PN.service_type, PN.inventory_type, P.package_code
			FROM _fis_package_inclusions as PN
			FULL OUTER JOIN _fis_package AS P on PN.fk_package_id = P.package_code
			WHERE PN.fk_package_id = '".$value['item_code']."'"));
			if($inclusions)
			return	$inclusions;
			else return [];
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getItemList(Request $request) {
		$value = (array)json_decode($request->post()['itemList']);
		try {
			$user_check = DB::select(DB::raw("SELECT * FROM _fis_items 
				WHERE item_code = '".$value['item_code']."'"));
				
			if($user_check)
				return	$user_check;
			else return [];
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getServiceList(Request $request) {
		$value = (array)json_decode($request->post()['itemList']);
		try {
			$user_check = DB::select(DB::raw("SELECT * FROM _fis_services 
				WHERE service_code = '".$value['item_code']."'"));
				
			if($user_check)

				return	$user_check;
			else return [];

		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getPackageListEdit(Request $request) {
		$value = (array)json_decode($request->post()['itemList']);
		try {
			$user_check = DB::select(DB::raw("SELECT * FROM _fis_package

				WHERE package_code = '".$value['item_code']."'"));
				
			if($user_check)
				
				return	$user_check;
			else return [];

		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getSupplierList(Request $request) {
		$value="";
		try {
		$supplier = DB::select(DB::raw("SELECT * FROM _fis_supplier"));
			if($supplier)
				return	$supplier;
				else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getPackageList(Request $request) {
		$value="";
		try {
		$user_check = DB::select(DB::raw("SELECT package_code as value, package_name as label FROM _fis_package"));
			if($user_check)
				return	$user_check;
				else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getPackageInclusions(Request $request) {
		$value="";
		try {
		$inclusions = DB::select(DB::raw("SELECT inc.item_id, inc.service_id, inc.quantity, inc.inclusionType, items.item_name, serv.service_name
			FROM _fis_package_inclusions as inc
			LEFT JOIN _fis_items as items ON inc.item_id = items.item_code
			LEFT JOIN _fis_services as serv ON inc.service_id = serv.service_code"));
			if($inclusions)
				return	$inclusions;
				else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getSupplierValue(Request $request) {
		$value = "";
		try {
		$supplier = DB::select(DB::raw("SELECT supplier_id as value, supplier_name as label, *   FROM _fis_supplier "));

			if($supplier)
			return	$supplier;
			else return [];
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function updateItems(Request $request)
	{
		try {
			$value = (array)json_decode($request->post()['itemupdate']);
			
			$inventory = FisItems::find($value['item_code']);
	   		$inventory->update([
			      'item_code' => $value['item_code'],
			      'item_name' => $value['item_name'],
			      'selling_price' => $value['selling_price'],
			      'unit_type' => $value['unit_type'],
			      'SLCode' => $value['SLCode'],
			      'income_SLCode' => $value['income_SLCode'],
			      'BatchNo' => $value['BatchNo'],
			      'rr_no' => $value['rr_no']
				]);
			return [
					'status'=>'saved',
					'message'=>$inventory
			];
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function updateService(Request $request)
	{
		try {
			$value = (array)json_decode($request->post()['serviceupdate']);
			
			$inventory = FisServices::find($value['service_code']);
	   		$inventory->update([
			      'service_code' => $value['service_code'],
			      'service_name' => $value['service_name'],
			      'SL_Code' => $value['SL_Code'],
			      'isActive' => $value['isActive'],
			      'selling_price' => $value['selling_price']
				]);
			return [
					'status'=>'saved',
					'message'=>$inventory
			];
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function updateSupplier(Request $request)
	{
		try {
			$value = (array)json_decode($request->post()['supplierupdate']);
			
			$supplier = FisSupplier::find($value['supplier_id']);
	   		$supplier->update([
			      'supplier_name' => $value['supplier_name'],
			      'contact_number' => $value['contact_number'],
			      'address' => $value['address']
				]);
			return [
					'status'=>'saved',
					'message'=>$supplier
			];
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function updatePackage(Request $request)
	{
		try {
			$value = (array)json_decode($request->post()['packageupdate']);
			$value['date_expired'] = date('Y-m-d H:i:s', strtotime($value['date_expired']));
			$package = FisPackage::find($value['package_code']);
	   		$package->update([
			      'package_code' => $value['package_code'],
			      'package_name' => $value['package_name'],
			      'date_expired' => $value['date_expired']
				]);
			return [
					'status'=>'saved',
					'message'=>$package
			];
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function deleteSupplier(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['supplierdelete']);
			
				$supplier = FisSupplier::find($value['supplier_id']);
	   			$supplier->delete();
			
			return [
					'status'=>'saved',
					'message'=>$supplier
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function deleteInventory(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['inventorydelete']);
				if(($value['type']) == 'Item'){
					$inventory = FisItems::find($value['item_code']);
	   				$inventory->delete();
				}

				elseif(($value['type']) == 'Service') {
					$value['service_code'] = $value['item_code'];
					$inventory = FisServices::find($value['service_code']);
	   				$inventory->delete();
				}

				elseif(($value['type']) == 'Package') {
					$value['package_code'] = $value['item_code'];
					$value['fk_package_id'] = $value['item_code'];
					$inventory = FisPackage::find($value['package_code']);
					$inclusions = FisInclusions::find($value['fk_package_id']);
	   				$inventory->delete();
	   				$inclusions->delete();
				}
				
			return [
					'status'=>'saved',
					'message'=>$inventory,$inclusions
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}




}
