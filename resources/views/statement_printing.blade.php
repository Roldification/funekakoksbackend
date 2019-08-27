<!doctype html>
<html>
	<head>
    	<style>
    	 @page{
    	 margin-top: 25px;
    	 }
    	 
    	 .row{
    	 display:blocked;
    	 margin:0px 25px 0px 25px; 
    	 }
    	 
    	 font {
    	  font-size:12px;
    	 }
    	</style>
    </head>
	<body>
	
		<div style="height:90px;">
         
         </div>
         
         <div class="row">
         	<table style="width:100%;">
         		<tr>
         			<td style="text-align:center;"><font face="segoeui" style="font-size:17px;"><strong>STATEMENT OF ACCOUNT</strong></font></td>
         		</tr>
         		<tr>
         			<td style="text-align:center;"><p><font face="segoeui" style="font-size:14px;"><strong>Name of Client: <?php echo $client ?></strong></font></p></td>
         		</tr>
         		<tr>
         			<td style="text-align:center;"><p><font face="segoeui" style="font-size:14px;"><strong>as of: <?php echo date('Y-m-d'); ?></strong></font></p></td>
         		</tr>
         	</table>
         	
         </div>
         
         <?php 
         	$totalPay = 0;
         	$paidAmount = 0;
         	$totalBalance = 0;
         	foreach ($accounts as $row)
         	{
         		$totalPay = $totalPay + ($row->packagePrice - $row->discount);
         	}
         	
         	foreach ($addservices as $rowx)
         	{
         		$totalPay = $totalPay + $rowx->total_price;
         	}
         	
         	foreach ($transactions as $rowz)
         	{
         		$paidAmount = $paidAmount + $rowz->AR_Credit;
         	}
         	
         	$totalBalance = $totalPay - $paidAmount;
         ?>
         
         <div class="row">
         	<table style="width:100%;">
         		<tr>
	    	 			<td style="width:40%;"><font face="segoeui"><strong>Particulars</strong></font></td>
	    	 			<td style="width:15%;"><font face="segoeui"><strong>Ref. No.</strong></font></td>
	    	 			<td style="width:15%;"><font face="segoeui"><strong>Qty./Days</strong></font></td>
	    	 			<td style="width:15%;"><font face="segoeui"><strong>Unit Price</strong></font></td>
	    	 			<td style="width:15%;"><font face="segoeui"><strong>Total</strong></font></td>     	 			
	    	 	</tr>
	    	 	<tr>
	    	 		<td colspan="4"><strong><font face="segoeui">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Package:</font></strong></td>
	    	 	</tr>
	    	 	<?php foreach ($accounts as $row)
					{	
					?>
					<tr>
						<td><font face="segoeui"><?php echo $row->package_name ?></font></td>
						<td><font face="segoeui"><?php echo $row->contract_no ?></font></td>
						<td>&nbsp;</td>
						<td><font face="segoeui"><?php echo number_format((double)$row->packagePrice - $row->discount, 2, '.', ',') ?></font></td>
						<td><font face="segoeui"><?php echo number_format((double)$row->packagePrice - $row->discount, 2, '.', ',')?></font></td>
					</tr>
					<?php 
					}
				?>
				<tr>
	    	 	<td>&nbsp;</td>
	    	 	</tr>
				<tr>
	    	 		<td colspan="4"><strong><font face="segoeui">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Additional Services:</font></strong></td>
	    	 	</tr>
	    	 	<?php foreach ($addservices as $rowx)
					{	
					?>
					<tr>
						<td><font face="segoeui"><?php echo $rowx->inclusionname ?></font></td>
						<td><font face="segoeui"><?php echo $rowx->contract_no ?></font></td>
						<td><font face="segoeui"><?php echo $rowx->quantity ?></font></td>
						<td><font face="segoeui"><?php echo number_format((double)$rowx->total_price, 2, '.', ',') ?></font></td>
						<td><font face="segoeui"><?php echo number_format((double)$rowx->total_price, 2, '.', ',')?></font></td>
					</tr>
					<?php 
					}
					?>
				 <tr>
	    	 		<td><strong><font face="segoeui">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total Bill</font></strong></td>
	    	 	 	<td></td>
					<td></td>
					<td></td>
					<td style="border-top:1px solid black;"><strong><font face="segoeui"><?php echo number_format((double)$totalPay, 2, '.', ',') ?></font></strong></td>
	    	 	 </tr>
	    	 	 <tr>
	    	 	  <td>&nbsp;</td>
	    	 	 </tr>
	    	 	 
				 <tr>
	    	 		<td colspan="4"><strong><font color="red" face="segoeui">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Less: Payment</font></strong></td>
	    	 	</tr>
	    	 	 <?php foreach ($transactions as $rowz)
					{	
					?>
					<tr>
						<td><font color="red" face="segoeui"><?php echo $rowz->typename ?></font></td>
						<td><font color="red" face="segoeui"><?php echo $rowz->reference_no ?></font></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><font color="red" face="segoeui"><?php echo number_format((double)$rowz->AR_Credit, 2, '.', ',') ?></font></td>
					</tr>
					<?php 
					}
					?>
				<tr>
	    	 		<td><strong><font face="segoeui">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total Payable</font></strong></td>
	    	 	 	<td></td>
					<td></td>
					<td></td>
					<td style="border-top:1px solid black;"><strong><font face="segoeui" style="font-size: 15px;"><?php echo number_format((double)$totalBalance, 2, '.', ',') ?></font></strong></td>
	    	 	 </tr>
	    	 	  <tr>
	    	 	  <td>&nbsp;</td>
	    	 	 </tr>
	    	 	 <tr>
	    	 		<td colspan="4"><font face="segoeui"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Assigned Charging</strong></font></td>
	    	 	</tr>
	    	 	 <?php foreach ($accountcharging as $rowy)
					{	
					?>
					<tr>
						<td><font face="segoeui"><?php echo $rowy->account_type ?></font></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><font face="segoeui"><?php echo number_format((double)$rowy->totalamt, 2, '.', ',') ?></font></td>
					</tr>
					<?php 
					}
					?>
					
         	</table>
         	
         	<hr/>
         
         <table style="width:30%; margin-top:10px;">
	    	 		<tr>
	    	 			<td><font face="segoeui"><strong>Prepared by:</strong></font></td>
	    	 			   	 			
	    	 		</tr>
	    	 		<tr>
	    	 			<td style="border-bottom:1px solid black; text-align:center;" ><font face="segoeui" style="font-size: 14px;"><?php echo $user ?></font></td> 
	    	 		</tr>
	    	 		
    	 </table>
    	 
         <table style="width:50%; margin-top:10px;">
	    	 		<tr>
	    	 			<td><font face="segoeui"><strong>Noted by:</strong></font></td>
	    	 			   	 			
	    	 		</tr>
	    	 		<tr>
	    	 			<td style="border-bottom:1px solid black; text-align:center;" ><font face="segoeui" style="font-size: 14px;">Maria April Villanueva</font></td> 
	    	 		</tr>
	    	 		<tr>
	    	 			<td><font face="segoeui" style="text-align:center; font-size: 9px;">FuneCare Service Manager</font></td> 
	    	 		</tr>
    	 </table>
    	 		
         </div>
         


		

	</body>
</html>
