<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
use App\FisCharging;
use App\FisPackageInclusions;
use App\FisPackageServiceDelete;
use App\FisPackageItemDelete;
use App\FisChapelPackage;
use App\FisChapelInclusions;
use App\FisChapelServInc;
use App\FisChapelItemInc;

class InventoryController extends Controller
{


	public function generateItemSales(Request $request) {
		$id = json_decode($request->post()['id']);
		//return $myid;
		
		$accounts = DB::select(DB::raw("select *, dbo._computeAge(birthday, getdate())as deceased_age from _SERVICE_CONTRACT_VIEW where contract_id=$id"));
		
		$inclusions = DB::select(DB::raw("select * from 
			(select i.item_code, item_name as inclusionname from _fis_item_sales sales
				inner join _fis_items i on sales.product_id = i.item_code
				where contract_id=$id
						union all
			 select CAST(s.id as varchar(3)) as item_code, service_name as inclusionname from _fis_service_sales ss
				inner join _fis_services s on s.id = ss.fk_service_id where fk_contract_id=$id
			)dfa order by item_code"));
		
		
		$mpdf = new \Mpdf\Mpdf();
	
		//$mpdf->Image('/images/funecare_contract.jpg', 0, 0, 210, 297, 'jpg', '', true, false);
		$mpdf->WriteHTML(view('sc_printing', ['accounts'=>$accounts, 'inclusions'=>$inclusions]));
		$mpdf->showImageErrors = true;
		$mpdf->Output();
	}

   public function insertRR(Request $request) {
		try {
			$value = (array)json_decode($request->post()['rrData']);	
			
			$value['date_received'] = date('Y-m-d', strtotime($value['date_received']));
					
			
			foreach ($value['rr_items'] as $row){
			try {
					
					
				
					$rr = FisRReport::create([
						'supplier_id' => $value['supplier_id'],
						'date_received' => $value['date_received'],
						'transactedBy' => $value['transactedBy'],
						'total_amount'=> $value['total_amount'],
						'branchCode'=> $row->branch_id,
						'rr_no'	=> $row->rr_no,
		        		'po_no'	=> $row->po_no,
				        'dr_no'	=> $row->dr_no,
				        'serialNo' => $row->serialNo,
				        'remarks'	=> $row->remarks,
						'item_id'=> $row->item_code,
						'item_name'=> $row->name,
						'cost'=> $row->cost,
						'date_entry' => date('Y-m-d'),
						'isPosted' => 1
					]);
					
					$productList = FisProductList::create([
						'fk_item_id' => $row->item_code,
						'batch_no' =>$rr->id,
						'serialNo'	=> $row->serialNo,
						'branch'=> $row->branch_id,
		        		'rr_no'	=> $row->rr_no,
				        'dr_no'	=> $row->dr_no,
				        'isEncumbered'	=> 1,
				       	'price' => $row->cost,
				        'date_entry' => date('Y-m-d'),
				        'transactedBy' => $value['transactedBy']
					]);	
					
					//for remaining balance
					$forInventoryCount = FisProductList::where([
							'fk_item_id'=>$row->item_code,
							'isEncumbered'=>1,
							'branch'=>$row->branch_id
					])->count();
					
					$inventory = FisItemInventory::create([
						'transaction_date' => date('Y-m-d'),
						'particulars ' => 'From Receiving Report',
						'dr_no'	=> $row->dr_no,
						'rr_no'	=> $row->rr_no,
						'process' => 'REC-IN',
						'remaining_balance'=>$forInventoryCount + 1,
						'product_id' => $row->item_code,
						'item_price' =>$row->cost,
						'remarks' => $row->remarks,
						'serialNo'	=> $row->serialNo,
						'p_sequence'=> $productList->id,
						'quantity'=> 1,
				        'transactedBy' => $value['transactedBy']
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

			if ($value['isInventoriable'] == 1) {
				$inventoryItems = FisItems::create([
				  'item_code' => $value['code'].'-'.$value['item_code'],
			      'item_name' => $value['item_name'],
			      'unit_type' => $value['unit_type'],
			      'BatchNo' => '-',
			      'SLCode' => $value['SLCode'],
			      'income_SLCode' => $value['income_SLCode'],
			      'isActive' => 1,
			      'isInventoriable' => $value['isInventoriable'],
			      'rr_no' => '-',
			      'selling_price' => $value['selling_price'],
			      'date_entry' => date('Y-m-d'),
			      'transactedBy' => $value['transactedBy'],
				]);
			}
			else if ($value['isInventoriable'] == 0) {
				$inventoryItems = FisItems::create([
				  'item_code' => $value['code'].'-'.$value['item_code'],
			      'item_name' => $value['item_name'],
			      'unit_type' => $value['unit_type'],
			      'BatchNo' => ' ',
			      'SLCode' => '-',
				  'income_SLCode' => $value['income_SLCode'],
			      'isActive' => 1,
			      'isInventoriable' => $value['isInventoriable'],
			      'rr_no' => ' ',
			      'selling_price' => $value['selling_price'],
			      'date_entry' => date('Y-m-d'),
			      'transactedBy' => $value['transactedBy'],
				]);
			}

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
			$service = FisServices::create([
				'service_name' => $value['service_name'],
				'selling_price' => $value['selling_price'],
				'SLCode' => $value['SLCode'],
				'isActive' => 1,
				'date_entry' => date('Y-m-d'),
				'transactedBy' => $value['transactedBy']
			]);
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


	public function insertInclusionsInv(Request $request) {
		try {
			$value = (array)json_decode($request->post()['inclusionsData']);
				$inclusion = FisInclusions::create([
				'fk_package_id'=> $value['package_id'],
				'item_id'=> $value['inventory_id'],
				'service_id '=> ' ',
				'quantity'=> $value['quantity'],
				'duration '=> ' ',
				'type_duration '=> $value['service_type'],
				'inclusionType'=> 'ITEM',
				'service_price'=> $value['inventory_price'],
				'total_amount'=> $value['total_price'],
				'dateEncoded'=> date('Y-m-d')
				]);	
	
			return [
				'status'=>'saved',
				'message'=>$inclusion
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function insertInclusionsServ(Request $request) {
		try {

			$value = (array)json_decode($request->post()['inclusionsData']);
				$inclusion = FisInclusions::create([
				'fk_package_id'=> $value['package_id'],
				'item_id'=> ' ',
				'service_id '=> $value['inventory_id'],
				'quantity'=> ' ',
				'duration '=> $value['service_length'],
				'type_duration '=> $value['service_type'],
				'inclusionType'=> 'SERV',
				'service_price'=> $value['inventory_price'],
				'total_amount'=> $value['total_price'],
				'dateEncoded'=> date('Y-m-d')
				]);	
	
			return [
				'status'=>'saved',
				'message'=>$inclusion
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
	   					'salesPrice'=>$value['salesPrice'],
	   					'isActive' => 1,
	   					'updateInclusionBy'=>$value['transactedBy']
	   				]);

			return [
				'status'=>'saved',
				'message'=>$packagePrice
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
			$packageData = FisPackage::create([
				  'package_code' => $value['package_code'],
			      'package_name' => $value['package_name'],
			      'isActive' => 0,
			      'discount'=> 0,
	   			  'standardPrice'=> 0,
	   			  'salesPrice'=> 0,
			      'date_created' => date('Y-m-d'),
			      'createdBy' => $value['transactedBy'],
			      'package_level' =>1
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

	public function insertChapelPackage(Request $request) {
		try {
			$value = (array)json_decode($request->post()['packagetitledata']);
			$packageData = FisChapelPackage::create([
			      'chapel_name' => $value['chapel_name'],
			      'isActive' => 0,
			      'discount'=> 0,
	   			  'standardPrice'=> 0,
	   			  'salesPrice'=> 0,
			      'date_created' => date('Y-m-d'),
			      'createdBy' => $value['transactedBy'],
			      'package_level' =>1
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
			$supplier = FisSupplier::create([
			      'supplier_name' => $value['supplier_name'],
			      'address' => $value['address'],
			      'contact_number' => '+63'.$value['contact_number'],
			      'transactedBy'=> $value['transactedBy'],
			      'date_entry' => date('Y-m-d')
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


	public function getItemPackage(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT item_code as value, item_name as label, *  FROM _fis_items WHERE isActive = '1'"));

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

	public function getChapelItem(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT item_code as value, item_name as label, *  FROM _fis_items WHERE isActive = '1' 
and left(item_code, 2)<>'01'"));

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


	public function getFunBranch(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT branchID as value , name as label from _fis_branch"));

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
		$user_check = DB::select(DB::raw("SELECT id as value, service_name as label, * FROM _fis_services WHERE isActive = '1'"));

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
		$user_check = DB::select(DB::raw("SELECT I.item_name, B.name as branchname, P.fk_item_id, P.batch_no, P.branch, P.serialNo, P.rr_no, P.dr_no, P.isEncumbered, P.price
			FROM _fis_productList as P
			FULL OUTER JOIN _fis_items AS I on P.fk_item_id = I.item_code 
			LEFT JOIN _fis_branch AS B on P.branch = B.branchID
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
		$inclusions = DB::select(DB::raw("SELECT PN.fk_package_id, PN.item_id, 
			I.item_name, PN.quantity, PN.service_price, PN.total_amount, 
			PN.type_duration, PN.duration, PN.inclusionType, P.package_code,
			PN.inclusionType
			FROM _fis_package_inclusions as PN
			FULL OUTER JOIN _fis_items AS I on PN.item_id = I.item_code
			FULL OUTER JOIN _fis_package AS P on PN.fk_package_id = P.package_code
			WHERE PN.inclusionType='ITEM' and  
			PN.fk_package_id = '".$value['item_code']."'
			union all
			SELECT PN.fk_package_id, PN.service_id, S.service_name, 
			PN.quantity, PN.service_price, 
			PN.total_amount, PN.type_duration, PN.duration, PN.inclusionType, 
			P.package_code, PN.inclusionType
			FROM _fis_package_inclusions as PN
			FULL OUTER JOIN _fis_services AS S on PN.service_id = S.id
			FULL OUTER JOIN _fis_package AS P on PN.fk_package_id = P.package_code
			WHERE inclusionType='SERV'  and  PN.fk_package_id = '".$value['item_code']."'"));
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
				WHERE id = '".$value['item_code']."'"));
				
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
		$user_check = DB::select(DB::raw("SELECT package_code as value, package_name as label FROM _fis_package where isActive = '1'"));
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

	public function getAddPackageList(Request $request) {
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
			LEFT JOIN _fis_services as serv ON inc.service_id = serv.id"));
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
		$supplier = DB::select(DB::raw("SELECT supplier_id as value, supplier_name as label   FROM _fis_supplier "));

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

	public function getRRList(Request $request) {
		$value = (array)json_decode($request->post()['supplierData']);
		try {
		$supplier = DB::select(DB::raw("SELECT S.supplier_id, S.supplier_name, RR.supplier_id, RR.rr_no, RR.po_no, RR.dr_no, 
			RR.serialNo, RR.date_received, RR.item_name, RR.cost, RR.remarks, B.name
			FROM _fis_receiving_report AS RR
			FULL OUTER JOIN _fis_supplier AS S ON RR.supplier_id = S.supplier_id
			left JOIN _fis_branch AS B ON RR.branchCode = B.branchID
			WHERE RR.supplier_id = '".$value['supplier_id']."'"));
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
			      'isInventoriable' => $value['isInventoriable'],
			      'transactedBy' => $value['transactedBy'],
			      'date_updated' => date('Y-m-d')
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
			
			$inventory = FisServices::find($value['id']);
	   		$inventory->update([
			      'id' => $value['id'],
			      'service_name' => $value['service_name'],
			      'SLCode' => $value['SLCode'],
			      'isActive' => $value['isActive'],
			      'selling_price' => $value['selling_price'],
			      'date_updated' => date('Y-m-d'),
			      'transactedBy' =>  $value['transactedBy'],
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
			      'address' => $value['address'],
			      'transactedBy' => $value['transactedBy'],
			      'date_updated' => date('Y-m-d')
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
			$package = FisPackage::find($value['package_code']);
	   		$package->update([
			      'package_name' => $value['package_name'],
			      'createdBy' => $value['transactedBy']
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

	public function deleteInc(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['inventorydelete']);

				if ($value['inventory_type'] == 'ITEM') {
					$value['item_id'] = $value['inclusion_id'];
					$inc = FisPackageItemDelete::find($value['item_id']);
		   			$inc->delete();
				}
				else if ($value['inventory_type'] == 'SERV') {
					$value['service_id'] = $value['inclusion_id'];
					$inc = FisPackageServiceDelete::find($value['service_id']);
		   			$inc->delete();
				}
			return [
					'status'=>'saved',
					'message'=>$inc
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
					if ($value['isActive'] == 1) {
						$inventory = FisItems::find($value['item_code']);
						$inventory->update([
		   					'isActive' => 0
		   				]);
					}
					elseif ($value['isActive'] == 0) {
						$inventory = FisItems::find($value['item_code']);
						$inventory->update([
		   					'isActive' => 1
		   				]);
					}
					
				}

				elseif(($value['type']) == 'Service') {
					$value['id'] = $value['item_code'];
	   				if ($value['isActive'] == 1) {
						$inventory = FisServices::find($value['id']);
						$inventory->update([
		   					'isActive' => 0
		   				]);
					}
					elseif ($value['isActive'] == 0) {
						$inventory = FisServices::find($value['id']);
						$inventory->update([
		   					'isActive' => 1
		   				]);
					}
				}

				elseif(($value['type']) == 'Casket Package') {
					$value['package_code'] = $value['item_code'];
					if ($value['isActive'] == 1) {
						$inventory = FisPackage::find($value['package_code']);
						$inventory->update([
		   					'isActive' => 0
		   				]);
					}
					elseif ($value['isActive'] == 0) {
						$inventory = FisPackage::find($value['package_code']);
						$inventory->update([
		   					'isActive' => 1
		   				]);
					}
				}

				elseif(($value['type']) == 'Chapel Package') {
					$value['chapel_code'] = $value['item_code'];
					if ($value['isActive'] == 1) {
						$inventory = FisChapelPackage::find($value['chapel_code']);
						$inventory->update([
		   					'isActive' => 0
		   				]);
					}
					elseif ($value['isActive'] == 0) {
						$inventory = FisChapelPackage::find($value['chapel_code']);
						$inventory->update([
		   					'isActive' => 1
		   				]);
					}
				}
				
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


	public function getChapelList(Request $request) {
		$value="";
		try {
		$user_check = DB::select(DB::raw("SELECT id as value, chapel_name as label FROM _fis_chapel_package"));
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

	public function getChapelInclusionList(Request $request) {
		$value = (array)json_decode($request->post()['incList']);
		try {
		$inclusions = DB::select(DB::raw("
			SELECT CI.fk_chapel_id as fk_package_id, CI.item_id, CI.quantity,
			CI.duration, CI.type_duration, CI.inclusionType, CI.transactedBy,
			CI.service_price, CI.total_amount, I.item_name
			FROM _fis_chapel_inclusions as CI
			FULL OUTER JOIN _fis_items AS I on CI.item_id = I.item_code
			FULL OUTER JOIN _fis_chapel_package AS CP on CI.fk_chapel_id = CP.id
			WHERE CI.inclusionType='ITEM' and  CI.fk_chapel_id = '".$value['item_code']."'
			union all
			SELECT CI.fk_chapel_id, CI.service_id, CI.quantity,
			CI.duration, CI.type_duration, CI.inclusionType, CI.transactedBy,
			CI.service_price, CI.total_amount, S.service_name
			FROM _fis_chapel_inclusions as CI
			FULL OUTER JOIN _fis_services AS S on CI.service_id = S.id
			FULL OUTER JOIN _fis_chapel_package AS CP on CI.fk_chapel_id = CP.id
			WHERE CI.inclusionType='SERV' and  CI.fk_chapel_id = '".$value['item_code']."'
			"));
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

	public function insertChapelIncInv(Request $request) {
		try {
			$value = (array)json_decode($request->post()['inclusionsData']);
				$inclusion = FisChapelInclusions::create([
				'fk_chapel_id'=> $value['package_id'],
				'item_id'=> $value['inventory_id'],
				'service_id '=> ' ',
				'quantity'=> $value['quantity'],
				'duration '=> ' ',
				'type_duration '=>  $value['service_type'],
				'inclusionType'=> 'ITEM',
				'service_price'=> $value['inventory_price'],
				'total_amount'=> $value['total_price'],
				'dateEncoded'=> date('Y-m-d'),
				'package_level'=>1
				]);	
	
			return [
				'status'=>'saved',
				'message'=>$inclusion
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function insertChapelIncServ(Request $request) {
		try {
			$value = (array)json_decode($request->post()['inclusionsData']);
				$inclusion = FisChapelInclusions::create([
				'fk_chapel_id'=> $value['package_id'],
				'item_id'=> ' ',
				'service_id '=> $value['inventory_id'],
				'quantity'=> ' ',
				'duration '=> $value['service_length'],
				'type_duration '=> $value['service_type'],
				'inclusionType'=> 'SERV',
				'service_price'=> $value['inventory_price'],
				'total_amount'=> $value['total_price'],
				'dateEncoded'=> date('Y-m-d'),
				'package_level'=>1
				]);	
	
			return [
				'status'=>'saved',
				'message'=>$inclusion
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function deleteChapelInc(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['inventorydelete']);

				if ($value['inventory_type'] == 'ITEM') {
					$value['item_id'] = $value['inclusion_id'];
					$inc = FisChapelItemInc::find($value['item_id']);
		   			$inc->delete();
				}
				else if ($value['inventory_type'] == 'SERV') {
					$value['service_id'] = $value['inclusion_id'];
					$inc = FisChapelServInc::find($value['service_id']);
		   			$inc->delete();
				}
			return [
					'status'=>'saved',
					'message'=>$inc
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function insertChapelInclusions(Request $request) {
		try {
			$value = (array)json_decode($request->post()['inclusionsData']);

			$value['id'] = $value['package_id'];
			$packagePrice = FisChapelPackage::find($value['id']);
	   		$packagePrice->update([
	   					'discount'=>$value['discount'],
	   					'standardPrice'=>$value['standardPrice'],
	   					'salesPrice'=>$value['salesPrice'],
	   					'isActive' => 1,
	   					'updateInclusionBy'=>$value['transactedBy']
	   				]);

			return [
				'status'=>'saved',
				'message'=>$packagePrice
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function getChapelListEdit(Request $request) {
		$value = (array)json_decode($request->post()['itemList']);
		try {
			$user_check = DB::select(DB::raw("SELECT * FROM _fis_chapel_package
				WHERE id = '".$value['item_code']."'"));
				
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

	public function updateChapelPackage(Request $request)
	{
		try {
			$value = (array)json_decode($request->post()['packageupdate']);
			$package = FisChapelPackage::find($value['id']);
	   		$package->update([
			      'chapel_name' => $value['chapel_name'],
			      'createdBy' => $value['transactedBy']
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


	public function casketPackages(Request $request)
	{
		$id = $request->post()['id'];
		
		$package = DB::select(DB::raw("
			SELECT * FROM _fis_package WHERE package_code = '".$id."'
			"));

		$items = DB::select(DB::raw("
			SELECT PCI.*, I.item_name FROM _fis_package_inclusions AS PCI 
			LEFT JOIN _fis_items AS I ON PCI.item_id = I.item_code
			WHERE inclusionType = 'ITEM' and fk_package_id = '".$id."'
			"));

		$service = DB::select(DB::raw("
			SELECT PCI.*, S.service_name FROM _fis_package_inclusions AS PCI 
			LEFT JOIN _fis_services AS S ON PCI.service_id = S.id
			WHERE inclusionType = 'SERV' and fk_package_id = '".$id."'
			"));
				
		$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'LEGAL']);
		$mpdf->SetDisplayMode('fullpage');
		$mpdf->WriteHTML(view('casket_packages', ['package'=>$package, 'items'=>$items, 'service'=>$service]));
		$mpdf->use_kwt = true; 
		$mpdf->SetTitle('Casket Package');
		$mpdf->Output('');
	}

	public function getAllItems(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("
			SELECT *, 'Item' as type FROM _fis_items
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

	public function getAllServices(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("
			SELECT *, id as item_code, 'Service' as type FROM _fis_services
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

	public function getAllCasketPackages(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("
			SELECT *, package_code as item_code, 'Casket Package' as type FROM _fis_package
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

	public function getAllChapelPackages(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("
			SELECT *, id as item_code, 'Chapel Package' as type FROM _fis_chapel_package
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


}
