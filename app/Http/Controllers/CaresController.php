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
			$value['dateIssue'] = date_format(date_create($value['dateIssue']), 'Y-m-d H:i:s');
			$value['dueDate'] = date_format(date_create($value['dueDate']), 'Y-m-d H:i:s');

			if ($value['membership_id']!="") {
			$memcount = FisCaresPlan::where(['membership_id'=>$value['membership_id']])->first();

				if($memcount)
				{
					return [
						'status'=>'unsaved',
						'message'=>'Member Already Exist.'
					];	
				}
			}

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
			
			// for installment
			if ($value['modePayment'] == 'Monthly') {
		   			$datetopass = date('Y-m-d');
		   			$hasMetSkip = false;

		   			if ($value['terms'] == '5 YEARS') {
		   				$terms = 60;
		   			}
		   			else if ($value['terms'] == '4 YEARS') {
			          $terms = 48;
			        }
			        else if ($value['terms'] == '3 YEARS') {
			          $terms = 36;
			        }
			        else if ($value['terms'] == '2 YEARS') {
			          $terms = 24;
			        }
			        else if ($value['terms'] == '1 YEAR') {
			          $terms = 12;
			        } 

	   			
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

	   				for ($i=0; $i<$terms; $i++) {
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
			   			$bal = $value['salePrice'] - $value['firstPayment']; 
			   			$transaction = FisContractTransaction::create([
					      'fk_contract_id' => $contract->id,
					      'dateSchedule' => $nextmonth,
					      'principal_balance'=> $bal,
			   			  'amount_instalment'=>$value['amtinstalment'],
			   			  'principal_paid'=>$value['firstPayment'],
					      'isPaid' =>'YES',
					      'date_pay' => date('Y-m-d'),
					      'transactedBy' => $value['transactedBy']
						]);
			   		}
			   		else{
			   			$transaction = FisContractTransaction::create([
					      'fk_contract_id' => $contract->id,
					      'dateSchedule' => $nextmonth,
					      'principal_balance'=>0,
			   			  'amount_instalment'=>$value['amtinstalment'],
			   			  'principal_paid'=>0,
					      'isPaid' =>'NO'
						]);
			   		}
			   		} // LOOP CLOSES
			} // if monthly

			else if ($value['modePayment'] == 'Quarterly') {
		   			$datetopass = date('Y-m-d');
		   			$hasMetSkip = false;

		   			if ($value['terms'] == '5 YEARS') {
		   				$terms = 60/3;
		   			}
		   			else if ($value['terms'] == '4 YEARS') {
			          $terms = 48/3;
			        }
			        else if ($value['terms'] == '3 YEARS') {
			          $terms = 36/3;
			        }
			        else if ($value['terms'] == '2 YEARS') {
			          $terms = 24/3;
			        }
			        else if ($value['terms'] == '1 YEAR') {
			          $terms = 12/3;
			        } 

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
	   			
	   				$beginDay = date('d', strtotime($datetopass));
				   	for ($i=0; $i<$terms; $i++) {
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
				   			$bal = $value['salePrice'] - $value['firstPayment']; 
				   			$transaction = FisContractTransaction::create([
						      'fk_contract_id' => $contract->id,
						      'dateSchedule' => $nextmonth,
						      'principal_balance'=> $bal,
				   			  'amount_instalment'=>$value['amtinstalment'],
				   			  'principal_paid'=>$value['firstPayment'],
						      'isPaid' =>'YES',
						      'date_pay' => date('Y-m-d'),
						      'transactedBy' => $value['transactedBy']
							]);
				   		}

				   		else{
				   			$transaction = FisContractTransaction::create([
						      'fk_contract_id' => $contract->id,
						      'dateSchedule' => $nextmonth,
						      'principal_balance'=>0,
				   			  'amount_instalment'=>$value['amtinstalment'],
				   			  'principal_paid'=>0,
						      'isPaid' =>'NO'
							]);
				   		}
				   	} // loop close
			} // if quarterly

			else if ($value['modePayment'] == 'Annually') {
		   			$datetopass = date('Y-m-d');
		   			$hasMetSkip = false;

		   			if ($value['terms'] == '5 YEARS') {
		   				$terms = 5;
		   			}
		   			else if ($value['terms'] == '4 YEARS') {
			          $terms = 4;
			        }
			        else if ($value['terms'] == '3 YEARS') {
			          $terms = 3;
			        }
			        else if ($value['terms'] == '2 YEARS') {
			          $terms = 2;
			        }
	
	   			
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

	   				for ($i=0; $i<$terms; $i++) {
			   		$time = strtotime($datetopass);
			   		$currentmonth = $datetopass;

			   		if(!$hasMetSkip)
			   		 {
			   		 	if($i==0)
			   		 		$nextmonth = $currentmonth;
			   		 	else
			   		 	$nextmonth = date('Y-m-d', strtotime("+1 year", $time));
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
			   			$bal = $value['salePrice'] - $value['firstPayment']; 
			   			$transaction = FisContractTransaction::create([
					      'fk_contract_id' => $contract->id,
					      'dateSchedule' => $nextmonth,
					      'principal_balance'=> $bal,
			   			  'amount_instalment'=>$value['amtinstalment'],
			   			  'principal_paid'=>$value['firstPayment'],
					      'isPaid' =>'YES',
					      'date_pay' => date('Y-m-d'),
					      'transactedBy' => $value['transactedBy']
						]);
			   		}
			   		else{
			   			$transaction = FisContractTransaction::create([
					      'fk_contract_id' => $contract->id,
					      'dateSchedule' => $nextmonth,
					      'principal_balance'=>0,
			   			  'amount_instalment'=>$value['amtinstalment'],
			   			  'principal_paid'=>0,
					      'isPaid' =>'NO'
						]);
			   		}
			   		} // LOOP CLOSES
			} // if annually
			
			else if ($value['modePayment'] == 'Spot Cash') {
					$contract = FisContractProf::create([
							'fk_profile_id'=> $planProfile->id,
				   			'package_code'=>$value['package_code'],
				   			'dateIssue'=>$value['dateIssue'],
				   			'payingPeriod'=>$value['terms'],
				   			'modePayment'=>$value['modePayment'],
				   			'contractPrice'=>$value['salePrice'],
				   			'amountInstalment'=>0,
				   			'firstPayment'=>$value['firstPayment'],
				   			'dueDate'=>date('Y-m-d'),
				   			'isActive' =>'ACTIVE',
				   			'balance' =>0,
				   			'date_created' => date('Y-m-d'),
				   			'transactedBy' => $value['transactedBy']
					]);

			   		$transaction = FisContractTransaction::create([
					      'fk_contract_id' => $contract->id,
					      'dateSchedule' => $value['dateIssue'],
					      'principal_balance'=> 0,
			   			  'amount_instalment'=>$value['salePrice'],
			   			  'principal_paid'=>$value['firstPayment'],
					      'isPaid' =>'YES',
					      'date_pay' => date('Y-m-d'),
					      'transactedBy' => $value['transactedBy']
					]);
			
			} // if spot cash 

			return [
				'status'=>'saved',
				'message'=>$planProfile,$contract,$transaction
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
			C.id as c_id, C.package_code, C.fk_profile_id, CONVERT(VARCHAR(30),C.dateIssue,101) AS dateIssue , C.payingPeriod, C.modePayment, 
			CONVERT(VARCHAR(30),C.contractPrice,0) AS contractPrice, CONVERT(VARCHAR(30),C.amountInstalment,0) AS amountInstalment, 
			CONVERT(VARCHAR(30),C.firstPayment,0) AS firstPayment, CONVERT(VARCHAR(30),C.dueDate,0) AS dueDate,  CONVERT(VARCHAR(30),C.balance,0) AS balance, C.isActive
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
				$value = (array)json_decode($request->post()['memberData']);
				
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
	   					'UpdateInclusionBy'=>$value['transactedBy']
	   				]);
			foreach ($value['inclusions'] as $row){
			try {
					$value['fk_package_id'] = $value['package_code'];
					$inclusion = FisCaresInclusion::find($value['fk_package_id']);
					$inclusion = FisCaresInclusion::updateOrCreate([
					'fk_package_id'=> $value['package_code'],
					'inclusion_name'=> $row->inventory_name,
					'inclusion_ql'=> $row->inventory_ql,
					'inclusion_uom'=> $row->inventory_uom,
					'inclusion_price '=> $row->inventory_price,
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

	public function insertPlanInclusionsItems(Request $request) {
		try {
			$value = (array)json_decode($request->post()['inclusionsData']);
				$inclusion = FisCaresInclusion::Create([
				'fk_package_id'=> $value['package_code'],
				'inclusion_name'=> $value['inventory_name'],
				'inclusion_ql'=> $value['inventory_ql'],
				'inclusion_uom'=> $value['inventory_uom'],
				'inclusion_price '=> $value['inventory_price'],
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
				$value['fk_package_id'] = $value['package'];
				$inc = FisCaresInclusion::find($value['fk_package_id']);
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
		$package = DB::select(DB::raw("SELECT package_code, package_name, CONVERT(VARCHAR(30),salesPrice,0) AS salesPrice FROM _fis_cares_package WHERE package_code = '".$value['package_code']."'
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
		 	CONVERT(VARCHAR(30),C.dateIssue,101) AS dateIssue, C.payingPeriod, C.modePayment, 
			CONVERT(VARCHAR(30),C.contractPrice,0) AS contractPrice, CONVERT(VARCHAR(30),C.amountInstalment,0) AS amountInstalment, 
			CONVERT(VARCHAR(30),C.firstPayment,0) AS firstPayment, CONVERT(VARCHAR(30),C.dueDate,0) AS dueDate, C.isActive, 
			CONVERT(VARCHAR(30),C.balance,0) AS balance,
			T.id as transaction_id, T.fk_contract_id, CONVERT(VARCHAR(30),T.dateSchedule,101) AS dateSchedule, 
			CONVERT(VARCHAR(30),T.principal_balance,0) AS principal_balance, CONVERT(VARCHAR(30),T.amount_instalment,0) AS amount_instalment,
			CONVERT(VARCHAR(30),T.principal_paid,0) AS principal_paid, T.isPaid, CONVERT(VARCHAR(30),T.date_pay,101) AS date_pay,
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
		 	CONVERT(VARCHAR, C.dateIssue, 101) AS dateIssue, C.payingPeriod, C.modePayment, 
			CONVERT(VARCHAR(30),C.contractPrice,0) AS contractPrice, C.amountInstalment, C.firstPayment, 
			CONVERT(VARCHAR, C.dueDate, 101) as dueDate, C.isActive, CONVERT(VARCHAR(30),C.balance,0) as balance,
			T.id as transaction_id, T.fk_contract_id, CONVERT(VARCHAR, T.dateSchedule, 101) AS dateSchedule, CONVERT(VARCHAR(30),T.principal_balance,0) as principal_balance, 
			CONVERT(VARCHAR(30),T.amount_instalment,0) AS amount_instalment,
			CONVERT(VARCHAR(30),T.principal_paid,0) AS principal_paid, T.isPaid, CONVERT(VARCHAR, T.date_pay, 101) AS date_pay,
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

	public function updatePay(Request $request)
	{
		try {	

			$value = (array)json_decode($request->post()['payData']);
			$value['id'] = $value['transaction_id'];
			$value['datePay'] = date('Y-m-d', strtotime($value['datePay']));

			$transaction = FisContractTransaction::find($value['id']);
			$bal = $value['principal_balance'] - $value['amount'];

			if ($value['amount'] == $value['amount_instalment']) {
				$transaction->update([
		   			'date_pay'=> date('Y-m-d'),
		   			'principal_paid'=>$value['amount'],
		   			'principal_balance'=>$bal,
		   			'isPaid'=>'YES'
			   	]);
			}

			elseif($value['amount'] != $value['amount_instalment']){
				$transaction->update([
		   			'date_pay'=> date('Y-m-d'),
		   			'principal_paid'=>$value['amount'],
		   			'principal_balance'=>$bal,
		   			'isPaid'=>'PARTIAL'
			   	]);
			}

			if ($value['isPaid'] == 'PARTIAL' ) {
				$sum = $value['amount']+$value['principal_paid'];
				$transaction->update([
		   			'date_pay'=> date('Y-m-d'),
		   			'principal_paid'=>$sum,
		   			'principal_balance'=>$bal,
		   			'isPaid'=>'YES'
			   	]);
			}
			
	   			

			$value['id'] = $value['fk_contract_id'];
			$contract = FisContractProf::find($value['id']);
	   		$contract->update([
		   			'balance'=>$bal
			   ]);


			return [
				'status'=>'saved',
				'message'=>$transaction, $contract
			];
				
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function caresAgreement(Request $request)
	{
		$id = json_decode($request->post()['id']);
		
		$accounts = DB::select(DB::raw("
			SELECT *, SUBSTRING (CF.middlename, 1, 1) as middleInitial FROM _fis_cares_profile AS CF
			INNER JOIN _fis_cares_contract AS CC
			ON CF.id = CC.fk_profile_id
			INNER JOIN _fis_settings_relation AS SR
			ON CF.b_relationship = SR.rtd_id 
			WHERE CC.ID = $id
			"));
				
		$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'LEGAL', [300, 300]]);
	
		$mpdf->WriteHTML(view('cares_agreement', ['accounts'=>$accounts]));
		$mpdf->use_kwt = true; 
		$mpdf->Output();
	}

	public function getAllItemServ(Request $request) {
		try {
		$info = DB::select(DB::raw("
			SELECT item_code as value, item_name AS label, item_name, item_code FROM _fis_items 
			UNION ALL
			SELECT CAST(id as varchar(10)) as value, service_name AS label, service_name, CAST(id as varchar(10)) FROM _fis_services
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

}
