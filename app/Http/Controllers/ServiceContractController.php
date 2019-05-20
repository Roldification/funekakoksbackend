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

class ServiceContractController extends Controller
{
    //
    
	public function getDetailsOfContract(Request $request)
	{
		try {
			$service_contract = ServiceContract::where([
					'contract_id'=>$request->post()['contract_id']
			])->firstOrFail();
			
			if($service_contract->status=='ACTIVE' && $service_contract->isPosted==1)
			{
				$user_check = DB::select(DB::raw("select product_id, quantity, unit_type, price, total_price, 'item' as inclusiontype from _fis_item_sales sales
				inner join _fis_items i on sales.product_id = i.item_code
				where contract_id=".$request->post()['contract_id']));
				
				$sc_details = DB::select(DB::raw("select sc.contract_id, contract_no, fun_branch, contract_date, (s.lname + ', ' + s.fname + ' ' + s.mname)signee,
						s.address as signeeaddress, sc.discount, sc.grossPrice, sc.contract_amount, sc.contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
						d.birthday, d.address, d.causeOfDeath, sc.mort_viewing, cr.ReligionName, p.package_name
						from _fis_service_contract sc
						inner join _fis_signee s on sc.signee = s.id
						inner join _fis_deceased d on sc.deceased_id = d.id
						inner join _fis_package p on sc.package_class_id = p.id
						inner join ClientReligion cr on d.religion = cr.ReligionID
						where contract_id=".$service_contract->contract_id));
				
				
				$services = DB::select(DB::raw("select CAST(fk_service_id as varchar(10)) as id, service_duration, duration_unit, total_amount, total_amount as totprice, 'service' as inclusiontype from _fis_service_sales ss
				inner join _fis_services s on s.id = ss.fk_service_id
				where fk_contract_id=".$request->post()['contract_id']));
				
				
				return [
						'status'=>'success_posted',
						'message'=> [
								'service_contract' => $sc_details,
								'item_inclusions' => $user_check,
								'service_inclusions' => $services
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
					
					$sc_details = DB::select(DB::raw("select sc.contract_id, contract_no, fun_branch, contract_date, (s.lname + ', ' + s.fname + ' ' + s.mname)signee,
						s.address as signeeaddress, sc.discount, sc.grossPrice, sc.contract_amount, sc.contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
						d.birthday, d.address, d.causeOfDeath, sc.mort_viewing, cr.ReligionName, p.package_name
						from _fis_service_contract sc
						inner join _fis_signee s on sc.signee = s.id
						inner join _fis_deceased d on sc.deceased_id = d.id
						inner join _fis_package p on sc.package_class_id = p.id
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
	
	public function unpostContract(Request $request)
	{
		try {
			//$value = (array)json_decode($request->post()['contract_details']);
			
			$value = [];
			
			$value['item_inclusions'] = DB::select(DB::raw("select product_id as item_code, price, sales.id as sales_id, total_price as tot_price, quantity, discount, SLCode, income_SLCode, item_name from _fis_item_sales sales
					inner join _fis_items i on sales.product_id = i.item_code
					where contract_id=".$request->post()['contract_id']));
			
			$contractDetails = DB::select(DB::raw("select contract_amount, sc.status, sc.isPosted, fun_branch, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)sc_deceased, discount as sc_discount, contract_id as sc_id, contract_no as sc_number,
					(s.lname + ', ' + s.fname + ' ' + s.mname)sc_signee
					from _fis_service_contract sc 
					inner join _fis_deceased d on d.id = sc.deceased_id
					inner join _fis_signee s on sc.signee = s.id
					where contract_id=".$request->post()['contract_id']));
			
			$value['sc_amount']	= $contractDetails[0]->contract_amount;
			$value['sc_branch']	= $contractDetails[0]->fun_branch;
			$value['sc_deceased']	= $contractDetails[0]->sc_deceased;
			$value['sc_discount']	= $contractDetails[0]->sc_discount;
			$value['sc_id']	= $contractDetails[0]->sc_id;
			$value['sc_number']	= $contractDetails[0]->sc_number;
			$value['sc_signee']	= $contractDetails[0]->sc_signee;
			$value['status']	= $contractDetails[0]->status;
			$value['isPosted']	= $contractDetails[0]->isPosted;
			
			if($value['status']=="CANCELLED")
			{
				return [
						'status'=>'saved',
						'message'=>'Contract is already Cancelled.'
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
				
				return [
						'status'=>'saved',
						'message'=>'Unposted Contract Successfully Cancelled.'
				];
				
			}
			
			
		 	$value['item_inventory'] = DB::select(DB::raw("select SLCode, p_sequence as id, item_code, item_name, inventory.item_price , inventory.serialNo from _fis_item_inventory inventory
					inner join _fis_items i on inventory.product_id = i.item_code
					where contract_id=".$request->post()['contract_id']));
		 	
		 	$value['service_inclusions'] = DB::select(DB::raw("select ss.id as sales_id, SLCode, grossAmount as amount, service_duration as duration, s.id, discount as less, service_name, total_amount as tot_price, duration_unit as type_duration from _fis_service_sales ss
					inner join _fis_services s on ss.fk_service_id = s.id where fk_contract_id=".$request->post()['contract_id']));
		 	
		 	
			//return $value;
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
			
			
			DB::beginTransaction();
			
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
				$pushDetails['amount']=$value['sc_amount'] - $contract_discount;
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
										'fk_scID'=>$sc->contract_id,
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
}
