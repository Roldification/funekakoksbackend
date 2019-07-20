<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\FisCaresPlan;
use App\FisPlanPackage;
use App\FisCaresInclusion;
use App\FisCaresPackage;

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
				'dateIssue' => $value['dateIssue'],
				'payingPeriod' => $value['payingPeriod'],
				'modePayment' => $value['modePayment'],
				'contractPrice' => $value['contractPrice'],
				'amountInstalment' => $value['amountInstalment'],
				'firstPayment' => $value['firstPayment'],
				'dueDate' => $value['dueDate'],
				'date_created' => date('Y-m-d'),
				'transactedBy' => $value['transactedBy']
			]);
			return [
				'status'=>'saved',
				'message'=>$planProfile
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
			P.b_relationship, P.b_contact_number, P.dateIssue, P.payingPeriod, P.modePayment, P.contractPrice,
			P.amountInstalment, P.firstPayment, P.dueDate
			FROM _fis_cares_profile as P
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

	public function updatePlanInfo(Request $request)
	{
		try {
				$value = (array)json_decode($request->post()['infoupdate']);
				$value['dateIssue'] = date('Y-m-d', strtotime($value['dateIssue']));
				$value['dueDate'] = date('Y-m-d', strtotime($value['dueDate']));
				$rtd = FisCaresPlan::find($value['membership_id']);
	   			$rtd->update([
	   				'is_member'=>$value['is_member'],
	   				'membership_id'=>$value['membership_id'],
	   				'firstName'=>$value['firstName'],
	   				'middleName'=>$value['middleName'],
	   				'address'=>$value['address'],
	   				'contact_number'=>$value['contact_number'],
	   				'b_firstName'=>$value['b_firstName'],
	   				'b_middleName'=>$value['b_middleName'],
	   				'b_lastName'=>$value['b_lastName'],
	   				'b_relationship'=>$value['b_relationship'],
	   				'b_contact_number'=>$value['b_contact_number'],
	   				'dateIssue'=>$value['dateIssue'],
	   				'payingPeriod'=>$value['payingPeriod'],
	   				'modePayment'=>$value['modePayment'],
	   				'contractPrice'=>$value['contractPrice'],
	   				'amountInstalment'=>$value['amountInstalment'],
	   				'firstPayment'=>$value['firstPayment'],
	   				'dueDate'=>$value['dueDate'],
	   				'transactedBy'=>$value['transactedBy']
	   			]);
			
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
		$package = DB::select(DB::raw("SELECT * from _fis_cares_package"));
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

}
