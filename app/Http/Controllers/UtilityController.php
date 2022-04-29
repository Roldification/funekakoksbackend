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
use App\SystemUser;
use Illuminate\Database\Eloquent\Builder;
use App\AccountingHelper;
use App\FisCashDeposit;

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
					->where('fk_cash_deposit_id', 0)
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
					->where('fk_cash_deposit_id', 0)
					->get();

	    $cashDeposits = FisRemittanceDetails::with(['cashdeposits.signee'])->where('fk_remittance_header_id', $request->post()['request_header'])
										->where('fk_misc_payment_id', 0)
										->where('fk_sc_payment_id', 0)
										->where('fk_cash_deposit_id', '<>', 0)
										->get();
		
	  return [
	  		'payments'=> $scpayments,
	  		'misc_payments'=> $receiptPayments,
			'cash_deposits'=> $cashDeposits
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
		->select("_fis_sc_payments.*")
		->selectRaw("'' as stub_no")
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
					->select("_fis_sales_transaction.*")
					->selectRaw("'' as stub_no")
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
		
		$cashDeposit = FisCashDeposit::with('signee')
										->select('_fis_cash_deposit.*')
										->selectRaw("'' as stub_no")
										->whereDoesntHave('remittancedetails', function($query) {
											$query->whereIn('approve_status', ['PENDING', 'APPROVED']);
										})
										->where('transacted_by', $request->post()['username'])
										->where('branch', $request->post()['branch'])
										->get();

		return [
				'payments'=>$scpayments,
				'receiptpayments'=>$receiptPayments,
				'cashDeposit'=>$cashDeposit
		];
		
	}
	
	public function saveRemittance(Request $request)
	{
		
		try {
			$header = new FisRemittanceHeader();
			
			DB::beginTransaction();
			
			$sc_cash_receipt = $request->post()['sc_cash_receipts'];
			$misc_cash_receipt = $request->post()['misc_cash_receipts'];
			$cash_deposit = $request->post()['cash_deposit'];
			
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
					'fk_cash_deposit_id'=>0,
					'amount'=>$row['AR_Credit'],
					'transacted_by'=>$request->post()['username'],
					'date_transacted'=>date('Y-m-d H:i:s'),
					'approve_status'=>'PENDING',
					'date_approved'=>'1/1/1900',
					'approved_by'=>'',
					'stub_no'=>$row['stub_no'],
					'fk_remittance_header_id'=>$header_remittance->id	
				]);
				
			}
			
			foreach ($misc_cash_receipt as $row)
			{
				FisRemittanceDetails::create([
						'fk_sc_payment_id'=>0,
						'fk_cash_deposit_id'=>0,
						'fk_misc_payment_id'=>$row['id'],
						'amount'=>$row['AR_Credit'],
						'transacted_by'=>$request->post()['username'],
						'date_transacted'=>date('Y-m-d H:i:s'),
						'approve_status'=>'PENDING',
						'date_approved'=>'1/1/1900',
						'approved_by'=>'',
						'stub_no'=>$row['stub_no'],
						'fk_remittance_header_id'=>$header_remittance->id
				]);
				
			}

			foreach ($cash_deposit as $row)
			{
				FisRemittanceDetails::create([
						'fk_sc_payment_id'=>0,
						'fk_cash_deposit_id'=>$row['id'],
						'fk_misc_payment_id'=>0,
						'amount'=>$row['amount'],
						'transacted_by'=>$request->post()['username'],
						'date_transacted'=>date('Y-m-d H:i:s'),
						'approve_status'=>'PENDING',
						'date_approved'=>'1/1/1900',
						'approved_by'=>'',
						'stub_no'=>$row['stub_no'],
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

		$statusArray = [];
		if($request->showPending=='true')
		array_push($statusArray, 'PENDING');

		if($request->showApproved=='true')
		array_push($statusArray, 'APPROVED');
		
		return FisRemittanceHeader::with('remittancedetails')
		->join('SystemUser', 'requested_by', 'UserName')
		->where('FKBranchID', '202')
		->whereIn('approve_status', $statusArray)->get();
		
	}
	
	
	public function approveRemittance(Request $request)
	{
		try {
			
			DB::beginTransaction();
			$header = FisRemittanceHeader::where('id', $request->post()['request_id'])->first();
			$user  = SystemUser::where('UserName', $header->requested_by)->first();

			$details = FisRemittanceDetails::with(['scpayments.paymenttype','miscpayments.paymenttype'])->where('fk_remittance_header_id',  $request->post()['request_id']);
			

			// DB::rollBack();

			// return [
			// 	'message' => $details->get()
			// ];

			$acctgHeader = [];
			$acctgHeader['branch_code'] = $user->FKBranchID;
			$acctgHeader['transaction_date'] = date('Y-m-d');
			$acctgHeader['transaction_code'] = "JNLVOUCHER";
			$acctgHeader['username'] = $request->post()['username'];
			$acctgHeader['reference'] = "coll.".$user->FKBranchID.".".$header->id;
			$acctgHeader['status'] = 1;
			$acctgHeader['particulars'] = "Collection remittance";
			$acctgHeader['customer'] = $header->requested_by;
			$acctgHeader['checkno'] = "";

			$acctgDetails = [];
			$pushDetails = [];

			$collectionDetails = $details->get();
			$totalAmount = 0;
			foreach ($collectionDetails as $row) {
				$totalAmount+=$row['amount'];
			}

			// assume this is a cash collection transfer
			$pushDetails['entry_type']="DR";
			$pushDetails['SLCode']="1-1-112-03-004";
			$pushDetails['amount']=$totalAmount;
			$pushDetails['detail_particulars']="sdf";
			array_push($acctgDetails, $pushDetails);



			
			foreach ($collectionDetails as $row)
			{
				if(!is_null($row['scpayments'])) {
					$pushDetails['entry_type']="CR";
					$pushDetails['SLCode']=$row['scpayments']->paymenttype->sl_credit;
					$pushDetails['amount']=$row['amount'];
					$pushDetails['detail_particulars']="this is particular details";
					array_push($acctgDetails, $pushDetails);
				}

				if(!is_null($row['miscpayments'])) {
					$pushDetails['entry_type']="CR";
					$pushDetails['SLCode']=$row['miscpayments']->paymenttype->sl_credit;
					$pushDetails['amount']=$row['amount'];
					$pushDetails['detail_particulars']="this is particular details";
					array_push($acctgDetails, $pushDetails);
				}


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

			$saveAccounting =  AccountingHelper::processAccounting($acctgHeader, $acctgDetails);


			
			if($saveAccounting['status']=='saved')
				{
					DB::commit();
					return [
							'status'=>'saved',
							'message'=>''
					];
				}
				
				else
				{
					DB::rollback();
					return $saveAccounting;
				}
			
		} catch (\Exception $e) {
			DB::rollback();
			return [
					'status'=>'error',
					'message'=>$e->getMessage()
			];
		}
		
	}

	public function insertCashDeposit(Request $request) {
		
		$contracts = (array)json_decode($request->post()['cashdeposit']);
		DB::beginTransaction();

		$cashDep = FisCashDeposit::create([
			'fk_signee_id'=>$contracts['signee_id'],
			'amount'=>$contracts['amount_pay'],
			'status'=>'POSTED',
			'remarks'=>$contracts['remarks'],
			'transacted_by'=>$contracts['transactedBy'],
			'date_transacted'=>date('Y-m-d H:i:s'),
			'cancelled_by'=>null,
			'date_cancelled'=>null
		]);

		$acctgHeader = [];
		$acctgHeader['branch_code'] = $contracts['branch'];
		$acctgHeader['transaction_date'] = date('Y-m-d');
		$acctgHeader['transaction_code'] = "JNLVOUCHER";
		$acctgHeader['username'] = $contracts['transactedBy'];
		$acctgHeader['reference'] = "cashDep.AP.".$contracts['branch'].".".$cashDep->id;
		$acctgHeader['status'] = 1;
		$acctgHeader['particulars'] = "Cash Deposit Declaration";
		$acctgHeader['customer'] = "";
		$acctgHeader['checkno'] = "";

		$acctgDetails = [];
		$pushDetails = [];

		$pushDetails['entry_type']="DR";
		$pushDetails['SLCode']="1-1-101-01-004";
		$pushDetails['amount']=$contracts['amount_pay'];
		$pushDetails['detail_particulars']="sdf";
		array_push($acctgDetails, $pushDetails);

		$pushDetails['entry_type']="CR";
		$pushDetails['SLCode']="2-1-316-01-001";
		$pushDetails['amount']=$contracts['amount_pay'];
		$pushDetails['detail_particulars']="sdf";
		array_push($acctgDetails, $pushDetails);

		$saveAccounting =  AccountingHelper::processAccounting($acctgHeader, $acctgDetails);
		if($saveAccounting['status']=='saved')
				{
					DB::commit();
					return [
							'status'=>'saved',
							'message'=>''
					];
				}
				
				else
				{
					DB::rollback();
					return $saveAccounting;
				}
	}



	public function cancelCashDeposit(Request $request) {
		
		$contracts = (array)json_decode($request->post()['cashdeposit']);
		DB::beginTransaction();

		$cashDep = FisCashDeposit::where('id', $contracts['id'])->where('transacted_by', $contracts['transactedBy'])->first();

		if(!$cashDep) {
			DB::rollBack();
			return [
				'status'=>'failed '.$contracts['id'],
				'message'=>'no cash deposit found'
			];
		}

		$cashDep->update([
			'status'=>'CANCELLED',
			'cancelled_by'=>$contracts['transactedBy'],
			'date_cancelled'=>date('Y-m-d H:i:s')
		]);

		$acctgHeader = [];
		$acctgHeader['branch_code'] = $contracts['branch'];
		$acctgHeader['transaction_date'] = date('Y-m-d');
		$acctgHeader['transaction_code'] = "JNLVOUCHER";
		$acctgHeader['username'] = $contracts['transactedBy'];
		$acctgHeader['reference'] = "CNcashDep.AP.".$contracts['branch'].".".$cashDep->id;
		$acctgHeader['status'] = 1;
		$acctgHeader['particulars'] = "Cash Deposit Cancellation";
		$acctgHeader['customer'] = "";
		$acctgHeader['checkno'] = "";

		$acctgDetails = [];
		$pushDetails = [];

		$pushDetails['entry_type']="DR";
		$pushDetails['SLCode']="1-1-101-01-004";
		$pushDetails['amount']=$cashDep->amount;
		$pushDetails['detail_particulars']="sdf";
		array_push($acctgDetails, $pushDetails);

		$pushDetails['entry_type']="CR";
		$pushDetails['SLCode']="2-1-316-01-001";
		$pushDetails['amount']=$cashDep->amount;
		$pushDetails['detail_particulars']="sdf";
		array_push($acctgDetails, $pushDetails);

		$saveAccounting =  AccountingHelper::processAccounting($acctgHeader, $acctgDetails);
		if($saveAccounting['status']=='saved')
				{
					DB::commit();
					return [
							'status'=>'saved',
							'message'=>''
					];
				}
				
				else
				{
					DB::rollback();
					return $saveAccounting;
				}
	}
}