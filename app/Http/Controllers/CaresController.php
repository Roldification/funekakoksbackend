<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\FisCaresPlan;
use App\FisPlanPackage;
use App\FisCaresInclusion;
use App\FisCaresPackage;
use App\FisCreateContract;
use App\FisContractProf;
use App\FisContractTransaction;

class CaresController extends Controller
{
    public function insertPlanProfile(Request $request) {
		try {

			$value = (array)json_decode($request->post()['plandata']);
			$value['dateIssue'] = date('Y-m-d', strtotime($value['dateIssue']));
			$value['dueDate'] = date('Y-m-d', strtotime($value['dueDate']));
			$planProfile = FisCaresPlan::create([
				'is_member'	=> $value['is_member'],
				'membership_id'	=> $value['membership_id'],
				'firstName'	=> $value['firstName'],
        		'middleName'	=> $value['middleName'],
		        'lastName'	=> $value['lastName'],
		        'address'	=> $value['address'],
		       	'contact_number' => '+63'.$value['contact_number'],
		        'b_firstName'	=> $value['b_firstName'],
		        'b_middleName' => $value['b_middleName'],
		       	'b_lastName' => $value['b_lastName'],
				'b_relationship' => $value['b_relationship'],
				'b_contact_number' => '+63'.$value['b_contact_number'],
				'date_created' => date('Y-m-d'),
				'transactedBy' => $value['transactedBy']
			]);

			$bal = $value['salePrice'] - $value['firstPayment'];
			$contract = FisContractProf::create([
					'fk_profile_id'=> $planProfile->id,
		   			'package_code'=>$value['package_code'],
		   			'dateIssue'=>$value['dateIssue'],
		   			'payingPeriod'=>$value['terms'],
		   			'modePayment'=>$value['modePayment'],
		   			'contractPrice'=>$value['salePrice'],
		   			'amountInstalment'=>$value['amtinstalment'],
		   			'firstPayment'=>$value['firstPayment'],
		   			'dueDate'=>$value['dueDate'],
		   			'isActive' =>'ACTIVE',
		   			'balance' =>$bal,
		   			'date_created' => date('Y-m-d'),
		   			'transactedBy' => $value['transactedBy']
			]);


			if ($value['modePayment'] == 'Monthly') {
				//for installment
	   			$datetopass = date('Y-m-d');
	   			$hasMetSkip = false;
			   	for ($i=0; $i<$value['terms']; $i++) {

			   		$time = strtotime($datetopass);
			   		$currentmonth = $datetopass;

			   		if(!$hasMetSkip)
			   		 {
			   		 	if($i==0)
			   		 		$nextmonth = $currentmonth;
			   		 	else
			   		 	$nextmonth = date('Y-m-d', strtotime("+1 month", $time));
				   	 }
			   		else { 
			   			$nextmonth = $datetopass;
			   			$hasMetSkip = false;
			   		}

			   		if(date('m', strtotime($nextmonth)) -  date('m', strtotime($currentmonth)) >1 && date('Y', strtotime($nextmonth)) == date('Y', strtotime($currentmonth)))
			   		{
			   			$theyear = date('Y', strtotime($currentmonth));
			   			$themonth = str_pad(date('m', strtotime($currentmonth)) + 2, 2, "0", STR_PAD_LEFT);
			   			$prevmonth = str_pad($themonth - 1, 2, "0", STR_PAD_LEFT);
			   			$theday = date('d', strtotime($currentmonth));
			   			$nextmonth = date("Y-m-t", strtotime("".$theyear."-".$prevmonth."-01"));
			   			$datetopass = date("".$theyear."-".$themonth."-".$theday);
			   			$hasMetSkip = true;
			   		}

			   		else { $datetopass = $nextmonth; }
			   		if ($i==0) {
			   			$transaction = FisContractTransaction::create([
					      'fk_contract_id' => $contract->id,
					      'dateSchedule' => $nextmonth,
					      'principal_balance'=> $value['salePrice'],
			   			  'amount_instalment'=>$value['amtinstalment'],
			   			  'principal_paid'=>$value['firstPayment'],
					      'isPaid' =>1,
					      'date_pay' => date('Y-m-d'),
					      'transactedBy' => $value['transactedBy']
						]);
			   		}

			   		elseif ($i==1) {
			   			$transaction = FisContractTransaction::create([
					      'fk_contract_id' => $contract->id,
					      'dateSchedule' => $nextmonth,
					      'principal_balance'=>$bal,
			   			  'amount_instalment'=>$value['amtinstalment'],
			   			  'principal_paid'=>0,
					      'isPaid' =>0
						]);
			   		}

			   		else{
			   			$transaction = FisContractTransaction::create([
					      'fk_contract_id' => $contract->id,
					      'dateSchedule' => $nextmonth,
					      'principal_balance'=>0,
			   			  'amount_instalment'=>$value['amtinstalment'],
			   			  'principal_paid'=>0,
					      'isPaid' =>0
						]);
			   		}
			   		
			   	}

			   	return [
				'status'=>'saved',
				'message'=>$transaction
				];
			} // if monthly

			if ($value['modePayment'] == 'Quarterly') {
				//for installment
	   			$datetopass = date('Y-m-d');
	   			$hasMetSkip = false;
	   			$increment = $value['terms']/3;
	   			$beginDay = date('d', strtotime($datetopass));
			   	for ($i=0; $i<$increment; $i++) {

			   		$time = strtotime($datetopass);
			   		$currentmonth = $datetopass;

			   		if($i==0)
			   		 	$nextmonth = $currentmonth;

			   		 	else
			   		 	$nextmonth = date('Y-m-d', strtotime("+3 month", $time));

			   		if(date('d', strtotime($nextmonth)) !== date('d', strtotime($currentmonth)) && $i>0)
			   		{
			   			$theyear = date('Y', strtotime($nextmonth));
			   			$themonth = str_pad(date('m', strtotime($nextmonth)) - 1, 2, "0", STR_PAD_LEFT);
			   			//$prevmonth = str_pad($themonth - 1, 2, "0", STR_PAD_LEFT);
			   			$theday = date('d', strtotime($currentmonth));
			   			$nextmonth = date("Y-m-t", strtotime("".$theyear."-".$themonth."-01"));
			   			$datetopass = date("".$theyear."-".$themonth."-".$theday);



			   			$hasMetSkip = true;
			   		}

			   		else { $datetopass = $nextmonth; }

			   		if ($i==0) {
			   			$transaction = FisContractTransaction::create([
					      'fk_contract_id' => $contract->id,
					      'dateSchedule' => $nextmonth,
					      'principal_balance'=> $value['salePrice'],
			   			  'amount_instalment'=>$value['amtinstalment'],
			   			  'principal_paid'=>$value['firstPayment'],
					      'isPaid' =>1,
					      'date_pay' => date('Y-m-d'),
					      'transactedBy' => $value['transactedBy']
						]);
			   		}

			   		elseif ($i==1) {
			   			$transaction = FisContractTransaction::create([
					      'fk_contract_id' => $contract->id,
					      'dateSchedule' => $nextmonth,
					      'principal_balance'=>$bal,
			   			  'amount_instalment'=>$value['amtinstalment'],
			   			  'principal_paid'=>0,
					      'isPaid' =>0
						]);
			   		}

			   		else{
			   			$transaction = FisContractTransaction::create([
					      'fk_contract_id' => $contract->id,
					      'dateSchedule' => $nextmonth,
					      'principal_balance'=>0,
			   			  'amount_instalment'=>$value['amtinstalment'],
			   			  'principal_paid'=>0,
					      'isPaid' =>0
						]);
			   		}
			   		
			   	}

			   	return [
				'status'=>'saved',
				'message'=>$transaction
				];
				} // if monthly
			

			return [
				'status'=>'saved',
				'message'=>$planProfile, $contract
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function getMemberPlanInfo(Request $request) {
		$value = "";
		try {
		$info = DB::select(DB::raw("
			SELECT P.membership_id, P.is_member, (P.lastName + ', ' + P.firstName + ' ' + P.middleName) member_name, 
			P.firstName, P.middleName, P.lastName,  P.address, P.contact_number,
			(P.b_lastName + ', ' + P.b_firstName + ' ' + P.b_middleName) b_member_name, 
			P.b_firstName, P.b_middleName, P.b_lastName,
			P.b_relationship, P.b_contact_number,
			C.id as c_id, C.package_code, C.fk_profile_id, C.dateIssue, C.payingPeriod, C.modePayment, 
			C.contractPrice, C.amountInstalment, C.firstPayment, C.dueDate, C.isActive, C.balance
			FROM _fis_cares_profile AS P
			LEFT JOIN _fis_cares_contract AS C ON C.FK_PROFILE_ID = P.ID
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

	public function updatePlan(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['infoupdate']);
				$value['dateIssue'] = date('Y-m-d', strtotime($value['dateIssue']));
				$value['dueDate'] = date('Y-m-d', strtotime($value['dueDate']));
				
				$contract = FisCreateContract::find($value['fk_profile_id']);
	   			$contract->update([
		   				'fk_profile_id'=>$value['fk_profile_id'],
		   				'package_code'=>$value['package_code'],
		   				'dateIssue'=>$value['dateIssue'],
		   				'payingPeriod'=>$value['terms'],
		   				'modePayment'=>$value['modePayment'],
		   				'contractPrice'=>$value['contractPrice'],
		   				'amountInstalment'=>$value['amountInstalment'],
		   				'dueDate'=>$value['dueDate'],
		   				'transactedBy'=>$value['transactedBy'],
		   				'date_created' => date('Y-m-d'),
		   				'isActive' => 1,
			   	]);


	   			//for installment
	   			$datetopass = date('Y-m-d');
	   			$hasMetSkip = false;
			   	for ($i=0; $i<$value['terms']; $i++) {

			   		$time = strtotime($datetopass);
			   		$currentmonth = $datetopass;

			   		if(!$hasMetSkip)
			   		 {
			   		 	$nextmonth = date('Y-m-d', strtotime("+1 month", $time));
				   	 }
			   		else { 
			   			$nextmonth = $datetopass;
			   			$hasMetSkip = false;
			   		}

			   		if(date('m', strtotime($nextmonth)) -  date('m', strtotime($currentmonth)) >1 && date('Y', strtotime($nextmonth)) == date('Y', strtotime($currentmonth)))
			   		{
			   			$theyear = date('Y', strtotime($currentmonth));
			   			$themonth = str_pad(date('m', strtotime($currentmonth)) + 2, 2, "0", STR_PAD_LEFT);
			   			$prevmonth = str_pad($themonth - 1, 2, "0", STR_PAD_LEFT);
			   			$theday = date('d', strtotime($currentmonth));
			   			$nextmonth = date("Y-m-t", strtotime("".$theyear."-".$prevmonth."-01"));
			   			$datetopass = date("".$theyear."-".$themonth."-".$theday);
			   			$hasMetSkip = true;
			   		}

			   		else { $datetopass = $nextmonth; }

			   		$transaction = FisContractTransaction::create([
				      'fk_contract_id' => $contract->id,
				      'dateSchedule' => $nextmonth,
				      'principal_balance'=> $value['contractPrice'],
		   			  'amount_instalment'=>$value['amountInstalment'],
		   			  'principal_paid'=>0,
				      'isPaid' =>0,
				      'transactedBy' => $value['transactedBy']
					]);
			   	}

			   	
				return [
						'status'=>'saved',
						'message'=> $contract, $transaction
					];
				

		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	
	public function updateCaresInfo(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['infoupdate']);
				
				$info = FisCaresPlan::find($value['id']);

	   			$info->update([
		   			'membership_id'=>$value['membership_id'],
		   			'is_member'=>$value['is_member'],
		   			'firstName'=>$value['firstName'],
		   			'middleName'=>$value['middleName'],
		   			'lastName'=>$value['lastName'],
		   			'address'=>$value['address'],
		   			'contact_number'=>$value['contact_number'],
		   			'b_firstName'=>$value['b_firstName'],
		   			'b_middleName'=>$value['b_middleName'],
		   			'b_lastName'=>$value['b_lastName'],
		   			'b_relationship'=>$value['b_relationship'],
		   			'b_contact_number'=>$value['b_contact_number'],
		   			'date_created' => date('Y-m-d'),
		   			'transactedBy' =>$value['transactedBy']
			   	]);
			   	
				return [
					'status'=>'saved',
					'message'=> $info
				];
				
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function insertPlanPackage(Request $request) {
		try {
			$value = (array)json_decode($request->post()['packagetitledata']);
			$packageData = FisPlanPackage::create([
			      'package_code' => $value['package_code'],
			      'package_name' => $value['package_name'],
			      'isActive' =>0,
			      'discount'=>0,
	   			  'standardPrice'=>0,
	   			  'salesPrice'=>0,
			      'date_created' => date('Y-m-d'),
			      'createdBy' => $value['transactedBy']
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

	public function getPlanPackage(Request $request) {
		$value="";
		try {
		$package = DB::select(DB::raw("SELECT package_code as value, package_name as label FROM _fis_cares_package"));
			if($package)
				return	$package;
				else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function insertPlanInclusions(Request $request) {
		try {
			$value = (array)json_decode($request->post()['inclusionsData']);

			$packagePrice = FisCaresPackage::find($value['package_code']);
	   		$packagePrice->update([
	   					'standardPrice'=>$value['standardPrice'],
	   					'discount'=>$value['discount'],
	   					'salesPrice'=>$value['salesPrice'],
	   					'isActive' => 1,
	   					'createdBy'=>$value['transactedBy']
	   				]);
			foreach ($value['inclusions'] as $row){
			try {
					$inclusion = FisCaresInclusion::updateOrCreate([
					'fk_package_id'=> $value['package_code'],
					'inclusion_name'=> $row->inventory_name,
					'inclusion_ql'=> $row->inventory_ql,
					'inclusion_uom'=> $row->inventory_uom,
					'inclusion_price '=> $row->inventory_price,
					'createdBy'=> $value['transactedBy'],
					'dateEncoded'=> date('Y-m-d')
					]);	
	
			} catch (\Exception $e) {
			return [
				'message'=>$e->getMessage()
			]; }
			}


			return [
				'status'=>'saved',
				'message'=>$inclusion, $packagePrice
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function getInclusionCares(Request $request) {
		$value = (array)json_decode($request->post()['package_code']);
		try {
		$package = DB::select(DB::raw("SELECT fk_package_id, inclusion_id, inclusion_name, inclusion_ql, inclusion_uom, inclusion_price
			FROM _fis_cares_package_inclusion WHERE fk_package_id = '".$value['package']."'"));
			if($package)
				return	$package;
				else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}


	public function deleteCaresInc(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['inventorydelete']);
				$inc = FisCaresInclusion::find($value['inclusion_id']);
		   		$inc->delete();
				
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

	public function getCaresPackage(Request $request) {
		$value="";
		try {
		$package = DB::select(DB::raw("SELECT package_code, package_name, isActive, standardPrice, discount, salesPrice FROM _fis_cares_package"));
			if($package)
				return	$package;
				else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function planActivation(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['activationData']);

					if ($value['isActive'] == 1) {
						$inventory = FisCaresPackage::find($value['package_code']);
						$inventory->update([
		   					'isActive' => 0
		   				]);
					}
					elseif ($value['isActive'] == 0) {
						$inventory = FisCaresPackage::find($value['package_code']);
						$inventory->update([
		   					'isActive' => 1
		   				]);
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

	public function getPackages(Request $request) {
		$value = (array)json_decode($request->post()['packageData']);
		try {
			$user_check = DB::select(DB::raw("SELECT package_code, package_name, isActive, standardPrice, discount, salesPrice FROM _fis_cares_package
				WHERE package_code = '".$value['package_code']."'"));
				
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

	public function getPlanInclusions(Request $request) {
		$value = (array)json_decode($request->post()['prodList']);
		try {
		$user_check = DB::select(DB::raw("SELECT fk_package_id, inclusion_id, inclusion_name, inclusion_ql, inclusion_uom, inclusion_price FROM _fis_cares_package_inclusion WHERE fk_package_id = '".$value['package_code']."'"));
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

	public function getPlanPackageActive(Request $request) {
		$value="";
		try {
		$package = DB::select(DB::raw("SELECT package_code as value, package_name as label from _fis_cares_package where isActive = '1'"));
			if($package)
				return	$package;
				else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getActivePackageData(Request $request) {
		$value = (array)json_decode($request->post()['package_code']);
		try {
		$package = DB::select(DB::raw("SELECT package_code, package_name, salesPrice FROM _fis_cares_package where package_code = '".$value['package_code']."'
			"));
			if($package)
				return	$package;
				else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getPlanContractDetails(Request $request) {
		$value="";
		try {
		$package = DB::select(DB::raw("SELECT package_code as value, package_name as label from _fis_cares_package where isActive = '1'"));
			if($package)
				return	$package;
				else return [];
				
		} catch (\Exception $e) {
			return [
			'status'=>'error',
			'message'=>$e->getMessage()
			];
		}
	}

	public function getProfMember(Request $request)
	{
		$value="";
		
		try {
			$user_check = DB::select(DB::raw("SELECT top 5 id as value, (lastName + ', ' + firstName + ' ' + middleName)label  from _fis_cares_profile
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

	public function getPlanTransactions(Request $request) {
		$value = (array)json_decode($request->post()['planData']);
		try {
		$info = DB::select(DB::raw("
			SELECT P.id, (P.lastName + ', ' + P.firstName + ' ' + P.middleName) member_name, 
			(P.b_lastName + ', ' + P.b_firstName + ' ' + P.b_middleName) b_member_name,
		 	C.dateIssue, C.payingPeriod, C.modePayment, 
			C.contractPrice, C.amountInstalment, C.firstPayment, C.dueDate, C.isActive, C.balance,
			T.id as transaction_id, T.fk_contract_id, T.dateSchedule, T.principal_balance, T.amount_instalment,
			T.principal_paid, T.isPaid, T.date_pay,
			PC.package_name
			FROM _fis_cares_profile AS P
			LEFT JOIN _fis_cares_contract AS C ON C.FK_PROFILE_ID = P.ID
			LEFT JOIN _fis_cares_transaction AS T ON T.FK_CONTRACT_ID = C.ID
			LEFT JOIN _fis_cares_package AS PC ON PC.package_code = C.package_code
			WHERE T.FK_CONTRACT_ID = '".$value['id']."'
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

	public function getPlanTransactionsTwo(Request $request) {
		$value = (array)json_decode($request->post()['planData']);
		try {
		$info = DB::select(DB::raw("
			SELECT P.id, (P.lastName + ', ' + P.firstName + ' ' + P.middleName) member_name, 
			(P.b_lastName + ', ' + P.b_firstName + ' ' + P.b_middleName) b_member_name,
		 	C.dateIssue, C.payingPeriod, C.modePayment, 
			C.contractPrice, C.amountInstalment, C.firstPayment, C.dueDate, C.isActive, C.balance,
			T.fk_contract_id, T.dateSchedule, T.principal_balance, T.amount_instalment,
			T.principal_paid, T.isPaid, T.date_pay,
			PC.package_name
			FROM _fis_cares_profile AS P
			LEFT JOIN _fis_cares_contract AS C ON C.FK_PROFILE_ID = P.ID
			LEFT JOIN _fis_cares_transaction AS T ON T.FK_CONTRACT_ID = C.ID
			LEFT JOIN _fis_cares_package AS PC ON PC.package_code = C.package_code
			WHERE T.FK_CONTRACT_ID = '".$value['id']."'
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

	public function getPlanInclusionsDetails(Request $request) {
		$value = (array)json_decode($request->post()['planData']);
		try {
		$info = DB::select(DB::raw("
			SELECT * FROM _fis_cares_package_inclusion 
			WHERE fk_package_id = '".$value['package_code']."'
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

	public function updateCaresPackage(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['packageData']);
				
				$info = FisCaresPackage::find($value['package_code']);

	   			$info->update([
		   			'package_code'=>$value['package_code'],
		   			'package_name'=>$value['package_name']
			   	]);
			   	
				return [
					'status'=>'saved',
					'message'=> $info
				];
				
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

}
