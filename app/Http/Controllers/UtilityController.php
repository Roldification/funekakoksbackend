<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\FisSCPayments;
use App\ServiceContract;
use App\FisSalesTransaction;
use App\FisRemittanceHeader;
use App\FisRemittanceDetails;
use Illuminate\Database\Eloquent\Builder;


class UtilityController extends Controller
{
	
	public function getCashTransactionRequestOfUser(Request $request)
	{
		$scpayments = FisRemittanceDetails::with(['scpayments' => function($query) {
			$query->with(['header.itemsales' =>function($queryx) {
				$queryx->with('items')->where('price', '>', 0);
			},
			'header.servicesales'=> function($queryy) {
				$queryy->with('services')->where('total_amount', '>', 0);
			 }
			]);
		}])			->where('fk_remittance_header_id', $request->post()['request_header'])
					->where('fk_sc_payment_id', '<>', 0)
					->where('fk_misc_payment_id', 0)
					->get();
		
		
	   $receiptPayments= FisRemittanceDetails::with(['miscpayments' => function($query) {
			$query->with(['header.itemsales' =>function($queryx) {
							$queryx->with('items')->where('price', '>', 0);
			},
			'header.servicesales'=> function($queryy) {
				$queryy->with('services')->where('total_amount', '>', 0);
		     }
			]);
	   }])			->where('fk_remittance_header_id', $request->post()['request_header'])
					->where('fk_misc_payment_id', '<>', 0)
					->where('fk_sc_payment_id', 0)
					->get();
		
	  return [
	  		'payments'=> $scpayments,
	  		'misc_payments'=> $receiptPayments
	  ];
	}
	
	public function getCashTransactionOfUser(Request $request)
	{
		$scpayments = FisSCPayments::with(['header.itemsales' => function($query) {
						$query->with('items')->where('price', '>', 0);
					},
		'header.servicesales'=> function($query) {
				$query->with('services')->where('total_amount', '>', 0);	
		}])
		->join('_fis_service_contract', '_fis_sc_payments.contract_id', '_fis_service_contract.contract_id')
		->whereDoesntHave('remittancedetails', function($query) {
			$query->whereIn('approve_status', ['PENDING', 'APPROVED']);
		})
		->where('transactedBy', $request->post()['username'])
					->where('isRemitted', 0)
					->where('isCancelled', 0)
					->where('AR_Credit', '>', 0)
					->where('payment_mode', 1)
					->where('fun_branch', $request->post()['branch'])
					->get();
		
					
		$receiptPayments = FisSalesTransaction::with(['header.itemsales' => function($query) {
						$query->with('items')->where('price', '>', 0);
					},
					'header.servicesales'=> function($query) {
					$query->with('services')->where('total_amount', '>', 0);
					}])
					->join('_fis_itemsales_header', '_fis_sales_transaction.sales_id', '_fis_itemsales_header.id')
					->select('_fis_sales_transaction.*')
					->whereDoesntHave('remittancedetails', function($query) {
						$query->whereIn('approve_status', ['PENDING', 'APPROVED']);
					})
					->where('_fis_sales_transaction.transactedBy', $request->post()['username'])
						->where('isRemitted', 0)
						->where('_fis_sales_transaction.isCancelled', 0)
						->where('AR_Credit', '>', 0)
						->where('payment_mode', 1)
						->where('fun_branch', $request->post()['branch'])
						->get();
		

		return [
				'payments'=>$scpayments,
				'receiptpayments'=>$receiptPayments
		];
		
	}
	
	public function saveRemittance(Request $request)
	{
		
		try {
			$header = new FisRemittanceHeader();
			
			DB::beginTransaction();
			
			$sc_cash_receipt = $request->post()['sc_cash_receipts'];
			$misc_cash_receipt = $request->post()['misc_cash_receipts'];
			
			$header_remittance = FisRemittanceHeader::create([
					'requested_by'=> $request->post()['username'],
					'date_transacted'=>date('Y-m-d'),
					'approve_status'=>'PENDING',
					'date_approved'=>'1/1/1900',
					'approved_by'=>'',
					'denominations_json'=>json_encode($request->post()['denominations'])
			]);
			
			foreach ($sc_cash_receipt as $row)
			{
				FisRemittanceDetails::create([
					'fk_sc_payment_id'=>$row['payment_id'],
					'fk_misc_payment_id'=>0,
					'amount'=>$row['AR_Credit'],
					'transacted_by'=>$request->post()['username'],
					'date_transacted'=>date('Y-m-d H:i:s'),
					'approve_status'=>'PENDING',
					'date_approved'=>'1/1/1900',
					'approved_by'=>'',
					'fk_remittance_header_id'=>$header_remittance->id	
				]);
				
			}
			
			foreach ($misc_cash_receipt as $row)
			{
				FisRemittanceDetails::create([
						'fk_sc_payment_id'=>0,
						'fk_misc_payment_id'=>$row['id'],
						'amount'=>$row['AR_Credit'],
						'transacted_by'=>$request->post()['username'],
						'date_transacted'=>date('Y-m-d H:i:s'),
						'approve_status'=>'PENDING',
						'date_approved'=>'1/1/1900',
						'approved_by'=>'',
						'fk_remittance_header_id'=>$header_remittance->id
				]);
				
			}
			
			
			DB::commit();
						
			return [
					'status'=>'saved',
					'message'=>'successfully saved'
			];
			
			
		} catch (\Exception $e) {
			return [
					'status'=>'error',
					'message'=> $e->getMessage()
			];
		}
		
	}
	
	public function getRemittanceForApprovalHeader(Request $request)
	{
		return FisRemittanceHeader::with('remittancedetails')
				  ->join('SystemUser', 'requested_by', 'UserName')
				  ->where('FKBranchID', '202')
				  ->where('approve_status', '<>', 'REJECTED')
				 ->get();
		
	}
	
	
	public function approveRemittance(Request $request)
	{
		try {
			
			DB::beginTransaction();
			
			$header = FisRemittanceHeader::where('id', $request->post()['request_id']);
			
			$details = FisRemittanceDetails::where('fk_remittance_header_id',  $request->post()['request_id']);
			
			
			
			foreach ($details->get() as $row)
			{
				if($row['fk_sc_payment_id']!=0 || $row['fk_sc_payment_id']!="0")
				{
					FisSCPayments::where('payment_id', $row['fk_sc_payment_id'])
					->update([
							'isRemitted' => 1,
							'dateRemitted' => date('Y-m-d H:i:s'),
							'remittedTo' =>  $request->post()['username']
					]);
				}
				
				else if($row['fk_misc_payment_id']!=0 || $row['fk_misc_payment_id']!="0")
				{
					FisSalesTransaction::where('id', $row['fk_misc_payment_id'])
					->update([
							'isRemitted'=>1,
							'dateRemitted'=>date('Y-m-d H:i:s'),
							'remittedTo'=> $request->post()['username']
					]);
					
				}
				
			}
			
			$header->update([
					 'approve_status'=>'APPROVED',
					 'date_approved'=>date('Y-m-d H:i:s'),
					'approved_by'=>$request->post()['username']
					]);
			
			$details->update([
					'approve_status'=>'APPROVED',
					'date_approved'=>date('Y-m-d H:i:s'),
					'approved_by'=>$request->post()['username']
			]);
			
			DB::commit();
			
			return [
					'status'=>'ok',
					'message'=>'successfully approved'
			];
			
		} catch (\Exception $e) {
			
			return [
					'status'=>'error',
					'message'=>$e->getMessage()
			];
		}
		
	}
}