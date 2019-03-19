<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\SystemUser;
use Illuminate\Http\Request;
use App\AccessTokens;
use App\FisDeceased;


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
	
	public function insertAccess(Request $request)
	{
		
		try {
			
			$value = (array)json_decode($request->post()['userdata']);
			
			$user = SystemUser::create([
					'UserName'=> $value['username'],
					'Password'=>$value['password'],
					'LastName'=>$value['name'], //since quasar, started, it l
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
					'date_expire'=>date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s"). ' + 5 minute')),
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
