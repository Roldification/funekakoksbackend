<?php

namespace App\Http\Controllers;

use App\SystemUser;
use Illuminate\Http\Request;


class AccessController extends Controller
{
    //
    
	public function getUser()
	{	echo date('Y-m-d H:i:s');
		
		return SystemUser::all();
		
	}
	
	
	public function insertAccess(Request $request)
	{
		
		try {
			$user = SystemUser::create([
					'UserName'=>'jcambongga',
					'Password'=>'jcambongga',
					'LastName'=>'Cambongs',
					'FirstName'=>'Jelmar',
					'MiddleName'=>'Leforada',
					'UserStatus'=>1,
					'EmployeeID'=>'jdumanacal',
					'FKRoleID'=>'SOHEAD',
					'FKBranchID'=>'201',
					'DateLastPasswordChange'=>date('Y-m-d H:i:s'),
					'DisbursementLimit'=>0,
					'CashOnHand'=>0,
					'UserSLCode'=>'1-1-101-01-002',
					'CreatedBy'=>'sa',
					'CreatedDate'=>date('Y-m-d'),
					'UpdatedBy'=>'sa',
					'DateUpdated'=>date('Y-m-d'),
					
					
			]);
			
			return $user;
		} catch (\Exception $e) {
			return [
					'status' => $request->post(), //use $request->post when getting formData type of post request
					'message' => $e->getMessage()
			];
		}
		
		
	}
}
