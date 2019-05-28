<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\FisCaresPlan;

class CaresController extends Controller
{
    public function insertPlanProfile(Request $request) {
		try {

			$value2 = (array)json_decode($request->post()['signeedata']);
			$signeeProfile = FisSignee::create([
				'firstName'	=> $value2['firstName'],
        		'middleName'	=> $value2['middleName'],
		        'lastName'	=> $value2['lastName'],
		        'address'	=> $value2['address'],
		       	'contact_number' => $value2['contact_number'],
		        'b_firstName'	=> $value2['b_firstName'],
		        'b_middleName' => $value2['b_middleName'],
		       	'b_lastName' => $value2['b_lastName'],
				'b_relationship' => $value2['b_relationship'],
				'b_contact_number' => $value2['b_contact_number'],
				'dateIssue' => $value2['dateIssue'],
				'payingPeriod' => $value2['payingPeriod'],
				'modePayment' => $value2['modePayment'],
				'contractPrice' => $value2['contractPrice'],
				'amountInstalment' => $value2['amountInstalment'],
				'firstPayment' => $value2['firstPayment'],
				'dueDate' => $value2['dueDate']);
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

}
