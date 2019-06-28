<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\ServiceContract;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\AccessTokens;
use App\FisDeceased;
use App\FisItems;
use App\PackageName;
use App\ReceivingItems;
use App\FisSignee;
use App\FisInformant;
use App\FisRelation;
use App\FisItemSales;
use App\FisItemInventory;
use App\FisProductList;
use App\FisServiceSales;
use App\FisItemsalesHeader;
use App\FisTransactionHeader;
use App\AccountingHelper;
use App\FisBranch;
use App\SystemUser;
use App\FisMemberData;
use App\FisCharging;
use App\FisSCPayments;
use App\FisPaymentType;
use App\FisSalesTransaction;

class ServiceContractController extends Controller
{
    //
    
	public function getDetailsOfContract(Request $request)
	{
		try {
			$service_contract = ServiceContract::where([
					'contract_id'=>$request->post()['contract_id']
			])->firstOrFail();
			
			if(($service_contract->status=='ACTIVE' || $service_contract->status=='CANCELLED' || $service_contract->status=='CLOSED') && $service_contract->isPosted==1)
			{
				$availments = DB::select(DB::raw("select product_id, (CAST(quantity as varchar(5)) + ' ' + unit_type) as totquantity, price, total_price, 'item' as inclusiontype, i.item_name as inclusionname from _fis_item_sales sales
				inner join _fis_items i on sales.product_id = i.item_code
				where contract_id=".$request->post()['contract_id']."
				UNION ALL
				select CAST(fk_service_id as varchar(10)) as id, (CAST(service_duration as varchar(5)) + ' ' + duration_unit) as totquantity, total_amount, total_amount as totprice, 'service' as inclusiontype, s.service_name as inclusionname from _fis_service_sales ss
				inner join _fis_services s on s.id = ss.fk_service_id
				where fk_contract_id=".$request->post()['contract_id']));
				
				$sc_details = DB::select(DB::raw("select sc.contract_id, contract_no, fun_branch, contract_date, (s.firstname + ', ' + s.middlename + ' ' + s.lastname)signee,
					s.address as signeeaddress, sc.discount, sc.grossPrice, sc.contract_amount, sc.contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
					d.birthday, d.address, d.causeOfDeath, sc.mort_viewing, cr.ReligionName, p.package_name
					from _fis_service_contract sc 
					inner join (select * from _fis_profileheader where profile_type='Signee')s on sc.signee = s.id
					inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
								inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
								where profile_type='Decease')d on sc.deceased_id = d.id
					inner join _fis_package p on sc.package_class_id = p.package_code
					inner join ClientReligion cr on d.religion = cr.ReligionID
					where contract_id=".$service_contract->contract_id));
				
				$sc_transaction = DB::select(DB::raw("select payment_id, account_type, AR_Debit, AR_Credit, balance, tran_type, reference_no, payment_date, payment_mode, transactedBy, remarks, isCancelled from _fis_sc_payments sp inner join _fis_account a
					on a.account_id = sp.accountType
					where contract_id=".$service_contract->contract_id));
				
				
				/*$services = DB::select(DB::raw("select CAST(fk_service_id as varchar(10)) as id, service_duration, duration_unit, total_amount, total_amount as totprice, 'service' as inclusiontype from _fis_service_sales ss
				inner join _fis_services s on s.id = ss.fk_service_id
				where fk_contract_id=".$request->post()['contract_id'])); */
				
				
				return [
						'status'=>'success_posted',
						'message'=> [
								'service_contract' => $sc_details,
								'inclusions' => $availments,
								'transactions' => $sc_transaction
						]
				];
				
			}
			
			else
			{
				try {
					$user_check = DB::select(DB::raw("select item_code, item_name, quantity, price, discount, (price * quantity) as tot_price, SLCode, income_SLCode from
						(
						SELECT item_code, item_name, isnull(quantity, 0) as quantity, selling_price as price, 0 as discount, 0 as tot_price, SLCode, income_SLCode FROM _fis_items fi
						left join
						(
						select * from _fis_package_inclusions
						where fk_package_id=".$service_contract->package_class_id."
						and inclusionType='ITEM'
						)b on fi.item_code = b.item_id
						)sdf
						order by quantity desc,  item_code asc
						"));
					
					$sc_details = DB::select(DB::raw("select sc.contract_id, contract_no, fun_branch, contract_date, (s.firstname + ', ' + s.middlename + ' ' + s.lastname)signee,
					s.address as signeeaddress, sc.discount, sc.grossPrice, sc.contract_amount, sc.contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
					d.birthday, d.address, d.causeOfDeath, sc.mort_viewing, cr.ReligionName, p.package_name
					from _fis_service_contract sc 
					inner join (select * from _fis_profileheader where profile_type='Signee')s on sc.signee = s.id
					inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
								inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
								where profile_type='Decease')d on sc.deceased_id = d.id
					inner join _fis_package p on sc.package_class_id = p.package_code
					inner join ClientReligion cr on d.religion = cr.ReligionID
					where contract_id=".$service_contract->contract_id));
					
					
					$services = DB::select(DB::raw("select * from
						(
						SELECT fs.id, service_name, isnull(a.service_price, 0) as amount, 0 as less, isnull(duration, '') as duration, isnull(type_duration, '') as type_duration, isnull(a.service_price, 0) as tot_price, SLCode  FROM _fis_services fs
						left join
						(
						select * from _fis_package_inclusions where fk_package_id=".$service_contract->package_class_id." and inclusionType='SERV'
						)a on fs.id = a.service_id and fs.isActive=1
						)sdfa
						order by duration desc"));
						
						return [
								'status'=>'success_unposted',
								'message'=> [
										'service_contract' => $sc_details,
										'item_inclusions' => $user_check,
										'service_inclusions' => $services
								]
						];
					
				} catch (\Exception $e) {
					return [
							'status'=>'error',
							'message'=>$e->getMessage()
					];
				}
				
			}
			
		} catch (ModelNotFoundException $e) {
			return [
					'status'=>'error',
					'message'=>'contract not found'
			];
		}
		
		
		
	}
	
	
	public function removeCharging(Request $request)
	{
		$details = (array)json_decode($request->post()['payment_details']);
		
		try {
			$accountType = FisCharging::where(['fk_scID'=>$details['contract_id'], 'accountType'=>$details['account_type']])->delete();
			
			if($accountType)
			{
				return [
						'status'=>'saved',
						'message'=>'Account Charging successfully removed.'
				];
			}
			
			else
			{
				return [
						'status'=>'unsaved',
						'message'=>'Account Charging cannot be found or cannot be removed.'
				];
			}
		} catch (\Exception $e) {
			
			return [
					'status'=>'unsaved',
					'message'=> $e->getMessage()
			];
		}
	}
	
	public function unpostSales(Request $request)
	{
		try {
			$value = [];
			
			$value_api = (array)json_decode($request->post()['servicecontract']);
			
			
			$salesDetails = DB::select(DB::raw("select total_amount, balance, status, isPosted, fun_branch, '-' as sc_deceased, id, OR_no, client from _fis_itemsales_header
					where id=".$value_api['sales_id']));
			
			$value['total_amount']	= $salesDetails[0]->total_amount;
			$value['balance']	= $salesDetails[0]->balance;
			$value['status']	= $salesDetails[0]->status;
			$value['isPosted']	= $salesDetails[0]->isPosted;
			$value['fun_branch']	= $salesDetails[0]->fun_branch;
			$value['sc_deceased']	= $salesDetails[0]->sc_deceased;
			$value['id']	= $salesDetails[0]->id;
			$value['OR_no']	= $salesDetails[0]->OR_no;
			$value['client']	= $salesDetails[0]->client;
			
			DB::beginTransaction();
			
			if($value['status']=="CANCELLED")
			{
				return [
						'status'=>'unsaved',
						'message'=>'Merchandise Purchase is already Cancelled.'
				];
			}
			
			if($value['total_amount']!=$value['balance'])
			{
				return [
						'status'=>'unsaved',
						'message'=>'Please make sure Merchandise Purchase is not closed nor paid.'
				];
			}
			
			if($value['isPosted']=="0")
			{
				$sales = FisItemsalesHeader::find($value['id']);
				$sales->update([
						[
								'status'=>'CANCELLED',
								'isPosted'=>2
						]
				]);
				
				DB::commit();
				return [
						'status'=>'saved',
						'message'=>'Unposted Merchandise Purchase Successfully Cancelled.'
				];
				
			}
			
			$value['item_inclusions'] = DB::select(DB::raw("select product_id as item_code, price, sales.id as sales_id, total_price as tot_price, quantity, discount, SLCode, income_SLCode, item_name from _fis_item_sales sales
					inner join _fis_items i on sales.product_id = i.item_code
					where sales_id=".$value_api['sales_id']));
			
			
			$value['item_inventory'] = DB::select(DB::raw("select SLCode, p_sequence as id, item_code, item_name, inventory.item_price , inventory.serialNo from _fis_item_inventory inventory
					inner join _fis_items i on inventory.product_id = i.item_code
					where fk_sales_id=".$value_api['sales_id']));
			
			$value['service_inclusions'] = DB::select(DB::raw("select ss.id as sales_id, SLCode, grossAmount as amount, service_duration as duration, s.id, discount as less, service_name, total_amount as tot_price, duration_unit as type_duration from _fis_service_sales ss
					inner join _fis_services s on ss.fk_service_id = s.id where sales_id=".$value_api['sales_id']));
			
			
			$acctgHeader = [];
			$acctgHeader['branch_code'] = $value['fun_branch'];
			$acctgHeader['transaction_date'] = date('Y-m-d');
			$acctgHeader['transaction_code'] = "JNLVOUCHER";
			$acctgHeader['username'] = "hcalio";
			$acctgHeader['reference'] = "CSP".$value['OR_no'];
			$acctgHeader['status'] = 1;
			$acctgHeader['particulars'] = "Cancellation of Merch. Purchase #".$value['OR_no'];
			$acctgHeader['customer'] = $value['client'];
			$acctgHeader['checkno'] = "";
			
			$currentBranch = FisBranch::where([
					'branchID'=>$value['fun_branch']
			])->firstOrFail();
			
			
			$acctgDetails = [];
			
			$pushDetails = [];
			
			
			
			
			$sales = FisItemsalesHeader::find($value['id']);
			$sales->update(
					[
							'status'=>'CANCELLED',
							'isPosted'=>2
					]
					);
			
			
			$pushDetails['entry_type']="CR";
			$pushDetails['SLCode']="1-1-112-03-004";
			$pushDetails['amount']=$value['total_amount'];
			$pushDetails['detail_particulars']="To record AR from Merch. Puchase #".$value['OR_no']." Signee Name : ".$value['client'];
			array_push($acctgDetails, $pushDetails);
			
			//no discount "yet"
			
			foreach($value['item_inclusions'] as $row)
			{
				
				try {
					
					$itemSales = FisItemSales::find($row->sales_id);
					$itemSales->update([
							'isCancelled'=>1
					]);
					
					
					
					$pushDetails['entry_type']="DR";
					$pushDetails['SLCode']= $row->income_SLCode;
					$pushDetails['amount']= $row->tot_price;
					$pushDetails['detail_particulars']="Income ".$row->item_name." from Merch. Purchase #".$value['OR_no']." Signee: ".$value['client'];
					
					array_push($acctgDetails, $pushDetails);
					
					
					
				} catch (\Exception $e) {
					DB::rollback();
					return [
							'status'=>'unsaved',
							'message'=>$e->getMessage()
					];
					break;
				}
				
			}
			
			
			foreach($value['item_inventory'] as $row)
			{
				try {
					
					$productList = FisProductList::where([
							'id'=>$row->id,
							//'isEncumbered'=>1,
					])->firstOrFail();
					
					
					FisItemInventory::create(
							[
									'transaction_date'=>date('Y-m-d'),
									'particulars'=>'Purchased by Merch. Purchase #'.$value['OR_no'],
									'contract_id'=>0,
									'dr_no'=>'-',
									'rr_no'=>'-',
									'process'=>'IN',
									'remaining_balance'=>0,
									'product_id'=>$row->item_code,
									'quantity'=>1,
									'item_price'=>$row->item_price,
									'remarks'=>'-',
									'serialNo'=>$productList->serialNo,
									'p_sequence'=>$row->id,
									'fk_sales_id'=>$value['id'],
									'fk_ORNo'=>'',
							]);
					
					
					$productList->update([
							'isEncumbered'=>1
					]);
					
					
					
					if($row->SLCode!="-")
					{
						$pushDetails['entry_type']="CR";
						$pushDetails['SLCode']= $row->SLCode;
						$pushDetails['amount']= $productList->price;
						$pushDetails['detail_particulars']="To record Inventory of ".$row->item_name." from Merch. Purchase #".$value['OR_no']." Signee Name : ".$value['client'];
						array_push($acctgDetails, $pushDetails);
						
						$pushDetails['entry_type']="DR";
						$pushDetails['SLCode']= $currentBranch->borrowHO;
						$pushDetails['amount']= $productList->price;
						$pushDetails['detail_particulars']="To record Inventory of ".$row->item_name." from Merch. Purchase #".$value['OR_no']." Signee Name : ".$value['client'];
						array_push($acctgDetails, $pushDetails);
						
					}
					
					
				} catch (\Exception $e) {
					DB::rollback();
					return [
							'status'=>'unsaved',
							'message'=>$e->getMessage()
					];
					break;
					
				}
				
				
			}
			
			
			foreach($value['service_inclusions'] as $row)
			{
				
				try {
					
					$serviceSales = FisServiceSales::find($row->sales_id);
					$serviceSales->update([
							'isCancelled'=>1
					]);
					
					
					$pushDetails['entry_type']="DR";
					$pushDetails['SLCode']= $row->SLCode;
					$pushDetails['amount']= $row->tot_price;
					$pushDetails['detail_particulars']="Income of ".$row->service_name." from Merch. Purchase #".$value['OR_no']." Signee: ".$value['client'];
					
					array_push($acctgDetails, $pushDetails);
					
					
					
				} catch (\Exception $e) {
					DB::rollback();
					return [
							'status'=>'unsaved',
							'message'=>$e->getMessage()
					];
					break;
				}
				
			}
			
			
			
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
			DB::rollBack();
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];
		}
		
	}
	
	public function cancelPurchasePayment(Request $request)
	{
		try {
			$paydetails = (array)json_decode($request->post()['payment_details']);
			$pay_id	= $paydetails['payment_id'];

			
			try {
				$user = SystemUser::where(
						[
								'Password'=>$paydetails['password_input'],
								'UserName'=>$paydetails['username'],
								
						])->firstOrFail();
						
			} catch (\Exception $e) {
				return [
						'status'=>'unsaved',
						'message'=>'Incorrect Password'
				];
			}
			
			DB::beginTransaction();
			
			$transaction = FisSalesTransaction::where('id', $pay_id)
			->where('isCancelled', 0)
			->where('payment_date', date('Y-m-d'))
			->first();
			
			if($transaction)
			{
				$salesHead = FisItemsalesHeader::find($transaction->sales_id);
				$transaction->isCancelled = 1;
				$transaction->update();
				
				$remainingbalance_sales = $salesHead->balance + $transaction->AR_Credit;
				$transaction_sale = FisSalesTransaction::create([
						'sales_id'=>$salesHead->id,
						'accountType'=>$transaction->accountType, //2 is for peronal. see _fis_account table
						'AR_Debit'=>$transaction->AR_Credit,
						'AR_Credit'=>0,
						'balance'=>$remainingbalance_sales,
						'reference_no'=>"(".$transaction->reference_no.")",
						'payment_date'=>date('Y-m-d'),
						'transactedBy'=>'hcalio',
						'payment_mode'=>$transaction->payment_mode,
						'isCancelled'=>0,
						'isRemitted'=>0,
						'remittedTo'=>'',
						'isPosted'=>1,
						'remarks'=>'Cancellation of Purchase Payment',
						'tran_type'=>$transaction->tran_type == 'PAYPARTIAL' ? 'CANPAYPARTIAL' : 'CANPAYCLOSE',
				]);
				
				$salesHead->update([
						'balance'=>$remainingbalance_sales,
						'status'=>'ACTIVE'
				]);
				
				
				$acctgHeader_pay = [];
				$acctgDetails_pay = [];
				$pushDetails_pay= [];
				
				$paytype = FisPaymentType::find($transaction->payment_mode);
				
				$acctgHeader_pay['branch_code'] = $salesHead->fun_branch;
				$acctgHeader_pay['transaction_date'] = date('Y-m-d');
				$acctgHeader_pay['transaction_code'] = $paytype->trandesc;
				$acctgHeader_pay['username'] = 'hcalio';
				$acctgHeader_pay['reference'] = "CNMercPay-".$transaction->reference_no;
				$acctgHeader_pay['status'] = $paytype->trantype;
				$acctgHeader_pay['particulars'] = "Cancellation of Merch Payment w/ Ref. #".$transaction->reference_no;
				$acctgHeader_pay['customer'] = $salesHead->client;
				$acctgHeader_pay['checkno'] = "";
				
				
				
				$pushDetails_pay['entry_type']="CR";
				$pushDetails_pay['SLCode']=$paytype->sl_debit;
				$pushDetails_pay['amount']=$transaction->AR_Credit;
				$pushDetails_pay['detail_particulars']="Cancellation of Merch Payment w/ Ref. #".$transaction->reference_no;
				array_push($acctgDetails_pay, $pushDetails_pay);
				
				$pushDetails_pay['entry_type']="DR";
				$pushDetails_pay['SLCode']=$paytype->sl_credit;
				$pushDetails_pay['amount']=$transaction->AR_Credit;
				$pushDetails_pay['detail_particulars']="Cancellation of Merch Payment w/ Ref. #".$transaction->reference_no;
				array_push($acctgDetails_pay, $pushDetails_pay);
				
				$saveacctg = AccountingHelper::processAccounting($acctgHeader_pay, $acctgDetails_pay);
				
				if($saveacctg['status']=='saved')
				{
					DB::commit();
					
					$sc_transaction = DB::select(DB::raw("select id, account_type, AR_Debit, AR_Credit, balance, tran_type, reference_no, payment_date, payment_mode, transactedBy, remarks, isCancelled from _fis_sales_transaction sp inner join _fis_account a
							on a.account_id = sp.accountType
							where sales_id=$salesHead->id"));
					
					
					return [
							'status'=>'saved',
							'message'=>'Payment successfully cancelled.',
							'purchase_tran'=>$sc_transaction
					];
				}
				
				else
				{
					DB::rollback();
					return $saveacctg;
				}
				
			}
			
			else
			{
				DB::rollBack();
				return [
						'status'=>'unsaved',
						'message'=>'No payment found. Or payment is already cancelled',
				];
			}
			
			
		} catch (\Exception $e) {
			return [
					'status'=>'unsaved',
					'message'=>$e->getMessage(),
			];
		}
		
	}
	
	public function cancelPayment(Request $request)
	{
		try {
			$paydetails = (array)json_decode($request->post()['payment_details']);
			$pay_id	= $paydetails['payment_id'];
		//	return $paydetails;
			
			try {
				$user = SystemUser::where(
						[
								'Password'=>$paydetails['password_input'],
								'UserName'=>$paydetails['username'],
								
						])->firstOrFail();
						
			} catch (\Exception $e) {
				return [
						'status'=>'unsaved',
						'message'=>'Incorrect Password'
				];
			}
			
			
			DB::beginTransaction();
			
			$payment = FisSCPayments::where('payment_id', $pay_id)
			->where('isCancelled', 0)
			->where('payment_date', date('Y-m-d'))
			->first();
			//return $payment;	
			if(!$payment)
			{
				return [
						'status'=>'unsaved',
						'message'=>'No payment found. Or payment is already cancelled',
				];
			}
			

			
			try {
				$charging = FisCharging::where([
						'fk_scID'=>$payment->contract_id,
						'accountType'=>$payment->accountType,
				])->firstOrFail();
				$chargePayment = $charging->balance + $payment->AR_Credit;
				
			
				
				$charging->update([
						'balance'=> $chargePayment
				]);
				
				
				
			} catch (\Exception $e) {
				DB::rollBack();
				return [
						'status'=>'unsaved',
						'message'=>$e->getMessage(),
				];
			}
			
			
			$contract = ServiceContract::where('contract_id', $payment->contract_id)
			->whereNotIn('status', ['DRAFT', 'CANCELLED'])
			->first();
			
			if(!$contract)
			{
				DB::rollBack();
				return [
						'status'=>'unsaved',
						'message'=>'Payment cannot be cancelled.',
				];
				
			}
			
			$remainingbalance = $contract->contract_balance + $payment->AR_Credit;
			
			$contract->update([
					'contract_balance'=> $remainingbalance,
					'status'=> 'ACTIVE'
			]);
			
			$scpayment = FisSCPayments::create([
					'contract_id'=>$contract->contract_id,
					'accountType'=>$payment->accountType,
					'AR_Debit'=>$payment->AR_Credit,
					'AR_Credit'=>0,
					'balance'=>$remainingbalance,
					'reference_no'=>'('.$payment->reference_no.')',
					'payment_date'=>date('Y-m-d'),
					'payment_mode'=>$payment->payment_mode,
					'transactedBy'=>'hcalio',
					'isCancelled'=>0,
					'isRemitted'=>0,
					'remittedTo'=>'',
					'isPosted'=>1,
					'remarks'=>'Cancellation of',
					'tran_type'=>$payment->tran_type == 'PAYPARTIAL' ? 'CANPAYPARTIAL' : 'CANPAYCLOSE',
			]);
			
			$payment->update([
					'isCancelled'=>1
			]);
			
			$acctgHeader_pay = [];
			$acctgDetails_pay = [];
			$pushDetails_pay= [];
			
			$paytype = FisPaymentType::find($payment->payment_mode);
			
			$acctgHeader_pay['branch_code'] = $contract->fun_branch;
			$acctgHeader_pay['transaction_date'] = date('Y-m-d');
			$acctgHeader_pay['transaction_code'] = $paytype->trandesc;
			$acctgHeader_pay['username'] = 'hcalio';
			$acctgHeader_pay['reference'] = "CSCPay".$contract->contract_no."-".$payment->reference_no;
			$acctgHeader_pay['status'] = $paytype->trantype;
			$acctgHeader_pay['particulars'] = "Posting of Cancellation of SC Payment w/ SC #".$contract->contract_no;
			$acctgHeader_pay['customer'] = "";
			$acctgHeader_pay['checkno'] = "";
			
			
			
			$pushDetails_pay['entry_type']="CR";
			$pushDetails_pay['SLCode']=$paytype->sl_debit;
			$pushDetails_pay['amount']=$payment->AR_Credit;
			$pushDetails_pay['detail_particulars']="To record cancellation payment from SC Ref#".$payment->reference_no;
			array_push($acctgDetails_pay, $pushDetails_pay);
			
			$pushDetails_pay['entry_type']="DR";
			$pushDetails_pay['SLCode']=$paytype->sl_credit;
			$pushDetails_pay['amount']=$payment->AR_Credit;
			$pushDetails_pay['detail_particulars']="To record cancellation payment from SC Ref#".$payment->reference_no;
			array_push($acctgDetails_pay, $pushDetails_pay);
			
			$saveacctg = AccountingHelper::processAccounting($acctgHeader_pay, $acctgDetails_pay);
			
			if($saveacctg['status']!='saved')
			{
				DB::rollback();
				return $saveacctg;
			}
			
			else {
				
				DB::commit();
				
				$sc_transaction = DB::select(DB::raw("select payment_id, account_type, AR_Debit, AR_Credit, balance, tran_type, reference_no, payment_date, payment_mode, transactedBy, remarks, isCancelled from _fis_sc_payments sp inner join _fis_account a
					on a.account_id = sp.accountType
					where contract_id=".$payment->contract_id));
				
				return [
						'status'=>'saved',
						'message'=>'Successfully Posted Payment',
						'sc_transaction'=>$sc_transaction
				];
				
			}
			
		} catch (\Exception $e) {
			DB::rollBack();
			return [
					'status'=>'unsaved',
					'message'=>$e->getMessage(),
			];
		}
	}
	
	public function unpostContract(Request $request)
	{
		try {
			
			
			$value_api = (array)json_decode($request->post()['servicecontract']);
			
		
			
			try {
				$user = SystemUser::where(
						[
								'Password'=>$value_api['password_input'],
								'UserName'=>$value_api['username'],
								
						])->firstOrFail();
				
			} catch (\Exception $e) {
				return [
						'status'=>'unsaved',
						'message'=>'Incorrect Password'
				];
			}
			
			
			$value['item_inclusions'] = DB::select(DB::raw("select product_id as item_code, price, sales.id as sales_id, total_price as tot_price, quantity, discount, SLCode, income_SLCode, item_name from _fis_item_sales sales
					inner join _fis_items i on sales.product_id = i.item_code
					where contract_id=".$value_api['contract_id']));
			
			$contractDetails = DB::select(DB::raw("select contract_amount, contract_balance, grossPrice, sc.status, sc.isPosted, fun_branch, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)sc_deceased, discount as sc_discount, contract_id as sc_id, contract_no as sc_number,
					(s.lastname + ', ' + s.firstname + ' ' + s.middlename)sc_signee
					from _fis_service_contract sc 
					inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
								inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
								where profile_type='Decease')d on d.id = sc.deceased_id
					inner join (select * from _fis_profileheader where profile_type='Signee')s on sc.signee = s.id
					where contract_id=".$value_api['contract_id']));
			
			$value['sc_amount']	= $contractDetails[0]->contract_amount;
			$value['sc_branch']	= $contractDetails[0]->fun_branch;
			$value['sc_deceased']	= $contractDetails[0]->sc_deceased;
			$value['sc_discount']	= $contractDetails[0]->sc_discount;
			$value['sc_id']	= $contractDetails[0]->sc_id;
			$value['sc_number']	= $contractDetails[0]->sc_number;
			$value['sc_signee']	= $contractDetails[0]->sc_signee;
			$value['status']	= $contractDetails[0]->status;
			$value['isPosted']	= $contractDetails[0]->isPosted;
			
			DB::beginTransaction();
			
			if($value['status']=="CANCELLED")
			{
				return [
						'status'=>'saved',
						'message'=>'Contract is already Cancelled.'
				];
			}
			
			
			if($contractDetails[0]->contract_amount!=$contractDetails[0]->contract_balance)
			{
				return [
						'status'=>'unsaved',
						'message'=>'Please make sure Contract is not closed or no payment has been made.'
				];
			}
			
			if($value['isPosted']=="0")
			{
				$sc = ServiceContract::find($value['sc_id']);
				$sc->update(
						[
								'status'=>'CANCELLED',
								'isPosted'=>2
						]
						);
				DB::commit();
				return [
						'status'=>'saved',
						'message'=>'Unposted Contract Successfully Cancelled.'
				];
				
			}
			
			
		 	$value['item_inventory'] = DB::select(DB::raw("select SLCode, p_sequence as id, item_code, item_name, inventory.item_price , inventory.serialNo from _fis_item_inventory inventory
					inner join _fis_items i on inventory.product_id = i.item_code
					where contract_id=".$value_api['contract_id']));
		 	
		 	$value['service_inclusions'] = DB::select(DB::raw("select ss.id as sales_id, SLCode, grossAmount as amount, service_duration as duration, s.id, discount as less, service_name, total_amount as tot_price, duration_unit as type_duration from _fis_service_sales ss
					inner join _fis_services s on ss.fk_service_id = s.id where fk_contract_id=".$value_api['contract_id']));
		 	
			/*
			 * here lies the posting of contract.
			 * 1st step, update the balance of contract,
			 * 2nd step, record items get,
			 * 3rd step, record services get,
			 * 4th step,  record inventory of items.
			 * 5th step contract posting?
			 */
			
			
			$contract_discount = 0;
			
			$contract_discount = is_numeric($value['sc_discount']) ? $value['sc_discount'] : 0;
			
			
			
			
			$acctgHeader = [];
			$acctgHeader['branch_code'] = '201';
			$acctgHeader['transaction_date'] = date('Y-m-d');
			$acctgHeader['transaction_code'] = "JNLVOUCHER";
			$acctgHeader['username'] = "hcalio";
			$acctgHeader['reference'] = "CSC".$value['sc_number'];
			$acctgHeader['status'] = 1;
			$acctgHeader['particulars'] = "Cancellation of SC #".$value['sc_number'];
			$acctgHeader['customer'] = $value['sc_signee'];
			$acctgHeader['checkno'] = "";
			
			
			$currentBranch = FisBranch::where([
					'branchID'=>'201'
			])->firstOrFail();
			
			
			$acctgDetails = [];
			
			$pushDetails = [];
			

				
				
				$sc = ServiceContract::find($value['sc_id']);
				$sc->update(
						[
								'status'=>'CANCELLED',
								'isPosted'=>2
						]
						);
				
				
				$pushDetails['entry_type']="CR";
				$pushDetails['SLCode']="1-1-112-03-004";
				$pushDetails['amount']=$contractDetails[0]->grossPrice - $contract_discount;
				$pushDetails['detail_particulars']="To record AR from Service Contract No.".$value['sc_number']." Signee Name : ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
				array_push($acctgDetails, $pushDetails);
				
				if($contract_discount>0)
				{
					$pushDetails['entry_type']="CR";
					$pushDetails['SLCode']="4-1-411-01-001";
					$pushDetails['amount']= $contract_discount;
					$pushDetails['detail_particulars']="To record Discount from SC No.".$value['sc_number']." Signee Name : ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
					array_push($acctgDetails, $pushDetails);
				}
				
				
				
				foreach($value['item_inclusions'] as $row)
				{
					
					try {

						$itemSales = FisItemSales::find($row->sales_id);
						$itemSales->update([
								'isCancelled'=>1
						]);

						
						
						$pushDetails['entry_type']="DR";
						$pushDetails['SLCode']= $row->income_SLCode;
						$pushDetails['amount']= $row->tot_price;
						$pushDetails['detail_particulars']="Income ".$row->item_name." from SC No.".$value['sc_number']." Signee: ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
						
						array_push($acctgDetails, $pushDetails);
						
						
						
					} catch (\Exception $e) {
						DB::rollback();
						return [
								'status'=>'unsaved',
								'message'=>$e->getMessage()
						];
						break;
					}
					
				}
				
				
				foreach($value['item_inventory'] as $row)
				{
					try {
						
						$productList = FisProductList::where([
								'id'=>$row->id,
								//'isEncumbered'=>1,
						])->firstOrFail();
						
						
						FisItemInventory::create(
								[
										'transaction_date'=>date('Y-m-d'),
										'particulars'=>'Purchased by SC. #'.$sc->contract_no,
										'contract_id'=>$sc->contract_id,
										'dr_no'=>'-',
										'rr_no'=>'-',
										'process'=>'IN',
										'remaining_balance'=>0,
										'product_id'=>$row->item_code,
										'quantity'=>1,
										'item_price'=>$row->item_price,
										'remarks'=>'-',
										'serialNo'=>$productList->serialNo,
										'p_sequence'=>$row->id,
										'fk_sales_id'=>0,
										'fk_ORNo'=>'',
								]);
						

						$productList->update([
								'isEncumbered'=>1
						]);
						
						
						
						if($row->SLCode!="-")
						{
							$pushDetails['entry_type']="CR";
							$pushDetails['SLCode']= $row->SLCode;
							$pushDetails['amount']= $productList->price;
							$pushDetails['detail_particulars']="To record Inventory of ".$row->item_name." from SC No.".$value['sc_number']." Signee Name : ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
							array_push($acctgDetails, $pushDetails);
							
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= $currentBranch->borrowHO;
							$pushDetails['amount']= $productList->price;
							$pushDetails['detail_particulars']="To record Inventory of ".$row->item_name." from SC No.".$value['sc_number']." Signee Name : ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
							array_push($acctgDetails, $pushDetails);
							
						}
						
						
					} catch (\Exception $e) {
						DB::rollback();
						return [
								'status'=>'unsaved',
								'message'=>$e->getMessage()
						];
						break;
						
					}
					
					
				}
				
				
				foreach($value['service_inclusions'] as $row)
				{
					
					try {
						
						$serviceSales = FisServiceSales::find($row->sales_id);
						$serviceSales->update([
								'isCancelled'=>1
						]);

												
						$pushDetails['entry_type']="DR";
						$pushDetails['SLCode']= $row->SLCode;
						$pushDetails['amount']= $row->tot_price;
						$pushDetails['detail_particulars']="Income of ".$row->service_name." from SC #".$value['sc_number']." Signee: ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
						
						array_push($acctgDetails, $pushDetails);
						
						
						
					} catch (\Exception $e) {
						DB::rollback();
						return [
								'status'=>'unsaved',
								'message'=>$e->getMessage()
						];
						break;
					}
					
				}
				
		
				
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
					'status' => 'unsaved',
					'message' => $e->getMessage()
			];
		}
		
	}
	
	
	public function getAccountsOfClient(Request $request)
	{
		$clientid = $request->post()['client'];
		
		try {
			$signee = FisMemberData::find($clientid);
			
			if($signee->profile_type=='Signee')
			{
				
				$contracts = DB::select(DB::raw("select contract_id, contract_no, contract_date, contract_amount, contract_balance, status from _fis_service_contract where signee=$clientid"));
				$merchandises = DB::select(DB::raw("select id, OR_no as reference_no, date as posting_date, total_amount as amount, balance, status from _fis_itemsales_header where signee_id=$clientid"));
				
				return [
						'status'=>'success',
						'message'=> [
								'signee' => $signee,
								'contractlist' => $contracts,
								'merchandises' => $merchandises
						]
				];
			}
			
			else
			{
				return [
						'status'=>'error',
						'message'=>'Profile Not a signee'
				];
			}
			
			
		} catch (\Exception $e) {
			return [
					'status'=>'error',
					'message'=>$e->getMessage()
			];
		}
		//return $clientid;
	}
	
	public function getCharging(Request $request)
	{
		$contract = $request->post()['contractid'];
		$charging = DB::select(DB::raw("select account_type as accountTypeLabel, accountType, total_amount, balance from _fis_sc_charging sc  inner join _fis_account a
			on a.account_id = sc.accountType
			where fk_scID='$contract'"));
		
		return $charging;
		
	}
	
	public function insertCharging(Request $request)
	{
		try {
			$value = (array)json_decode($request->post()['payment_details']);
			$value['balance'] = $value['total_amount'];
			$value['date'] = date('Y-m-d H:i:s');
			$charging = FisCharging::create($value);
			return [
					'status'=>'saved',
					'message'=>''
			];
			
			
		} catch (\Exception $e) {
			return [
					'status'=>'unsaved',
					'message'=>$e->getMessage()
			];
		}
		
	}
	
	public function getPurchaseDetails(Request $request)
	{
		try {
			
			$salesid = $request->post()['sales_id'];
			
			
			
			$availments = DB::select(DB::raw("select product_id as code, (CAST(quantity as varchar(5)) + ' ' + unit_type) as quantity, price, total_price, 'item' as type, i.item_name as description from _fis_item_sales sales
				inner join _fis_items i on sales.product_id = i.item_code
				where sales_id=$salesid
				UNION ALL
				select CAST(fk_service_id as varchar(10)) as id, (CAST(service_duration as varchar(5)) + ' ' + duration_unit) as totquantity, total_amount, total_amount as totprice, 'service' as inclusiontype, s.service_name as inclusionname from _fis_service_sales ss
				inner join _fis_services s on s.id = ss.fk_service_id
				where sales_id=$salesid"));
			
			$sc_transaction = DB::select(DB::raw("select id, account_type, AR_Debit, AR_Credit, balance, tran_type, reference_no, payment_date, payment_mode, transactedBy, remarks, isCancelled from _fis_sales_transaction sp inner join _fis_account a
					on a.account_id = sp.accountType
					where sales_id=$salesid"));
			
			
			$header = FisItemsalesHeader::find($salesid); 
			
			
			return [
					'status'=>'success',
					'message'=> [
							'signee' => $header,
							'purchases' => $availments,
							'transactions' => $sc_transaction
					]
			];
			
			
		} catch (\Exception $e) {
			
			return [
					'status'=>'failed',
					'message'=> $e->getMessage()
			];
		}
	}
}
