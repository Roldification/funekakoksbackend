<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\SystemUser;
use Illuminate\Http\Request;
use App\AccessTokens;
use App\FisDeceased;
use App\FisItems;
use App\ServiceContract;
use App\PackageName;
use App\ReceivingItems;
use App\FisSignee;
use App\FisInformant;
use App\FisRelation;
use App\FisItemSales;
use App\FisItemInventory;
use App\FisProductList;
use App\FisServiceSales;


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

	public function insertInventory(Request $request) {
		try {
			$value = (array)json_decode($request->post()['inventorydata']);
			$inventoryItems = FisItems::create($value);
			foreach ($value as $row)
			{
				$selection = DB::select(DB::raw("select fk_item_id, id as value, id as label, price as sublabel from
						_fis_productlist where isEncumbered=1 and branch='".$branch."'
						and fk_item_id='".$row->item_code."'"));
				
				array_push($itemSelection, $selection);
				
				$presentation = DB::select(DB::raw("select top ".$row->quantity." item_code, item_name, pl.id as serialno, ".$row->price." as sell_price from _fis_productlist pl
					inner join _fis_items i on pl.fk_item_id = i.item_code
					where isEncumbered=1 and branch='".$branch."'and fk_item_id='".$row->item_code."'
					order by id"));
				
				array_push($itemPresentation, $presentation);
				
				
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
	
	public function insertPackagemodal(Request $request) {
		try {
			$value = (array)json_decode($request->post()['packagemodaldata']);
			$packageName = PackageName::create($value);
			return [
				'status'=>'saved',
				'message'=>$packageName
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}
	
	public function insertItemInclusions(Request $request) {
		try {
			$value = (array)json_decode($request->post()['ItemInclusionsdata']);	
			$packageName = PackageName::create($value);
			return [
				'status'=>'saved',
				'message'=>$packageName
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

	public function insertContract(Request $request)
	{
		try {
			
			$value = (array)json_decode($request->post()['servicecontract']);
			
			$value['contract_balance'] = $value['contract_amount'];
			$value['burial_time'] = date('Y-m-d H:i:s', strtotime($value['burial_time']));
			$serviceContract = ServiceContract::create($value);
			
			$user_check = DB::select(DB::raw("select * from
				(
				SELECT item_code, item_name, isnull(quantity, 0) as quantity, 0 as price, 0 as discount, 0 as tot_price FROM _fis_items fi
				left join 
				(
				select * from _fis_package_inclusions
				where fk_package_id=".$serviceContract->package_class_id."
				and inclusionType='ITEM'
				)b on fi.item_code = b.item_id
				)sdf
				order by quantity desc,  item_code asc
				"));
			
			    $sc_details = DB::select(DB::raw("select sc.contract_id, contract_no, contract_date, (s.lname + ', ' + s.fname + ' ' + s.mname)signee,
					s.address as signeeaddress, sc.discount, sc.grossPrice, sc.contract_amount, sc.contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
					d.birthday, d.address, d.causeOfDeath, sc.mort_viewing, cr.ReligionName
					from _fis_service_contract sc 
					inner join _fis_signee s on sc.signee = s.id
					inner join _fis_deceased d on sc.deceased_id = d.id
					inner join _fis_package p on sc.package_class_id = p.id
					inner join ClientReligion cr on d.religion = cr.ReligionID
					where contract_id=".$serviceContract->contract_id)); 
			    
		   
			    $services = DB::select(DB::raw("select * from
					(
					SELECT fs.id, service_name, 0 as amount, 0 as less, isnull(duration, '') as duration, isnull(type_duration, '') as type_duration, 0 as tot_price  FROM _fis_services fs
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

	public function insertSigneeProfile(Request $request) {
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

	public function insertInformantProfile(Request $request) {
		try {
			$value = (array)json_decode($request->post()['informantdata']);
			$informantProfile = FisInformant::create([
				'isTCMember' => $value['isTCMember'],
        		'firstName'	=> $value['firstName'],
		        'middleName' => $value['middleName'],
		        'lastName' => $value['lastName'],
		       	'address' => $value['address'],
		        'contactNo' => $value['contactNo'],
		       	'incentives' => $value['incentives'],
				'date_entry' => date('Y-m-d'),
				'transactedBy' => 'hcalio']);
			return [
				'status'=>'saved',
				'message'=>$informantProfile
			];
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function insertDeceaseProfile(Request $request){
		try {
			$value = (array)json_decode($request->post()['deceasedata']);
			$deceasedProfile = FisDeceased::create([
				'isTCMember' => $value['isTCMember'],
        		'firstname'	=> $value['firstname'],
		        'middlename' => $value['middlename'],
		        'lastname' => $value['lastname'],
		        'birthday' => date('Y-m-d', strtotime($value['birthday'])),
		        'age' => $value['age'],
		        'religion' => $value['religion'],
		        'address' => $value['address'],
		        'datedied' => date('Y-m-d', strtotime($value['datedied'])),
		        'causeOfDeath' => $value['causeOfDeath'],
		        'deathPlace' => $value['deathPlace'],
		        'primary_branch' => $value['primary_branch'],
		        'servicing_branch' => $value['servicing_branch'],
		        'signee_id' => $value['signee_id'],
		        'signee_name' => $value['signee_name'],
		        'informant_id' => $value['informant_id'],
		        'informant_name' => $value['informant_name'],
		        'relationToSignee' => $value['relationToSignee'],
		       	'date_entry' => date('Y-m-d'),
				'transactedBy' => 'hcalio']);

			return [
				'status'=>'saved',
				'message'=>$deceasedProfile
			];


		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	// END OF INSERT FUNCTION
	


	public function samplepdf()
	{
		$mpdf = new \Mpdf\Mpdf();
		$mpdf->WriteHTML('<h1>Hello world!</h1>');
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
	   		
	   		
	   	DB::beginTransaction();
	   		//check if serial no. is repeated since laravel 5.7 does not provide distinct validation
	   		$valarr = array_count_values(array_column($value['item_inventory'], 'serialno'));
	   		$equivalence = array_sum($valarr) / count($valarr);
	   		
	   		if($equivalence!=1)
	   			return [
	   					'status' => 'unsaved',
	   					'message' => 'Serial No. repitition found. Make sure we do not repeat serial no.',
	   			];
	   			
	   			$sc = ServiceContract::find($value['sc_id']);
	   			$sc->update(
	   					['contract_amount'=>$value['sc_amount'],
	   							'grossPrice'=>	$value['sc_amount'],
	   							'isPosted'=>1
	   					]
	   					);
	   			
	   			
	   					
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
	   									'isCancelled'=>0
	   									
	   							]
	   							);
	   					
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
	   									'p_sequence'=>$row->serialno,
	   									'fk_scID'=>$sc->contract_id,
	   									'fk_ORNo'=>'',
	   							]);
	   					
	   					$productList = FisProductList::where([
	   							'id'=>$row->serialno,
	   							'isEncumbered'=>1,
	   					])->firstOrFail();
	   					
	   					//$productList = FisProductList::find($row->serialno);
	   					$productList->update([
	   						'isEncumbered'=>0
	   					]);
	   					
	   					
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
	   							'OR_no'=>'',
	   							'isWalkin'=>0,
	   							'client'=>'',
	   							'signeeID'=>$sc->signee,
	   							'isCancelled'=>0,
	   							
	   					]);
	   					
	   					
	   					
	   					
	   				} catch (\Exception $e) {
	   					DB::rollback();
	   					return [
	   							'status'=>'unsaved',
	   							'message'=>$e->getMessage()
	   					];
	   					break;
	   				}
	   				
	   			}

	  		
	   	
	   		
	   		DB::commit();
	  		return [
	  			'status'=>'saved',
	  			'message'=>''
	  		];
	  	
	   	
	
	   	
	   } catch (\Exception $e) {
	   	DB::rollback();
	   	return [
	   			'status' => 'unsaved',
	   			'message' => $e->getMessage()
	   	];
	   }
		
	}
	
	
	public function getMinimalProbabilities(Request $request)
	{
		try {
			$value = (array)json_decode($request->post()['items']);
			$branch = $request->post()['branch'];
			$itemSelection = [];
			$itemPresentation = [];
			
			foreach ($value as $row)
			{
				$selection = DB::select(DB::raw("select fk_item_id, serialno as value, serialno as label from
						_fis_productlist where isEncumbered=1 and branch='".$branch."'
						and fk_item_id='".$row->item_code."'"));
				
				array_push($itemSelection, $selection);
				
				$presentation = DB::select(DB::raw("select top ".$row->quantity." item_code, item_name, serialno from _fis_productlist pl
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
			$user_check = DB::select(DB::raw("select top 5 id as value, (lname + ', ' + fname + ' ' + mname)label  from _fis_signee
			where (lname + ', ' + fname + ' ' + mname) like '".$request->post()['name']."%'"));
			
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
			$user_check = DB::select(DB::raw("select top 5 id as value, (lastname + ', ' + firstname + ' ' + middlename)label  from _fis_deceased
			where (lastname + ', ' + firstname + ' ' + middlename) like '".$request->post()['name']."%'"));
			
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
			$user_check = DB::select(DB::raw("select ReligionID as value, ReligionName as label from clientreligion"));
			
			
			$branches = 
			[['value'=>'001', 'label'=>'MAIN'],
			 ['value'=>'002', 'label'=>'NABUN'],
			 ['value'=>'003', 'label'=>'CARMEN']
			];
			
			
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
	
			$cemeteries = DB::select(DB::raw("select label, value from _fis_locations where type='cemetery'"));
			$churches = DB::select(DB::raw("select label, value from _fis_locations where type='church'"));
			
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
			
			
			$user_check = DB::select(DB::raw("select * from SystemUser where username='".$value['username']."' and password='".$value['password']."'"));
			
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


	public function getInventorySearch(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("select * from _fis_items"));
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
		$user_check = DB::select(DB::raw("select * from _fis_settings_relation"));
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
		$user_check = DB::select(DB::raw("select rtd_id as value, relation as label from _fis_settings_relation"));

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

	public function getPackageList(Request $request) {
		$value="";
		try {
		$user_check = DB::select(DB::raw("select id as value, package_name as label from _fis_package"));
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
	
	public function getPackageItemInclusions(Request $request) {
		$value = "";
		try {
		$user_check = DB::select(DB::raw("select item_code as value, item_name as label from _fis_items"));
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
		$user_check = DB::select(DB::raw("select top 5 id as value, (lastName + ', ' + firstName + ' ' + middleName) label  from _fis_informant
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

	public function getMemberDeceased(Request $request) {
		$value = "";
		try {
		$deceased = DB::select(DB::raw("select * from  _fis_deceased
		left join clientreligion on _fis_deceased.religion = clientreligion.ReligionID
		left join _fis_settings_relation on _fis_deceased.relationToSignee = _fis_settings_relation.rtd_id "));	

			if($deceased)
			return	$deceased;
			else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getMemberSignee(Request $request) {
		$value = "";
		try {
		$signee = DB::select(DB::raw("select * from _fis_signee"));	

			if($signee)
			return	$signee;
			else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getMemberInformant(Request $request) {
		$value = "";
		try {
		$informant = DB::select(DB::raw("select * from  _fis_informant"));	

			if($informant)
			return	$informant;
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

	public function updateDeceased(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['deceasedDataupdate']);
			
				$deceased = FisDeceased::find($value['id']);
	   			$deceased ->update(
	   					[
			   			'firstname' => $value['firstname'],
						'middlename' => $value['middlename'],
						'lastname' => $value['lastname'],
						'isTCMember' => $value['isTCMember'],
						'birthday' => $value['birthday'],
						'datedied' => $value['datedied'],
						'address' => $value['address'],
						'causeOfDeath' => $value['causeOfDeath'],
						'deathPlace' => $value['deathPlace'],
						'religion' => $value['religion'],
						'primary_branch' => $value['primary_branch'],
						'servicing_branch' => $value['servicing_branch'],
						'age' => $value['age'],
						'relationToSignee' => $value['relationToSignee'],
						'signee_id' => $value['signee_id'],
						'signee_name' => $value['signee_name'],
						'informant_id' => $value['informant_id'],
						'informant_name' => $value['informant_name']
	   					]);
			
			return [
					'status'=>'saved',
					'message'=>$deceased
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	} 

	public function updateSignee(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['signeeDataupdate']);
			
				$signee = FisSignee::find($value['id']);
	   			$signee ->update(
	   					[
			   			'fname' => $value['fname'],
						'mname' => $value['mname'],
						'lname' => $value['lname'],
						'isTCMember' => $value['isTCMember'],
						'contactNo' => $value['contactNo'],
						'address' => $value['address'],
						'email_address' => $value['email_address'],
						'fb_account' => $value['fb_account']
	   					]);
			
			return [
					'status'=>'saved',
					'message'=>$signee
			];
			
		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function updateInformant(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['informantDataupdate']);
			
				$informant = FisInformant::find($value['id']);
	   			$informant ->update(
	   					[
			   			'firstName' => $value['firstName'],
						'middleName' => $value['middleName'],
						'lastName' => $value['lastName'],
						'isTCmember' => $value['isTCmember'],
						'address' => $value['address'],
						'contactNo' => $value['contactNo'],
						'incentives' => $value['incentives']
	   					]);
			
			return [
					'status'=>'saved',
					'message'=>$informant
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

	// CLOSE OF DELETE

}
