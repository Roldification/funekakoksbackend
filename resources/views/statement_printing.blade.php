<!doctype html>
<html>
	<head>
	<link href="https://fonts.googleapis.com/css?family=Raleway&display=swap" rel="stylesheet">
    <style>
   	body{
   		font-family: 'Raleway', sans-serif;
   	}
   	h3{
   		font-family: 'Raleway', sans-serif;
   		font-weight: bold;
   		font-size: 20px;
   	}
    .row{
    	 display:blocked;
    	 margin:0px 40px 0px 40px; 
    }

    .padding-top{
    	margin-top: 15px;
    	margin-bottom: 15px;
    }

    .heading-border{
    	border-top: 2px solid #000000;
    	border-bottom: 2px solid #000000;
    }
    </style>
    </head>
	<body>

		<header>
			<img src="../public/images/header-soa.jpg" style="width: 100%;">         
   		</header>
	         
         <div class="row padding-top">
         	<table style="width:100%;">
         		<tr>
         			<td style="text-align:center;"><h4>STATEMENT OF ACCOUNT</h4></td>
         		</tr>
         		<tr>
         			<td style="text-align:center;">As of <?php echo date('F d, Y'); ?></td>
         		</tr>
         	</table>
         </div>

         <div class="row padding-top">
         	Name of Client: <strong><?php echo $client ?></strong>
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
         	<table style="width:100%; margin-top: 5px;">
         		<tr>
	    	 			<td style="width:30%; font-size: 12px;" class="heading-border">Particulars</td>
	    	 			<td style="width:20%; font-size: 12px;" class="heading-border">Ref. No</td>
	    	 			<td style="width:15%; font-size: 12px;" class="heading-border">Qty./Days</td>
	    	 			<td style="width:15%; font-size: 12px;" class="heading-border">Unit Price</td>
	    	 			<td style="width:15%; font-size: 12px;" class="heading-border">Total</td>     	 			
	    	 	</tr>
	    	 	<tr>
	    	 		<td colspan="4" style="font-size: 12px;"><strong><font face="segoeui">Casket Package:</font></strong></td>
	    	 	</tr>
	    	 	<?php foreach ($accounts as $row)
					{	
					?>
					<tr>
						<td style="font-size: 10px;"><?php echo $row->package_name ?></td>
						<td style="font-size: 10px;"><?php echo $row->contract_no ?></td>
						<td>&nbsp;</td>
						<td style="font-size: 10px;"><?php echo number_format((double)$row->packagePrice - $row->discount, 2, '.', ',') ?></td>
						<td style="font-size: 10px;"><?php echo number_format((double)$row->packagePrice - $row->discount, 2, '.', ',')?></td>
					</tr>
					<?php 
					}
				?>

				<tr>
	    	 		<td colspan="4" style="font-size: 12px;"><strong>Additional Services:</strong></td>
	    	 	</tr>
	    	 	<?php foreach ($addservices as $rowx)
					{	
					?>
					<tr>
						<td style="font-size: 10px;"><?php echo $rowx->inclusionname ?></td>
						<td style="font-size: 10px;"><?php echo $rowx->contract_no ?></td>
						<td style="font-size: 10px;"><?php echo $rowx->quantity ?></td>
						<td style="font-size: 10px;"><?php echo number_format((double)$rowx->total_price, 2, '.', ',') ?></td>
						<td style="font-size: 10px;"><?php echo number_format((double)$rowx->total_price, 2, '.', ',')?></td>
					</tr>
					<?php 
					}
					?>
				 <tr>
	    	 		<td></td>
	    	 	 	<td></td>
					<td></td>
					<td></td>
					<td style="border-top:1px solid black; font-size: 12px;"><strong><font face="segoeui" ><?php echo number_format((double)$totalPay, 2, '.', ',') ?></font></strong></td>
	    	 	 </tr>
	    	</table>

	    	<table style="width:100%;">	 
				 <tr>
	    	 		<td style="font-size: 12px;"><strong>LESS:</strong></td>
	    	 	</tr>
	    	</table>
	    	<table style="width:100%;">	
	    	 	<?php foreach ($transactions as $rowz)
				{?>
					<tr>
						<td style="width:245px; font-size: 10px;"><?php echo $rowz->typename ?></td>
						<td style="width:380px; font-size: 10px;"><?php echo $rowz->reference_no ?></td>
						<td style="width:110px; font-size: 10px;"><?php echo number_format((double)$rowz->AR_Credit, 2, '.', ',') ?></td>
					</tr>
				<?php } ?>
					<tr>
						<td width="245px"></td>
						<td width="380px"></td>
						<td style="border-top:1px solid black; font-size: 12px;"><strong><font face="segoeui" ><?php echo number_format((double)$paidAmount, 2, '.', ',') ?></font></strong></td>
					</tr>
			</table>
			<hr/>
			<table style="width:100%;">
					<tr>
	    	 		<td style="width:245px; font-size: 12px;"><strong>Total Payable</strong></td>
	    	 	 	<td style="width:400px; font-size: 12px;"></td>
					<td style="width:110px; font-size: 12px; border-bottom:3px double #000000;"><strong><?php echo number_format((double)$totalBalance, 2, '.', ',') ?></strong></td>
	    	 	 	</tr>
	    	</table>
	    	<table style="width:100%; margin-top: 5px;">
	    	 	 <tr>
	    	 	  <td></td>
	    	 	 </tr>
	    	 	 <tr>
	    	 		<td style="font-size: 12px;"><strong>Assigned Charging:</strong></td>
	    	 	 </tr>
	    	 	 <?php foreach ($accountcharging as $rowy) { ?>
				 <tr>
					<td style="width:645px; font-size: 12px;"><?php echo $rowy->account_type ?></td>
					<td style="width:110px; font-size: 12px;"><?php echo number_format((double)$rowy->totalamt, 2, '.', ',') ?></td>
				 </tr>
				 <?php } ?>
					
         	</table>
         	
         	<hr/>
         	<div style="font-size: 12px; text-align: left;">Note: <span style="font-style: italic; font-weight:bold;">PLEASE SETTLE ON OR BEFORE THE INTERMENT DATE. THANK YOU.</span></div>
         
         <table style="width:100%; margin-top:20px;">
	    	 		<tr>
	    	 			<td style="width:300px; font-size: 12px; text-align: left;">
	    	 				<strong>Prepared by:</strong>
	    	 				<br/><br/>
	    	 				<strong><?php echo $user ?></strong>
	    	 				<br/>
	    	 			</td>	 			
	    	 			<td style="width:50px;"></td>
	    	 			<td style="width:350px; font-size: 12px;">
	    	 				<strong>Noted by:</strong>
	    	 				<br/><br/>
	    	 				<strong>MS. MARIA APRIL V. BANDALA</strong>
	    	 				<br/>
	    	 				<span style="font-style: italic; font-size: 12px;">FuneCare Service Manager</span>
	    	 			</td>	 			
	    	 		</tr>
	    	 		
    	 </table> 		
         </div>         
	</body>
</html>
