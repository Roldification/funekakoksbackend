<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\SystemUser;
use Illuminate\Http\Request;
use App\AccessTokens;
use App\FisDeceased;
use App\ServiceContract;


class AccessController extends Controller
{
    //
    
	public function getUser()
	{	echo date('Y-m-d H:i:s');
		
		return SystemUser::all();
		
	}
	
	public function insertDeceaseProfile(Request $request)
	{
		try {
			$value = (array)json_decode($request->post()['deceasedata']);
			
			//return $value;
			
			$deceaseProfile = FisDeceased::create($value);
			
			return [
					'status'=>'saved',
					'message'=>$deceaseProfile
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
			$value = (array)json_decode($request->post()['items']);
			$branch = $request->post()['branch'];
			$itemSelection = [];
			$itemPresentation = [];
			
			foreach ($value as $row)
			{
				$selection = DB::select(DB::raw("select fk_item_id, serialno from
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
					s.address as signeeaddress, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
					d.birthday, d.address, d.causeOfDeath, sc.mort_viewing, cr.ReligionName
					from _fis_service_contract sc 
					inner join _fis_signee s on sc.signee = s.id
					inner join _fis_deceased d on sc.deceased_id = d.id
					inner join _fis_package p on sc.package_class_id = p.id
					inner join ClientReligion cr on d.religion = cr.ReligionID
					where contract_id=".$serviceContract->id)); 
							
			
			return [
					'status'=>'saved',
					'message'=> [
							'service_contract' => $sc_details,
							'item_inclusions' => $user_check
					]
			];
			

		} catch (\Exception $e) {
			
			return [
					'status'=>'unsaved',
					'message'=>$e->getMessage()
			];
			
		}
		
		
		
	}
	
	public function insertAccess(Request $request)
	{
		
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
			
		//	return $user;
		} catch (\Exception $e) {
			
			return [
					'status' => 'unsaved',
					'message' => $e->getMessage(), //use $request->post when getting formData type of post request
					//'message' => $e->getMessage()
			];
		}
		
		
	}
	
	
	public function getSignee(Request $request)
	{
		//$value = (array)json_decode($request->post());
		$value="";
		
		try {
			$user_check = DB::select(DB::raw("select top 5 id as value, (lname + ', ' + fname + ' ' + mname)label  from _fis_signee
			where (lname + ', ' + fname + ' ' + mname) like '".$request->post()['name']."%'"));
			
		//	return $request->post()['name'];
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
		//$value = (array)json_decode($request->post());
		$value="";
		
		try {
			$user_check = DB::select(DB::raw("select top 5 id as value, (lastname + ', ' + firstname + ' ' + middlename)label  from _fis_deceased
			where (lastname + ', ' + firstname + ' ' + middlename) like '".$request->post()['name']."%'"));
			
			//	return $request->post()['name'];
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
	
	
	
	public function getPackageList(Request $request)
	{
		//$value = (array)json_decode($request->post());
		$value="";
		
		try {
			$user_check = DB::select(DB::raw("select id as value, package_name as label from _fis_package"));
			
			//	return $request->post()['name'];
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
		//$value = (array)json_decode($request->post());
		$value="";
		
		try {
			$user_check = DB::select(DB::raw("select ReligionID as value, ReligionName as label from clientreligion"));
			
			
			$branches = 
			[['value'=>'001', 'label'=>'MAIN'],
			 ['value'=>'002', 'label'=>'NABUN'],
			 ['value'=>'003', 'label'=>'CARMEN']
			];
			
			
			//	return $request->post()['name'];
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
			//$param = $request->post()['name'];
			
			
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
}
