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
use App\FisItemTransfer;
use App\FisPackage;
use App\FisProfLogs;
use App\FisPendingContract;
use App\FisCancelMerchandise;

class ServiceContractController extends Controller
{

    
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

				$sc_details = DB::select(DB::raw("SELECT sc.contract_id, contract_no, fun_branch, CONVERT(VARCHAR(30),contract_date,101)contract_date, (s.lastname + ', ' + s.firstname + ' ' + s.middlename)signee,
					s.address as signeeaddress, s.customer_id as signee_cid, d.customer_id as deceased_cid, sc.remarks, CONVERT(VARCHAR(30),sc.burial_time,22)burial_time, CONVERT(VARCHAR(30),sc.discount,0)discount, CONVERT(VARCHAR(30),sc.grossPrice,0)grossPrice, CONVERT(VARCHAR(30),sc.contract_amount,0)contract_amount, CONVERT(VARCHAR(30),sc.contract_balance,0)contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
					CONVERT(VARCHAR(30),d.birthday,101)birthday, d.address, d.causeOfDeath, sc.embalming_place, cr.ReligionName, p.package_name, p.package_code, sc.status, sc.signee as signee_id, sc.has_tax, sc.tax_deferred, sc.tax_deferred_balance
					from _fis_service_contract sc 
					inner join (select ph.* from _fis_profileheader ph inner join _fis_ProfileLogs pl on ph.id = pl.fk_profile_id where pl.profile_type='Signee')s on sc.signee = s.id
					inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
					inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
					inner join _fis_ProfileLogs pl on ph.id = pl.fk_profile_id
					where pl.profile_type='Decease')d on sc.deceased_id = d.id
					inner join _fis_package p on sc.package_class_id = p.package_code
					inner join ClientReligion cr on d.religion = cr.ReligionID
					where contract_id=".$service_contract->contract_id));
				
				/*$sc_details = DB::select(DB::raw("select sc.contract_id, contract_no, fun_branch,  CONVERT(VARCHAR(30),contract_date,101)contract_date, (s.lastname + ', ' + s.firstname + ' ' + s.middlename)signee,
					s.address as signeeaddress, s.customer_id as signee_cid, d.customer_id as deceased_cid, sc.remarks, CONVERT(VARCHAR(30),sc.burial_time,22)burial_time, CONVERT(VARCHAR(30),sc.discount,0)discount, CONVERT(VARCHAR(30),sc.grossPrice,0)grossPrice, CONVERT(VARCHAR(30),sc.contract_amount,0)contract_amount, CONVERT(VARCHAR(30),sc.contract_balance,0)contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
					CONVERT(VARCHAR(30),d.birthday,101)birthday, d.address, d.causeOfDeath, sc.embalming_place, cr.ReligionName, p.package_name, sc.status, sc.signee as signee_id
					from _fis_service_contract sc 
					inner join (select * from _fis_profileheader where profile_type='Signee')s on sc.signee = s.id
					inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
								inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
								where profile_type='Decease')d on sc.deceased_id = d.id
					inner join _fis_package p on sc.package_class_id = p.package_code
					inner join ClientReligion cr on d.religion = cr.ReligionID
					where contract_id=".$service_contract->contract_id));*/
				
				$sc_transaction = DB::select(DB::raw("select payment_id, account_type, AR_Debit, AR_Credit, balance, isnull(tax_contribution, 0)tax_contribution, isnull(tax_balance, 0)tax_balance, tran_type, reference_no, payment_date, payment_mode, transactedBy, remarks, isCancelled from _fis_sc_payments sp inner join _fis_account a
					on a.account_id = sp.accountType
					where contract_id=".$service_contract->contract_id));
				
				
				/*$services = DB::select(DB::raw("select CAST(fk_service_id as varchar(10)) as id, service_duration, duration_unit, total_amount, total_amount as totprice, 'service' as inclusiontype from _fis_service_sales ss
				inner join _fis_services s on s.id = ss.fk_service_id
				where fk_contract_id=".$request->post()['contract_id'])); */
				
				$sc_details[0]->has_tax = $sc_details[0]->has_tax == 1 ? true : false;
				
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
					$user_check = DB::select(DB::raw("SELECT item_code, item_name, quantity, price, discount, (price * quantity) as tot_price, SLCode, income_SLCode FROM
						(
						SELECT item_code, isActive, item_name, isnull(quantity, 0) as quantity, selling_price as price, 0 as discount, 0 as tot_price, SLCode, income_SLCode FROM _fis_items fi
						LEFT JOIN
						(
						SELECT * FROM _fis_package_inclusions
						WHERE fk_package_id='".$service_contract->package_class_id."'
						AND inclusionType='ITEM'
						)b on fi.item_code = b.item_id where isActive = 1
						)sdf 
						where (left(item_code,2)<>'01' or quantity>=1)
						order by item_code asc, quantity 
						"));
					
					
					/*$sc_details = DB::select(DB::raw("select sc.contract_id, contract_no, fun_branch,  CONVERT(VARCHAR(30),contract_date,101)contract_date, (s.lastname + ', ' + s.firstname + ' ' + s.middlename)signee,
					s.address as signeeaddress, s.customer_id as signee_cid, d.customer_id as deceased_cid, sc.remarks, CONVERT(VARCHAR(30),sc.burial_time,22)burial_time, sc.discount, sc.grossPrice, sc.contract_amount, sc.contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
					CONVERT(VARCHAR(30),d.birthday,101)birthday, d.address, d.causeOfDeath, sc.embalming_place, cr.ReligionName, p.package_name, sc.status, sc.signee as signee_id
					from _fis_service_contract sc 
					inner join (select * from _fis_profileheader where profile_type='Signee')s on sc.signee = s.id
					inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
								inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
								where profile_type='Decease')d on sc.deceased_id = d.id
					inner join _fis_package p on sc.package_class_id = p.package_code
					inner join ClientReligion cr on d.religion = cr.ReligionID
					where contract_id=".$service_contract->contract_id));*/

					$sc_details = DB::select(DB::raw("SELECT sc.contract_id, contract_no, fun_branch,  CONVERT(VARCHAR(30),contract_date,101)contract_date, (s.lastname + ', ' + s.firstname + ' ' + s.middlename)signee,
						s.address as signeeaddress, s.customer_id as signee_cid, d.customer_id as deceased_cid, sc.remarks, CONVERT(VARCHAR(30),sc.burial_time,22)burial_time, sc.discount, sc.grossPrice, sc.contract_amount, sc.contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
						CONVERT(VARCHAR(30),d.birthday,101)birthday, d.address, d.causeOfDeath, sc.embalming_place, cr.ReligionName, p.package_name, p.package_code, sc.status, sc.signee as signee_id, sc.has_tax, sc.tax_deferred, sc.tax_deferred_balance
						from _fis_service_contract sc 
						inner join (select ph.* from _fis_profileheader ph
						inner join _fis_ProfileLogs pl on ph.id = pl.fk_profile_id where pl.profile_type='Signee')s on sc.signee = s.id
						inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
						inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
						inner join _fis_ProfileLogs pl on ph.id = pl.fk_profile_id
						where pl.profile_type='Decease')d on sc.deceased_id = d.id
						inner join _fis_package p on sc.package_class_id = p.package_code
						inner join ClientReligion cr on d.religion = cr.ReligionID
						where contract_id=".$service_contract->contract_id));
					
					
					$services = DB::select(DB::raw("SELECT * FROM
					(
					SELECT fs.id, isActive, service_name, isnull(a.service_price, 0) as amount, 0 as less, isnull(duration, '') as duration, isnull(type_duration, '') as type_duration, isnull(a.service_price, 0) as tot_price, SLCode  FROM _fis_services fs
					LEFT JOIN
					(
					SELECT * FROM _fis_package_inclusions WHERE fk_package_id='".$service_contract->package_class_id."' and inclusionType='SERV'
					)a on fs.id = a.service_id WHERE fs.isActive=1)sdfa
					ORDER BY duration desc"));
					
					
					$package_selected = DB::select(DB::raw("select * from 
						(
						SELECT
						case when item_id = '-' then CAST(service_id as varchar(5))
						else item_id end as columnid,
						isnull(item_name, service_name) as name,
						case when quantity < 1 then duration
						else quantity end as quantity,
						isnull(unit_type, type_duration) as uom,
						service_price as price, total_amount as total_price
						FROM _fis_package_inclusions fpi
						left join _fis_items i on fpi.item_id = i.item_code
						left join _fis_services s on fpi.service_id = s.id
						WHERE fk_package_id='".$service_contract->package_class_id."'
						)fas
						order by columnid"));
					
					$chapel_rentals = DB::select(DB::raw("select id as value, chapel_name as label from _fis_chapel_package"));
					
					$sc_details[0]->has_tax = $sc_details[0]->has_tax == 1 ? true : false;
					
						return [
								'status'=>'success_unposted',
								'message'=> [
										'service_contract' => $sc_details,
										'item_inclusions' => $user_check,
										'service_inclusions' => $services,
										'package_selected' => $package_selected,
										'chapel_rentals' => $chapel_rentals
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
	
	public function getChapelInclusions(Request $request)
	{
		
		$id = $request->post()['id'];
		$packageid = $request->post()['package_id'];
		try {
		 
			
		  $package = FisPackage::find($packageid);
		  $levelid = $package['package_level'];
		  $items = DB::select(DB::raw("select * from 
			(
			SELECT
			case when item_id = '-' then CAST(service_id as varchar(5))
			else item_id end as columnid,
			isnull(item_name, service_name) as name,
			case when quantity < 1 then duration
			else quantity end as quantity,
			isnull(unit_type, type_duration) as uom,
			service_price as price, total_amount as total_price
			FROM _fis_chapel_inclusions fpi
			left join _fis_items i on fpi.item_id = i.item_code
			left join _fis_services s on fpi.service_id = s.id
			WHERE fk_chapel_id=$id and package_level<=$levelid
			)fas
			order by columnid"));
			
			
		  return [
		  	'status'=>$levelid,
		  	'message'=>$items
		  ];
			
		} catch (\Exception $e) {
			return [
					'status'=>'error',
					'message'=>$e->getMessage()
			];
		}
		
	}
	
	
	public function resetCharging(Request $request)
	{
		$details = json_decode($request->post()['payment_details']);
	
		try {
			$isExists = DB::update('update _fis_sc_charging
							set total_amount = total_amount - balance,
							balance = 0
							where fk_scID =? and total_amount <> balance', [$details]);
			
		
				return [
						'status'=>'saved',
						'message'=>''
				];
			
			
		} catch (\Exception $e) {
			return [
					'status'=>'unsaved',
					'message'=> $e->getMessage()
			];
		}
		
		
	}
	
	
	public function removeCharging(Request $request)
	{
		$details = (array)json_decode($request->post()['payment_details']);
		
		try {
			
			$isPaid = FisSCPayments::where(['contract_id'=>$details['contract_id'], 'accountType'=>$details['account_type'], 'isCancelled'=>0, ['AR_Credit','>', 0]])->count();
			
			if($isPaid>=1)
			{
				return [
						'status'=>'unsaved',
						'message'=>'Account Charging already has a payment made.'
				];
			}
			
			else
			{
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
			}
		} catch (\Exception $e) {
			
			return [
					'status'=>'unsaved',
					'message'=> $e->getMessage()
			];
		}
	}
	
	
	public function getMemberBranch(Request $request)
	{
		try {
			$customerid = $request->post()['customer_id'];
			
			switch (substr($customerid, 0, 3)){
				case '001':
					$dbranch = DB::connection('main');
					break;
				case '002':
					$dbranch = DB::connection('nabun');
					break;
				case '003':
					$dbranch = DB::connection('carmen');
					break;
				case '004':
					$dbranch = DB::connection('bajada');
					break;
				case '005':
					$dbranch = DB::connection('matina');
					break;
				case '006':
					$dbranch = DB::connection('mintal');
					break;
				case '007':
					$dbranch = DB::connection('panabo');
					break;
				case '008':
					$dbranch = DB::connection('market');
					break;
				
				default:
					$dbranch = DB::connection('main');
			}
			
			
			
			
			$accounts = $dbranch->select("select top 1 accountnumber, scproductname, accountstatus, openingdate, openedby, balance  from shareaccount sa
													inner join shareproduct sp on sa.fkscproductidaccount = sp.SCProductID
													where fkcustomeridaccount='".$customerid."' and accountstatus='Active'
													union all
													select accountnumber, saproductname, accountstatus, openingdate, openedby, balance  from savingsaccount sa
													inner join savingsproduct sp on sa.fksaproductidaccount = sp.saproductid
													where fkcustomeridaccount='".$customerid."' and accountstatus='Active'");
			
			
			$loans = $dbranch->select("select accountnumber, loanproductname, principal, principalbalance, disburseddate, numberofterm, maturitydate, 0 as del_age from loanaccount la
													inner join loanproduct lp on la.fkloanproductidacct = lp.loanproductid
													where fkcustomeridloan='".$customerid."' and left(accountstatus,3) in ('CUR', 'PAS')");
			
			$insurances = $dbranch->select("Execute _CACInfo '".$customerid."', '".date('m/d/Y')."'");
			$dependents = $dbranch->select("select (deplastname + ', ' + depfirstname + ' ' + depmiddlename)fullname, relationship, case gender
													when '0' then 'MALE'
													when '1' then 'FEMALE'
													else '-' end as gender, birthdate, IsBeneficiary from _clientdependents where fkcustomeriddep='".$customerid."'");
			return [
					'status'=>'success',
					'message'=>[
							'accounts'=>$accounts,
							'loans'=>$loans,
							'insurances'=>$insurances,
							'dependents'=>$dependents
					]
			];
			
		} catch (\Exception $e) {
			return [
					'status'=>'error',
					'message'=> $e->getMessage()
			];
		}
		
	}
	
	
	public function transferItemDetails(Request $request)
	{
		try {
			$value = [];
			$fromBranch = $request->post()['branch'];
			
			$branches = FisBranch::select('branchID as value', 'name as label')->where('branchID', '<>', $fromBranch)->get();
			
			$items = DB::select(DB::raw("select item_code as value, item_name as label from _fis_items where isActive=1"));
			
			return [
					'status'=>'success',
					'message'=> [
							'branches'=>$branches,
							'items'=>$items
					]
			];
			
			
		} catch (\Exception $e) {
			
			return [
					'status'=>'error',
					'message'=>$e->getMessage()
			];
		}
		
	}
	
	public function processTransfer(Request $request)
	{
		$value = (array)json_decode($request->post()['transfer_details']);
		
		try {
			DB::beginTransaction();
			$dbDestinationBranch = DB::connection('sqlsrv');
			$dbDestinationBranch->beginTransaction();
			
			$valarr = array_count_values(array_column($value['itemList'], 'id'));
			$equivalence = array_sum($valarr) / count($valarr);
			
			if($equivalence!=1)
				return [
						'status' => 'unsaved',
						'message' => 'Serial No. repitition found. Make sure we do not repeat serial no.',
				];
		    
			
			foreach ($value['itemList'] as $row)
			{
				/*
				 * 1. out sa source branch
				 * 2. entry sa source branch
				 * 3. in sa destination branch
				 * 4. entry sa destination branch
				 */
				 
				try {
					$productList = FisProductList::where([
							'id'=>$row->id,
							'isEncumbered'=>1,
					])->firstOrFail();
					
					
					
					if(!$productList)
						return [
								'status'=>'unsaved',
								'message'=>'No item available for '.$row->item_name.'.'
						];
						
						
					/*	FisItemTransfer::create([
								'transferFrom'=>$value['']
						]); */
						
						//for remaining balance
						$forInventoryCount = FisProductList::where([
								'fk_item_id'=>$row->item_code,
								'isEncumbered'=>1,
								'branch'=>$value['transferFrom']
						])->count();
						
						
					
						FisItemInventory::create(
								[
										'transaction_date'=>date('Y-m-d'),
										'particulars'=>'Transferred to '.$value['transferTo'],
										'contract_id'=>'-',
										'dr_no'=>'-',
										'rr_no'=>'-',
										'process'=>'TRAN-OUT',
										'remaining_balance'=>$forInventoryCount - 1,
										'product_id'=>$row->item_code,
										'quantity'=>1,
										'item_price'=>$row->sell_price,
										'remarks'=>'-',
										'serialNo'=>'-',
										'p_sequence'=>$row->id,
										'fk_sales_id'=>0,
										'fk_ORNo'=>'',
										'transactedBy'=>$value['transactedBy']
								]);

						$productList->update([
								'isEncumbered'=>0
						]);
						
						if(strlen($row->SLCode)>1)
						{
							$acctgHeader = [];
							$acctgHeader['branch_code'] = $value['transferFrom'];
							$acctgHeader['transaction_date'] = date('Y-m-d');
							$acctgHeader['transaction_code'] = "JNLVOUCHER";
							$acctgHeader['username'] = $value['transactedBy'];
							$acctgHeader['reference'] = "TRAN-OUT-".$row->id;
							$acctgHeader['status'] = 1;
							$acctgHeader['particulars'] = "Funecare Item Transfer #".$row->id;
							$acctgHeader['customer'] = "";
							$acctgHeader['checkno'] = "";
							
							$currentBranch = FisBranch::where([
									'branchID'=>$value['transferFrom']
							])->firstOrFail();
							
							$acctgDetails = [];
							$pushDetails = [];
							
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= $currentBranch->borrowHO;
							$pushDetails['amount']= $row->sell_price;
							$pushDetails['detail_particulars']="To record transfer of ".$row->item_name." from ".$value['transferFrom']." to ".$value['transferTo'];
							array_push($acctgDetails, $pushDetails);
							
							$pushDetails['entry_type']="CR";
							$pushDetails['SLCode']= $row->SLCode;
							$pushDetails['amount']= $row->sell_price;
							$pushDetails['detail_particulars']="To record transfer of ".$row->item_name." from ".$value['transferFrom']." to ".$value['transferTo'];
							array_push($acctgDetails, $pushDetails);
							
							
							$saveAccounting = AccountingHelper::processAccounting($acctgHeader, $acctgDetails);
							
							if(!$saveAccounting['status']=='saved')
							{
								DB::rollback();
								return $saveAccounting;
							}
						}
						
						
						
						/*
						 * part 2
						 */

						$productListSource = new FisProductList;
						$productListSource->setConnection('sqlsrv');
						
						$productNew = $productListSource::create([
								'fk_item_id' => $row->item_code,
								'batch_no' =>$productList->batch_no,
								'serialNo'	=> $row->serialno,
								'branch'=> $value['transferTo'],
								'rr_no'	=> $productList->rr_no,
								'dr_no'	=> $productList->dr_no,
								'isEncumbered'	=> 1,
								'price' => $row->sell_price,
								'date_entry' => date('Y-m-d'),
								'transactedBy' => $value['transactedBy']
						]);	
						
						$inventoryitem = new FisItemInventory;
						$inventoryitem->setConnection('sqlsrv');
						
						
						//for remaining balance
						$forInventoryCount = FisProductList::where([
								'fk_item_id'=>$row->item_code,
								'isEncumbered'=>1,
								'branch'=>$value['transferTo']
						])->count();
						
						
						$newInventory = $inventoryitem::create(
								[
										'transaction_date'=>date('Y-m-d'),
										'particulars'=>'Transferred from '.$value['transferFrom'],
										'contract_id'=>'-',
										'dr_no'=>'-',
										'rr_no'=>'-',
										'process'=>'TRAN-IN',
										'remaining_balance'=>$forInventoryCount,
										'product_id'=>$row->item_code,
										'quantity'=>1,
										'item_price'=>$row->sell_price,
										'remarks'=>'-',
										'serialNo'=>'-',
										'p_sequence'=>$productNew->id,
										'fk_sales_id'=>0,
										'fk_ORNo'=>'',
										'transactedBy'=>$value['transactedBy']
								]);
						
						
						if(strlen($row->SLCode)>1)
						{
							$acctgHeader = [];
							$acctgHeader['branch_code'] = $value['transferTo'];
							$acctgHeader['transaction_date'] = date('Y-m-d');
							$acctgHeader['transaction_code'] = "JNLVOUCHER";
							$acctgHeader['username'] = $value['transactedBy'];
							$acctgHeader['reference'] = "TRAN-IN-".$row->id;
							$acctgHeader['status'] = 1;
							$acctgHeader['particulars'] = "Funecare Item Transfer #".$row->id;
							$acctgHeader['customer'] = "";
							$acctgHeader['checkno'] = "";
							
							$destinationBranch = FisBranch::where([
									'branchID'=>$value['transferTo']
							])->firstOrFail();
							
							$acctgDetails = [];
							$pushDetails = [];
							
							$pushDetails['entry_type']="CR";
							$pushDetails['SLCode']= $destinationBranch->borrowHO;
							$pushDetails['amount']= $row->sell_price;
							$pushDetails['detail_particulars']="To record transfer of ".$row->item_name." from ".$value['transferFrom']." to ".$value['transferTo'];
							array_push($acctgDetails, $pushDetails);
							
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= $row->SLCode;
							$pushDetails['amount']= $row->sell_price;
							$pushDetails['detail_particulars']="To record transfer of ".$row->item_name." from ".$value['transferFrom']." to ".$value['transferTo'];
							array_push($acctgDetails, $pushDetails);
							
							
							$saveAccounting = AccountingHelper::processAccounting($acctgHeader, $acctgDetails);
							
							if(!$saveAccounting['status']=='saved')
							{
								DB::rollback();
								return $saveAccounting;
							}
						}

				}
				catch(\Exception $e)
				{
					DB::rollback();
					$dbDestinationBranch->rollBack();
					
					return [
							'status'=>'unsaved',
							'message'=>$e->getMessage()
					];
					break;
				}

			}
			
			DB::commit();
			$dbDestinationBranch->commit();
			
			return [
					'status'=>'saved',
					'message'=>'Successfully Transferred Items.'
					];
			
			
		} catch (\Exception $e) {
			DB::rollback();
			$dbDestinationBranch->rollBack();
			
			return [
					'status'=>'unsaved',
					'message'=> $e->getMessage()
			];
		}
		
	}
	
	
	
	public function processDeduction(Request $request)
	{
		$value = (array)json_decode($request->post()['transfer_details']);
		
		try {
			DB::beginTransaction();
			$dbDestinationBranch = DB::connection('sqlsrv');
			$dbDestinationBranch->beginTransaction();
			
			$valarr = array_count_values(array_column($value['itemList'], 'id'));
			$equivalence = array_sum($valarr) / count($valarr);
			
			if($equivalence!=1)
				return [
						'status' => 'unsaved',
						'message' => 'Serial No. repitition found. Make sure we do not repeat serial no.',
				];
				
				
				foreach ($value['itemList'] as $row)
				{
					/*
					 * 1. out sa source branch
					 * 2. entry sa source branch
					 * 3. in sa destination branch
					 * 4. entry sa destination branch
					 */
					
					try {
						$productList = FisProductList::where([
								'id'=>$row->id,
								'isEncumbered'=>1,
						])->firstOrFail();
						
						
						
						if(!$productList)
							return [
									'status'=>'unsaved',
									'message'=>'No item available for '.$row->item_name.'.'
							];
							
							
							/*	FisItemTransfer::create([
							 'transferFrom'=>$value['']
							 ]); */
							
							$forInventoryCount = FisProductList::where([
									'fk_item_id'=>$row->item_code,
									'isEncumbered'=>1,
									'branch'=>$value['transferFrom']
							])->count();
							
							
							
							FisItemInventory::create(
									[
											'transaction_date'=>date('Y-m-d'),
											'particulars'=>$value['purpose'],
											'contract_id'=>'-',
											'dr_no'=>'-',
											'rr_no'=>'-',
											'process'=>'OUT',
											'remaining_balance'=>$forInventoryCount - 1,
											'product_id'=>$row->item_code,
											'quantity'=>1,
											'item_price'=>$row->sell_price,
											'remarks'=>'-',
											'serialNo'=>'-',
											'p_sequence'=>$row->id,
											'fk_sales_id'=>0,
											'fk_ORNo'=>'',
											'transactedBy'=>$value['transactedBy']
									]);
							
							$productList->update([
									'isEncumbered'=>0
							]);
							
							if(strlen($row->SLCode)>1)
							{
								$acctgHeader = [];
								$acctgHeader['branch_code'] = $value['transferFrom'];
								$acctgHeader['transaction_date'] = date('Y-m-d');
								$acctgHeader['transaction_code'] = "JNLVOUCHER";
								$acctgHeader['username'] = $value['transactedBy'];
								$acctgHeader['reference'] = "OUT-".$row->id;
								$acctgHeader['status'] = 1;
								$acctgHeader['particulars'] = $value['purpose'];
								$acctgHeader['customer'] = "";
								$acctgHeader['checkno'] = "";
								
								$currentBranch = FisBranch::where([
										'branchID'=>$value['transferFrom']
								])->firstOrFail();
								
								$acctgDetails = [];
								$pushDetails = [];
								
								$pushDetails['entry_type']="DR";
								$pushDetails['SLCode']= $row->SLCode;
								$pushDetails['amount']= $row->sell_price;
								$pushDetails['detail_particulars']="To record deduction of ".$row->item_name." from ".$value['transferFrom'];
								array_push($acctgDetails, $pushDetails);
								
								$pushDetails['entry_type']="CR";
								$pushDetails['SLCode']= $currentBranch->borrowHO;
								$pushDetails['amount']= $row->sell_price;
								$pushDetails['detail_particulars']="To record deduction of ".$row->item_name." from ".$value['transferFrom'];
								array_push($acctgDetails, $pushDetails);
								
								
								$saveAccounting = AccountingHelper::processAccounting($acctgHeader, $acctgDetails);
								
								if(!$saveAccounting['status']=='saved')
								{
									DB::rollback();
									return $saveAccounting;
								}
							}

					}
					catch(\Exception $e)
					{
						DB::rollback();
						$dbDestinationBranch->rollBack();
						
						return [
								'status'=>'unsaved',
								'message'=>$e->getMessage()
						];
						break;
					}
					
				}
				
				DB::commit();
				$dbDestinationBranch->commit();
				
				return [
						'status'=>'saved',
						'message'=>'Successfully Deducted Items.'
				];
				
				
		} catch (\Exception $e) {
			DB::rollback();
			$dbDestinationBranch->rollBack();
			
			return [
					'status'=>'unsaved',
					'message'=> $e->getMessage()
			];
		}
		
	}
	
	
	public function getTheItems(Request $request)
	{
		try {
			$item_code = $request->post()['fk_item_code'];
			$branch = $request->post()['branch'];
			$quantity = $request->post()['quantity'];
			$itemSelection = [];
			$itemPresentation = [];
			
			$itemSelection = DB::select(DB::raw("SELECT fk_item_id, id as value, serialno as label, price as sublabel from
						_fis_productlist where isEncumbered=1 and branch='".$branch."'
						and fk_item_id='".$item_code."'"));
			
		
			
			
			$itemPresentation = DB::select(DB::raw("select top ".$quantity." item_code, item_name, pl.id, serialno, pl.price as sell_price, SLCode from _fis_productlist pl
					inner join _fis_items i on pl.fk_item_id = i.item_code
					where isEncumbered=1 and branch='".$branch."'and fk_item_id='".$item_code."'
					order by id"));
			
			if(count($itemPresentation)!=$quantity)
				return [
						'status'=>'error',
						'message'=>'Insufficient count for selected item. Only '.count($itemPresentation).' left.'
				];
				
		
			return [
					'status'=>'success',
					'selection' => $itemSelection,
					'presentation' => $itemPresentation,
					
			];
			
			
		} catch (\Exception $e) {
			return [
					'status'=>'error',
					'message'=>$e->getMessage()
			];
		}
		
	}
	
	public function unpostSales(Request $request)
	{
		try {
			$value = [];
			
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
			
			
			$salesDetails = DB::select(DB::raw("select total_amount, balance, status, isPosted, fun_branch, '-' as sc_deceased, id, OR_no, client, has_tax, tax_deferred from _fis_itemsales_header
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
			
			
			$value['item_inventory'] = DB::select(DB::raw("select SLCode, p_sequence as id, item_code, item_name, inventory.item_price , inventory.serialNo, expense_SLCode from _fis_item_inventory inventory
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
			$total_taxed_items = 0;
			
			foreach($value['item_inclusions'] as $row)
			{
				
				try {
					
					$itemSales = FisItemSales::find($row->sales_id);
					$itemSales->update([
							'isCancelled'=>1
					]);
					
					//start of accounting cancellation
					
					if($salesDetails[0]->has_tax==1 || $salesDetails[0]->has_tax=='1')
					{
						$income_deducted = number_format((float)$row->tot_price / 1.12, 2, '.', '');
						$output_tax = number_format($row->tot_price - $income_deducted, 2, '.', '');
						
						
						
						$total_taxed_items += $output_tax;
						
						while( (float)$total_taxed_items > (float)$salesDetails[0]->tax_deferred)
						{
							$new_output_tax = $output_tax - ((float)$total_taxed_items - (float)$salesDetails[0]->tax_deferred);
							$total_taxed_items -= $output_tax;
							$output_tax = $new_output_tax;
							$total_taxed_items += $output_tax;
							
							$income_deducted = $row->tot_price - $output_tax;
						}
						
						$pushDetails['entry_type']="DR";
						$pushDetails['SLCode']= "4-1-410-03-001"; //--charge everything to miscellaneous income
						$pushDetails['amount']= $income_deducted;
						$pushDetails['detail_particulars']="Reversal of Merchandise";
						
						array_push($acctgDetails, $pushDetails);
						
						
						$pushDetails['entry_type']="DR";
						$pushDetails['SLCode']= "7-4-000-01"; //--deferred output tax
						$pushDetails['amount']= $output_tax;
						$pushDetails['detail_particulars']="Reversal of Merchandise";
						
						
						
						
						
						array_push($acctgDetails, $pushDetails);
						
						
					}
					//end of accounting cancellation
					
					else {
						$pushDetails['entry_type']="DR";
						$pushDetails['SLCode']= "4-1-410-03-001";
						$pushDetails['amount']= $row->tot_price;
						$pushDetails['detail_particulars']="Income ".$row->item_name." from Merch. Purchase #".$value['OR_no']." Signee: ".$value['client'];
						
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
			
			
			foreach($value['item_inventory'] as $row)
			{
				try {
					
					$productList = FisProductList::where([
							'id'=>$row->id,
							//'isEncumbered'=>1,
					])->firstOrFail();
					
					
					//for remaining balance
					$forInventoryCount = FisProductList::where([
							'fk_item_id'=>$row->item_code,
							'isEncumbered'=>1,
							'branch'=>$value['fun_branch']
					])->count();
					
					
					FisItemInventory::create(
							[
									'transaction_date'=>date('Y-m-d'),
									'particulars'=>'Cancelled Purchase #'.$value['OR_no'],
									'contract_id'=>0,
									'dr_no'=>'-',
									'rr_no'=>'-',
									'process'=>'CAN-IN',
									'remaining_balance'=> $forInventoryCount + 1,
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
						$pushDetails['entry_type']="DR";
						$pushDetails['SLCode']= $row->SLCode;
						$pushDetails['amount']= $productList->price;
						$pushDetails['detail_particulars']="To record Inventory of ".$row->item_name." from Merch. Purchase #".$value['OR_no']." Signee Name : ".$value['client'];
						array_push($acctgDetails, $pushDetails);
						
						$debitSL = $row->expense_SLCode == "BRANCH" ? $currentBranch->borrowHO : $row->expense_SLCode;
						
						$pushDetails['entry_type']="CR";
						$pushDetails['SLCode']= $debitSL;
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
					
					//start of cancellation
					if($salesDetails[0]->has_tax==1 || $salesDetails[0]->has_tax=='1')
					{
						$income_deducted = number_format((float)$row->tot_price / 1.12, 2, '.', '');
						$output_tax = number_format($row->tot_price - $income_deducted, 2, '.', '');
						
						
						$total_taxed_items += $output_tax;
						
						while( (float)$total_taxed_items > (float)$salesDetails[0]->tax_deferred)
						{
							$new_output_tax = $output_tax - ((float)$total_taxed_items - (float)$salesDetails[0]->tax_deferred);
							$total_taxed_items -= $output_tax;
							$output_tax = $new_output_tax;
							$total_taxed_items += $output_tax;
							
							$income_deducted = $row->tot_price - $output_tax;
						}
						
						
						if(strpos($row->service_name, "GIFT COUPON")!== false)
						{
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= $currentBranch->borrowHO;
							$pushDetails['amount']= $income_deducted;
							$pushDetails['detail_particulars']="Cancellation of ".$row->service_name." from Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
						}
						
						else
						{
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= $row->SLCode;
							$pushDetails['amount']= $income_deducted;
							$pushDetails['detail_particulars']="Cancellation of ".$row->service_name." from Merch. Ref #".$value['OR_no']." Signee: ".$value['client'];
						}
						
						array_push($acctgDetails, $pushDetails);
						
						$pushDetails['entry_type']="DR";
						$pushDetails['SLCode']= "7-4-000-01"; //--deferred output tax
						$pushDetails['amount']= $output_tax;
						$pushDetails['detail_particulars']="deferred output tax";
						
						array_push($acctgDetails, $pushDetails);
						
						
					}
					//end of cancellation
					
					else
					{
						if(strpos($row->service_name, "GIFT COUPON")!== false)
						{
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= $currentBranch->borrowHO;
							$pushDetails['amount']= $row->tot_price;
							$pushDetails['detail_particulars']="Cancellation of ".$row->service_name." from Merch. Ref#".$salesHead->OR_no." Client: ".$salesHead->client;
						}
						
						else
						{
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= $row->SLCode;
							$pushDetails['amount']= $row->tot_price;
							$pushDetails['detail_particulars']="Cancellation of ".$row->service_name." from Merch. Ref #".$value['OR_no']." Signee: ".$value['client'];
						}
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
			
			
			
			$saveAccounting =  AccountingHelper::processAccounting($acctgHeader, $acctgDetails);
			
			if($saveAccounting['status']=='saved')
			{

				$pendingUpdate = FisCancelMerchandise::find($value_api['merchandise_id']);
				$pendingUpdate->update(
						[
						'status'=>'APPROVED',
						'approvedBy'=>$value_api['username'],
						'dateApproved'=> date('Y-m-d')
						]);

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
						'transactedBy'=>$paydetails['username'],
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
				$acctgHeader_pay['username'] = $paydetails['username'];
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
			$remainingtaxbalance = $contract->tax_deferred_balance + $payment->tax_contribution;
			
			$contract->update([
					'contract_balance'=> $remainingbalance,
					'status'=> 'ACTIVE',
					'tax_deferred_balance' => $remainingtaxbalance
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
					'transactedBy'=>$paydetails['username'],
					'isCancelled'=>0,
					'isRemitted'=>0,
					'remittedTo'=>'',
					'isPosted'=>1,
					'remarks'=>'Cancellation of',
					'tran_type'=>$payment->tran_type == 'PAYPARTIAL' ? 'CANPAYPARTIAL' : 'CANPAYCLOSE',
					'tax_contribution'=>0,
					'tax_balance'=> $remainingtaxbalance
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
			$acctgHeader_pay['username'] = $paydetails['username'];
			$acctgHeader_pay['reference'] = "CSCPay-".$payment->reference_no;
			$acctgHeader_pay['status'] = $paytype->trantype;
			$acctgHeader_pay['particulars'] = "Posting of Cancellation of SC Payment w/ SC #".$contract->contract_no;
			$acctgHeader_pay['customer'] = "";
			$acctgHeader_pay['checkno'] = "";
			
			$currentBranch = FisBranch::where([
					'branchID'=>$contract->fun_branch
			])->firstOrFail();
			
			
			$creditSL = $paytype->sl_debit== "BRANCH" ? $currentBranch->borrowCode : $paytype->sl_debit;
			
			if($paytype->sl_debit== "BRANCH-HO")
				$creditSL = $currentBranch->borrowHO;
			
			
			
			$pushDetails_pay['entry_type']="CR";
			$pushDetails_pay['SLCode']=$creditSL;
			$pushDetails_pay['amount']=$payment->AR_Credit;
			$pushDetails_pay['detail_particulars']="To record cancellation payment from SC Ref#".$payment->reference_no;
			array_push($acctgDetails_pay, $pushDetails_pay);
			
			$pushDetails_pay['entry_type']="DR";
			$pushDetails_pay['SLCode']=$paytype->sl_credit;
			$pushDetails_pay['amount']=$payment->AR_Credit;
			$pushDetails_pay['detail_particulars']="To record cancellation payment from SC Ref#".$payment->reference_no;
			array_push($acctgDetails_pay, $pushDetails_pay);
			
			//start of tax acctg
			$pushDetails_pay['entry_type']="CR";
			$pushDetails_pay['SLCode']='2-1-316-08-003';
			$pushDetails_pay['amount']=$payment->tax_contribution;
			$pushDetails_pay['detail_particulars']="To record cancellation payment from SC Ref#".$payment->reference_no;
			array_push($acctgDetails_pay, $pushDetails_pay);
			
			$pushDetails_pay['entry_type']="DR";
			$pushDetails_pay['SLCode']='2-1-317-01-001';
			$pushDetails_pay['amount']=$payment->tax_contribution;
			$pushDetails_pay['detail_particulars']="To record cancellation payment from SC Ref#".$payment->reference_no;
			array_push($acctgDetails_pay, $pushDetails_pay);
			//end of tax acctg
			
			
			
			
			
			
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
			
			$contractDetails = DB::select(DB::raw("select contract_amount, contract_balance, grossPrice, sc.status, sc.isPosted, fun_branch, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)sc_deceased, (discount + chapel_discount) as sc_discount, contract_id as sc_id, contract_no as sc_number,
					(s.lastname + ', ' + s.firstname + ' ' + s.middlename)sc_signee, date_posted, has_tax, tax_deferred
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
								'contract_no'=>$value_api['contract_no'].'-CNCLD',
								'isPosted'=>2
						]
						);
				DB::commit();
				return [
						'status'=>'saved',
						'message'=>'Unposted Contract Successfully Cancelled.'
				];
				
			}
			/*if(date('Y-m-d')." 00:00:00.000"!=$contractDetails[0]->date_posted)
			{
				return [
						'status'=>'unsaved',
						'message'=>'Invalid date'
				];
			}*/
			
			
			
			
		 	$value['item_inventory'] = DB::select(DB::raw("select SLCode, p_sequence as id, item_code, item_name, inventory.item_price , inventory.serialNo, expense_SLCode from _fis_item_inventory inventory
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
			$acctgHeader['branch_code'] = $value['sc_branch'];
			$acctgHeader['transaction_date'] = date('Y-m-d');
			$acctgHeader['transaction_code'] = "JNLVOUCHER";
			$acctgHeader['username'] = $value_api['username'];
			$acctgHeader['reference'] = "CSC".$value['sc_number'];
			$acctgHeader['status'] = 1;
			$acctgHeader['particulars'] = "Cancellation of SC #".$value['sc_number'];
			$acctgHeader['customer'] = $value['sc_signee'];
			$acctgHeader['checkno'] = "";
			
			
			$currentBranch = FisBranch::where([
					'branchID'=>$value['sc_branch']
			])->firstOrFail();
			
			
			$acctgDetails = [];
			
			$pushDetails = [];
			
				
				
				
				$sc = ServiceContract::find($value['sc_id']);
				$sc->update(
						[
								'status'=>'CANCELLED',
								'contract_no'=>$value_api['contract_no'].'-CNCLD',
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
				
				$total_taxed_items = 0 - $contract_discount;
				$hasDiscountConsidered = false;
				foreach($value['item_inclusions'] as $row)
				{
					
					try {

						$itemSales = FisItemSales::find($row->sales_id);
						$itemSales->update([
								'isCancelled'=>1
						]);
						
						//start of tax accounting
						if($contractDetails[0]->has_tax==1)
						{
							//if casket, deduct the discount to the sales
							if(!$hasDiscountConsidered && substr($row->item_code, 0, 2)=='01')
							{
								$income_deducted = number_format(((float)$row->tot_price - $contract_discount) / 1.12, 2, '.', '');
								$output_tax = number_format(($row->tot_price - $contract_discount) - $income_deducted, 2, '.', '');
								
								$income_deducted = $row->tot_price - $output_tax;
								$hasDiscountConsidered = true;
								
							}
							
							else
							{$income_deducted = number_format((float)$row->tot_price / 1.12, 2, '.', '');
							
							$output_tax = number_format($row->tot_price - $income_deducted, 2, '.', '');
							}
							
							$total_taxed_items += $output_tax;
							
							while( (float)$total_taxed_items > (float)$contractDetails[0]->tax_deferred)
							{
								$new_output_tax = $output_tax - ((float)$total_taxed_items - (float)$contractDetails[0]->tax_deferred);
								$total_taxed_items -= $output_tax;
								$output_tax = $new_output_tax;
								$total_taxed_items += $output_tax;
								
								$income_deducted = $row->tot_price - $output_tax;
							}
							
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= $row->income_SLCode;
							$pushDetails['amount']= $income_deducted;
							$pushDetails['detail_particulars']="Reversal of Income (VAT)";
							
							array_push($acctgDetails, $pushDetails);
							
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= '2-1-316-08-003'; //-->output tax
							$pushDetails['amount']= $output_tax;
							$pushDetails['detail_particulars']="Reversal of Income (VAT)";
							
							array_push($acctgDetails, $pushDetails);
						}
						
						else
						{
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= $row->income_SLCode;
							$pushDetails['amount']= $row->tot_price;
							$pushDetails['detail_particulars']="Income ".$row->item_name." from SC No.".$value['sc_number']." Signee: ".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
							
							array_push($acctgDetails, $pushDetails);
						}
						//end of tax accounting
						
						
						
						
						
						
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
						
						//for remaining balance
						$forInventoryCount = FisProductList::where([
								'fk_item_id'=>$row->item_code,
								'isEncumbered'=>1,
								'branch'=>$value['sc_branch']
						])->count();
						
						
						FisItemInventory::create(
								[
										'transaction_date'=>date('Y-m-d'),
										'particulars'=>'Cancelled SC. #'.$sc->contract_no,
										'contract_id'=>$sc->contract_id,
										'dr_no'=>'-',
										'rr_no'=>'-',
										'process'=>'CAN-IN',
										'remaining_balance'=> $forInventoryCount + 1,
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
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= $row->SLCode;
							$pushDetails['amount']= $productList->price;
							$pushDetails['detail_particulars']="Inv. of ".$row->item_name." from SC No.".$value['sc_number']."-".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
							array_push($acctgDetails, $pushDetails);
							
							$debitSL = $row->expense_SLCode == "BRANCH" ? $currentBranch->borrowHO : $row->expense_SLCode;
							
							$pushDetails['entry_type']="CR";
							$pushDetails['SLCode']= $debitSL;
							$pushDetails['amount']= $productList->price;
							$pushDetails['detail_particulars']="Inv. of ".$row->item_name." from SC No.".$value['sc_number']."-".$value['sc_signee']."  for the Late : ".$value['sc_deceased'];
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
						
						//start of tax accounting
						if($contractDetails[0]->has_tax==1)
						{
							
							//if chapel, deduct the discount to the sales
							//if chapel, deduct the discount to the sales
							if(!$hasDiscountConsidered && stripos($row->service_name, 'CHAPEL')!==false)
							{
								$income_deducted = number_format(((float)$row->tot_price - $contract_discount) / 1.12, 2, '.', '');
								$output_tax = number_format(($row->tot_price - $contract_discount) - $income_deducted, 2, '.', '');
								
								$income_deducted = $row->tot_price - $output_tax;
								$hasDiscountConsidered = true;
								
							}
							
							else
							{ $income_deducted = number_format((float)$row->tot_price / 1.12, 2, '.', '');
							
								$output_tax = number_format($row->tot_price - $income_deducted, 2, '.', '');
							}
							
							$income_deducted = number_format((float)$row->tot_price / 1.12, 2, '.', '');
							$output_tax = number_format($row->tot_price - $income_deducted, 2, '.', '');
							
							$total_taxed_items += $output_tax;
							
							while( (float)$total_taxed_items > (float)$contractDetails[0]->tax_deferred)
							{
								$new_output_tax = $output_tax - ((float)$total_taxed_items - (float)$contractDetails[0]->tax_deferred);
								$total_taxed_items -= $output_tax;
								$output_tax = $new_output_tax;
								$total_taxed_items += $output_tax;
								
								$income_deducted = $row->tot_price - $output_tax;
							}
							
							
							if(strpos($row->service_name, "GIFT COUPON") !== false)
							{
								$pushDetails['entry_type']="DR";
								$pushDetails['SLCode']= $currentBranch->borrowHO;
								$pushDetails['amount']= $income_deducted;
								$pushDetails['detail_particulars']="New entry";
								
							}
							
							else
							{
								$pushDetails['entry_type']="DR";
								$pushDetails['SLCode']= $row->SLCode;
								$pushDetails['amount']= $income_deducted;
								$pushDetails['detail_particulars']="New entry";
								
							}
							
							array_push($acctgDetails, $pushDetails);
							
							$pushDetails['entry_type']="DR";
							$pushDetails['SLCode']= '2-1-316-08-003'; //-->output tax
							$pushDetails['amount']= $output_tax;
							$pushDetails['detail_particulars']="New entry";
							
							array_push($acctgDetails, $pushDetails);
						}
						
						else
						{
							if(strpos($row->service_name, "GIFT COUPON")!== false)
							{
								$pushDetails['entry_type']="DR";
								$pushDetails['SLCode']= $currentBranch->borrowHO;
								$pushDetails['amount']= $row->tot_price;
								$pushDetails['detail_particulars']="Cancellation of ".$row->service_name." from SC #".$value['sc_number']."-".$value['sc_signee']." for the Late: ".$value['sc_deceased'];
							}
							
							else
							{
								$pushDetails['entry_type']="DR";
								$pushDetails['SLCode']= $row->SLCode;
								$pushDetails['amount']= $row->tot_price;
								$pushDetails['detail_particulars']="Cancellation of ".$row->service_name." from SC #".$value['sc_number']."-".$value['sc_signee']." for the Late: ".$value['sc_deceased'];
							}
							array_push($acctgDetails, $pushDetails);
						}
						//end of tax accounting

						
						
						
						
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

					$pendingUpdate = FisPendingContract::find($value_api['contract_id']);
					$pendingUpdate->update(
						[
								'status'=>'APPROVED',
								'approvedBy'=>$value_api['username'],
								'dateApproved'=> date('Y-m-d'),
								'contract_no'=>$value_api['contract_no'].'-CNCLD',
						]
						);
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
		$client =  (array)json_decode($request->post()['client']);
		
		$clientid = $client['client'];
		$branch = $client['branch'];
		try {
			/*$signee = FisMemberData::find($clientid);*/
			$signee = DB::select(DB::raw("
			SELECT PH.id, PH.customer_id, PH.firstname, PH.middlename, PH.lastname,
			PH.contact_no, PH.address, PH.is_member, PL.profile_type, CONVERT(VARCHAR(30),PH.date_entry,101) AS date_entry from _fis_ProfileHeader AS PH
			INNER JOIN _fis_ProfileLogs as PL ON PL.fk_profile_id = PH.id
			WHERE PL.fk_profile_id = '".$client['client']."' and PL.profile_type= '".$client['profile_type']."'
				"));
			
			foreach ($signee as $row) {
				if($row->profile_type=='Signee' || $row->profile_type=='Walk-in')
				{
					
					/*$contracts = DB::select(DB::raw("
						select contract_id, contract_no, CONVERT(VARCHAR(30),contract_date,101) AS contract_date, contract_amount, contract_balance, status, deceased_id 
					from _fis_service_contract where signee=$clientid and fun_branch='".$branch."'
					"));*/

					$contracts = DB::select(DB::raw("
						SELECT (''+PH.firstname+' '+PH.middlename+' '+PH.lastname) AS deceased_fullname, SC.contract_id, SC.contract_no, CONVERT(VARCHAR(30),SC.contract_date,101) AS contract_date, 
						SC.contract_amount, SC.contract_balance, SC.status, SC.deceased_id 
						FROM _fis_service_contract AS SC
						INNER JOIN _fis_ProfileHeader AS PH ON sc.deceased_id = ph.id
						WHERE SC.signee= $clientid AND SC.fun_branch='".$branch."'
					"));

					$merchandises = DB::select(DB::raw("SELECT id, OR_no as reference_no, date as posting_date, 
						CONVERT(VARCHAR(30),total_amount,0) as amount, 
						CONVERT(VARCHAR(30),balance,0) as balance, status 
						FROM _fis_itemsales_header  WHERE signee_id = $clientid and fun_branch='".$branch."'"));
					
					return [
							'status'=>'success',
							'message'=> [
									'signee' => $row,
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
			
			$isExists = DB::update('update _fis_sc_charging set total_amount = total_amount + '.$value['total_amount'].', balance = balance + '.$value['total_amount'].'
									where fk_scID = ? and accountType = ?', [$value['fk_scID'], $value['accountType']]);
			
			
			
			if($isExists>=1)
			{
				return [
						'status'=>'savedupdate',
						'message'=>$isExists
				];
				
			}
			
			else
			{
				$value['balance'] = $value['total_amount'];
				$value['date'] = date('Y-m-d H:i:s');
				$charging = FisCharging::create($value);
				
				return [
						'status'=>'saved',
						'message'=>''
				];
			}
			
			
			
		
			
			
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
	
	public function updateContract(Request $request)
	{
		try {
			$value = (array)json_decode($request->post()['servicecontract']);
			$value['burial_time'] = date_format(date_create($value['burial_time']), 'Y-m-d H:i:s');
			$value['date_updated'] = date('Y-m-d');
			//return $value;
			$serviceContract= ServiceContract::find($value['contract_id']); //FisRelation::find($value['rtd_id']);
			$isUpdateSuccessful = $serviceContract->update($value);
			
			if(!$isUpdateSuccessful)
			{
				return [
						'status'=>'unsaved',
						'message'=>'Update failed.'
				];
			}
			/*
			 * Selection of Updated Contract (for return)
			 */
			
			if(($value['status']=='ACTIVE' || $value['status']=='CANCELLED' || $value['status']=='CLOSED') && $value['isPosted']==1)
			{
				$availments = DB::select(DB::raw("select product_id, (CAST(quantity as varchar(5)) + ' ' + unit_type) as totquantity, price, total_price, 'item' as inclusiontype, i.item_name as inclusionname from _fis_item_sales sales
				inner join _fis_items i on sales.product_id = i.item_code
				where contract_id=".$value['contract_id']."
				UNION ALL
				select CAST(fk_service_id as varchar(10)) as id, (CAST(service_duration as varchar(5)) + ' ' + duration_unit) as totquantity, total_amount, total_amount as totprice, 'service' as inclusiontype, s.service_name as inclusionname from _fis_service_sales ss
				inner join _fis_services s on s.id = ss.fk_service_id
				where fk_contract_id=".$value['contract_id']));
				
				$sc_details = DB::select(DB::raw("select sc.contract_id, contract_no, fun_branch, contract_date, 
					(s.firstname + ', ' + s.middlename + ' ' + s.lastname)signee,
					s.address as signeeaddress, sc.remarks, sc.burial_time, sc.discount, sc.grossPrice, sc.contract_amount, sc.contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
					d.birthday, d.address, d.causeOfDeath, sc.mort_viewing, sc.embalming_place, cr.ReligionName,  p.package_code, p.package_name
					from _fis_service_contract sc
					inner join (select ph.* from _fis_profileheader ph inner join _fis_ProfileLogs  pl on ph.id = pl.fk_profile_id where pl.profile_type='Signee')s on sc.signee = s.id
					inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
					inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
					inner join _fis_ProfileLogs pl on ph.id = pl.fk_profile_id
					where pl.profile_type='Decease')d on sc.deceased_id = d.id
					inner join _fis_package p on sc.package_class_id = p.package_code
					inner join ClientReligion cr on d.religion = cr.ReligionID
					where contract_id =".$value['contract_id']));
				
				$sc_transaction = DB::select(DB::raw("select payment_id, account_type, AR_Debit, AR_Credit, balance, tran_type, reference_no, payment_date, payment_mode, transactedBy, remarks, isCancelled from _fis_sc_payments sp inner join _fis_account a
					on a.account_id = sp.accountType
					where contract_id=".$value['contract_id']));

				
				
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
					$user_check = DB::select(DB::raw("SELECT item_code, item_name, quantity, price, discount, (price * quantity) as tot_price, SLCode, income_SLCode FROM
						(
						SELECT item_code, isActive, item_name, isnull(quantity, 0) as quantity, selling_price as price, 0 as discount, 0 as tot_price, SLCode, income_SLCode FROM _fis_items fi
						LEFT JOIN
						(
						SELECT * FROM _fis_package_inclusions
						WHERE fk_package_id='".$value['package_class_id']."'
						AND inclusionType='ITEM'
						)b on fi.item_code = b.item_id where isActive = 1
						)sdf
						where (left(item_code,2)<>'01' or quantity>=1)
						order by item_code asc, quantity
						"));
					
					
					/*$sc_details = DB::select(DB::raw("select sc.contract_id, contract_no, fun_branch, contract_date, (s.firstname + ', ' + s.middlename + ' ' + s.lastname)signee,
					s.address as signeeaddress, sc.remarks, sc.burial_time, sc.discount, sc.grossPrice, sc.contract_amount, sc.contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
					d.birthday, d.address, d.causeOfDeath, sc.mort_viewing, sc.embalming_place, cr.ReligionName, p.package_code, p.package_name
					from _fis_service_contract sc
					inner join (select ph.* from _fis_profileheader ph inner join _fis_ProfileLogs  pl on ph.id = pl.fk_profile_id where pl.profile_type='Signee')s on sc.signee = s.id
					inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
					inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
					inner join _fis_ProfileLogs pl on ph.id = pl.fk_profile_id
					where pl.profile_type='Decease')d on sc.deceased_id = d.id
					inner join _fis_package p on sc.package_class_id = p.package_code
					inner join ClientReligion cr on d.religion = cr.ReligionID
					where contract_id =".$value['contract_id']));*/
					
					$sc_details = DB::select(DB::raw("SELECT sc.contract_id, contract_no, fun_branch,  CONVERT(VARCHAR(30),contract_date,101)contract_date, (s.lastname + ', ' + s.firstname + ' ' + s.middlename)signee,
						s.address as signeeaddress, s.customer_id as signee_cid, d.customer_id as deceased_cid, sc.remarks, CONVERT(VARCHAR(30),sc.burial_time,22)burial_time, sc.discount, sc.grossPrice, sc.contract_amount, sc.contract_balance, (d.lastname + ', ' + d.firstname + ' ' + d.middlename)deceased, dbo._ComputeAge(d.birthday, getdate())deceasedage,
						CONVERT(VARCHAR(30),d.birthday,101)birthday, d.address, d.causeOfDeath, sc.embalming_place, cr.ReligionName, p.package_code, p.package_name, sc.status, sc.signee as signee_id, sc.package_class_id
						from _fis_service_contract sc
						inner join (select ph.* from _fis_profileheader ph
						inner join _fis_ProfileLogs pl on ph.id = pl.fk_profile_id where pl.profile_type='Signee')s on sc.signee = s.id
						inner join (select ph.*, birthday, date_died, causeOfDeath, religion, primary_branch, servicing_branch, deathPlace, relationToSignee from _fis_profileheader ph
						inner join _fis_Deceaseinfo di on ph.id = di.fk_profile_id
						inner join _fis_ProfileLogs pl on ph.id = pl.fk_profile_id
						where pl.profile_type='Decease')d on sc.deceased_id = d.id
						inner join _fis_package p on sc.package_class_id = p.package_code
						inner join ClientReligion cr on d.religion = cr.ReligionID
						where contract_id=".$value['contract_id']));
					
					$services = DB::select(DB::raw("SELECT * FROM
					(
					SELECT fs.id, isActive, service_name, isnull(a.service_price, 0) as amount, 0 as less, isnull(duration, '') as duration, isnull(type_duration, '') as type_duration, isnull(a.service_price, 0) as tot_price, SLCode  FROM _fis_services fs
					LEFT JOIN
					(
					SELECT * FROM _fis_package_inclusions WHERE fk_package_id='".$value['package_class_id']."' and inclusionType='SERV'
					)a on fs.id = a.service_id WHERE fs.isActive=1)sdfa
					ORDER BY duration desc"));
					
					
					$package_selected = DB::select(DB::raw("select * from
						(
						SELECT
						case when item_id = '-' then CAST(service_id as varchar(5))
						else item_id end as columnid,
						isnull(item_name, service_name) as name,
						case when quantity < 1 then duration
						else quantity end as quantity,
						isnull(unit_type, type_duration) as uom,
						service_price as price, total_amount as total_price
						FROM _fis_package_inclusions fpi
						left join _fis_items i on fpi.item_id = i.item_code
						left join _fis_services s on fpi.service_id = s.id
						WHERE fk_package_id='".$value['package_class_id']."'
						)fas
						order by columnid"));
					
					$chapel_rentals = DB::select(DB::raw("select id as value, chapel_name as label from _fis_chapel_package"));
					
					return [
							'status'=>'success_unposted',
							'message'=> [
									'service_contract' => $sc_details,
									'item_inclusions' => $user_check,
									'service_inclusions' => $services,
									'package_selected' => $package_selected,
									'chapel_rentals' => $chapel_rentals
							]
					];
					
				} catch (\Exception $e) {
					return [
							'status'=>'unsaved',
							'message'=>'Update was successful. but we have error '.$e->getMessage()
					];
				}
				
			}
			/*
			 * End of Selection of Updated Contract
			 */
		} catch (\Exception $e) {
			$preMessage = $isUpdateSuccessful ? 'Update was successful. but we have error ' : '';
			return [
					'status'=>'unsaved',
					'message'=>$preMessage.''.$e->getMessage()
			];
		}
		
	}
	
	public function getContractInfo(Request $request)
	{
		try {
			
			$contract = $request->post()['contract_id'];
			
			$sc_details = DB::select(DB::raw("select * from _fis_service_contract
					where contract_id=$contract"));
			
			
			$signee_info = DB::select(DB::raw("SELECT PH.id, (PH.lastname + ', ' + PH.firstname + ' ' + PH.middlename) as name FROM dbo._fis_ProfileHeader AS PH
				LEFT JOIN _fis_ProfileLogs AS PL ON PH.id = PL.fk_profile_Id
				WHERE (PL.profile_type = 'Signee') and PH.id=".$sc_details[0]->signee));
			
			$deceased_info = DB::select(DB::raw("SELECT PH.id, (PH.lastname + ', ' + PH.firstname + ' ' + PH.middlename) as name
				FROM dbo._fis_ProfileHeader AS PH
				LEFT JOIN _fis_ProfileLogs AS PL ON PH.id = PL.fk_profile_Id
				WHERE (PL.profile_type = 'Decease') and PH.id =".$sc_details[0]->deceased_id));
			
			return [
					'status'=>'success',
					'message'=> [
							'sc_details' => $sc_details,
							'signee_info' => $signee_info,
							'deceased_info' => $deceased_info
					]
			];
			
			
		} catch (\Exception $e) {
			return [
					'status'=>'failed',
					'message'=> $e->getMessage()
			];
		}
	}


	public function pendingContract(Request $request) {
		try {
			$value = (array)json_decode($request->post()['servicecontract']);

		
			if ($value['contract_id']!="") {
			$info = FisPendingContract::where(['contract_id'=>$value['contract_id']])->first();

				if($info)
				{
					return [
						'status'=>'unsaved',
						'message'=>'Contract is already waiting for approval.'
					];	
				}
			}

			$data = FisPendingContract::create([
					'contract_id'=>$value['contract_id'],
					'contract_no'=>$value['contract_no'],
					'remarks'=>$value['remarks_input'],
					'status'=>'PENDING',
					'requestBy'=>$value['username'],
					'dateRequest' =>  date('Y-m-d')
			]);
			return [
				'status'=>'saved',
				'message'=>$data
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}


	public function getPendingContract(Request $request) {
		$value = "";
		try {
		$info = DB::select(DB::raw("
			SELECT * FROM _fis_service_contract_status
			ORDER BY id DESC
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

	public function getPendingContractCount(Request $request) {
		$value = "";
		try {
		$info = DB::select(DB::raw("
			SELECT SUM(pending) total_pending
			FROM (
				SELECT COUNT(*) as pending FROM _fis_service_contract_status WHERE STATUS = 'PENDING'
				UNION ALL
				SELECT COUNT(*) as pending FROM _fis_cancel_merchandise WHERE STATUS = 'PENDING'
			) pending_tables
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

	public function getPendingSContractCount(Request $request) {
		$value = "";
		try {
		$info = DB::select(DB::raw("
			SELECT COUNT(*) as pending FROM _fis_service_contract_status WHERE STATUS = 'PENDING'
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

	public function getPendingMContractCount(Request $request) {
		$value = "";
		try {
		$info = DB::select(DB::raw("
			SELECT COUNT(*) as pending FROM _fis_cancel_merchandise WHERE STATUS = 'PENDING'
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

	public function disapproveContract(Request $request)
	{
		try {
			
			$value = (array)json_decode($request->post()['servicecontract']);
			
			try {
				$user = SystemUser::where([
					'Password'=>$value['password_input'],
					'UserName'=>$value['username'],
				])->firstOrFail();

			} catch (\Exception $e) {
				return [
						'status'=>'unsaved',
						'message'=>'Incorrect Password'
				];
			}

			$dis = FisPendingContract::find($value['contract_id']);
					$dis->delete();

			   	/*$dis->update([
			   	'status'=>'DISAPPROVED',
			   	'approvedBy'=>$value['username'],
				'dateApproved'=> date('Y-m-d')
			   	]);*/

			   	return [
					'status'=>'saved',
					'message'=>$dis
				];

		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function insertCancelSales(Request $request) {
		try {
			$value = (array)json_decode($request->post()['servicecontract']);

		
			if ($value['merchandise_id']!="") {
			$info = FisCancelMerchandise::where(['merchandise_id'=>$value['merchandise_id']])->first();

				if($info)
				{
					return [
						'status'=>'unsaved',
						'message'=>'Merchandise is already waiting for approval.'
					];	
				}
			}

			$data = FisCancelMerchandise::create([
					'client'=>$value['client'],
					'merchandise_id'=>$value['merchandise_id'],
					'OR_no'=>$value['OR_no'],
					'remarks'=>$value['remarks'],
					'status'=>'PENDING',
					'requestBy'=>$value['username'],
					'dateRequest' =>  date('Y-m-d')
			]);
			return [
				'status'=>'saved',
				'message'=>$data
			];
			
		} catch (\Exception $e) {
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

	public function getPendingMerchandise(Request $request) {
		$value = "";
		try {
		$info = DB::select(DB::raw("
			SELECT * FROM _fis_cancel_merchandise
			ORDER BY id DESC
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

	public function disapproveMerchandise(Request $request)
	{
		try {
			
			$value = (array)json_decode($request->post()['servicecontract']);
			
			try {
				$user = SystemUser::where([
					'Password'=>$value['password_input'],
					'UserName'=>$value['username'],
				])->firstOrFail();

			} catch (\Exception $e) {
				return [
						'status'=>'unsaved',
						'message'=>'Incorrect Password'
				];
			}

			$dis = FisCancelMerchandise::find($value['merchandise_id']);
			   	$dis->delete();

			   	return [
					'status'=>'saved',
					'message'=>$dis
				];

		} catch (\Exception $e) {
			
			return [
				'status'=>'unsaved',
				'message'=>$e->getMessage()
			];	
		}
	}

}
