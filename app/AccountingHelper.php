<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class AccountingHelper extends Model
{
    //
    
	
	public static function processAccounting($header, $details)
	{
		try {
			
	

			/*
			 * First, check if amount is balanced.
			 */
			$amountCredit = 0;
			$amountDebit = 0;
			foreach ($details as $row)
			{
				if($row['entry_type']=='CR')
					$amountCredit+=(float)$row['amount'];
				else if($row['entry_type']=='DR')
					$amountDebit+=(float)$row['amount'];
				
			}
			
			
			if(number_format($amountCredit, 2)!=number_format($amountDebit, 2))
				return [
					'status'=>'acctg. error',
					'message'=>'Unbalanced Transaction Debit: '.$amountDebit.' '.'Credit: '.$amountCredit,
					'details'=>$details
				];
			
			
			
			/*
			 *Getting Transaction No. from Branch
			 */
			$transNo = DB::select(DB::raw("exec _InterBranchGetTransNo @Branch='".$header['branch_code']."'"));
			
			/*
			 * Insert into Transaction Header
			 */
			MiscTransactionHeader::create(
					[
					'TransactionNumber'=>$transNo[0]->MiscTrnNo,
					'TransactionDate'=>$header['transaction_date'],
					'FKBranchIDMisc'=>$header['branch_code'],
					'FKMiscTransactionCode'=>$header['transaction_code'],
					'ReferenceNumber'=>$header['reference'],
					'FKDepBankCodeMisc'=>'-',
					'Amount'=> $amountCredit + $amountDebit,
					'TransactedBy'=>$header['username'],
					'OverrideBy'=>'',
					'TransactionTime'=>$header['transaction_date']." ".date('H:i:s'),
					'StationName'=>'HAROLD', //to be processed
					'IsCancelled'=>0,
					'Status'=>$header['status'],
					'DisbursedDate'=>$header['transaction_date'],
					'UpdatedBy'=>$header['username'],
					'DisapprovedDate'=>'1900-01-01 00:00:00.000',
					'DisapprovedBy'=>'',
					'Particulars'=>$header['particulars'],
					'CustomerName'=>$header['customer'],
					'CheckNumber'=>$header['checkno'],
					'rowversion'=>date('Y-m-d H:i:s'),
					'FKSourceOfFund'=>'01'
					]
					);
			
			
				$rownumber = 1;
			foreach ($details as $row)
			{
				if($row['amount']==0 || $row['amount']=='0')
				{continue; }
				
				MiscTransactionDetail::create([
					'FKTransactionNumberMiscDet'=>$transNo[0]->MiscTrnNo,
					'FKTransactionDateMiscDet'=>$header['transaction_date'],
					'FKBranchIDMiscDet'=>$header['branch_code'],
					'FKSLCodeMiscDet'=>$row['SLCode'],
					'Amount'=>$row['amount'],
					'BalanceLocator'=>$row['entry_type'],
					'EntryNumber'=>$rownumber
						
				]);
				
				
				$particularsID = DB::table('_GLJVLEDPARTICULARS')->max('PARTICULARNO');
				
				GLJVLEDPARTICULARS::create([
						'PARTICULARNO'=> $particularsID + 1,
						'MISCTRANLINK'=>$transNo[0]->MiscTrnNo,
						'ENTRYNOLINK'=>$rownumber,
						'PARTICULARS'=>$row['detail_particulars'],
						'FKBRANCHCODE'=>$header['branch_code']
				]);
				
				
				
				$rownumber++;
			}
			
			
			return [
					'status'=>'saved',
					'message'=>'saved!'
			];
			
			
			
		} catch (\Exception $e) {
			return [
				'status'=>'acctg. error',
				'message'=>$e->getMessage()
			];
		}
	}
	
	
	public static function fixShortageOfEntry($paid_amount, $remaining_tax_balance) : float
	{
		if($paid_amount < $remaining_tax_balance)
		{
			return $remaining_tax_balance;
		}
		
		else return $paid_amount;
		
	}
	
}
