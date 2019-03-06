<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ServiceContract;

class ContractController extends Controller
{
    
	
	public function CreateContract(Request $request)
	{
		$value = (array)json_decode($request->post()['servcontract']);
		
		$this->validate($request, [
			'contract_no' => 'required|min:3|unique:contract_no'
		]);
		
		$servicecontract = ServiceContract::create(
				[
				  //insert array here		
				]
				);
		
		
		
	}
	
	
	public function PostContract(Request $request)
	{
		
	}
}
