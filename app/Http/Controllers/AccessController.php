<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\SystemUser;
use Illuminate\Http\Request;
use App\AccessTokens;
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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\FisCharging;




class AccessController extends Controller
{
    //
    public function getUser()
	{	echo date('Y-m-d H:i:s');
		return SystemUser::all();
		
	}


    public function insertPlanProfile(Request $request) {
		try {

			$value2 = (array)json_decode($request->post()['signeedata']);
			$signeeProfile = FisSignee::create([
				'isTCMember'	=> $value2['isTCMember'],
        		'fname'	=> $value2['fname'],
		        'mname'	=> $value2['mname'],
		        'lname'	=> $value2['lname'],
		       	'address' => $value2['address'],
		        'contactNo'	=> $value2['contactNo'],
		        'email_address' => $value2['email_address'],
		       	'fb_account' => $value2['fb_account'],
				'date_entry' => date('Y-m-d'),
				'transactedBy' => 'hcalio']);
			return [
				'status'=>'saved',
				'message'=>$signeeProfile
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}
    
	// ALL INSERT HERE
	public function insertRelation(Request $request) {
		try {
			$value = (array)json_decode($request->post()['relationdata']);
			$relationData = FisRelation::create($value);
			return [
				'status'=>'saved',
				'message'=>$relationData
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}
	
	public function insertDeceaseProfile(Request $request)
	{
		try {
			
			$value = (array)json_decode($request->post()['deceasedata']);
			
	
			DB::beginTransaction();
			
			$profile = FisMemberData::create([
					'customer_id'=>$value['cidReference'],
					'firstname'=>$value['firstname'],
					'middlename'=>$value['middlename'],
					'lastname'=>$value['lastname'],
					'contact_no'=>'', //deceased has no contact number
					'address'=>$value['address'],
					'is_member'=>$value['isTCMember'],
					'profile_type'=>'Decease',
					'date_entry'=>date('Y-m-d'),
			]);
			
			$deceased = FisDeceased::create([
					'fk_profile_id'=>$profile->id,
					'birthday'=>$value['birthday'],
					'date_died'=>$value['datedied'],
					'causeOfDeath'=>$value['causeOfDeath'],
					'religion'=>$value['religion'],
					'primary_branch'=>$value['primary_branch'],
					'servicing_branch'=>$value['servicing_branch'],
					'deathPlace'=>$value['deathPlace'],
					'relationToSignee'=> 4 //$value['relationToSignee'],
			]);
			
			DB::commit();
			
			return [
					'status'=>'saved',
					'message'=>$profile,
			];
			
			
		} catch (\Exception $e) {
			DB::rollBack();
			
			return [
					'status'=>'unsaved',
					'message'=>$e->getMessage()
			];
			
		}
		
	}

	public function insertBranch(Request $request) {
		try {
			$value = (array)json_decode($request->post()['branchdata']);
			$branchData = FisBranches::create($value);
			return [
				'status'=>'saved',
				'message'=>$branchData
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function insertDriver(Request $request) {
		try {
	
			$value = (array)json_decode($request->post()['driverdata']);
			$driverData = FisDriver::create($value);
			return [
				'status'=>'saved',
				'message'=>$driverData
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function insertEmbalmer(Request $request) {
		try {
			$value = (array)json_decode($request->post()['embalmerdata']);
			$embalmerData = FisEmbalmer::create($value);
			return [
				'status'=>'saved',
				'message'=>$embalmerData
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
			$value = (array)json_decode($request->post()['packageData']);
			foreach ($value as $row){
			$inclusions = FisInclusions::create([
					'fk_package_id'=> $value['package_id'],
					'inclusion_id'=> $value['inventory_id'],
					'inclusion_name'=> $value['name'],
					'quantity'=> $value['quantity'],
					'service_type'=> $value['service_type'],
					'inventory_type'=> $value['inventory_type'],
					'transactedBy'=> 'hcalio',
					'dateEncoded'=> date('Y-m-d')
			]);
			}
			return [
				'status'=>'saved',
				'message'=>$inclusions
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}


	public function insertItemReceiving(Request $request) {
		try {
			$value = (array)json_decode($request->post()['receivingdata']);
			$value['date_received'] = date('Y-m-d H:i:s', strtotime($value['date_received']));
			$receivingItems = ReceivingItems::create($value);
			return [
				'status'=>'saved',
				'message'=>$receivingItems
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

	


	public function insertAccess(Request $request) {
		try {
			
			$value = (array)json_decode($request->post()['userdata']);
			
			$user = SystemUser::create([
					'UserName'=> $value['username'],
					'Password'=>$value['password'],
					'LastName'=>$value['name'],
					'FirstName'=>$value['name'],
					'MiddleName'=>$value['name'],
					'UserStatus'=>1,
					'EmployeeID'=>$value['username'],
					'FKRoleID'=>$value['roleid'],
					'FKBranchID'=>$value['branchid'],
					'DateLastPasswordChange'=>date('Y-m-d H:i:s'),
					'DisbursementLimit'=>0,
					'CashOnHand'=>0,
					'UserSLCode'=>'1-1-101-01-002',
					'CreatedBy'=>'sa',
					'CreatedDate'=>date('Y-m-d'),
					'UpdatedBy'=>'sa',
					'DateUpdated'=>date('Y-m-d'),	
			]);
				
			return [
					'status'=>'saved',
					'message'=>$user
			];
			
		} catch (\Exception $e) {
			
			return [
					'status' => 'unsaved',
					'message' => $e->getMessage(), //use $request->post when getting formData type of post request
			];
		}
	}


	public function insertMemberProfile(Request $request){
		try {
			$value = (array)json_decode($request->post()['memberdata']);
			$memberProfile = FisMemberData::create([
				  'profile_type' => $value['profile_type'],
			      'customer_id' => $value['customer_id'],
			      'is_member' => $value['is_member'],
			      'firstname' => $value['firstname'],
			      'middlename' => $value['middlename'],
			      'lastname' => $value['lastname'],
			      'address' => $value['address'],
			      'contact_no' => $value['contact_no'],
			      'date_entry' => date('Y-m-d')
				]);

			if(($value['profile_type']) == 'Decease'){
				$deceaseValue = (array)json_decode($request->post()['memberdata']);
				$memberProfile = FisDeceased::create([
				  'birthday' => date('Y-m-d', strtotime($deceaseValue['birthday'])),
				  'date_died' => date('Y-m-d', strtotime($deceaseValue['date_died'])),
			      'causeOfDeath' => $deceaseValue['causeOfDeath'],
			      'deathPlace' => $deceaseValue['deathPlace'],
			      'religion' => $deceaseValue['religion'],
			      'primary_branch' => $deceaseValue['primary_branch'],
			      'servicing_branch' => $deceaseValue['relationToSignee'],
			      'relationToSignee' => $deceaseValue['servicing_branch'],
			      'fk_profile_id' => $memberProfile->id
				]);


			}

			if (($value['profile_type']) == 'Signee') {
				$signeeValue = (array)json_decode($request->post()['memberdata']);
				$memberProfile = FisSignee::create([
			      'fb_account' => $signeeValue['fb_account'],
			      'email_address' => $signeeValue['email_address'],
			      'fk_profile_id' => $memberProfile->id
				]);
			}
			
			if (($value['profile_type']) == 'Informant') {
				$informantValue = (array)json_decode($request->post()['memberdata']);
				$memberProfile = FisInformant::create([
			      'incentives' => $informantValue['incentives'],
			      'fk_profile_id' => $memberProfile->id
				]);
			}
			
			return [
				'status'=>'saved',
				'message'=>$memberProfile
			];


		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}




	public function samplepdf(Request $request)

	{
		$id = json_decode($request->post()['id']);
		//return $myid;
		
		$accounts = DB::select(DB::raw("select *, dbo._computeAge(birthday, getdate())as deceased_age from _SERVICE_CONTRACT_VIEW where contract_id=$id"));
		
		$inclusions = DB::select(DB::raw("select item_name as inclusionname from _fis_item_sales sales
				inner join _fis_items i on sales.product_id = i.item_code
				where contract_id=$id
				
				union all
				
				select service_name as inclusionname from _fis_service_sales ss
				inner join _fis_services s on s.id = ss.fk_service_id
				where fk_contract_id=$id"));
		
		
		$mpdf = new \Mpdf\Mpdf();
	
		//$mpdf->Image('/images/funecare_contract.jpg', 0, 0, 210, 297, 'jpg', '', true, false);
		$mpdf->WriteHTML(view('sc_printing', ['accounts'=>$accounts, 'inclusions'=>$inclusions]));
		$mpdf->showImageErrors = true;
		$mpdf->Output();
	}
	
	
	public function validatorsField($validatorClass)
	{
		/*
		 * Harold 3/29/2019
		 * This function expects the class to be validated.
		 * Kindly add your validators here for validation purposes,
		 * whoever desires to transfer this to a model, feel free to do so.
		 * Just inform your co-developers.
		 */
		
		$validation = [];
		
		switch ($validatorClass)
		{
			case 'fisItemSales':
				$validation = [
					'product_id' => 'required',
					'quantity' => 'required',
				];
			break;
			
			case 'fisItemInventory':
				$validation = [
					//'item_inventory.*.serialno'=>'bullshit',
					'item_inventory.serialno'=>'required'
				];	
			break;
			
		}
		
		return $validation;
		
	}
	
	
	public function postContract(Request $request)
	{
	   try {
	   	$value = (array)json_decode($request->post()['contract_details']);
	   	
	  	
			
	   		/*
	   		 * here lies the posting of contract.
	   		 * 1st step, update the balance of contract,
	   		 * 2nd step, record items get,
	   		 * 3rd step, record services get,
	   		 * 4th step,  record inventory of items.
	   		 * 5th step contract posting?
	   		 */
	   		
	   		//$isInventoryValid = \Illuminate\Support\Facades\Validator::make($value, $this->validatorsField('fisItemInventory'));
	   		
	   	$contract_discount = 0;
	   	
	   	$contract_discount = is_numeric($value['sc_discount']) ? $value['sc_discount'] : 0;
	   	
	   		
	   	DB::beginTransaction();
	   	
	   		$acctgHeader = [];
	   		$acctgHeader['branch_code'] = '201';
	   		$acctgHeader['transaction_date'] = date('Y-m-d');
	   		$acctgHeader['transaction_code'] = "JNLVOUCHER";
	   		$acctgHeader['username'] = "hcalio";
	   		$acctgHeader['reference'] = "SC".$value['sc_number'];
	   		$acctgHeader['status'] = 1;
	   		$acctgHeader['particulars'] = "Funecare Service Contract #".$value['sc_number'];
	   		$acctgHeader['customer'] = $value['sc_signee'];
	   		$acctgHeader['checkno'] = "";
	   		
	   		
	   		$currentBranch = FisBranch::where([
	   				'branchID'=>'201'
	   		])->firstOrFail();
	   		
	   		
	   		$acctgDetails = [];
	   		
	   		$pushDetails = [];

	   		
	   		//check if serial no. is repeated since laravel 5.7 does not provide distinct validation
	   		$equivalence = 1;
	   		if(count($value['item_inclusions'])>=1)
	 		{
	 			$valarr = array_count_values(array_column($value['item_inventory'], 'id'));
	 			$equivalence = array_sum($valarr) / count($valarr);
	 		}
	   		
	   		if($equivalence!=1)
	   			return [
	   					'status' => 'unsaved',
	   					'message' => 'Serial No. repitition found. Make sure we do not repeat serial no.',
	   			];
	   			
	   			
	   			$sc = ServiceContract::find($value['sc_id']);
	   			$sc->update(
	   					['contract_amount'=>$value['sc_amount'] - $contract_discount,
	   					 'grossPrice'=>	$value['sc_amount'],
	   					 'contract_balance'=> $value['sc_amount'] - $contract_discount,
	   					 'status'=>'ACTIVE',
	   					 'discount'=>$contract_discount,
	   					 'isPosted'=>1
	   					]
	   					);
	   			
	   			$scpayment = FisSCPayments::create([
	   					'contract_id'=>$value['sc_id'],
	   					'accountType'=>2, 
	   					'AR_Debit'=>$value['sc_amount'] - $contract_discount,
	   					'AR_Credit'=>0,
	   					'balance'=>$value['sc_amount'] - $contract_discount,
	   					'reference_no'=>'RELEASE_'.$sc->contract_no,
	   					'payment_date'=>date('Y-m-d'),
	   					'payment_mode'=>3, //3 sa for the meantime
	   					'transactedBy'=>'hcalio',
	   					'isCancelled'=>0,
	   					'isRemitted'=>0,
	   					'remittedTo'=>'',
	   					'isPosted'=>1,
	   					'remarks'=>'SC Contract Posting',
	   					'tran_type'=>'RELEASE',
	   			]);
	   			
	   			
	   			$pushDetails['entry_type']="DR";
	   			$pushDetails['SLCode']="1-1-112-03-004";
	   			$pushDetails['amount']=$value['sc_amount'] - $contract_discount;
	   			$pushDetails['detail_particulars']="To record AR from Service Contract No.".$value['sc_number']." Signee Name : ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
	   			array_push($acctgDetails, $pushDetails);
	   			
	   			if($contract_discount>0)
	   			{
	   				$pushDetails['entry_type']="DR";
	   				$pushDetails['SLCode']="4-1-411-01-001";
	   				$pushDetails['amount']= $contract_discount;
	   				$pushDetails['detail_particulars']="To record Discount from SC No.".$value['sc_number']." Signee Name : ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
	   				array_push($acctgDetails, $pushDetails);
	   			}
	   			
	   			
	   			
	   			foreach($value['item_inclusions'] as $row)
	   			{	   	
	   				
	   				try {
	   					
	   					$inventoryCount = FisProductList::where([
	   							'fk_item_id'=>$row->item_code,
	   							'isEncumbered'=>1,
	   					])->count();
	   					
	   					if($inventoryCount<$row->quantity)
	   					{
	   						DB::rollback();
	   						return [
	   							'status'=>'unsaved',
	   							'message'=>'Insufficient amount for Item Code '.$row->item_code.', only '.$inventoryCount.' left',
	   						];
	   						
	   						break;
	   					}
	   					
	   					FisItemSales::create(
	   							[
	   									'product_id'=>$row->item_code,
	   									'quantity'=>$row->quantity,
	   									'date'=>date('Y-m-d'),
	   									'price'=>$row->price,
	   									'total_price'=>$row->tot_price,
	   									'discount'=>$row->discount,
	   									'isInContract'=>1,
	   									'contract_id'=>$value['sc_id'],
	   									'remarks'=>'',
	   									'isWalkin'=>0,
	   									'client'=>'',
	   									'signee_id'=>$sc->signee,
	   									'isPosted'=>1,
	   									'TransactedBy'=>'hcalio',
	   									'isRemitted'=>0,
	   									'remittedTo'=>'',
	   									'OR_no'=>'-',
	   									'isCancelled'=>0,
	   									'sales_id'=>0
	   									
	   							]
	   							);
	   					
	   					
	   					$pushDetails['entry_type']="CR";
	   					$pushDetails['SLCode']= $row->income_SLCode;
	   					$pushDetails['amount']= $row->tot_price;
	   					$pushDetails['detail_particulars']="Income ".$row->item_name." from SC No.".$value['sc_number']." Signee: ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
	   					
	   					array_push($acctgDetails, $pushDetails);
	   					
	   					
	   					
	   				} catch (\Exception $e) {
	   					DB::rollback();
	   					return [
	   							'status'=>'unsaved',
	   							'message'=>$e->getMessage()
	   					];
	   					break;
	   				}
	   				
	   			}
	   			
	   			
	   			foreach($value['item_inventory'] as $row)
	   			{
	   				try {
	   					
	   					$productList = FisProductList::where([
	   							'id'=>$row->id,
	   							'isEncumbered'=>1,
	   					])->firstOrFail();
	   					
	   					
	   					FisItemInventory::create(
	   							[
	   									'transaction_date'=>date('Y-m-d'),
	   									'particulars'=>'Purchased by SC. #'.$sc->contract_no,
	   									'contract_id'=>$sc->contract_id,
	   									'dr_no'=>'-',
	   									'rr_no'=>'-',
	   									'process'=>'OUT',
	   									'remaining_balance'=>0,
	   									'product_id'=>$row->item_code,
	   									'quantity'=>1,
	   									'item_price'=>$row->sell_price,
	   									'remarks'=>'-',
	   									'serialNo'=>'-',
	   									'p_sequence'=>$row->id,
	   									'fk_sales_id'=>0,
	   									'fk_ORNo'=>'',
	   							]);
	   					
	   					
	   					
	   					
	   					
	   					//$productList = FisProductList::find($row->serialno);
	   					$productList->update([
	   						'isEncumbered'=>0
	   					]);
	   					

	   					
	   					if($row->SLCode!="-")
	   					{
	   						$pushDetails['entry_type']="DR";
	   						$pushDetails['SLCode']= $row->SLCode;
	   						$pushDetails['amount']= $productList->price;
	   						$pushDetails['detail_particulars']="To record Inventory of ".$row->item_name." from SC No.".$value['sc_number']." Signee Name : ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
	   						array_push($acctgDetails, $pushDetails);
	   						
	   						$pushDetails['entry_type']="CR";
	   						$pushDetails['SLCode']= $currentBranch->borrowHO;
	   						$pushDetails['amount']= $productList->price;
	   						$pushDetails['detail_particulars']="To record Inventory of ".$row->item_name." from SC No.".$value['sc_number']." Signee Name : ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
	   						array_push($acctgDetails, $pushDetails);
	   						
	   					}
	   					
	   					
	   				} catch (\Exception $e) {
	   					DB::rollback();
	   					return [
	   							'status'=>'unsaved',
	   							'message'=>$e->getMessage()
	   					];
	   					break;
	   					
	   				}
	   				
	   				
	   			}
	   			
	   			
	   			foreach($value['service_inclusions'] as $row)
	   			{
	   				
	   				try {
	   					
	   					FisServiceSales::create([
	   							'fk_service_id'=>$row->id,
	   							'grossAmount'=>$row->amount,
	   							'isContract'=>1,
	   							'fk_contract_id'=>$value['sc_id'],
	   							'remarks'=>'-',
	   							'discount'=>$row->less,
	   							'dateApplied'=>date('Y-m-d'),
	   							'total_amount'=>$row->tot_price,
	   							'service_duration'=>$row->duration,
	   							'duration_unit'=>$row->type_duration,
	   							'reference'=>'-',
	   							'isPosted'=>1,
	   							'transactedBy'=>'hcalio',
	   							'isRemitted'=>0,
	   							'dateRemitted'=>'1/1/1900',
	   							'RemittedTo'=>'',
	   							'sales_id'=>0,
	   							'isWalkin'=>0,
	   							'client'=>'',
	   							'signeeID'=>$sc->signee,
	   							'isCancelled'=>0,
	   							
	   					]);
	   					
	   					
	   					$pushDetails['entry_type']="CR";
	   					$pushDetails['SLCode']= $row->SLCode;
	   					$pushDetails['amount']= $row->tot_price;
	   					$pushDetails['detail_particulars']="Income of ".$row->service_name." from SC #".$value['sc_number']." Signee: ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
	   					
	   					array_push($acctgDetails, $pushDetails);
	   					
	   					
	   					
	   				} catch (\Exception $e) {
	   					DB::rollback();
	   					return [
	   							'status'=>'unsaved',
	   							'message'=>$e->getMessage()
	   					];
	   					break;
	   				}
	   				
	   			}

	   			$saveAccounting =  AccountingHelper::processAccounting($acctgHeader, $acctgDetails);
	   	
	   			if($saveAccounting['status']=='saved')
	   			{
	   				DB::commit();
	   				return [
	   						'status'=>'saved',
	   						'message'=>''
	   				];
	   			}
	   			
	   			else
	   			{
	   				DB::rollback();
	   				return $saveAccounting;
	   			}
	   		
	   		
	  	
	   		
	   	
	   } catch (\Exception $e) {
	   	DB::rollback();
	   	return [
	   			'status' => 'unsaved',
	   			'message' => $e->getMessage()
	   	];
	   }
		
	}
	
	
	public function postBillingPayment(Request $request)
	{
		try {
			$value = (array)json_decode($request->post()['payment_details']);
		//	$value = (array)json_decode($request->post()['payment_details']);
			DB::beginTransaction();
			
			
			
			$tran_header = FisTransactionHeader::create((array)$value['bill_header']);
			
			foreach ($value['pay_details'] as $row)
			{
				switch ($row->commodity)
				{
					case "SERVICE CONTRACT" :
						/* STRAT FOR PAYMENT
						 * 1. DEDUCT TO CONTRACT
						 * 2. LOG TRANSACTION
						 * 3.ACCTG. ENTRY
						 */
						
						try {
							$charging = FisCharging::where([
							  'fk_scID'=>$row->id,
							  'accountType'=>$row->charge_account,
							])->firstOrFail();
							$chargePayment = $charging->balance - $row->amount;
							
							if($chargePayment<0)
							{
								DB::rollBack();
								return [
										'status'=>'unsaved',
										'message'=>'Only '.$charging->balance.' is allowed for payment of the chosen Acct. Type',
								];
							}
							
							$charging->update([
									'balance'=> $chargePayment
							]);
							
						} catch (\Exception $e) {
							DB::rollBack();
							return [
									'status'=>'unsaved',
									'message'=>$e->getMessage(),
							];
						}
						
						$contract = ServiceContract::where('contract_id', $row->id)
						->where('contract_balance', '>', 0)
						->whereNotIn('status', ['DRAFT', 'CANCELLED'])
						->first();
						
						if(!$contract)
						{
							DB::rollBack();
							return [
									'status'=>'unsaved',
									'message'=>'SC #'.$row->reference.' does not qualify for SC Payment',
							];
							
						}
						
						if($row->amount>$contract->contract_balance)
						{
							DB::rollBack();
							return [
								'status'=>'unsaved',
								'message'=>'SC #'.$row->reference.' payment is greater than the balance',
							];
						}
						
						$remainingbalance = $contract->contract_balance - $row->amount;
						
						$contract->update([
								'contract_balance'=> $remainingbalance,
								'status'=> $remainingbalance == 0 ? 'CLOSED' : $contract->status
						]);
						
						$scpayment = FisSCPayments::create([
						 'contract_id'=>$row->id,
						 'accountType'=>$row->charge_account,
						 'AR_Debit'=>0,
						 'AR_Credit'=>$row->amount,
						 'balance'=>$remainingbalance,
						 'reference_no'=>$value['bill_header']->reference,
						 'payment_date'=>date('Y-m-d'),
						 'payment_mode'=>$row->pay_type,
						 'transactedBy'=>'hcalio',
						 'isCancelled'=>0,
						 'isRemitted'=>0,
						 'remittedTo'=>'',
						 'isPosted'=>1,
						 'remarks'=>$value['bill_header']->remarks,
						 'tran_type'=>$remainingbalance== 0 ? 'PAYCLOSE' : 'PAYPARTIAL',
						]);
						
						
						$acctgHeader_pay = [];
						$acctgDetails_pay = [];
						$pushDetails_pay= [];
						
						$paytype = FisPaymentType::find($row->pay_type);
						
						$acctgHeader_pay['branch_code'] = $contract->fun_branch;
						$acctgHeader_pay['transaction_date'] = date('Y-m-d');
						$acctgHeader_pay['transaction_code'] = $paytype->trandesc;
						$acctgHeader_pay['username'] = 'hcalio';
						$acctgHeader_pay['reference'] = "SCPay".$contract->contract_no."-".$value['bill_header']->reference;
						$acctgHeader_pay['status'] = $paytype->trantype;
						$acctgHeader_pay['particulars'] = "Posting of SC Payment w/ SC #".$contract->contract_no;
						$acctgHeader_pay['customer'] = $value['bill_header']->client;
						$acctgHeader_pay['checkno'] = "";
						
						
						
						$pushDetails_pay['entry_type']="DR";
						$pushDetails_pay['SLCode']=$paytype->sl_debit;
						$pushDetails_pay['amount']=$row->amount;
						$pushDetails_pay['detail_particulars']="To payment from SC Ref#".$value['bill_header']->reference." Client: ".$value['bill_header']->client;
						array_push($acctgDetails_pay, $pushDetails_pay);
						
						$pushDetails_pay['entry_type']="CR";
						$pushDetails_pay['SLCode']=$paytype->sl_credit;
						$pushDetails_pay['amount']=$row->amount;
						$pushDetails_pay['detail_particulars']="To payment from SC Ref#".$value['bill_header']->reference." Client: ".$value['bill_header']->client;
						array_push($acctgDetails_pay, $pushDetails_pay);
						
						AccountingHelper::processAccounting($acctgHeader_pay, $acctgDetails_pay);
						
						
						
						break;
					case "ADDTL. PURCHASES":
						
						$salesheader = FisItemsalesHeader::where('id', $row->id)
						->where('balance','>',0)
						->whereNotIn('status', ['DRAFT', 'CANCELLED'])
						->first();
						
						if(!$salesheader)
						{
							DB::rollBack();
							return [
									'status'=>'unsaved',
									'message'=>'Sales #'.$row->reference.' does not qualify for Merchandise Payment',
							];
						}
						
						if($row->amount>$salesheader->balance)
						{
							DB::rollBack();
							return [
									'status'=>'unsaved',
									'message'=>'Sales #'.$row->reference.' payment is greater than the balance',
							];
						}
						
						$remainingbalance_sales = $salesheader->balance - $row->amount;
						
						$salesheader->update([
								'balance'=> $remainingbalance_sales,
								'status'=> $remainingbalance_sales == 0 ? 'CLOSED' : $salesheader->status
						]);
						
						
						
						$transaction_sale = FisSalesTransaction::create([
								'sales_id'=>$salesheader->id,
								'accountType'=>$row->charge_account, //2 is for peronal. see _fis_account table
								'AR_Debit'=>0,
								'AR_Credit'=>$row->amount,
								'balance'=>$remainingbalance_sales,
								'reference_no'=>$value['bill_header']->reference,
								'payment_date'=>date('Y-m-d'),
								'transactedBy'=>'hcalio',
								'payment_mode'=>$row->pay_type,
								'isCancelled'=>0,
								'isRemitted'=>0,
								'remittedTo'=>'',
								'isPosted'=>1,
								'remarks'=>$value['bill_header']->remarks,
								'tran_type'=>$remainingbalance_sales == 0 ? 'PAYCLOSE' : 'PAYPARTIAL',
						]);
						
						$acctgHeader_pay = [];
						$acctgDetails_pay = [];
						$pushDetails_pay= [];
						
						$paytype = FisPaymentType::find($row->pay_type);
						
						$acctgHeader_pay['branch_code'] = $salesheader->fun_branch;
						$acctgHeader_pay['transaction_date'] = date('Y-m-d');
						$acctgHeader_pay['transaction_code'] = $paytype->trandesc;
						$acctgHeader_pay['username'] = 'hcalio';
						$acctgHeader_pay['reference'] = "MerchPay".$salesheader->OR_no."-".$value['bill_header']->reference;
						$acctgHeader_pay['status'] = $paytype->trantype;
						$acctgHeader_pay['particulars'] = "Posting of Merch Payment w/ Ref. #".$salesheader->OR_no;
						$acctgHeader_pay['customer'] = $value['bill_header']->client;
						$acctgHeader_pay['checkno'] = "";
						
						
						
						$pushDetails_pay['entry_type']="DR";
						$pushDetails_pay['SLCode']=$paytype->sl_debit;
						$pushDetails_pay['amount']=$row->amount;
						$pushDetails_pay['detail_particulars']="Payment from Merch Ref#".$value['bill_header']->reference." Client: ".$value['bill_header']->client;
						array_push($acctgDetails_pay, $pushDetails_pay);
						
						$pushDetails_pay['entry_type']="CR";
						$pushDetails_pay['SLCode']=$paytype->sl_credit;
						$pushDetails_pay['amount']=$row->amount;
						$pushDetails_pay['detail_particulars']="Payment from Merch Ref#".$value['bill_header']->reference." Client: ".$value['bill_header']->client;
						array_push($acctgDetails_pay, $pushDetails_pay);
						
						AccountingHelper::processAccounting($acctgHeader_pay, $acctgDetails_pay);
						
						
						
						break;
					default:
						break;
					
				}
				
				
				
			}
			
			
			
			DB::commit();
			
			return [
				'status'=>'saved',
				'message'=>'Successfully Posted Payment',
			];
			
			
			
			
			
		} catch (\Exception $e) {
			DB::rollback();
			return [
					'status' => 'unsaved',
					'message' => $e->getMessage()
			];
		}
		
		
		
	}
	
	
	public function postPurchase(Request $request)
	{
	
		try {
			$value = (array)json_decode($request->post()['mechandise']);
			
			//return $value;
			DB::beginTransaction();
			
			$equivalence = 1;
			if(count($value['item_inclusions'])>=1)
			{
				$valarr = array_count_values(array_column($value['item_inventory'], 'id'));
				$equivalence = array_sum($valarr) / count($valarr);
			}
			
			
			if($equivalence!=1)
				return [
						'status' => 'unsaved',
						'message' => 'Serial No. repitition found. Make sure we do not repeat serial no.',
				];
			
				//start here
				
				$currentBranch = FisBranch::where([
						'branchID'=>$value['sales_header']->branch
				])->firstOrFail();
				
				if($value['sales_header']->reference == '' || $value['sales_header']->client=='')
				{
					DB::rollBack();
					return [
						'status'=>'unsaved',
						'message'=>'Please dont leave Client empty'
					];
					
				}
				
				$salesHead = FisItemsalesHeader::create([
					'OR_no'=>$value['sales_header']->reference,
					'date'=>date('Y-m-d H:i:s'),
					'transactedBy'=>$value['sales_header']->transactedBy,
					'client'=>$value['sales_header']->client,
					'signee_id'=>$value['sales_header']->signee_id,
					'isPosted'=>1,
					//'PayType'=>$value['sales_header']->PayType,
					'isCancelled'=>0,
					'total_amount'=>$value['grand_total'],
					'balance'=>$value['grand_total'],
					'status'=>'ACTIVE',
					'fun_branch'=>$value['sales_header']->branch
				]);
				
				$acctgHeader = [];
				$acctgHeader_pay = [];
				$acctgDetails_pay= [];
				$pushDetails_pay= [];
				
				$acctgHeader['branch_code'] = $value['sales_header']->branch;
				$acctgHeader['transaction_date'] = date('Y-m-d');
				$acctgHeader['transaction_code'] = "JNLVOUCHER";
				$acctgHeader['username'] = $salesHead->transactedBy;
				$acctgHeader['reference'] = "Merch".$salesHead->OR_no;
				$acctgHeader['status'] = 1;
				$acctgHeader['particulars'] = "Posting of Merchandise w/ Ref #".$salesHead->OR_no;
				$acctgHeader['customer'] = $salesHead->client;
				$acctgHeader['checkno'] = "";
				
				
				$acctgDetails = [];
				$pushDetails = [];
				
				$pushDetails['entry_type']="DR";
				$pushDetails['SLCode']="1-1-112-03-004";
				$pushDetails['amount']=$salesHead->balance;
				$pushDetails['detail_particulars']="To record AR from Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
				array_push($acctgDetails, $pushDetails);
				
				
				if(is_numeric($value['sales_header']->amount_pay) && $value['sales_header']->amount_pay>0)
				{
					if($value['sales_header']->amount_pay>$salesHead->balance)
					{
						DB::rollBack();
						return [
								'status'=>'unsaved',
								'message'=>'Amount paid is greater than the balance'
						];
					}
					
					
					$transactionsale = FisSalesTransaction::create([
							'sales_id'=>$salesHead->id,
							'accountType'=>2, //2 is for peronal. see _fis_account table
							'AR_Debit'=>0,
							'AR_Credit'=>$value['sales_header']->amount_pay,
							'balance'=>$salesHead->balance - $value['sales_header']->amount_pay,
							'reference_no'=>$value['sales_header']->reference,
							'payment_date'=>date('Y-m-d'),
							'transactedBy'=>$salesHead->transactedBy,
							'payment_mode'=>$value['sales_header']->PayType,
							'isCancelled'=>0,
							'isRemitted'=>0,
							'remittedTo'=>'',
							'isPosted'=>1,
							'remarks'=>'merchandising payment',
							'tran_type'=>$salesHead->balance - $value['sales_header']->amount_pay == 0 ? 'PAYCLOSE' : 'PAYPARTIAL',
					]);
					
					
					$salesHead->update([
							'balance' => $transactionsale->balance,
							'status'=> $transactionsale->balance == 0 ? 'CLOSED' : 'ACTIVE'
					]);
					
					$paytype = FisPaymentType::find($value['sales_header']->PayType);
					
					
					
					$acctgHeader_pay['branch_code'] = $value['sales_header']->branch;
					$acctgHeader_pay['transaction_date'] = date('Y-m-d');
					$acctgHeader_pay['transaction_code'] = $paytype->trandesc;
					$acctgHeader_pay['username'] = $salesHead->transactedBy;
					$acctgHeader_pay['reference'] = "Merch".$salesHead->OR_no;
					$acctgHeader_pay['status'] = $paytype->trantype;
					$acctgHeader_pay['particulars'] = "Posting of Merch. Payment w/ Ref #".$salesHead->OR_no;
					$acctgHeader_pay['customer'] = $salesHead->client;
					$acctgHeader_pay['checkno'] = "";
					
					
					
					$pushDetails_pay['entry_type']="DR";
					$pushDetails_pay['SLCode']=$paytype->sl_debit;
					$pushDetails_pay['amount']=$value['sales_header']->amount_pay;
					$pushDetails_pay['detail_particulars']="To payment from Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
					array_push($acctgDetails_pay, $pushDetails_pay);
					
					$pushDetails_pay['entry_type']="CR";
					$pushDetails_pay['SLCode']=$paytype->sl_credit;
					$pushDetails_pay['amount']=$value['sales_header']->amount_pay;
					$pushDetails_pay['detail_particulars']="To payment from Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
					array_push($acctgDetails_pay, $pushDetails_pay);
					
				}
				
				
				foreach($value['item_inclusions'] as $row)
				{
					
					try {
						
						$inventoryCount = FisProductList::where([
								'fk_item_id'=>$row->item_code,
								'isEncumbered'=>1,
								'branch'=>$value['sales_header']->branch
						])->count();
						
						if($inventoryCount<$row->quantity)
						{
							DB::rollback();
							return [
									'status'=>'unsaved',
									'message'=>'Insufficient amount for Item Code '.$row->item_code.', only '.$inventoryCount.' left',
							];
							
							break;
						}
						
						FisItemSales::create(
								[
										'product_id'=>$row->item_code,
										'quantity'=>$row->quantity,
										'date'=>date('Y-m-d'),
										'price'=>$row->price,
										'total_price'=>$row->tot_price,
										'discount'=>$row->discount,
										'isInContract'=>0,
										'contract_id'=>0,
										'remarks'=>'',
										'isWalkin'=>0,
										'client'=>$value['sales_header']->client,
										'signee_id'=>$value['sales_header']->signee_id,
										'isPosted'=>1,
										'TransactedBy'=>'hcalio',
										'isRemitted'=>0,
										'remittedTo'=>'',
										'OR_no'=>$value['sales_header']->reference,
										'isCancelled'=>0,
										'sales_id'=>$salesHead->id
										
								]
								);
						
						
						$pushDetails['entry_type']="CR";
						$pushDetails['SLCode']= $row->income_SLCode;
						$pushDetails['amount']= $row->tot_price;
						$pushDetails['detail_particulars']="Income ".$row->item_name." frm Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
						
						array_push($acctgDetails, $pushDetails);
						
						
					} catch (\Exception $e) {
						DB::rollback();
						return [
								'status'=>'unsaved',
								'message'=>$e->getMessage()
						];
						break;
					}
					
				}
				
				
				foreach($value['item_inventory'] as $row)
				{
					try {
						
						FisItemInventory::create(
								[
										'transaction_date'=>date('Y-m-d'),
										'particulars'=>'Purchased by '.$value['sales_header']->client,
										'contract_id'=>0,
										'dr_no'=>'-',
										'rr_no'=>'-',
										'process'=>'OUT',
										'remaining_balance'=>0,
										'product_id'=>$row->item_code,
										'quantity'=>1,
										'item_price'=>$row->sell_price,
										'remarks'=>'-',
										'serialNo'=>'-',
										'p_sequence'=>$row->id,
										'fk_sales_id'=>$salesHead->id,
										'fk_ORNo'=>$value['sales_header']->reference,
								]);
						
						$productList = FisProductList::where([
								'id'=>$row->id,
								'isEncumbered'=>1,
						])->firstOrFail();
						
						//$productList = FisProductList::find($row->serialno);
						$productList->update([
								'isEncumbered'=>0
						]);
						
						
						if($row->SLCode!="-")
						{
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= $row->SLCode;
							$pushDetails['amount']= $productList->price;
							$pushDetails['detail_particulars']="To record Inventory of ".$row->item_name." from Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
							array_push($acctgDetails, $pushDetails);
							
							$pushDetails['entry_type']="CR";
							$pushDetails['SLCode']= $currentBranch->borrowHO;
							$pushDetails['amount']= $productList->price;
							$pushDetails['detail_particulars']="To record Inventory of ".$row->item_name." from Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
							array_push($acctgDetails, $pushDetails);
							
						}
						
						
						
					} catch (\Exception $e) {
						DB::rollback();
						return [
								'status'=>'unsaved',
								'message'=>$e->getMessage()
						];
						break;
						
					}
					
					
				}
				
				
				foreach($value['service_inclusions'] as $row)
				{
					
					try {
						
						FisServiceSales::create([
								'fk_service_id'=>$row->id,
								'grossAmount'=>$row->amount,
								'isContract'=>1,
								'fk_contract_id'=>0,
								'remarks'=>'-',
								'discount'=>$row->less,
								'dateApplied'=>date('Y-m-d'),
								'total_amount'=>$row->tot_price,
								'service_duration'=>$row->duration,
								'duration_unit'=>$row->type_duration,
								'reference'=>$value['sales_header']->reference,
								'isPosted'=>1,
								'transactedBy'=>'hcalio',
								'isRemitted'=>0,
								'dateRemitted'=>'1/1/1900',
								'RemittedTo'=>'',
								'sales_id'=>$salesHead->id,
								'isWalkin'=>0,
								'client'=>$value['sales_header']->client,
								'signeeID'=>$value['sales_header']->signee_id,
								'isCancelled'=>0,
								
						]);
						
						$pushDetails['entry_type']="CR";
						$pushDetails['SLCode']= $row->SLCode;
						$pushDetails['amount']= $row->tot_price;
						$pushDetails['detail_particulars']="Income of ".$row->service_name." from Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
						
						array_push($acctgDetails, $pushDetails);
						
						
					} catch (\Exception $e) {
						DB::rollback();
						return [
								'status'=>'unsaved',
								'message'=>$e->getMessage()
						];
						break;
					}
					
					
					
				}
				
				
				
				$saveAccounting =  AccountingHelper::processAccounting($acctgHeader, $acctgDetails);
				
				if($saveAccounting['status']=='saved')
				{
					if(count($acctgHeader_pay)>=1)
					{
						$savePayAccounting = AccountingHelper::processAccounting($acctgHeader_pay, $acctgDetails_pay);
						if($savePayAccounting['status']=='saved')
						{
							DB::commit();
							return [
									'status'=>'saved',
									'message'=>''
							];
						}
						
						else
						{
							DB::rollback();
							return $saveAccounting;
						}
						
					}
					
					else
					{
						DB::commit();
						return [
								'status'=>'saved',
								'message'=>''
						];
					}
					
					
				}
				
				else
				{
					DB::rollback();
					return $saveAccounting;
				}
				
			
				return [
						'status'=>'saved',
						'message'=>''
				];
			    //end here
		} catch (\Exception $e) {
			return [
					'status'=>'unsaved',
					'message'=>$e->getMessage()
			];
		}
		
	}
	
	
	
	
	public function getBillingOfClient(Request $request)
	{
		try {
			//$request->post()['name']
			
			$qry = DB::select(DB::raw("SELECT commodity, id, reference, charge_account, 'PERSONAL' as charge_label, pay_type, 'Cash Payment' as pay_label, balance, amount FROM
					(
					select signee, 'SERVICE CONTRACT' as commodity, contract_id as id, contract_no as reference, 2 as charge_account, 1 as pay_type, contract_balance as balance, 0 as amount from _fis_service_contract where status='ACTIVE' and contract_balance>0
					and fun_branch='".$request->post()['funbranch']."'
					UNION ALL
					select signee_id as signee, 'ADDTL. PURCHASES' as commodity, id, OR_no as reference, 2 as charge_account, 1 as pay_type,
					balance,
					0 as amount
					from _fis_itemsales_header sh where status='ACTIVE'
					and fun_branch='".$request->post()['funbranch']."'
					)SDFA
					WHERE signee=".$request->post()['client_id']));
			
			return $qry;
			
			
		} catch (Exception $e) {
			return [
				'status'=>'error',
				'message'=>$e->getMessage()
			];
		}
		
	}
	
	
	
	public function getContractList(Request $request)
	{
		try {
			//$request->post()['name']
			
			$qry = DB::select(DB::raw("SELECT contract_id, contract_no, (s.lastname + ', ' + s.firstname + ' ' + s.middlename)signee, (d.lastname + ', ' + d.firstname + ' ' +d.middlename)deceased, contract_date FROM _FIS_SERVICE_CONTRACT sc
				inner join (select * from _fis_profileheader where profile_type='Signee')s on sc.signee = s.id
				inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
								inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
								where profile_type='Decease')d on sc.deceased_id = d.id"));
			
			return $qry;
			
			
		} catch (Exception $e) {
			return [
					'status'=>'error',
					'message'=>$e->getMessage()
			];
		}
		
	}
	
	
	
	public function getAccounts()
	{
		$accounts = DB::select(DB::raw("select account_id as value, account_type as label from _fis_account"));
		$payment_type = DB::select(DB::raw("select typeid as value, typename as label from _fis_paymenttype"));
		
		return [
			'accounts' => $accounts,
			'payment_type' => $payment_type
		];
		
	}
	
	
	public function getItemsServicesForMerchandising(Request $request)
	{
		try {
			$user_check = DB::select(DB::raw("SELECT item_code, item_name, 0 as quantity, selling_price as price, 0 as discount, 0 as tot_price, SLCode, income_SLCode FROM
				_fis_items fi
				order by item_code asc
				"));
			
			$services = DB::select(DB::raw("SELECT fs.id, service_name, 0 as amount, 0 as less,
				0 as duration, '' as type_duration, 0 as tot_price, SLCode
				FROM _fis_services fs"));
			
			return [
				'status'=>'ok',
				'message'=> [
					'item_inclusions' => $user_check,
					'service_inclusions' => $services
				]
			];
			
			
			
		} catch (\Exception $e) {
			return [
				'status'=>'error',
				'message'=>$e->getMessage(),
			];
		}
		
	}
	

	
	public function insertContract(Request $request)
	{
		try {
			
			$value = (array)json_decode($request->post()['servicecontract']);
			
			$sc_count = ServiceContract::where('fun_branch', $value['fun_branch'])->count();
			
			$value['contract_no'] = date('Y')."-".str_pad($sc_count, 5, '0', STR_PAD_LEFT);
			$value['contract_balance'] = $value['contract_amount'];
			$value['contract_date'] = date('Y-m-d');
			$value['burial_time'] = date('Y-m-d H:i:s', strtotime($value['burial_time']));
			$serviceContract = ServiceContract::create($value);
			
			$user_check = DB::select(DB::raw("select item_code, item_name, quantity, price, discount, (price * quantity) as tot_price, SLCode, income_SLCode from
				(
				SELECT item_code, item_name, isnull(quantity, 0) as quantity, selling_price as price, 0 as discount, 0 as tot_price, SLCode, income_SLCode FROM _fis_items fi
				left join 
				(
				select * from _fis_package_inclusions
				where fk_package_id=".$serviceContract->package_class_id."
				and inclusionType='ITEM'
				)b on fi.item_code = b.item_id
				)sdf
				order by quantity desc,  item_code asc
				"));
			
			    $sc_details = DB::select(DB::raw("select sc.contract_id, contract_no, fun_branch, contract_date, (s.firstname + ', ' + s.middlename + ' ' + s.lastname)signee,
					s.address as signeeaddress, sc.discount, sc.grossPrice, sc.contract_amount, sc.contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
					d.birthday, d.address, d.causeOfDeath, sc.mort_viewing, cr.ReligionName, p.package_name
					from _fis_service_contract sc 
					inner join (select * from _fis_profileheader where profile_type='Signee')s on sc.signee = s.id
					inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
								inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
								where profile_type='Decease')d on sc.deceased_id = d.id
					inner join _fis_package p on sc.package_class_id = p.package_code
					inner join ClientReligion cr on d.religion = cr.ReligionID
					where contract_id=".$serviceContract->contract_id)); 
			    
		   
			    $services = DB::select(DB::raw("select * from
					(
					SELECT fs.id, service_name, isnull(a.service_price, 0) as amount, 0 as less, isnull(duration, '') as duration, isnull(type_duration, '') as type_duration, isnull(a.service_price, 0) as tot_price, SLCode  FROM _fis_services fs
					left join
					(
					select * from _fis_package_inclusions where fk_package_id=".$serviceContract->package_class_id." and inclusionType='SERV'
					)a on fs.id = a.service_id and fs.isActive=1
					)sdfa
					order by duration desc"));
							
			return [
						'status'=>'saved',
						'message'=> [
								'service_contract' => $sc_details,
								'item_inclusions' => $user_check,
								'service_inclusions' => $services
						]
				   ];
			
		} catch (\Exception $e) {
	
			return [
					'status'=>'unsaved',
					'message'=>$e->getMessage()
			];
			
		}
	}

	
	

	public function getMinimalProbabilities(Request $request)
	{
		try {
			$valueScaffold = (array)json_decode($request->post()['items_scaffold']);
			$value = $valueScaffold['items'];
			$branch = $valueScaffold['branch'];
			$itemSelection = [];
			$itemPresentation = [];
			
			foreach ($value as $row)
			{
				$selection = DB::select(DB::raw("SELECT fk_item_id, id as value, serialno as label, price as sublabel from
						_fis_productlist where isEncumbered=1 and branch='".$branch."'
						and fk_item_id='".$row->item_code."'"));
				
				array_push($itemSelection, $selection);
				

				$presentation = DB::select(DB::raw("select top ".$row->quantity." item_code, item_name, pl.id, serialno, ".$row->price." as sell_price, SLCode from _fis_productlist pl
					inner join _fis_items i on pl.fk_item_id = i.item_code
					where isEncumbered=1 and branch='".$branch."'and fk_item_id='".$row->item_code."'
					order by id"));
				
				array_push($itemPresentation, $presentation);
				
				
			}
			
			return [
					'selection' => $itemSelection,
					'presentation' => $itemPresentation,
					'vals' => $value
			];
			
		} catch (\Exception $e) {
			
			return [
					'status'=>'error',
					'message'=>$e->getMessage()
			];
			
		}
	}
	
	public function getSignee(Request $request)
	{
		$value="";
		
		try {
			$user_check = DB::select(DB::raw("SELECT top 5 id as value, (lastname + ', ' + firstname + ' ' + middlename)label  from _fis_profileheader
			where profile_type='Signee' and (lastname + ', ' + firstname + ' ' + middlename) like '".$request->post()['name']."%'"));
			
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
	
	
	public function getDeceased(Request $request)
	{
		$value="";
		
		try {
			$user_check = DB::select(DB::raw("SELECT top 5 id as value, (lastname + ', ' + firstname + ' ' + middlename)label  from _fis_ProfileHeader
			where profile_type='Decease' and (lastname + ', ' + firstname + ' ' + middlename) like '".$request->post()['name']."%'"));
			
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
		
	public function getDeceaseDropdowns(Request $request)
	{

		$value="";
		
		try {
			$user_check = DB::select(DB::raw("SELECT ReligionID as value, ReligionName as label from clientreligion"));
			$branches = DB::select(DB::raw("SELECT branch_code as value, branch_name as label from _fis_settings_branches"));
			
			if($user_check)
				return	[
					'religion' => $user_check,
					'branches' => $branches
				];
				else return [];
				
		} catch (\Exception $e) {
			return [
					'status'=>'error',
					'message'=>$e->getMessage()
			];
		}
	}
	
	
	public function getSCLocations(Request $request)
	{
		try {
	
			$cemeteries = DB::select(DB::raw("SELECT label, value from _fis_locations where type='cemetery'"));
			$churches = DB::select(DB::raw("SELECT label, value from _fis_locations where type='church'"));
			
			return [
				'cemeteries' => $cemeteries,
				'churches' => $churches
			];
			
			
			
		} catch (\Exception $e) {
			return [
					'status'=>'error',
					'message'=>$e->getMessage()
			];
		}
		
	}

		
	public function loginUser(Request $request)
	{
		try {
			$value = (array)json_decode($request->post()['userdata']);
			
			
			$user_check = DB::select(DB::raw("SELECT * from SystemUser where username='".$value['username']."' and password='".$value['password']."'"));
			
			if($user_check)
			{
				//create an access token for the user
				$accessToken = AccessTokens::create([
					'username'=>$value['username'],
					'api_token'=>substr(md5(uniqid(mt_rand(), true)), 0, 30),
					'date_issued'=>date('Y-m-d H:i:s'),
					'date_expire'=>date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s"). ' + 5 days')),
					'updated_at'=>date('Y-m-d'),
					'created_at'=>date('Y-m-d'),
				]);
				
				return [
						'status'=>'saved',
						'accesstoken'=>$accessToken,
						'user'=> $user_check
				];
				
			}
			
			else return [
					'status'=>'error',
					'message'=>'Invalid Username/Password.'
			]; 
			
		} catch (\Exception $e) {
			return [
					'status'=>'error',
					'message' => $e->getMessage()
			];
		}
		
		
	}

	public function getInventoryList(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT item_name, selling_price, item_code,  isActive, 'Item' as type FROM _fis_items
			UNION ALL
			SELECT service_name, selling_price, service_code, isActive, 'Service' as type FROM _fis_services"));

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

	public function getRelation(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT * from _fis_settings_relation"));
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

	public function getRelationValue(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT rtd_id as value, relation as label from _fis_settings_relation"));

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

	public function getBranch(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT * from _fis_settings_branches"));
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

	public function getBranchValue(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT branch_id as value, branch_name as label from _fis_settings_branches"));

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

	public function getDriver(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT * from _fis_settings_driver"));
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

	public function getDriverValue(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT driver_id as value, driver_name as label from _fis_settings_driver"));

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

	public function getEmbalmer(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT * from _fis_settings_embalmer"));
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

	public function getEmbalmerValue(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT embalmer_id as value, embalmer_name as label from _fis_settings_embalmer"));

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
	

	public function getInformant(Request $request)
	{
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT top 5 id as value, (lastName + ', ' + firstName + ' ' + middleName) label  from _fis_informant
			where (lastName + ', ' + firstName + ' ' + middleName) like '".$request->post()['name']."%'"));
			
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

	public function getMemberInfo(Request $request) {
		$value = "";
		try {
		$info = DB::select(DB::raw("SELECT 
		(P.lastname + ', ' + P.firstname + ' ' + P.middlename) member_name,  
		P.lastname, P.firstname, P.middlename, P.id, P.customer_id,  P.contact_no, P.address, P.is_member, P.profile_type, P.date_entry, 
		D.fk_profile_id, D.birthday, D.date_died, D.causeOfDeath, D.religion, D.primary_branch, D.servicing_branch, D.deathPlace, D.relationToSignee,
		S.fk_profile_id, S.fb_account, S.email_address,
		I.fk_profile_id, I.incentives
		from _fis_profileHeader as P
		FULL OUTER JOIN _fis_deceaseInfo AS D on P.id = D.fk_profile_id
		FULL OUTER JOIN _fis_informantInfo AS I  on P.id = I.fk_profile_id
		FULL OUTER JOIN _fis_signeeInfo AS S on P.id = S.fk_profile_id"));	

			if($info)
			return	$info;
			else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getSLCode(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("SELECT SLCode as value, SLName as label FROM slaccounts where classification='Detail'"));
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

	// ADD ALL THE 'UPDATE' HERE
	public function updateRelation(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['relationdataupdate']);
			
				$rtd = FisRelation::find($value['rtd_id']);
	   			$rtd->update(
	   					['relation'=>$value['relation']]);
			
			return [
					'status'=>'saved',
					'message'=>$rtd
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function updateBranch(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['branchdataupdate']);
			
				$branch = FisBranches::find($value['branch_id']);
	   			$branch->update(
	   					['branch_name'=>$value['branch_name']]);
			
			return [
					'status'=>'saved',
					'message'=>$branch
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function updateDriver(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['driverdataupdate']);
			
				$driver = FisDriver::find($value['driver_id']);
	   			$driver->update(
	   					['driver_name'=>$value['driver_name']]);
			
			return [
					'status'=>'saved',
					'message'=>$driver
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function updateEmbalmer(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['embalmerdataupdate']);
			
				$embalmer = FisEmbalmer::find($value['embalmer_id']);
	   			$embalmer->update(
	   					['embalmer_name'=>$value['embalmer_name']]);
			
			return [
					'status'=>'saved',
					'message'=>$embalmer
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function updateInfo(Request $request)
	{
		try {
			$value = (array)json_decode($request->post()['infoupdate']);
			
	   		$memberProfile = FisMemberData::find($value['id']);
	   		$memberProfile->update(
	   			[ 'profile_type' => $value['profile_type'],
			      'customer_id' => $value['customer_id'],
			      'is_member' => $value['is_member'],
			      'firstname' => $value['firstname'],
			      'middlename' => $value['middlename'],
			      'lastname' => $value['lastname'],
			      'address' => $value['address'],
			      'contact_no' => $value['contact_no'],
			      'date_entry' => date('Y-m-d')
				]);

	   			if(($value['profile_type']) == 'Decease'){
				$value['fk_profile_id'] = $value['id'];
				$memberProfile = FisDeceased::find($value['fk_profile_id']);
				$memberProfile->update([
				  'birthday' => date('Y-m-d', strtotime($value['birthday'])),
				  'date_died' => date('Y-m-d', strtotime($value['date_died'])),
			      'causeOfDeath' => $value['causeOfDeath'],
			      'deathPlace' => $value['deathPlace'],
			      'religion' => $value['religion'],
			      'primary_branch' => $value['primary_branch'],
			      'servicing_branch' => $value['servicing_branch'],
			      'relationToSignee' => $value['relationToSignee']
				]);
				}

				elseif (($value['profile_type']) == 'Signee') {
				$value['fk_profile_id'] = $value['id'];
				$memberProfile = FisSignee::find($value['fk_profile_id']);
				$memberProfile->update([
			      'fb_account' => $value['fb_account'],
			      'email_address' => $value['email_address']
				]);
				}
				
				elseif (($value['profile_type']) == 'Informant') {
				$value['fk_profile_id'] = $value['id'];
				$memberProfile = FisInformant::find($value['fk_profile_id']);
				$memberProfile->update(['incentives' => $value['incentives']]);
				}
			
			return [
					'status'=>'saved',
					'message'=>$memberProfile
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
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



	// CLOSE OF UPDATE

	// ADD ALL THE 'DELETE' HERE
	public function deleteRelation(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['relationdatadelete']);
			
				$rtd = FisRelation::find($value['rtd_id']);
	   			$rtd->delete();
			
			return [
					'status'=>'saved',
					'message'=>$rtd
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function deleteBranch(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['branchdatadelete']);
			
				$branch = FisBranches::find($value['branch_id']);
	   			$branch->delete();
			
			return [
					'status'=>'saved',
					'message'=>$branch
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function deleteDriver(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['driverdatadelete']);
			
				$driver = FisDriver::find($value['driver_id']);
	   			$driver->delete();
			
			return [
					'status'=>'saved',
					'message'=>$driver
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function deleteEmbalmer(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['embalmerdatadelete']);
			
				$embalmer = FisEmbalmer::find($value['embalmer_id']);
	   			$embalmer->delete();
			
			return [
					'status'=>'saved',
					'message'=>$embalmer
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
					$value['SLCode'] = $value['item_code'];
					$inventory = FisServices::find($value['SLCode']);
	   				$inventory->delete();
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


}
