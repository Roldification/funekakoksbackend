<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\SystemUser;
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
use App\FisIncentives;
use App\FisPassword;
use App\FisLocation;
use App\FisProfileLogs;
use App\FisIncentivesLedger;
use App\FisIncMonthly;
use App\FisIncQuarterly;
use App\FisIncYearly;



class AccessController extends Controller
{
    //
    public function getUser()
	{	echo date('Y-m-d H:i:s');
		return SystemUser::all();
		
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

	public function insertLocation(Request $request) {
		try {
			$value = (array)json_decode($request->post()['locationdata']);
			$locationData = FisLocation::create([
					'label'=>$value['location'],
					'value'=>$value['location'],
					'type'=>$value['type'],
					'transactedBy'=>$value['transactedBy']
			]);
			return [
				'status'=>'saved',
				'message'=>$locationData
			];
			
		} catch (\Exception $e) {
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


	public function insertAccess(Request $request) {
		try {
			
			$value = (array)json_decode($request->post()['userdata']);
			
			if ($value['username']!="") {
			$memcount = SystemUser::where(['username'=>$value['username']])->first();

				if($memcount)
				{
					return [
						'status'=>'unsaved',
						'message'=>'Username Already Exist.'
					];	
				}
			}

			$user = SystemUser::create([
					'UserName'=> $value['username'],
					'Password'=>$value['password'],
					'LastName'=>$value['Lastname'],
					'FirstName'=>$value['Firstname'],
					'MiddleName'=>$value['Middlename'],
					'UserStatus'=>0,
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
					'DateUpdated'=>date('Y-m-d')
			]);
				
			return [
					'status'=>'saved',
					'message'=>$user
			];
			
		} catch (\Exception $e) {
			
			return [
					'status' => 'unsaved',
					'message' => $e->getMessage() //use $request->post when getting formData type of post request
			];
		}
	}


	public function insertMemberProfile(Request $request){
		try {
			$value = (array)json_decode($request->post()['memberdata']);
			
			if ($value['customer_id']!="") {
			$memcount = FisMemberData::where(['customer_id'=>$value['customer_id']])->first();

				if($memcount)
				{
					return [
						'status'=>'unsaved',
						'message'=>'Member Already Exist.'
					];	
				}
			}

				$memberProfile = FisMemberData::create([
				  'profile_type' => $value['profile_type'],
			      'customer_id' => $value['customer_id'],
			      'is_member' => $value['is_member'],
			      'firstname' => $value['firstname'],
			      'middlename' => $value['middlename'],
			      'lastname' => $value['lastname'],
			      'address' => $value['address'],
			      'contact_no' => '+63'.$value['contact_no'],
			      'date_entry' => date('Y-m-d'),
			      'transactedBy' => $value['transactedBy'],
				  'is_senior_or_disabled' => isset($value['is_senior_or_disabled']) ? $value['is_senior_or_disabled'] : 0,
						'is_taxable' => isset($value['is_taxable']) ? $value['is_taxable'] : 0,
				]);

				if(($value['profile_type']) == 'Decease'){
				$deceaseValue = (array)json_decode($request->post()['memberdata']);
				$value['date_died'] = date_format(date_create($value['date_died']), 'Y-m-d H:i:s');
				$deceaseProfile = FisDeceased::create([
				  /*'birthday' => date('Y-m-d', strtotime($deceaseValue['birthday'])),*/
				  'birthday' => date_format(date_create($value['birthday']), 'Y-m-d H:i:s'),
				  'date_died' => $value['date_died'],
			      'causeOfDeath' => $deceaseValue['causeOfDeath'],
			      'deathPlace' => $deceaseValue['deathPlace'],
			      'religion' => $deceaseValue['religion'],
			      'primary_branch' => $deceaseValue['primary_branch'],
			      'relationToSignee' => $deceaseValue['relationToSignee'],
			      'gender' => $deceaseValue['gender'],
			      'occupation' => $deceaseValue['occupation'],
			      'fk_profile_id' => $memberProfile->id
				]);

				$profileLogs= FisProfileLogs::create([
				      'fk_profile_id' => $memberProfile->id,
				      'profile_type' => $value['profile_type'],
				      'isActive' => 1,
				      'date_created' =>  date('Y-m-d'),
				      'createdBy' => $value['transactedBy']
					]);

				return [
					'status'=>'saved',
					'message'=>$memberProfile, $deceaseProfile, $profileLogs
				];

				}


				if (($value['profile_type']) == 'Signee') {

					$signeeValue = (array)json_decode($request->post()['memberdata']);
					$signeeProfile = FisSignee::create([
				      'fb_account' => $signeeValue['fb_account'],
				      'email_address' => $signeeValue['email_address'],
				      'fk_profile_id' => $memberProfile->id
					]);

					$profileLogs= FisProfileLogs::create([
				      'fk_profile_id' => $memberProfile->id,
				      'profile_type' => $value['profile_type'],
				      'isActive' => 1,
				      'date_created' =>  date('Y-m-d'),
				      'createdBy' => $value['transactedBy']
					]);

					return [
					'status'=>'saved',
					'message'=>$memberProfile, $signeeProfile, $profileLogs
					];
				}
			
				if (($value['profile_type']) == 'Walk-in') {
					$profileLogs= FisProfileLogs::create([
				      'fk_profile_id' => $memberProfile->id,
				      'profile_type' => $value['profile_type'],
				      'isActive' => 1,
				      'date_created' =>  date('Y-m-d'),
				      'createdBy' => $value['transactedBy']
					]);

					return [
					'status'=>'saved',
					'message'=>$memberProfile, $profileLogs
					];
				}

				if (($value['profile_type']) == 'Informant') {
					/*$informantValue = (array)json_decode($request->post()['memberdata']);

					$informantValue['date_inform'] = date('Y-m-d', strtotime($informantValue['date_inform']));
					$informantProfile = FisInformant::create([
				      'incentives' => $informantValue['incentives'],
				      'remarks' => $informantValue['remarks'],
				      'date_inform' =>  date('Y-m-d'),
				      'status' => 'UNCLAIMED',
				      'fk_profile_id' => $memberProfile->id
				  	]);
*/
				  	$profileLogs= FisProfileLogs::create([
				      'fk_profile_id' => $memberProfile->id,
				      'profile_type' => $value['profile_type'],
				      'isActive' => 1,
				      'date_created' =>  date('Y-m-d'),
				      'createdBy' => $value['transactedBy']
					]);

					return [
					'status'=>'saved',
					'message'=>$memberProfile, $profileLogs /*$informantProfile,*/ 
				];

				}

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
		
		$inclusions = DB::select(DB::raw("select * from
		(select i.item_code, item_name as inclusionname, CAST(quantity as varchar(3)) + ' ' + unit_type as quantity, total_price,
				case when left(i.item_code, 2)='01' then 1
				else (select count(*)sdf from _fis_package_inclusions where inclusiontype='ITEM' and fk_package_id='".$accounts[0]->package_class_id."' and item_id = i.item_code)
				end as ispackage, i.print_index
				from _fis_item_sales sales
				inner join _fis_items i on sales.product_id = i.item_code
				where contract_id=$id and isViewPrint=1
				union all
				select CAST(s.id as varchar(3)) as item_code, service_name as inclusionname, CAST(service_duration as varchar(3)) + ' ' + duration_unit as quantity, total_amount,
				(select count(*)sdf from _fis_package_inclusions where inclusiontype='SERV' and fk_package_id='".$accounts[0]->package_class_id."' and service_id = s.id)ispackage, s.print_index
				from _fis_service_sales ss
				inner join _fis_services s on s.id = ss.fk_service_id where fk_contract_id=$id and isViewPrint=1
				)dfa order by print_index"));
		
		$totalAdditionalAmount = 0;
		
		foreach ($inclusions as $row)
		{
			if(!$row->ispackage)
			{
				$totalAdditionalAmount = $totalAdditionalAmount + $row->total_price;
			}
		}
				
				
		/*$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [210, 1189]]);*/
		$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' =>'LETTER']);
		//$mpdf->Image('/images/funecare_contract.jpg', 0, 0, 210, 297, 'jpg', '', true, false);
		$mpdf->WriteHTML(view('sc_printing', ['accounts'=>$accounts, 'inclusions'=>$inclusions, 'totalAdditionalAmount'=>$totalAdditionalAmount]));
		$mpdf->use_kwt = true; 
		$mpdf->Output();
	}

	public function printDSWD(Request $request)
	{
		$id = json_decode($request->post()['id']);
		//return $myid;
		
		$accounts = DB::select(DB::raw("select *, dbo._computeAge(birthday, getdate())as deceased_age from _SERVICE_CONTRACT_VIEW where contract_id=$id"));
		
		$inclusions = DB::select(DB::raw("select * from
		(select i.item_code, item_name as inclusionname, CAST(quantity as varchar(3)) + ' ' + unit_type as quantity, total_price,
				case when left(i.item_code, 2)='01' then 1
				else (select count(*)sdf from _fis_package_inclusions where inclusiontype='ITEM' and fk_package_id='".$accounts[0]->package_class_id."' and item_id = i.item_code)
				end as ispackage, i.print_index
				from _fis_item_sales sales
				inner join _fis_items i on sales.product_id = i.item_code
				where contract_id=$id and isViewPrint=1
				union all
				select CAST(s.id as varchar(3)) as item_code, service_name as inclusionname, CAST(service_duration as varchar(3)) + ' ' + duration_unit as quantity, total_amount,
				(select count(*)sdf from _fis_package_inclusions where inclusiontype='SERV' and fk_package_id='".$accounts[0]->package_class_id."' and service_id = s.id)ispackage, s.print_index
				from _fis_service_sales ss
				inner join _fis_services s on s.id = ss.fk_service_id where fk_contract_id=$id and isViewPrint=1
				)dfa order by print_index"));
		
		$totalAdditionalAmount = 0;
		
		foreach ($inclusions as $row)
		{
			if(!$row->ispackage)
			{
				$totalAdditionalAmount = $totalAdditionalAmount + $row->total_price;
			}
		}
				
				
		$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'LETTER']);
	
		//$mpdf->Image('/images/funecare_contract.jpg', 0, 0, 210, 297, 'jpg', '', true, false);
		$mpdf->WriteHTML(view('sc_printing_dswd', ['accounts'=>$accounts, 'inclusions'=>$inclusions, 'totalAdditionalAmount'=>$totalAdditionalAmount]));
		$mpdf->use_kwt = true; 
		$mpdf->Output();
	}
	
	public function statementPrint(Request $request)
	{
		$id = $request->post()['id'];
		
		$contracts = (array)json_decode($request->post()['contractparam']);
		
		$params = "";
		
		for($x=0; $x<count($contracts); $x++)
		{
			$params = $params.$contracts[$x];
			
			if($x + 1 != count($contracts))
			{
				$params = $params.",";
			}
			
		}
		
		echo $params;
		
		//return $myid;
		
		$accounts = DB::select(DB::raw("select *, dbo._computeAge(birthday, getdate())as deceased_age,
			isnull((
			select sum(total_price) as packagePrice from
			(
			select i.item_code, item_name as inclusionname, CAST(quantity as varchar(3)) + ' ' + unit_type as quantity, total_price,
							case when left(i.item_code, 2)='01' then 1
							when (select count(*)sdf from _fis_package_inclusions where inclusiontype='ITEM' and fk_package_id=scv.package_class_id and item_id = i.item_code) >=1 then 1
							else 0
							end as ispackage
							from _fis_item_sales sales
							inner join _fis_items i on sales.product_id = i.item_code
							where contract_id=scv.contract_id
							union all
							select CAST(s.id as varchar(3)) as item_code, service_name as inclusionname, CAST(service_duration as varchar(3)) + ' ' + duration_unit as quantity, total_amount,
							(select count(*)sdf from _fis_package_inclusions where inclusiontype='SERV' and fk_package_id=scv.package_class_id and service_id = s.id)ispackage
							from _fis_service_sales ss
							inner join _fis_services s on s.id = ss.fk_service_id where fk_contract_id=scv.contract_id
			)ff where ispackage=1),0) as packagePrice
			from _SERVICE_CONTRACT_VIEW scv where signee=$id
			and scv.contract_id in (".$params.")
			AND status='ACTIVE'"));
		
		$additionalServices = [];
		
		foreach ($accounts as $row)
		{
			$extra = DB::select(DB::raw("select '".$row->contract_no."' as contract_no, * from
				(select i.item_code, item_name as inclusionname, CAST(quantity as varchar(3)) + ' ' + unit_type as quantity, total_price,
				case when left(i.item_code, 2)='01' then 1
				else (select count(*)sdf from _fis_package_inclusions where inclusiontype='ITEM' and fk_package_id='".$row->package_class_id."' and item_id = i.item_code)
				end as ispackage
				from _fis_item_sales sales
				inner join _fis_items i on sales.product_id = i.item_code
				where contract_id=".$row->contract_id."
				union all
				select CAST(s.id as varchar(3)) as item_code, service_name as inclusionname, CAST(service_duration as varchar(3)) + ' ' + duration_unit as quantity, total_amount,
				(select count(*)sdf from _fis_package_inclusions where inclusiontype='SERV' and fk_package_id='".$row->package_class_id."' and service_id = s.id)ispackage
				from _fis_service_sales ss
				inner join _fis_services s on s.id = ss.fk_service_id where fk_contract_id=".$row->contract_id."
				)dfa  where ispackage=0 order by item_code"));
			
				foreach ($extra as $rows)
				{
					array_push($additionalServices, $rows);
				}	
		}

		$decease_name = DB::select(DB::raw("
					SELECT * FROM _fis_service_contract AS SC
					LEFT JOIN _fis_ProfileHeader as PH ON PH.id = SC.deceased_id
					LEFT JOIN _fis_deceaseInfo as DI ON DI.id = PH.id 
					WHERE contract_id in (".$params.")
					"));
		
		$accountcharging = DB::select(DB::raw("select sum(balance)totalamt, account_type from _fis_sc_charging
				inner join _fis_account on accountType = account_id
				where fk_scID in
				(
				select contract_id from _fis_service_contract where signee=$id and status='ACTIVE'
				and contract_id in (".$params.")
				) and isCancelled=0
				group by account_type"));
		
		$transactions = DB::select(DB::raw("select AR_Credit, reference_no, typename from _fis_sc_payments
				inner join _fis_paymenttype on payment_mode = typeid
				where contract_id in
				(
				select contract_id from _fis_service_contract where signee=$id and status='ACTIVE'
				and contract_id in (".$params.")
				)
				and left(tran_type,3)='PAY' and isCancelled=0"));
		
		$mpdf = new \Mpdf\Mpdf();
		$mpdf= new \Mpdf\Mpdf(['mode' => 'utf-8','format' => 'Letter','margin_left' => 0,'margin_right' => 0,'margin_top' => 0,'margin_bottom' => 0,'margin_header' => 0,'margin_footer' => 0]); //use this customization
		//$mpdf->Image('/images/funecare_contract.jpg', 0, 0, 210, 297, 'jpg', '', true, false);
		$mpdf->WriteHTML(view('statement_printing', ['client'=>$request->post()['client'],'user'=>$request->post()['user'], 'accounts'=>$accounts, 'addservices'=>$additionalServices, 'accountcharging'=>$accountcharging, 'transactions'=>$transactions, 
			'decease_name'=>$decease_name]));
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
	   		 * 
	   		 */
	   		
	   		//$isInventoryValid = \Illuminate\Support\Facades\Validator::make($value, $this->validatorsField('fisItemInventory'));
	   		
	   	$contract_discount = 0;
	   	$setDiscount = $value['sc_discount'] + $value['sc_chapel_discount'];
	   	$contract_discount = is_numeric($setDiscount) ? $setDiscount: 0;
	   	
	   		
	   	DB::beginTransaction();
	   	
	   		$acctgHeader = [];
	   		$acctgHeader['branch_code'] = $value['sc_branch'];
	   		$acctgHeader['transaction_date'] = date('Y-m-d');
	   		$acctgHeader['transaction_code'] = "JNLVOUCHER";
	   		$acctgHeader['username'] = $value['sc_transactedBy'];
	   		$acctgHeader['reference'] = "SC".$value['sc_number'];
	   		$acctgHeader['status'] = 1;
	   		$acctgHeader['particulars'] = "Funecare Service Contract #".$value['sc_number'];
	   		$acctgHeader['customer'] = $value['sc_signee'];
	   		$acctgHeader['checkno'] = "";
	   		
	   		
	   		$currentBranch = FisBranch::where([
	   				'branchID'=>$value['sc_branch']
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
	   			
	   			if($sc->has_tax==1)
	   			{
	   				$tax_deferred = ((float)$value['sc_net_amount'] / 1.12) * 0.12;
	   				$value['tax_deferred'] = number_format($tax_deferred, 2, '.', '');
	   			}
	   			
	   			else $value['tax_deferred'] = 0;
	   			
	   			$sc->update(
	   					['contract_amount'=>$value['sc_net_amount'], 
	   					 'grossPrice'=>	$value['sc_net_amount'] + $contract_discount,
	   					 'contract_balance'=> $value['sc_net_amount'],
	   					 'status'=>'ACTIVE',
	   				     'chapel_amount'=>$value['sc_chapel_amount'],
	   					 'chapel_discount'=>$value['sc_chapel_discount'],
	   					 'package_amount'=>$value['sc_amount'],
	   					 'chapel_selected'=>$value['sc_chapel_selected'],
	   					 'discount'=>$value['sc_discount'],
	   					 'date_posted'=>date('Y-m-d'),
	   					 'isPosted'=>1,
	   					 'tax_deferred'=> $value['tax_deferred'],
	   					 'tax_deferred_balance' => $value['tax_deferred']
	   					]
	   					);
	   			
	   			$scpayment = FisSCPayments::create([
	   					'contract_id'=>$value['sc_id'],
	   					'accountType'=>2, 
	   					'AR_Debit'=>$value['sc_net_amount'],
	   					'AR_Credit'=>0,
	   					'balance'=>$value['sc_net_amount'],
	   					'reference_no'=>'RELEASE_'.$sc->contract_no,
	   					'payment_date'=>date('Y-m-d'),
	   					'payment_mode'=>3, //3 sa for the meantime
	   					'transactedBy'=>$value['sc_transactedBy'],
	   					'isCancelled'=>0,	
	   					'isRemitted'=>0,
	   					'remittedTo'=>'',
	   					'isPosted'=>1,
	   					'remarks'=>'SC Contract Posting',
	   					'tran_type'=>'RELEASE',
	   					'tax_contribution'=> 0,
	   					'tax_balance' => $value['tax_deferred']
	   			]);
	   			
	   			
	   			$pushDetails['entry_type']="DR";
	   			$pushDetails['SLCode']="1-1-112-03-004";
	   			$pushDetails['amount']=$value['sc_net_amount'];
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
	   			
	   			$total_taxed_items = 0;
	   			$hasDiscountConsidered = false;
	   			foreach($value['item_inclusions'] as $row)
	   			{	   	
	   				
	   				try {
	   					
	   					$inventoryCount = FisProductList::where([
	   							'fk_item_id'=>$row->item_code,
	   							'isEncumbered'=>1,
	   							'branch'=>$value['sc_branch']
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
	   									'TransactedBy'=>$value['sc_transactedBy'],
	   									'isRemitted'=>0,
	   									'remittedTo'=>'',
	   									'OR_no'=>'-',
	   									'isCancelled'=>0,
	   									'sales_id'=>0
	   									
	   							]
	   							);
	   					
	   					
	   					
	   					
	   					
	   					
	   					if($sc->has_tax==1)
	   					{	
	   						//if casket, deduct the discount to the sales
	   						if(!$hasDiscountConsidered && substr($row->item_code, 0, 2)=='01')
	   						{
	   							$income_deducted = number_format(((float)$row->tot_price - $contract_discount) / 1.12, 2, '.', '');
	   							$output_tax = number_format(($row->tot_price - $contract_discount) - $income_deducted, 2, '.', '');
	   							
	   							$income_deducted = $row->tot_price - $output_tax;
	   							$hasDiscountConsidered = true;
	   							
	   						}
	   						
	   						else
	   						{$income_deducted = number_format((float)$row->tot_price / 1.12, 2, '.', '');
	   						
	   						 $output_tax = number_format($row->tot_price - $income_deducted, 2, '.', '');
	   						}
	   				
	   						
	   						$total_taxed_items += $output_tax;
	   						
	   				
	   						
	   						while( (float)$total_taxed_items > (float)$value['tax_deferred'])
	   						{

	   							$new_output_tax = $output_tax - ((float)$total_taxed_items - (float)$value['tax_deferred']);
	   							$total_taxed_items -= $output_tax;
	   							$output_tax = $new_output_tax;
	   							$total_taxed_items += $output_tax;
	   							
	   							$income_deducted = $row->tot_price - $output_tax;
	   						} 
	   						
	   						$pushDetails['entry_type']="CR";
	   						$pushDetails['SLCode']= $row->income_SLCode;
	   						$pushDetails['amount']= $income_deducted;
	   						$pushDetails['detail_particulars']="Income ".$row->item_name." from SC No.".$value['sc_number']." Signee: ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
	   					
	   						array_push($acctgDetails, $pushDetails);
	   						
	   						$pushDetails['entry_type']="CR";
	   						$pushDetails['SLCode']= '2-1-316-08-003'; //-->output tax
	   						$pushDetails['amount']= $output_tax;
	   						$pushDetails['detail_particulars']="Income ".$row->item_name." from SC No.".$value['sc_number']." Signee: ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
	   					
	   						array_push($acctgDetails, $pushDetails);
	   					}
	   					
	   					else
	   					{
	   						$pushDetails['entry_type']="CR";
	   						$pushDetails['SLCode']= $row->income_SLCode;
	   						$pushDetails['amount']= $row->tot_price;
	   						$pushDetails['detail_particulars']="Income ".$row->item_name." from SC No.".$value['sc_number']." Signee: ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
	   					
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
	   			
	   			
	   			foreach($value['item_inventory'] as $row)
	   			{
	   				try {
	   					
	   					$productList = FisProductList::where([
	   							'id'=>$row->id,
	   							'isEncumbered'=>1,
	   							'branch'=>$value['sc_branch']
	   					])->firstOrFail();
	   					
	   					//for remaining balance
	   					$forInventoryCount = FisProductList::where([
	   							'fk_item_id'=>$row->item_code,
	   							'isEncumbered'=>1,
	   							'branch'=>$value['sc_branch']
	   					])->count();
	   					
	   					
	   					FisItemInventory::create(
	   							[
	   									'transaction_date'=>date('Y-m-d'),
	   									'particulars'=>'Purchased by SC. #'.$sc->contract_no,
	   									'contract_id'=>$sc->contract_id,
	   									'dr_no'=>'-',
	   									'rr_no'=>'-',
	   									'process'=>'PUR-OUT',
	   									'remaining_balance'=> $forInventoryCount - 1,
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
	   						$pushDetails['entry_type']="CR";
	   						$pushDetails['SLCode']= $row->SLCode;
	   						$pushDetails['amount']= $productList->price;
	   						$pushDetails['detail_particulars']="To record Inventory of ".$row->item_name." from SC No.".$value['sc_number']." Signee Name : ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
	   						array_push($acctgDetails, $pushDetails);
	   						
	   						//different credit for each item. edited by harold 9/4/2019
	   						$creditSL = $row->expense_SLCode == "BRANCH" ? $currentBranch->borrowHO : $row->expense_SLCode;
	   						
	   						$pushDetails['entry_type']="DR";
	   						$pushDetails['SLCode']= $creditSL;
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
	   							'transactedBy'=>$value['sc_transactedBy'],
	   							'isRemitted'=>0,
	   							'dateRemitted'=>'1/1/1900',
	   							'RemittedTo'=>'',
	   							'sales_id'=>0,
	   							'isWalkin'=>0,
	   							'client'=>'',
	   							'signeeID'=>$sc->signee,
	   							'isCancelled'=>0,
	   							
	   					]);
	   					
	   					
	   					
	 
	   					//for tax
	   					if($sc->has_tax==1)
	   					{	
	   						//if chapel, deduct the discount to the sales	   						
	   						if(!$hasDiscountConsidered && stripos($row->service_name, 'CHAPEL')!==false)
	   						{
	   							$income_deducted = number_format(((float)$row->tot_price - $contract_discount) / 1.12, 2, '.', '');
	   							$output_tax = number_format(($row->tot_price - $contract_discount) - $income_deducted, 2, '.', '');
	   							
	   							$income_deducted = $row->tot_price - $output_tax;
	   							$hasDiscountConsidered = true;
	   							
	   						}
	   						
	   						else
	   						{ $income_deducted = number_format((float)$row->tot_price / 1.12, 2, '.', '');
	   						
	   						  $output_tax = number_format($row->tot_price - $income_deducted, 2, '.', '');
	   						}
	   						
	   						
	   						
	   						$output_tax = number_format($row->tot_price - $income_deducted, 2, '.', '');
							
	   						$total_taxed_items += $output_tax;
	   						
	   						while( (float)$total_taxed_items > (float)$value['tax_deferred'])
	   						{
	   							$new_output_tax = $output_tax - ((float)$total_taxed_items - (float)$value['tax_deferred']);
	   							$total_taxed_items -= $output_tax;
	   							$output_tax = $new_output_tax;
	   							$total_taxed_items += $output_tax;
	   							
	   							$income_deducted = $row->tot_price - $output_tax;
	   						}
	   						
	   						
	   						if(strpos($row->service_name, "GIFT COUPON") !== false)
	   						{
	   							$pushDetails['entry_type']="CR";
	   							$pushDetails['SLCode']= $currentBranch->borrowHO;
	   							$pushDetails['amount']= $income_deducted;
	   							$pushDetails['detail_particulars']="New entry";
	   							
	   						}
	   						
	   						else
	   						{
	   							$pushDetails['entry_type']="CR";
	   							$pushDetails['SLCode']= $row->SLCode;
	   							$pushDetails['amount']= $income_deducted;
	   							$pushDetails['detail_particulars']="New entry";
	   							
	   						}
	   						
	   						array_push($acctgDetails, $pushDetails);
	   						
	   						$pushDetails['entry_type']="CR";
	   						$pushDetails['SLCode']= '2-1-316-08-003'; //-->output tax
	   						$pushDetails['amount']= $output_tax;
	   						$pushDetails['detail_particulars']="New entry";
	   						
	   						array_push($acctgDetails, $pushDetails);
	   					}
	   					
	   					else
	   					{
	   						if(strpos($row->service_name, "GIFT COUPON") !== false)
	   						{
	   							$pushDetails['entry_type']="CR";
	   							$pushDetails['SLCode']= $currentBranch->borrowHO;
	   							$pushDetails['amount']= $row->tot_price;
	   							$pushDetails['detail_particulars']="New entry";
	   							
	   						}
	   						
	   						else
	   						{
	   							$pushDetails['entry_type']="CR";
	   							$pushDetails['SLCode']= $row->SLCode;
	   							$pushDetails['amount']= $row->tot_price;
	   							$pushDetails['detail_particulars']="New entry";
	   							
	   						}
	   						
	   						
	   						array_push($acctgDetails, $pushDetails);
	   					}
	   					
	   					
	   					//end for tax
	   					
	   					
	   					//array_push($acctgDetails, $pushDetails);
	   					
	   					
	   					
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
	   				
	   				
	   				
	   				$availments = DB::select(DB::raw("select product_id, (CAST(quantity as varchar(5)) + ' ' + unit_type) as totquantity, price, total_price, 'item' as inclusiontype, i.item_name as inclusionname from _fis_item_sales sales
				inner join _fis_items i on sales.product_id = i.item_code
				where contract_id=".$value['sc_id']."
				UNION ALL
				select CAST(fk_service_id as varchar(10)) as id, (CAST(service_duration as varchar(5)) + ' ' + duration_unit) as totquantity, total_amount, total_amount as totprice, 'service' as inclusiontype, s.service_name as inclusionname from _fis_service_sales ss
				inner join _fis_services s on s.id = ss.fk_service_id
				where fk_contract_id=".$value['sc_id']));
	   				
	   				$sc_details = DB::select(DB::raw("select sc.contract_id, contract_no, fun_branch, contract_date, 
					(s.firstname + ', ' + s.middlename + ' ' + s.lastname)signee,
					s.address as signeeaddress, sc.remarks, sc.burial_time, sc.discount, sc.grossPrice, sc.contract_amount, sc.contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
					d.birthday, d.address, d.causeOfDeath, sc.mort_viewing, cr.ReligionName, p.package_name, sc.has_tax, sc.tax_deferred, sc.tax_deferred_balance
					from _fis_service_contract sc
					inner join (select ph.* from _fis_profileheader ph inner join _fis_ProfileLogs  pl on ph.id = pl.fk_profile_id where pl.profile_type='Signee')s on sc.signee = s.id
					inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
					inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
					inner join _fis_ProfileLogs pl on ph.id = pl.fk_profile_id
					where pl.profile_type='Decease')d on sc.deceased_id = d.id
					inner join _fis_package p on sc.package_class_id = p.package_code
					inner join ClientReligion cr on d.religion = cr.ReligionID
					where contract_id =".$value['sc_id']));
	   				
	   				$sc_transaction = DB::select(DB::raw("select payment_id, account_type, AR_Debit, AR_Credit, balance, isnull(tax_contribution, 0)tax_contribution, isnull(tax_balance, 0)tax_balance, tran_type, reference_no, payment_date, payment_mode, transactedBy, remarks, isCancelled from _fis_sc_payments sp inner join _fis_account a
					on a.account_id = sp.accountType
					where contract_id=".$value['sc_id']));
	   				
	   				
	   				$sc_details[0]->has_tax = $sc_details[0]->has_tax == 1 ? true : false;
	   				
	   				return [
	   						'status'=>'saved',
	   						'message'=>[
	   								'service_contract' => $sc_details,
	   								'inclusions' => $availments,
	   								'transactions' => $sc_transaction
	   						]
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
						
						if($row->amount<0)
							break;
						
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
						
						$tax_dedication = 0;
						
						if($contract->has_tax==1)
						{
							$taxamt = ((float)$row->amount / 1.12) * 0.12;
							$tax_dedication = number_format($taxamt, 2, '.', '');
							
						}

						$remainingbalance = $contract->contract_balance - $row->amount;
						
						if($remainingbalance==0)
						{
							//do this trick if and only if shortage is less than 0.1 !!!
							if($contract->tax_deferred_balance - $tax_dedication < 0.1)
								$tax_dedication = $contract->tax_deferred_balance;
						}
						
						
						$remainingtaxbalance = $contract->tax_deferred_balance - $tax_dedication;
						
						//if remaining tax balance is below zero, deduct to excess to tax dedication
						while($remainingtaxbalance<0)
						{
							$tax_dedication = $tax_dedication - ($remainingtaxbalance * -1);
							$remainingtaxbalance = $contract->tax_deferred_balance - $tax_dedication;
						}
						
					
						
						
						
						$contract->update([
								'contract_balance'=> $remainingbalance,
								'tax_deferred_balance'=>$remainingtaxbalance,
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
						 'transactedBy'=>$value['bill_header']->transactedBy,
						 'isCancelled'=>0,
						 'isRemitted'=>0,
						 'remittedTo'=>'',
						 'isPosted'=>1,
						 'remarks'=>$value['bill_header']->remarks,
						 'tran_type'=>$remainingbalance== 0 ? 'PAYCLOSE' : 'PAYPARTIAL',
						 'tax_contribution'=>$tax_dedication,
						 'tax_balance'=>$remainingtaxbalance
						]);
						
						
						$acctgHeader_pay = [];
						$acctgDetails_pay = [];
						$pushDetails_pay= [];
						
						$paytype = FisPaymentType::find($row->pay_type);
						
						$acctgHeader_pay['branch_code'] = $contract->fun_branch;
						$acctgHeader_pay['transaction_date'] = date('Y-m-d');
						$acctgHeader_pay['transaction_code'] = $paytype->trandesc;
						$acctgHeader_pay['username'] = $value['bill_header']->transactedBy;
						$acctgHeader_pay['reference'] = "SCPay".$contract->contract_no."-".$value['bill_header']->reference;
						$acctgHeader_pay['status'] = $paytype->trantype;
						$acctgHeader_pay['particulars'] = "Posting of SC Payment w/ SC #".$contract->contract_no;
						$acctgHeader_pay['customer'] = $value['bill_header']->client;
						$acctgHeader_pay['checkno'] = "";
						
						
						$currentBranch = FisBranch::where([
								'branchID'=>$contract->fun_branch
						])->firstOrFail();
						
						
						$debitSL = $paytype->sl_debit== "BRANCH" ? $currentBranch->borrowCode : $paytype->sl_debit;
						
						if($paytype->sl_debit== "BRANCH-HO")
							$debitSL = $currentBranch->borrowHO;
						
						
						$pushDetails_pay['entry_type']="DR";
						$pushDetails_pay['SLCode']=$debitSL;
						$pushDetails_pay['amount']=$row->amount;
						$pushDetails_pay['detail_particulars']="To payment from SC Ref#".$value['bill_header']->reference." Client: ".$value['bill_header']->client;
						array_push($acctgDetails_pay, $pushDetails_pay);
						
						
						$pushDetails_pay['entry_type']="CR";
						$pushDetails_pay['SLCode']=$paytype->sl_credit;
						$pushDetails_pay['amount']=$row->amount;
						$pushDetails_pay['detail_particulars']="To payment from SC Ref#".$value['bill_header']->reference." Client: ".$value['bill_header']->client;
						array_push($acctgDetails_pay, $pushDetails_pay);
						
						if($contract->has_tax==1)
						{
							//debit deferred tax
							$pushDetails_pay['entry_type']="DR";
							$pushDetails_pay['SLCode']='2-1-316-08-003';
							$pushDetails_pay['amount']=$tax_dedication;
							$pushDetails_pay['detail_particulars']="To payment from SC Ref#".$value['bill_header']->reference." Client: ".$value['bill_header']->client;
							array_push($acctgDetails_pay, $pushDetails_pay);
							
							//credit output tax
							$pushDetails_pay['entry_type']="CR";
							$pushDetails_pay['SLCode']='2-1-317-01-001';
							$pushDetails_pay['amount']=$tax_dedication;
							$pushDetails_pay['detail_particulars']="To payment from SC Ref#".$value['bill_header']->reference." Client: ".$value['bill_header']->client;
							array_push($acctgDetails_pay, $pushDetails_pay);	
						}
						
						
						$saveacctg = AccountingHelper::processAccounting($acctgHeader_pay, $acctgDetails_pay);
						
						if($saveacctg['status']!='saved')
						{
							DB::rollback();
							return $saveacctg;
						}
						
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
						
						
						$tax_merchandise_dedication = 0;
						
					/*	if($salesheader->has_tax==1)
						{
							$taxamt = ((float)$row->amount / 1.12) * 0.12;
							$tax_merchandise_dedication= number_format($taxamt, 2, '.', '');
							
							
						} */
						
						
						
						$remainingbalance_sales = $salesheader->balance - $row->amount;
						
						/*if($remainingbalance_sales==0)
						{
							//do this trick if and only if shortage is less than 0.1 !!!
							if($salesheader->tax_deferred_balance - $tax_merchandise_dedication< 0.1)
								$tax_merchandise_dedication = $salesheader->tax_deferred_balance;
						} */
						
						$remaining_sales_taxbalance = 0;
						//$remaining_sales_taxbalance = $salesheader->tax_deferred_balance - $tax_merchandise_dedication;
						
						
						//if remaining tax balance is below zero, deduct to excess to tax dedication
						/*while($remaining_sales_taxbalance<0)
						{
							$tax_merchandise_dedication= $tax_merchandise_dedication- ($remainingbalance_sales * -1);
							$remaining_sales_taxbalance= $salesheader->tax_deferred_balance - $tax_merchandise_dedication;
						} */

						$salesheader->update([
								'balance'=> $remainingbalance_sales,
								'tax_deferred_balance'=> $remaining_sales_taxbalance,
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
								'transactedBy'=>$value['bill_header']->transactedBy,
								'payment_mode'=>$row->pay_type,
								'isCancelled'=>0,
								'isRemitted'=>0,
								'remittedTo'=>'',
								'isPosted'=>1,
								'remarks'=>$value['bill_header']->remarks,
								'tran_type'=>$remainingbalance_sales == 0 ? 'PAYCLOSE' : 'PAYPARTIAL',
								'tax_distribution'=> $tax_merchandise_dedication,
								'tax_balance'=> $remaining_sales_taxbalance
						]);
						
						$acctgHeader_pay = [];
						$acctgDetails_pay = [];
						$pushDetails_pay= [];
						
						$paytype = FisPaymentType::find($row->pay_type);
						
						$acctgHeader_pay['branch_code'] = $salesheader->fun_branch;
						$acctgHeader_pay['transaction_date'] = date('Y-m-d');
						$acctgHeader_pay['transaction_code'] = $paytype->trandesc;
						$acctgHeader_pay['username'] = $value['bill_header']->transactedBy;
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
						
						
						/*if($salesheader->has_tax==1)
						{
							//debit deferred tax
							$pushDetails_pay['entry_type']="DR";
							$pushDetails_pay['SLCode']='7-4-000-01';
							$pushDetails_pay['amount']=$tax_merchandise_dedication;
							$pushDetails_pay['detail_particulars']="Payment from Merch Ref#".$value['bill_header']->reference." Client: ".$value['bill_header']->client;
							array_push($acctgDetails_pay, $pushDetails_pay);
							
							//credit output tax
							$pushDetails_pay['entry_type']="CR";
							$pushDetails_pay['SLCode']='7-4-000-01';
							$pushDetails_pay['amount']=$tax_merchandise_dedication;
							$pushDetails_pay['detail_particulars']="Payment from Merch Ref#".$value['bill_header']->reference." Client: ".$value['bill_header']->client;
							array_push($acctgDetails_pay, $pushDetails_pay);
						} */
						
						$saveacctg = AccountingHelper::processAccounting($acctgHeader_pay, $acctgDetails_pay);
						if($saveacctg['status']!='saved')
						{
							DB::rollback();
							return $saveacctg;
						}
						
						
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
				
				if($value['sales_header']->client=='')
				{
					DB::rollBack();
					return [
						'status'=>'unsaved',
						'message'=>'Please dont leave Client empty'
					];
					
				}
				
				$sc_count = FisItemsalesHeader::where('fun_branch', $value['sales_header']->branch)->count();
				
				$value['merchandise_no'] = "M".date('Y')."-".str_pad($sc_count, 5, '0', STR_PAD_LEFT);
				$value['tax_deferred'] = 0;
				if($value['sales_header']->has_tax==1 || $value['sales_header']->has_tax=='1')
				{
					$tax_deferred = ((float)$value['grand_total'] / 1.12) * 0.12;
					$value['tax_deferred'] = number_format($tax_deferred, 2, '.', '');
					
				}
				
				
				
				$salesHead = FisItemsalesHeader::create([
					'OR_no'=>$value['merchandise_no'],
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
					'fun_branch'=>$value['sales_header']->branch,
					'is_member'=>$value['sales_header']->is_member,
					'is_senior_or_disabled'=>$value['sales_header']->is_senior_or_disabled,
					'has_tax'=>$value['sales_header']->has_tax,
					'tax_deferred'=>$value['tax_deferred'],
					'tax_deferred_balance'=>$value['tax_deferred'],
						
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
				
				
				$transactionsale = FisSalesTransaction::create([
						'sales_id'=>$salesHead->id,
						'accountType'=>2, //2 is for peronal. see _fis_account table
						'AR_Debit'=>$salesHead->total_amount,
						'AR_Credit'=>0,
						'balance'=>$salesHead->total_amount,
						'reference_no'=>$salesHead->OR_no,
						'payment_date'=>date('Y-m-d'),
						'transactedBy'=>$salesHead->transactedBy,
						'payment_mode'=>$value['sales_header']->PayType,
						'isCancelled'=>0,
						'isRemitted'=>0,
						'remittedTo'=>'',
						'isPosted'=>1,
						'remarks'=>'merchandise purchase posting',
						'tran_type'=>'RELEASE',
				]);
				
				
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
					
					
					if($value['sales_header']->reference=="")
					{
						DB::rollBack();
						return [
								'status'=>'unsaved',
								'message'=>'Reference must not be blank.'
						];
					}
					
					
					
					
					$tax_dedication = 0;
					
					if($value['sales_header']->has_tax==1)
					{
						$taxamt = ((float)$value['sales_header']->amount_pay / 1.12) * 0.12;
						$tax_dedication = number_format($taxamt, 2, '.', '');
					}

					$remainingbalance = $salesHead->balance - $value['sales_header']->amount_pay;
					
					if($remainingbalance==0)
					{
						//do this trick if and only if shortage is less than 0.1 !!!
						if($value['tax_deferred']- $tax_dedication< 0.1)
							$tax_dedication = $value['tax_deferred'];
					}
					
					$remainingtaxbalance = $value['tax_deferred'] - $tax_dedication;
					
					//if remaining tax balance is below zero, deduct to excess to tax dedication
					while($remainingtaxbalance<0)
					{
						$tax_dedication = $tax_dedication - ($remainingtaxbalance * -1);
						$remainingtaxbalance = $value['tax_deferred']- $tax_dedication;
					}
					
					
					
					
					
					$transactionsale = FisSalesTransaction::create([
							'sales_id'=>$salesHead->id,
							'accountType'=>2, //2 is for peronal. see _fis_account table
							'AR_Debit'=>0,
							'AR_Credit'=>$value['sales_header']->amount_pay,
							'balance'=>$remainingbalance,
							'reference_no'=>$value['sales_header']->reference,
							'payment_date'=>date('Y-m-d'),
							'transactedBy'=>$salesHead->transactedBy,
							'payment_mode'=>$value['sales_header']->PayType,
							'isCancelled'=>0,
							'isRemitted'=>0,
							'remittedTo'=>'',
							'isPosted'=>1,
							'remarks'=>'merchandising payment',
							'tax_distribution'=> $tax_dedication,
							'tax_balance' => $remainingtaxbalance,
							'tran_type'=>$salesHead->balance - $value['sales_header']->amount_pay == 0 ? 'PAYCLOSE' : 'PAYPARTIAL',
					]);
					
					
					$salesHead->update([
							'balance' => $transactionsale->balance,
							'tax_deferred_balance' => $remainingtaxbalance,
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
				
				$total_taxed_items = 0;
				
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
										'TransactedBy'=>$salesHead->transactedBy,
										'isRemitted'=>0,
										'remittedTo'=>'',
										'OR_no'=>$value['sales_header']->reference,
										'isCancelled'=>0,
										'sales_id'=>$salesHead->id
										
								]
								);
						
						if($value['sales_header']->has_tax==1 || $value['sales_header']->has_tax=='1')
						{
							$income_deducted = number_format((float)$row->tot_price / 1.12, 2, '.', '');
							$output_tax = number_format($row->tot_price - $income_deducted, 2, '.', '');
							
							
							
							$total_taxed_items += $output_tax;
							
							while( (float)$total_taxed_items > (float)$value['tax_deferred'])
							{	
								$new_output_tax = $output_tax - ((float)$total_taxed_items - (float)$value['tax_deferred']);
								$total_taxed_items -= $output_tax;
								$output_tax = $new_output_tax;
								$total_taxed_items += $output_tax;
								
								$income_deducted = $row->tot_price - $output_tax;
							}
							
							
							$pushDetails['entry_type']="CR";
							$pushDetails['SLCode']= "4-1-410-03-001"; //--charge everything to miscellaneous income
							$pushDetails['amount']= $income_deducted;
							$pushDetails['detail_particulars']="Income ".$row->item_name." frm Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
							
							array_push($acctgDetails, $pushDetails);
							
							
							$pushDetails['entry_type']="CR";
							$pushDetails['SLCode']= "2-1-317-01-001"; //--deferred output tax
							$pushDetails['amount']= $output_tax;
							$pushDetails['detail_particulars']="Income ".$row->item_name." frm Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
							
							
							
							
							
							array_push($acctgDetails, $pushDetails);
							
							
						}
						
						else
						{
							$pushDetails['entry_type']="CR";
							$pushDetails['SLCode']= "4-1-410-03-001"; //--charge everything to miscellaneous income
							$pushDetails['amount']= $row->tot_price;
							$pushDetails['detail_particulars']="Income ".$row->item_name." frm Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
							
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
				
				
				foreach($value['item_inventory'] as $row)
				{
					try {
						
						//for remaining balance
						$forInventoryCount = FisProductList::where([
								'fk_item_id'=>$row->item_code,
								'isEncumbered'=>1,
								'branch'=>$value['sales_header']->branch
						])->count();
						
						
						FisItemInventory::create(
								[
										'transaction_date'=>date('Y-m-d'),
										'particulars'=>'Purchased by '.$value['sales_header']->client,
										'contract_id'=>0,
										'dr_no'=>'-',
										'rr_no'=>'-',
										'process'=>'PUR-OUT',
										'remaining_balance'=> $forInventoryCount - 1,
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
							$pushDetails['entry_type']="CR";
							$pushDetails['SLCode']= $row->SLCode;
							$pushDetails['amount']= $productList->price;
							$pushDetails['detail_particulars']="To record Inventory of ".$row->item_name." from Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
							array_push($acctgDetails, $pushDetails);
							
							
							//different credit for each item. edited by harold 9/4/2019
							$creditSL = $row->expense_SLCode == "BRANCH" ? $currentBranch->borrowHO : $row->expense_SLCode;
												
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= $creditSL;
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
								'transactedBy'=>$salesHead->transactedBy,
								'isRemitted'=>0,
								'dateRemitted'=>'1/1/1900',
								'RemittedTo'=>'',
								'sales_id'=>$salesHead->id,
								'isWalkin'=>0,
								'client'=>$value['sales_header']->client,
								'signeeID'=>$value['sales_header']->signee_id,
								'isCancelled'=>0,
								
						]);
						
						
						
						if($value['sales_header']->has_tax==1 || $value['sales_header']->has_tax=='1')
						{
							$income_deducted = number_format((float)$row->tot_price / 1.12, 2, '.', '');
							$output_tax = number_format($row->tot_price - $income_deducted, 2, '.', '');
							
							
							$total_taxed_items += $output_tax;
							
							while( (float)$total_taxed_items > (float)$value['tax_deferred'])
							{
								$new_output_tax = $output_tax - ((float)$total_taxed_items - (float)$value['tax_deferred']);
								$total_taxed_items -= $output_tax;
								$output_tax = $new_output_tax;
								$total_taxed_items += $output_tax;
								
								$income_deducted = $row->tot_price - $output_tax;
							}
							
							
							if(strpos($row->service_name, "GIFT COUPON") !== false)
							{
								$pushDetails['entry_type']="CR";
								$pushDetails['SLCode']= $currentBranch->borrowHO;
								$pushDetails['amount']= $income_deducted;
								$pushDetails['detail_particulars']="Income of ".$row->service_name." from Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
							}
							
							
							else
							{
								$pushDetails['entry_type']="CR";
								$pushDetails['SLCode'] = "4-1-410-03-001"; //--charge everything to miscellaneous income
								$pushDetails['amount']= $income_deducted;
								$pushDetails['detail_particulars']="Income of ".$row->service_name." from Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
								
							}
							
							array_push($acctgDetails, $pushDetails);
							
							$pushDetails['entry_type']="CR";
							$pushDetails['SLCode']= "2-1-317-01-001"; //--deferred output tax
							$pushDetails['amount']= $output_tax;
							$pushDetails['detail_particulars']="deferred output tax";
							
							array_push($acctgDetails, $pushDetails);
							
							
						}
						
						else
						{
							if(strpos($row->service_name, "GIFT COUPON") !== false)
							{
								$pushDetails['entry_type']="CR";
								$pushDetails['SLCode']= $currentBranch->borrowHO;
								$pushDetails['amount']= $row->tot_price;
								$pushDetails['detail_particulars']="Income of ".$row->service_name." from Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
							}
							
							
							else
							{
								$pushDetails['entry_type']="CR";
								$pushDetails['SLCode'] = "4-1-410-03-001"; //--charge everything to miscellaneous income
								$pushDetails['amount']= $row->tot_price;
								$pushDetails['detail_particulars']="Income of ".$row->service_name." from Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
								
							}
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
			
			$qry = DB::select(DB::raw("SELECT commodity, id, reference, charge_account, 'PERSONAL' as charge_label, pay_type, 'Cash Payment' as pay_label, balance, amount, has_tax FROM
					(
					select signee, 'SERVICE CONTRACT' as commodity, contract_id as id, contract_no as reference, 2 as charge_account, 1 as pay_type, contract_balance as balance, 0 as amount, isnull(has_tax, 0) as has_tax from _fis_service_contract where status='ACTIVE' and contract_balance>0
					and fun_branch='".$request->post()['funbranch']."'
					UNION ALL
					select signee_id as signee, 'ADDTL. PURCHASES' as commodity, id, OR_no as reference, 2 as charge_account, 1 as pay_type,
					balance,
					0 as amount, isnull(has_tax, 0) as has_tax
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
			
			$qry = DB::select(DB::raw("
				SELECT status, contract_id, contract_no, (s.lastname + ', ' + s.firstname + ' ' + s.middlename)signee, 
				(d.lastname + ', ' + d.firstname + ' ' +d.middlename)deceased, contract_date, package_name, 
				 CONVERT(VARCHAR(30),contract_amount,0) AS contract_amount, 
				 CONVERT(VARCHAR(30),contract_balance,0) AS contract_balance, 
				 CONVERT(VARCHAR(30),package_amount,0) AS package_amount, 
				 CONVERT(VARCHAR(30),grossPrice,0) AS grossPrice, 
				 CONVERT(VARCHAR(30),sc.discount,0) AS discount
				 FROM _FIS_SERVICE_CONTRACT sc
				inner join _fis_package pk on sc.package_class_id = pk.package_code 
inner join (select ph.* from _fis_profileheader ph
inner join _fis_ProfileLogs pl on ph.id = pl.fk_profile_id where pl.profile_type='Signee' )s on sc.signee = s.id
inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id 
inner join _fis_ProfileLogs pl on ph.id = pl.fk_profile_id
where pl.profile_type='Decease')d 
on sc.deceased_id = d.id where sc.status<>'CANCELLED' and sc.fun_branch='".$request->post()['branch']."' order by sc.contract_id desc
				"));
			
			return $qry;
			
			
		} catch (Exception $e) {
			return [
					'status'=>'error',
					'message'=>$e->getMessage()
			];
		}
		
	}

	public function getIncentives(Request $request) {
		try {
		$incentives = DB::select(DB::raw("
			SELECT P.id, P.customer_id,(P.lastname + ', ' + P.firstname + ' ' + P.middlename) member_name, 
			P.firstname, P.lastname, P.middlename, P.contact_no, P.address, P.is_member, 'Informant' as profile_type,
			PL.isActive
			from _fis_profileHeader as P 
			LEFT JOIN _fis_ProfileLogs AS PL ON P.id = PL.fk_profile_id
			WHERE PL.profile_type = 'Informant'
			"));
			if($incentives)
				return	$incentives;
				else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}
	
	
	
	public function getAccounts()
	{
		$accounts = DB::select(DB::raw("select account_id as value, account_type as label from _fis_account"));
		$accounts2 = DB::select(DB::raw("SELECT account_id as value, account_type as label FROM _fis_account WHERE account_type NOT IN('TC_MORTUARY') "));
		$payment_type = DB::select(DB::raw("select typeid as value, typename as label from _fis_paymenttype"));
		
		return [
			'accounts' => $accounts,
			'accounts2' => $accounts2,
			'payment_type' => $payment_type
		];
		
	}
	
	
	public function getItemsServicesForMerchandising(Request $request)
	{
		try {
			$user_check = DB::select(DB::raw("SELECT item_code, item_name, 0 as quantity, selling_price as price, 0 as discount, 0 as tot_price, SLCode, income_SLCode FROM
				_fis_items fi
				WHERE isActive = '1' and left(fi.item_code,2)<>'01'
				order by item_code asc
				"));
			
			$services = DB::select(DB::raw("SELECT fs.id, service_name, 0 as amount, 0 as less,
				0 as duration, '' as type_duration, 0 as tot_price, SLCode
				FROM _fis_services fs
				WHERE isActive = '1'"));
			
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
			
			$sc_number = ''.$value['fun_branch'].'-'.date("Y").'-'.$value['contract_no'];

			if ($sc_number!="") {
			$memcount = ServiceContract::where(['contract_no'=>$sc_number])->first();

				if($memcount)
				{
					return [
						'status'=>'unsaved',
						'message'=>'Contact Number Already Exist.'
					];	
				}
			}

			$sc_count = ServiceContract::where('fun_branch', $value['fun_branch'])->count();
			
			//$value['contract_no'] = $value['fun_branch']."-".date('Y')."-".str_pad($sc_count, 5, '0', STR_PAD_LEFT);
			$value['contract_no'] = $value['fun_branch']."-".date('Y')."-".str_pad($value['contract_no'], 5, '0', STR_PAD_LEFT);
			$value['contract_balance'] = $value['contract_amount'];
			$value['contract_date'] = date('Y-m-d');
			$value['burial_time'] = date_format(date_create($value['burial_time']), 'Y-m-d H:i:s');
			$serviceContract = ServiceContract::create($value);
			
			$user_check = DB::select(DB::raw("SELECT item_code, item_name, isActive, quantity, price, discount, (price * quantity) as tot_price, SLCode, income_SLCode FROM
				(SELECT item_code, isActive, item_name, isnull(quantity, 0) as quantity, selling_price as price, 0 as discount, 0 as tot_price, SLCode, income_SLCode FROM _fis_items fi 
				LEFT JOIN (SELECT * FROM _fis_package_inclusions
				WHERE fk_package_id='".$serviceContract->package_class_id."'
				AND inclusionType='Item')b ON fi.item_code = b.item_id WHERE isActive='1')sdf 
				where (left(item_code,2)<>'01' or quantity>=1)
				order by item_code asc, quantity "));
			
			    $sc_details = DB::select(DB::raw("select sc.contract_id, contract_no, fun_branch, contract_date, 
					(s.firstname + ', ' + s.middlename + ' ' + s.lastname)signee,
					s.address as signeeaddress, sc.remarks, sc.burial_time, sc.discount, sc.grossPrice, sc.contract_amount, sc.contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
					d.birthday, d.address, d.causeOfDeath, sc.mort_viewing, cr.ReligionName, p.package_name, p.package_code, sc.embalming_place, sc.has_tax, sc.tax_deferred, sc.tax_deferred_balance
					from _fis_service_contract sc
					inner join (select ph.* from _fis_profileheader ph inner join _fis_ProfileLogs  pl on ph.id = pl.fk_profile_id where pl.profile_type='Signee')s on sc.signee = s.id
					inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
					inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
					inner join _fis_ProfileLogs pl on ph.id = pl.fk_profile_id
					where pl.profile_type='Decease')d on sc.deceased_id = d.id
					inner join _fis_package p on sc.package_class_id = p.package_code
					inner join ClientReligion cr on d.religion = cr.ReligionID
					where contract_id =".$serviceContract->contract_id)); 
			    
			    $services = DB::select(DB::raw("select * from
					(
					SELECT fs.id, service_name, isnull(a.service_price, 0) as amount, 0 as less, isnull(duration, '') as duration, isnull(type_duration, '') as type_duration, isnull(a.service_price, 0) as tot_price, SLCode  FROM _fis_services fs
					left join
					(
					select * from _fis_package_inclusions where fk_package_id='".$serviceContract->package_class_id."' and inclusionType='SERV'
					)a on fs.id = a.service_id and fs.isActive=1
					)sdfa
					order by duration desc"));
				
			    $package_selected = DB::select(DB::raw("select * from
						(
						SELECT
						case when item_id = '-' then CAST(service_id as varchar(5))
						else item_id end as columnid,
						isnull(item_name, service_name) as name,
						case when quantity < 1 then duration
						else quantity end as quantity,
						isnull(unit_type, type_duration) as uom,
						service_price as price, total_amount as total_price
						FROM _fis_package_inclusions fpi
						left join _fis_items i on fpi.item_id = i.item_code
						left join _fis_services s on fpi.service_id = s.id
						WHERE fk_package_id='".$serviceContract->package_class_id."'
						)fas
						order by columnid"));
			    
			    $chapel_rentals = DB::select(DB::raw("select id as value, chapel_name as label from _fis_chapel_package"));
			    
			    $sc_details[0]->has_tax = $sc_details[0]->has_tax == 1 ? true : false;
			    
			return [
						'status'=>'saved',
						'message'=> [
								'service_contract' => $sc_details,
								'item_inclusions' => $user_check,
								'service_inclusions' => $services,
								'package_selected' => $package_selected,
								'chapel_rentals' => $chapel_rentals
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
				

				$presentation = DB::select(DB::raw("select top ".$row->quantity." item_code, item_name, pl.id, serialno, ".$row->price." as sell_price, SLCode, expense_SLCode from _fis_productlist pl
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
		
		
		$appendix = isset($request->post()['typesearch']) ? "PL.profile_type in ('Walk-in', 'Signee') " : "PL.profile_type in ('Walk-in', 'Signee') ";
		
		try {
			$user_check = DB::select(DB::raw("SELECT top 5 PH.id as value, (PH.lastname + ', ' + PH.firstname + ' ' + PH.middlename)label, (select cast(count(*) as varchar(2)) + ' CONTRACT/S AVAILED' AS contractsavailed from _fis_service_contract where signee=PH.id) as sublabel  from _fis_profileheader AS PH
			LEFT JOIN _fis_ProfileLogs AS PL ON PH.id = PL.fk_profile_id
			WHERE ".$appendix." and (PH.lastname + ', ' + PH.firstname + ' ' + PH.middlename) like '%".$request->post()['name']."%'"));


			
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


	public function getContractDecease(Request $request)
	{
		$value="";
		
	
		try {
			$user_check = DB::select(DB::raw("
			SELECT top 5 SC.contract_id as value,  (PH.lastname + ', ' + PH.firstname + ' ' + PH.middlename)label, 
			SC.contract_no as sublabel, PC.package_name, CONVERT(VARCHAR(30),SC.package_amount,0) AS package_amount,
			SC.contract_id, 
			CONVERT(VARCHAR(30),SALES.price,0) AS casket_price
			FROM _fis_service_contract AS SC
			INNER JOIN _fis_ProfileHeader AS PH ON SC.deceased_id = PH.id
			INNER JOIN _fis_ProfileLogs AS PL ON PH.id = PL.fk_profile_id
			INNER JOIN _fis_package AS PC ON SC.package_class_id = PC.package_code
			INNER JOIN _fis_item_sales AS SALES ON SALES.contract_id = SC.contract_id
			WHERE PL.profile_type = 'Decease'
			AND SC.status not in ('CANCELLED', 'DRAFT') AND left(SALES.product_id,2)='01'
			AND (PH.lastname + ', ' + PH.firstname + ' ' + PH.middlename) like '".$request->post()['name']."%'
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

	public function getInformantSearch(Request $request)
	{
		$value="";
		

		try {
			$user_check = DB::select(DB::raw("SELECT top 5 PH.id as value, (PH.lastname + ', ' + PH.firstname + ' ' + PH.middlename)label  from _fis_profileheader AS PH
			LEFT JOIN _fis_ProfileLogs AS PL ON PH.id = PL.fk_profile_id
			where PL.profile_type= 'Informant' and (PH.lastname + ', ' + PH.firstname + ' ' + PH.middlename) like '".$request->post()['name']."%'"));
			
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
			$user_check = DB::select(DB::raw("SELECT top 5 PH.id as value, (PH.lastname + ', ' + PH.firstname + ' ' + PH.middlename)label, isnull(is_taxable, 0) taxable  from _fis_ProfileHeader AS PH
			LEFT JOIN _fis_ProfileLogs AS PL ON PH.id = PL.fk_profile_id
			where PL.profile_type='Decease' and (PH.lastname + ', ' + PH.firstname + ' ' + PH.middlename) like '".$request->post()['name']."%'"));
			
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

	public function getWalkin(Request $request)
	{
		$value="";
		
		try {
			$user_check = DB::select(DB::raw("SELECT top 5 PH.id as value, (PH.lastname + ', ' + PH.firstname + ' ' + PH.middlename)label, PH.firstname, PH.lastname, PH.middlename, PH.address, PH.contact_no  from _fis_ProfileHeader AS PH
			LEFT JOIN _fis_ProfileLogs AS PL ON PH.id = PL.fk_profile_id
			where PL.profile_type='Walk-in' and (PH.lastname + ', ' + PH.firstname + ' ' + PH.middlename) like '%".$request->post()['name']."%'"));
			
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

	public function getCCLocations(Request $request)
	{
		try {
	
			$CC = DB::select(DB::raw("SELECT * FROM _fis_locations"));
			
			if($CC)
				return	$CC;
				else return [];
			
			
			
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
	

			/*$user_check = DB::select(DB::raw("SELECT * from SystemUser inner join institutionparameter on 1=1 where UserStatus = 1 and username='".$value['username']."'"));
			
			foreach ($user_check as $row){
				
					if (Hash::check($value['password'], $row->Password)) {
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

					
					else
					{
						return [
								'status'=>'error',
								'message'=>'Invalid Username/Password or Account Disabled.'
						];
						
					}
		

			} */

			
					$user_check = DB::select(DB::raw("SELECT * from SystemUser inner join institutionparameter on 1=1 where UserStatus = 1 and UserName='".$value['username']."' and Password='".$value['password']."'"));

					if ($user_check) {
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
					else
					{
						return [
								'status'=>'error',
								'message'=>'Invalid Username/Password or Account Disabled.'
						];
						
					}
			
		} catch (\Exception $e) {
			return [
					'status'=>'error',
					'message' => $e->getMessage()
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
		$user_check = DB::select(DB::raw("SELECT branch_code as value, branch_name as label from _fis_settings_branches"));

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

	public function updateLocation(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['locationUpdate']);
			
				$cc = FisLocation::find($value['id']);
	   			$cc->update([
	   					'label'=>$value['location'],
	   					'value'=>$value['location'],
	   					'type'=>$value['type']
	   				]);
			
			return [
					'status'=>'saved',
					'message'=>$cc
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}
	

	public function updatePassword(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['passswordUpdate']);
				$password = FisPassword::find($value['UserName']);


				
				/*if (Hash::check($value['old_password'], $password->Password)){
					$password->update([
	   						'Password'=>bcrypt($value['new_password'])
	   					]);

					return [
						'status'=>'saved',
						'message'=>$password
					];
				}*/

				if ($value['old_password'] == $password->Password) {
					$password->update([
	   						'Password'=>$value['new_password']
	   					]);

					return [
						'status'=>'saved',
						'message'=>$password
					];
				}
	   			else{
	   				return [
						'status'=>'unsaved'
					];
	   			}
			
				
			
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
				$branch = FisBranches::find($value['branch_code']);
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
			      'date_updated' => date('Y-m-d'),
			      'transactedBy' => $value['transactedBy']
				]);

	   			if(($value['profile_type']) == 'Decease'){
				$value['fk_profile_id'] = $value['id'];
				$memberProfile = FisDeceased::find($value['fk_profile_id']);
				$memberProfile->update([
				  'birthday' => date_format(date_create($value['birthday']), 'Y-m-d H:i:s'),
				  'date_died' => date_format(date_create($value['date_died']), 'Y-m-d H:i:s'),
			      'causeOfDeath' => $value['causeOfDeath'],
			      'deathPlace' => $value['deathPlace'],
			      'religion' => $value['religion'],
			      'primary_branch' => $value['primary_branch'],
			      'relationToSignee' => $value['relationToSignee']
				]);
				}

				else if (($value['profile_type']) == 'Signee') {
				$value['fk_profile_id'] = $value['id'];
				$memberProfile = FisSignee::find($value['fk_profile_id']);
				$memberProfile->update([
			      'fb_account' => $value['fb_account'],
			      'email_address' => $value['email_address']
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

	public function updateIncDetails(Request $request)
	{
		try {
			$value = (array)json_decode($request->post()['incentivesData']);
			
	   		$incDetails = FisIncentives::find($value['id']);
	   		$incDetails->update(
	   			[ 'incentives' => $value['incentives'],
			      'member_type' => $value['member_type'],
			      'date_inform' => date_format(date_create($value['date_inform']), 'Y-m-d H:i:s'),
			      'pull_out' => $value['pull_out'],
			      'remarks' => $value['remarks']
				]);
	
			
			return [
					'status'=>'saved',
					'message'=>$incDetails
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function updateIncentives(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['incentivesData']);

				if ($value['amount'] == $value['inc_to_claim']) {
					$incentives = FisIncentives::find($value['id']);
					$incentives->update(
	   					['status'=> 'CLAIMED',
	   					'remarks'=> $value['remarks'],
	   					'date_claim'=> date('Y-m-d'),
	   					'balance' => 0,
	   					'transactedClaimedBy'=> $value['transactedBy']
	   				]);
				
		   			$incentives_trans = FisIncentivesLedger::create([
					  'transaction_id' => $incentives->id,
				      'informant_id' => $value['fk_profile_id'],
				      'contract_no' => $value['contract_no'],
				      'package_availed' => $value['package_amount'],
				      'reference_no'=> $value['reference_no'],
				      'incentives' => $value['inc_to_claim'],
				      'date_claim'=> date('Y-m-d'),
				      'status'=> 'CLAIMED',
				      'balance' => 0,
				      'amount'=> $value['amount'],
				      'transactedBy'=> $value['transactedBy']
					]);
				}

			
				else if ($value['status'] == 'PARTIAL') {
					if ($value['amount'] == $value['balance']) {
						$bal =  $value['balance'] - $value['amount'];
						$incentives = FisIncentives::find($value['id']);
						$incentives->update(
		   					['status'=> 'CLAIMED',
		   					'balance' => $bal,
		   					'remarks'=> $value['remarks'],
		   					'date_claim'=> date('Y-m-d'),
		   					'transactedClaimedBy'=> $value['transactedBy']
		   				]);
				
			   			$incentives_trans = FisIncentivesLedger::create([
						  'transaction_id' => $incentives->id,
					      'informant_id' => $value['fk_profile_id'],
					      'contract_no' => $value['contract_no'],
					      'balance' => $bal,
					      'amount'=> $value['amount'],
					      'package_availed' => $value['package_amount'],
					      'reference_no'=> $value['reference_no'],
					      'incentives' => $value['inc_to_claim'],
					      'date_claim'=> date('Y-m-d'),
					      'status'=> 'CLAIMED',
					      'transactedBy'=> $value['transactedBy']
						]);
					}

					else {
						$bal =   $value['balance'] - $value['amount'];
						$incentives = FisIncentives::find($value['id']);
						$incentives->update(
		   					['status'=> 'PARTIAL',
		   					'balance' => $bal,		
		   					'remarks'=> $value['remarks'],
		   					'date_claim'=> date('Y-m-d'),
		   					'transactedClaimedBy'=> $value['transactedBy']
		   				]);
				
			   			$incentives_trans = FisIncentivesLedger::create([
						  'transaction_id' => $incentives->id,
					      'informant_id' => $value['fk_profile_id'],
					      'contract_no' => $value['contract_no'],
					      'balance' => $bal,
					      'amount'=> $value['amount'],
					      'package_availed' => $value['package_amount'],
					      'reference_no'=> $value['reference_no'],
					      'incentives' => $value['inc_to_claim'],
					      'date_claim'=> date('Y-m-d'),
					      'status'=> 'PARTIAL',
					      'transactedBy'=> $value['transactedBy']
						]);
					}
					
				}

				else {
					$bal =  $value['inc_to_claim'] - $value['amount'] ;
					$incentives = FisIncentives::find($value['id']);
					$incentives->update(
	   					['status'=> 'PARTIAL',
	   					'balance' => $bal,
	   					'remarks'=> $value['remarks'],
	   					'date_claim'=> date('Y-m-d'),
	   					'transactedClaimedBy'=> $value['transactedBy']
	   				]);
				
		   			$incentives_trans = FisIncentivesLedger::create([
					  'transaction_id' => $incentives->id,
				      'informant_id' => $value['fk_profile_id'],
				      'contract_no' => $value['contract_no'],
				      'amount'=> $value['amount'],
				      'balance' => $bal,
				      'package_availed' => $value['package_amount'],
				      'reference_no'=> $value['reference_no'],
				      'incentives' => $value['inc_to_claim'],
				      'date_claim'=> date('Y-m-d'),
				      'status'=> 'PARTIAL',
				      'transactedBy'=> $value['transactedBy']
					]);
				}

					
			
			return [
					'status'=>'saved',
					'message'=>$incentives,$incentives_trans
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

	public function deleteLocation(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['locationDelete']);
			
				$cc = FisLocation::find($value['id']);
	   			$cc->delete();
			
			return [
					'status'=>'saved',
					'message'=>$cc
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
			
				$branch = FisBranches::find($value['branch_code']);
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


	public function getUserDetails(Request $request) {
		$value = (array)json_decode($request->post()['UserName']);
		try {
		$info = DB::select(DB::raw("
			SELECT *, B.name as branch_name FROM SystemUser AS S 
			INNER JOIN _fis_branch AS B ON S.FKBranchID = B.branchID
			WHERE S.UserName = '".$value['UserName']."'
			"));	

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


	public function updateUserDetailInfo(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['usersData']);
			
				$user = FisPassword::find($value['UserName']);
			
					$user->update(
	   					['Position'=> $value['Position'],
	   					'FirstName'=> $value['FirstName'],
	   					'MiddleName'=> $value['MiddleName'],
	   					'LastName'=> $value['LastName'],
	   					'DateUpdated'=> date('Y-m-d'),
	   					'UpdatedBy'=> $value['UserName']
	   				]);
				
	   				
			
			return [
					'status'=>'saved',
					'message'=>$user
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function getActiveAccount(Request $request) {
		$value = "";
		try {
		$info = DB::select(DB::raw("
			SELECT S.UserName,(S.LastName+', '+S.FirstName+' '+S.MiddleName) AS full_name, S.UserStatus, S.FKRoleID, S.FKBranchID,
			B.name as branch_name FROM SystemUser AS S 
			INNER JOIN _fis_branch AS B ON S.FKBranchID = B.branchID
			"));	

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

	public function updateAccountRole(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['userData']);
			
				$user = FisPassword::find($value['UserName']);
			
					$user->update(
	   					['FKRoleID'=> $value['role']
	   				]);
				
	   				
			
			return [
					'status'=>'saved',
					'message'=>$user
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function deactivateUser(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['userData']);
			
				$user = FisPassword::find($value['UserName']);
			
					$user->update(
	   					['UserStatus'=>0]);
				
	   				
			
			return [
					'status'=>'saved',
					'message'=>$user
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function activateUser(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['userData']);
			
				$user = FisPassword::find($value['UserName']);
			
					$user->update(
	   					['UserStatus'=>1]);
				
	   				
			
			return [
					'status'=>'saved',
					'message'=>$user
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function getIncentivesId(Request $request) {
		$value = (array)json_decode($request->post()['dataIncentives']);
		try {
			$user_check = DB::select(DB::raw("
				SELECT id,informant_id, decease_id, decease_name, contract_no, package_name, CONVERT(VARCHAR(30),package_amount,0) AS package_amount, CONVERT(VARCHAR(30),balance,0) AS balance, 
				CONVERT(VARCHAR(30),date_inform,22) AS date_inform,
				pull_out, remarks, CONVERT(VARCHAR(30),casket_price,0) AS casket_price, CONVERT(VARCHAR(30),incentives,0) AS incentives, 
				status, CONVERT(VARCHAR(30),date_claim,22) AS date_claim, member_type
				FROM _fis_informantInfo WHERE informant_id = '".$value['fk_profile_id']."'
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

	public function AddIncentives(Request $request) {
		try {

			$value = (array)json_decode($request->post()['incentivesData']);

			/*if ($value['contract_no']!="") {
			$info = FisInformant::where(['contract_no'=>$value['contract_no']])->first();

				if($info)
				{
					return [
						'status'=>'unsaved',
						'message'=>'Deceased Already Exist.'
					];	
				}
			}*/

					$memberProfile = FisInformant::create([
					  'informant_id' => $value['informant_id'],
					  'decease_id' => $value['decease_id'],
				      'decease_name' => $value['decease_name'],
				      'contract_no' => $value['contract_no'],
				      'package_name' => $value['package_name'],
				      'package_amount' => $value['package_amount'],
				      'date_inform' => date_format(date_create($value['date_inform']), 'Y-m-d H:i:s'),
				      'pull_out' =>  $value['pull_out'],
				      'remarks' =>  $value['remarks'],
				      'member_type' =>  $value['list'],
				      'casket_price' =>  $value['casket_price'],
				      'incentives' =>  $value['incentives'],
				      'balance' =>  $value['incentives'],
				      'status' => 'UNCLAIMED',
				      'createdBy' =>  $value['createdBy']
				  	]);
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

	public function getSigneeDetails(Request $request) {
		$value = "";
		try {
		$info = DB::select(DB::raw("
		SELECT P.id, P.customer_id,(P.lastname + ', ' + P.firstname + ' ' + P.middlename) member_name, 
		P.firstname, P.lastname, P.middlename, P.contact_no, P.address, P.is_member, 
		PL.profile_type, CONVERT(VARCHAR(30),PL.date_created,101)date_created,
		D.id as decease_id, D.fk_profile_id, CONVERT(VARCHAR(30),D.birthday,101)birthday, CONVERT(VARCHAR(30),D.date_died,22)date_died, D.causeOfDeath, D.religion,
		D.primary_branch, D.deathPlace, D.relationToSignee,
		S.id as signee_id, S.fk_profile_id, S.fb_account, S.email_address
		from _fis_profileHeader as P 
		LEFT JOIN _fis_deceaseInfo AS D on P.id = D.fk_profile_id
		LEFT JOIN _fis_signeeInfo AS S on P.id = S.fk_profile_id
		LEFT JOIN _fis_ProfileLogs AS PL ON P.id = PL.fk_profile_id
		ORDER BY PL.date_created ASC
		"));	

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

	/*public function getDeceasedDetails(Request $request) {
		$value = "";
		try {
		$info = DB::select(DB::raw("
		SELECT P.id, P.customer_id,(P.lastname + ', ' + P.firstname + ' ' + P.middlename) member_name, 
		P.firstname, P.lastname, P.middlename, P.contact_no, P.address, P.is_member, 'Decease' as profile_type,
		D.id as decease_id, D.fk_profile_id, D.birthday, D.date_died, D.causeOfDeath, D.religion,
		D.primary_branch, D.servicing_branch, D.deathPlace, D.relationToSignee
		from _fis_profileHeader as P 
		LEFT JOIN _fis_deceaseInfo AS D on P.id = D.fk_profile_id
		LEFT JOIN _fis_ProfileLogs AS PL ON P.id = PL.fk_profile_id
		WHERE PL.profile_type = 'Decease'
		"));	

			if($info)
			return	$info;
			else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}*/

	public function getInformantDetails(Request $request) {
		$value = "";
		try {

		$info = DB::select(DB::raw("
		SELECT P.id, P.customer_id,(P.lastname + ', ' + P.firstname + ' ' + P.middlename) member_name, 
		P.firstname, P.lastname, P.middlename, P.contact_no, P.address, P.is_member, 'Informant' as profile_type
		from _fis_profileHeader as P 
		LEFT JOIN _fis_ProfileLogs AS PL ON P.id = PL.fk_profile_id
		WHERE PL.profile_type = 'Informant'	
		"));

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

	/*public function getWalkinDetails(Request $request) {
		$value = "";
		try {

		$info = DB::select(DB::raw("
		SELECT P.id, P.customer_id,(P.lastname + ', ' + P.firstname + ' ' + P.middlename) member_name, 
		P.firstname, P.lastname, P.middlename, P.contact_no, P.address, P.is_member, 'Walkin' as profile_type
		from _fis_profileHeader as P 
		LEFT JOIN _fis_ProfileLogs AS PL ON P.id = PL.fk_profile_id
		WHERE PL.profile_type = 'Walkin'	
		"));

			if($info)
			return	$info;
			else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}*/


	public function insertProfile(Request $request){
		try {

				$value = (array)json_decode($request->post()['memberdata']);

				if ($value['id']!="") {
				$memcount = FisProfileLogs::where(['fk_profile_id'=>$value['id'],'profile_type'=>$value['change_profile_type']])->first();

					if($memcount)
					{
						return [
							'status'=>'unsaved',
							'message'=>'Profile Type Already Exist.'
						];	
					}
				}
	

				if(($value['change_profile_type']) == 'Decease'){
				$value['date_died'] = date_format(date_create($value['date_died']), 'Y-m-d H:i:s');
				$memberProfile = FisDeceased::create([
				  'birthday' => date('Y-m-d', strtotime($value['birthday'])),
				  'date_died' => $value['date_died'],
			      'causeOfDeath' => $value['causeOfDeath'],
			      'deathPlace' => $value['deathPlace'],
			      'religion' => $value['religion'],
			      'primary_branch' => $value['primary_branch'],
			      'relationToSignee' => $value['relationToSignee'],
			      'fk_profile_id' => $value['id']
				]);

				$profileLogs= FisProfileLogs::create([
				      'fk_profile_id' => $value['id'],
				      'profile_type' => $value['change_profile_type'],
				      'isActive' => 1,
				      'date_created' =>  date('Y-m-d'),
				      'createdBy' => $value['transactedBy']
					]);

					return [
					'status'=>'saved',
					'message'=>$memberProfile, $profileLogs
					];
				}

				else if (($value['change_profile_type']) == 'Signee') {
					$memberProfile= FisSignee::create([
				      'fb_account' => $value['fb_account'],
				      'email_address' => $value['email_address'],
				      'fk_profile_id' => $value['id']
					]);

					$profileLogs= FisProfileLogs::create([
				      'fk_profile_id' => $value['id'],
				      'profile_type' => $value['change_profile_type'],
				      'isActive' => 1,
				      'date_created' =>  date('Y-m-d'),
				      'createdBy' => $value['transactedBy']
					]);

					return [
					'status'=>'saved',
					'message'=>$memberProfile, $profileLogs
					];
				}
			
				else if (($value['change_profile_type']) == 'Informant') {
				  	$profileLogs= FisProfileLogs::create([
				      'fk_profile_id' => $value['id'],
				      'profile_type' => $value['change_profile_type'],
				      'isActive' => 1,
				      'date_created' =>  date('Y-m-d'),
				      'createdBy' => $value['transactedBy']
					]);

					return [
					'status'=>'saved',
					'message'=>$profileLogs
				];

				}
			
				
		

		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}


	public function deactivateProfile(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['userData']);
			
				$user = FisProfileLogs::find($value['id']);
			
					$user->update(
	   					['isActive'=>0]);
				
	   				
			
			return [
					'status'=>'saved',
					'message'=>$user
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function activateProfile(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['userData']);
			
				$user = FisProfileLogs::find($value['id']);
			
					$user->update(
	   					['isActive'=>1]);
				
	   				
			
			return [
					'status'=>'saved',
					'message'=>$user
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}


	public function adminUpdateInc(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['incData']);
			
				$user = FisIncentives::find($value['id']);
			
					$user->update(
	   					['status'=>'UNCLAIMED']);
				
	   				
			
			return [
					'status'=>'saved',
					'message'=>$user
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function generateIncentives(Request $request)
	{
		$type = $request->post()['type'];
		$date = $request->post()['date'];
		$id = $request->post()['id'];
		$transactedBy = $request->post()['transactedBy'];

		
		if ($type == 'MONTHLY') {
			$mci = $request->post()['mci'];

			$report = DB::select(DB::raw("
			SELECT top 5 I.informant_id, I.decease_id, I. decease_name, I.contract_no,  CONVERT(VARCHAR(30),I.package_amount,0) AS package_amount, 
			CONVERT(VARCHAR(30),I.incentives,0) AS incentives,
			(PH.lastname+', '+PH.firstname+ ' '+PH.middlename)AS informant_name, I.date_inform
			FROM _fis_informantInfo AS I
			LEFT JOIN _fis_ProfileHeader AS PH ON I.informant_id = PH.id
			WHERE informant_id = '".$id."' and MONTH(date_inform) = MONTH('".$date."') AND YEAR(date_inform) = YEAR('".$date."') AND  package_amount > '13000'
			"));

			if(count($report)>=5) 
			{
				$info = FisIncMonthly::whereMonth('date', date('m', strtotime($date)))->whereYear('date', date('Y', strtotime($date)))->where(['date_type'=>$type])->first();

				if($info)
				{
					$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'LEGAL', [300, 300]]);
					$mpdf->WriteHTML(view('incentives_report', ['report'=>$report, 'date'=>$date, 'mci'=>$mci]));
					$mpdf->use_kwt = true; 
					$mpdf->Output();		
				}

				else{
					$sumincentives = 0;
					foreach ($report as $row) {
						$sumincentives = $sumincentives + $row->incentives;
					}

					$inc = 0;
					$inc = $sumincentives * 0.3;

					$monthly = FisIncMonthly::create([
						'informant_id'=>$id,
						'date_type'=>$type,
						'date'=>$date,
						'amount' => $inc,
						'mci_no' => $mci,
						'basic_inc' => $sumincentives,
						'isTrigger' => 1,
						'transactedBy'=>$transactedBy
					]);

					$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'LEGAL', [300, 300]]);
					$mpdf->WriteHTML(view('incentives_report', ['report'=>$report, 'date'=>$date, 'mci'=>$mci]));
					$mpdf->use_kwt = true; 
					$mpdf->Output();
				}	
			}

			else if (count($report)==0) {
				echo "No Data!";
			}

			else echo "Invalid Selection!";
		} // close for monthly

		else if ($type == 'QUARTERLY') {
			$code = (array)json_decode($request->post()['code']);
			$qci = $request->post()['qci'];
		
			$params = "";
		
			for($x=0; $x<count($code); $x++)
			{
				$params = $params.$code[$x];
				
				if($x + 1 != count($code))
				{
					$params = $params.",";
				}
				
			}
			
			echo $params;

			$report = DB::select(DB::raw("
			SELECT (PH.lastname+', '+PH.firstname+ ' '+PH.middlename)AS informant_name, I.date, CONVERT(VARCHAR(30),I.amount,0) AS amount,
			I.mci_no, CONVERT(VARCHAR(30),I.basic_inc,0) AS basic_inc
			FROM _fis_informant_monthly as I
			LEFT JOIN _fis_ProfileHeader AS PH ON I.informant_id = PH.id
			WHERE I.informant_id = '".$id."' and I.id in (".$params.")
			"));

			
			if ($report>0) {
				$info = FisIncQuarterly::whereMonth('date', date('m', strtotime($date)))->whereYear('date', date('Y', strtotime($date)))->first();

				if($info)
				{
					$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'LEGAL', [300, 300]]);
					$mpdf->WriteHTML(view('incentives_quarterly', ['report'=>$report, 'date'=>$date, 'qci'=>$qci]));
					$mpdf->use_kwt = true; 
					$mpdf->Output();		
				}

				else{

					$sumbasic = 0;
		 			$sumincentives = 0;
					foreach ($report as $row) {
						$sumbasic = $sumbasic + $row->basic_inc;
						$sumincentives = $sumincentives + $row->amount;
					}

					$inc = 0;
					$inc = $sumincentives * 0.45;

					$monthly = FisIncQuarterly::create([
						'informant_id'=>$id,
						'date_type'=>$type,
						'date'=>$date,
						'amount' => $inc,
						'basic_inc' => $sumbasic,
						'qci_no' => $qci,
						'isTrigger' => 1,
						'transactedBy'=>$transactedBy
					]);

					$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'LEGAL', [300, 300]]);
					$mpdf->WriteHTML(view('incentives_quarterly', ['report'=>$report, 'date'=>$date, 'qci'=>$qci]));
					$mpdf->use_kwt = true; 
					$mpdf->Output();
				}
			}

			else {
				echo "No Data!";
			}
						
		} // quarteryly close

		else if ($type == 'YEARLY') {
			$code = (array)json_decode($request->post()['code']);
			$ypb = $request->post()['ypb'];
		
			$params = "";
		
			for($x=0; $x<count($code); $x++)
			{
				$params = $params.$code[$x];
				
				if($x + 1 != count($code))
				{
					$params = $params.",";
				}
				
			}
			
			echo $params;

			$report = DB::select(DB::raw("
			SELECT (PH.lastname+', '+PH.firstname+ ' '+PH.middlename)AS informant_name, I.date, CONVERT(VARCHAR(30),I.amount,0) AS amount,
			I.qci_no, CONVERT(VARCHAR(30),I.basic_inc,0) AS basic_inc
			FROM _fis_informant_quarterly as I
			LEFT JOIN _fis_ProfileHeader AS PH ON I.informant_id = PH.id
			WHERE I.informant_id = '".$id."' and I.id in (".$params.")
			"));

			
			if ($report>0) {
				$info = FisIncYearly::whereMonth('date', date('m', strtotime($date)))->whereYear('date', date('Y', strtotime($date)))->first();

				if($info)
				{
					$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'LEGAL', [300, 300]]);
					$mpdf->WriteHTML(view('incentives_yearly', ['report'=>$report, 'date'=>$date, 'ypb'=>$ypb]));
					$mpdf->use_kwt = true; 
					$mpdf->Output();		
				}

				else{

					$sumbasic = 0;
		 			$sumincentives = 0;
					foreach ($report as $row) {
						$sumbasic = $sumbasic + $row->basic_inc;
						$sumincentives = $sumincentives + $row->amount;
					}

					$inc = 0;
					$inc = $sumincentives * 0.45;

					$monthly = FisIncYearly::create([
						'informant_id'=>$id,
						'date_type'=>$type,
						'date'=>$date,
						'amount' => $inc,
						'basic_inc' => $sumbasic,
						'ypb_no' => $ypb,
						'isTrigger' => 1,
						'transactedBy'=>$transactedBy
					]);

					$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'LEGAL', [300, 300]]);
					$mpdf->WriteHTML(view('incentives_yearly', ['report'=>$report, 'date'=>$date, 'ypb'=>$ypb]));
					$mpdf->use_kwt = true; 
					$mpdf->Output();
				}
			}

			else {
				echo "No Data!";
			}
						
		} // yearly close

			
	}


	public function insertMCI(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['mciData']);
			
				$rtd = FisIncMonthly::find($value['id']);
	   			$rtd->update(
	   					['mci_no'=>$value['mci_no']]);
			
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

	/*public function generateIncentives(Request $request)
	{
		 $type = $request->post()['type'];
		 //$date = $request->post()['date'];

		$incentives = (array)json_decode($request->post()['incentivesData']);
		
		$params = "";
		
		for($x=0; $x<count($incentives); $x++)
		{
			$params = $params.$incentives[$x];
			
			if($x + 1 != count($incentives))
			{
				$params = $params.",";
			}
			
		}
		

		if ($type == 'MONTHLY') {
			$report = DB::select(DB::raw("
			SELECT top 5 I.informant_id, I.decease_id, I. decease_name, I.contract_no,  CONVERT(VARCHAR(30),I.package_amount,0) AS package_amount, 
			CONVERT(VARCHAR(30),I.incentives,0) AS incentives,
			(PH.lastname+', '+PH.firstname+ ' '+PH.middlename)AS informant_name, I.date_inform
			FROM _fis_informantInfo AS I
			LEFT JOIN _fis_ProfileHeader AS PH ON I.informant_id = PH.id
			WHERE I.package_amount > '10000' and I.id in (".$params.")
			"));


			if(count($report)>=5) 
			{

			for($x=0; $x<5; $x++)
			{
				$user = FisIncentives::find($incentives[$x]);
				$user->update(
		   		['isTagged'=>1 ]);	
			}



			$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'LEGAL', [300, 300]]);
			$mpdf->WriteHTML(view('incentives_report', ['report'=>$report]));
			$mpdf->use_kwt = true; 
			$mpdf->Output();

			}

			else echo "Invalid Selection!";
			
		}		
	}*/


	public function idlePassword(Request $request)
	{
		try {

				$value_api = (array)json_decode($request->post()['userData']);
				
				$user = SystemUser::where(
						[
								'Password'=>$value_api['password_input'],
								'UserName'=>$value_api['username'],
								
						])->firstOrFail();

				if ($user) {
					return [
						'status'=>'saved',
					];
				}

				else
					{
						return [
								'status'=>'error',
						];
						
					}

			} catch (\Exception $e) {
				return [
						'status'=>'unsaved',
				];
			}
		
		
	}

	public function getLedgerData(Request $request) {
		try {
		$value = (array)json_decode($request->post()['dataIncentives']);

		$user_check = DB::select(DB::raw("
			SELECT * FROM  _fis_informant_ledger WHERE transaction_id = '".$value['trans_id']."'
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


	public function getMonthly(Request $request) {
		$value = (array)json_decode($request->post()['dataIncentives']);
		try {
			$user_check = DB::select(DB::raw("
				SELECT * FROM _fis_informant_monthly WHERE informant_id = '".$value['fk_profile_id']."'
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

	public function getYearly(Request $request) {
		$value = (array)json_decode($request->post()['dataIncentives']);
		try {
			$user_check = DB::select(DB::raw("
				SELECT * FROM _fis_informant_quarterly WHERE informant_id = '".$value['fk_profile_id']."'
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
