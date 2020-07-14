<!doctype html>
<html>
    <head>
    	<style>
    	 @page{
    	 margin: 0px;
    	 }
    	 
    	 .row{
    	 display:blocked;
    	 margin:0px 25px 0px 25px; 
    	 }
    	 
    	 font {
    	  font-size:12px;
    	 }
    	 
    	 .subheader {
    	 font-size:<?php echo count($inclusions) > 24 ? "10px" : "10px"; ?>;
    	 }
    	 
    	 .tablevalue
    	 {
    	 font-size:<?php echo count($inclusions) > 24 ? "9px" : "9px"; ?>;
    	 }
    	 
    	</style>
    </head>
    <body>
    			
   
         <div style="height:90px;">
      
         </div>			    	 	  
    	 <div class="row" style="margin-top:5px;">
    	 	<table style="width:100%;">
    	 		<tr>
    	 			<td style="width:250px;"> </td>
    	 			<td style="width:300px;"> </td>
    	 			<td><font face="segoeui"><strong>Date:</strong> <?php echo date('F d, Y') ?></font></td>
    	 		</tr>
    	 	</table>
    	
    	 </div>
    	 
    	 
    	 <div class="row"  style="margin-top:5px; padding:15px;">
    	 	<table style="width:100%;">
    	 		<tr>
    	 			<td style="width:19%;"><font face="segoeui"><strong>Charge to Mr./Ms./Mrs.</strong></font></td>
    	 			<td style="width:44%; border-bottom:1px solid black;"><font face="segoeui"><?php echo $accounts[0]->fname." ".$accounts[0]->mname." ".$accounts[0]->lname; ?></font></td>
    	 			<td style="width:12%; text-align:right;"><font face="segoeui"><strong>Contact No.</strong></font></td>
    	 			<td style="width:20%; border-bottom:1px solid black;"><font face="segoeui"><?php echo $accounts[0]->contact_no; ?></font></td>
    	 			
    	 		</tr>
    	 	</table>
    		<table style="width:100%;">
    	 		<tr>
    	 			<td style="width:12%;"><font face="segoeui"><strong>Address</strong></font></td>
    	 			<td style="width:85%; border-bottom:1px solid black;" ><font face="segoeui"><?php echo $accounts[0]->signee_address; ?></font></td>

    	 			
    	 		</tr>
    	 	</table>
    	 </div>
    	 
    	
    	 
    	 <div class="row" style="border:1px solid black; text-align:center;">
    	 	<font face="segoeui"><strong>For the Burial and Funeral Services Rendered to</strong></font>
    	 </div>
    	 
    	 <div class="row" style="border:1px solid black; padding:5px 15px 5px 15px;">
				<table style="width:100%;">
					<tr>
						<td style="width:10%;"><font face="segoeui"><strong>Deceased:</strong></font></td>
						<td style="width:45%; border-bottom:1px solid black;"><font face="segoeui"><?php echo $accounts[0]->firstname." ".$accounts[0]->middlename." ".$accounts[0]->lastname; ?></font></td>
						<td style="width:7%;  text-align:right;"><font face="segoeui"><strong>Age:</strong></font></td>
						<td style="width:7%; border-bottom:1px solid black;"><font face="segoeui"><?php echo $accounts[0]->deceased_age; ?></font></td>
						<td style="width:16%;  text-align:right;"><font face="segoeui"><strong>Date of Birth:</strong></font></td>
						<td style="width:15%; border-bottom:1px solid black;"><font face="segoeui"><?php echo date('Y-m-d', strtotime($accounts[0]->birthday)); ?></font></td>
					</tr>
				</table>
				
				<table style="width:100%;">
					<tr>
						<td style="width:9%;"><font face="segoeui"><strong>Address:</strong></font></td>
						<td style="width:55%; border-bottom:1px solid black;"><font face="segoeui"><?php echo $accounts[0]->deceased_address; ?></font></td>
						<td style="width:13%;"><font face="segoeui"><strong>Death:</strong></font></td>
						<td style="width:21%; border-bottom:1px solid black;"><font face="segoeui"><?php echo $accounts[0]->causeOfDeath; ?></font></td>
					</tr>
				</table>
				<table style="width:100%;">
					<tr>
						<td style="width:10%;"><font face="segoeui"><strong>Date Died:</strong></font></td>
						<td style="width:25%; border-bottom:1px solid black;"><font face="segoeui"><?php echo date('M d, Y', strtotime($accounts[0]->date_died)); ?></font></td>
						<td style="width:5%;"><font face="segoeui"><strong>Time:</strong></font></td>
						<td style="width:10%; border-bottom:1px solid black;"><font face="segoeui"><?php echo date('h:i A', strtotime($accounts[0]->date_died)); ?></font></td>
						<td style="width:15%; text-align:right;"><font face="segoeui"><strong>Place of Death:</strong></font></td>
						<td style="width:35%; border-bottom:1px solid black;"><font face="segoeui"><?php echo $accounts[0]->deathPlace; ?></font></td>
					</tr>
				</table>
				
    	 </div>
    	 
    	 <div class="row" style="margin-top:5px;">
    	 	<font face="segoeui" style="text-decoration: underline;">OTHER DETAILS</font>
    	 </div>
    	 
    	  <div class="row" style="padding:5px 15px 5px 15px;">
				<table style="width:100%;">
					<tr>
						<td style="width:15%;"><font face="segoeui"><strong>Place of Viewing:</strong></font></td>
						<td style="width:40%; border-bottom:1px solid black;"><font face="segoeui"><?php echo $accounts[0]->embalming_place; ?></font></td>
						<td style="width:10%; text-align:right;"><font face="segoeui"><strong>Religion:</strong></font></td>
						<td style="width:35%; border-bottom:1px solid black;"><font face="segoeui"><?php echo $accounts[0]->ReligionName; ?></font></td>
					</tr>
				</table>
				<table style="width:100%;">
					<tr>
						<td style="width:15%;"><font face="segoeui"><strong>Date of Burial:</strong></font></td>
						<td style="width:25%; border-bottom:1px solid black;"><font face="segoeui"><?php echo date('Y-m-d', strtotime($accounts[0]->burial_time)); ?></font></td>
						<td style="width:5%;"><font face="segoeui"><strong>Time:</strong></font></td>
						<td style="width:10%; border-bottom:1px solid black;"><font face="segoeui"><?php echo date('H:i:s', strtotime($accounts[0]->burial_time)); ?></font></td>
						<td style="width:10%; text-align:right;"><font face="segoeui"><strong>Church:</strong></font></td>
						<td style="width:35%; border-bottom:1px solid black;"><font face="segoeui"><?php echo $accounts[0]->church; ?></font></td>
					</tr>
				</table>
				<table style="width:100%;">
	    	 		<tr>
	    	 			<td style="width:15%;"><font face="segoeui"><strong>Place of Burial:</strong></font></td>
	    	 			<td style="width:85%; border-bottom:1px solid black;" ><font face="segoeui"><?php echo $accounts[0]->burial_place; ?></font></td>
	
	    	 			
	    	 		</tr>
    	 		</table>
    	 </div>
    	 
    	 <div class="row" style="margin-top:5px;">
    	 	<font face="segoeui" style="text-decoration: underline;">SCOPE OF SERVICES</font>
    	 </div>
    	 
    	 <div class="row" style="border:1px solid black; height:295px;">
    	 		<table style="width:100%;">
	    	 		<tr>
	    	 			<td style="width:50%;"><font face="segoeui"><strong>Particulars</strong></font></td>
	    	 			<td style="width:20%;"><font face="segoeui"><strong>Particulars</strong></font></td>
	    	 			<td style="width:10%;"><font face="segoeui"><strong>Unit Price</strong></font></td>
	    	 			<td style="width:10%;"><font face="segoeui"><strong>Discount</strong></font></td>
	    	 			<td style="width:10%;"><font face="segoeui"><strong>Total</strong></font></td>   	 			
	    	 		</tr>
	    	 		<tr>
	    	 			<td colspan="2"><font face="segoeui" id="subheader">Casket Including Services</font></td>
	    	 			
	    	 			<td><font face="segoeui" style="font-size:12px;"><?php echo number_format((double)$accounts[0]->grossPrice - $totalAdditionalAmount, 2, '.', ','); ?></font></td>
	    	 			<td><font face="segoeui" style="font-size:12px;"><?php echo number_format((double)$accounts[0]->discount, 2, '.', ','); ?></font></td>
	    	 			<td><font face="segoeui" style="font-size:12px;"><?php echo number_format((double)$accounts[0]->contract_amount - $totalAdditionalAmount, 2, '.', ','); ?></font></td>
	    	 		</tr>
	    	 		<tr>
	    	 			<td><font face="segoeui">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Package Inclusions: <?php echo $accounts[0]->package_name ?></font></td>
	    	 		</tr>
	    	 		
	    	 		
	    	 		<?php 
	    	 		foreach ($inclusions as $row)
	    	 		{
	    	 			if($row->ispackage)
	    	 			{
	    	 			?>
	    	 			
	    	 		 <tr>
	    	 			<td><font face="segoeui" class="tablevalue">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $row->inclusionname; ?></font></td>
	    	 			<td><font face="segoeui" class="tablevalue"><?php echo $row->quantity; ?></font></td>
	    	 		 </tr>
	    	 			
	    	 			<?php 
	    	 			}
	    	 		}
	    	 		?>
	    	 		<tr>
	    	 			<td><font face="segoeui" class="subheader">Additional Service</font></td>
	    	 			<td></td>
	    	 			<td></td>
	    	 			<td></td>
	    	 		</tr>
	    	 		<?php 
	    	 		foreach ($inclusions as $row)
	    	 		{
	    	 			if(!$row->ispackage)
	    	 			{
	    	 			?>
	    	 			
	    	 		 <tr>
	    	 			<td><font face="segoeui" class="tablevalue">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $row->inclusionname; ?></font></td>
	    	 			<td><font face="segoeui" class="tablevalue"><?php echo $row->quantity; ?></font></td>
	    	 			<td><font face="segoeui" class="tablevalue"><?php echo $row->total_price>0 ? number_format((double)$row->total_price, 2, '.', ',') : ''; ?></font></td>
	    	 			<td><font face="segoeui" class="tablevalue"><?php echo $row->total_price>0 ? number_format((double)0, 2, '.', ','): ''; ?></font></td>
	    	 			<td><font face="segoeui" class="tablevalue"><?php echo $row->total_price>0 ? number_format((double)$row->total_price, 2, '.', ','): ''; ?></font></td>
	    	 		 </tr>
	    	 			
	    	 			<?php 
	    	 			}
	    	 		}
	    	 		?>
    	 		</table>
    	 </div>
    	 <div class="row" style="border:1px solid black; height:15px;">
    	 	<table style="width:100%;">
    	 		<tr>
    	 			<td style="text-align: left;"><font face="segoeui" style="font-size:9px;">Total Net Payable:</font></td>
    	 			<td style="text-align: right"><font face="segoeui" style="font-size:14px; font-weight:bold;"><?php echo number_format((double)$accounts[0]->contract_amount, 2, '.', ','); ?></font></td>
    	 		</tr>
    	 	</table>
    	 </div>
    	 <div class="row">
    	 	<table style="width:100%;">
	    	 		<tr>
	    	 			<td style="width:10%;"><font face="segoeui"><strong>Remarks:</strong></font></td>
	    	 			<td style="width:90%; border-bottom:1px solid black;" ><font face="segoeui"><?php echo $accounts[0]->remarks; ?></font></td>
	
	    	 			
	    	 		</tr>
    	 		</table>
    	 </div>
    	 
    	 <div style="height:150px; width:360px; position:absolute; left:25px; padding-top:10px;">
    	 		<table style="width:100%;">
	    	 		<tr>
	    	 			<td><font face="segoeui"><strong>Prepared by:</strong></font></td>
	    	 			   	 			
	    	 		</tr>
	    	 		<tr>
	    	 			<td style="border-bottom:1px solid black; text-align:center;" ><font face="segoeui" style="font-size: 14px;"><?php echo $accounts[0]->created_by; ?></font></td> 
	    	 		</tr>
    	 		</table>
    	 		
    	 		<table style="width:100%; margin-top:10px;">
	    	 		<tr>
	    	 			<td><font face="segoeui"><strong>Noted by:</strong></font></td>
	    	 			   	 			
	    	 		</tr>
	    	 		<tr>
	    	 			<td style="border-bottom:1px solid black; text-align:center;" ><font face="segoeui" style="font-size: 14px;">MARIA APRIL VILLANUEVA</font></td> 
	    	 		</tr>
	    	 		<tr>
	    	 			<td><font face="segoeui" style="font-size: 9px;">FuneCare Service Manager</font></td> 
	    	 		</tr>
    	 		</table>
    	 		
    	 		<table style="width:100%; margin-top:10px;">
	    	 		<tr>
	    	 			<td><font face="segoeui"><strong>Approved by:</strong></font></td>
	    	 			   	 			
	    	 		</tr>
	    	 		<tr>
	    	 			<td style="border-bottom:1px solid black; text-align:center;" ><font face="segoeui" style="font-size: 14px;">JURIS D. PEREZ, CPA, MBA</font></td> 
	    	 		</tr>
	    	 		<tr>
	    	 			<td><font face="segoeui" style="font-size: 9px;">Chief Executive Officer</font></td> 
	    	 		</tr>
    	 		</table>
    	 </div>
    	 
    	 <div style="height:305px; width:370px; padding-top:10px; position:absolute; left:395px;">
    	 	<div>
    	 		<font face="segoeui" ><strong>TERMS</strong></font>
    	 		<p align="justify" style="margin-top:-4px;">
    	 			<font face="segoeui" style="font-size: 9px;">For the Burial and Funeral services rendered to the aforementioned deceased and to our full satisfaction. The undersigned agree to pay in FULL balance  the stated services or submit the mortuary requirements on or before the interment schedule. <i><b>NO PAYMENT NO INTERMENT.</b></i>
    	 			</font>
    	 		</p>
    	 		
    	 		<p align="justify" style="margin-top:20px;">
    	 			<font face="segoeui" style="font-size: 9px;">That any damages to the facilities and equipment used during the term of the signed contract shall be accounted by the contracting party.
    	 			</font>
    	 		</p>
    	 		
    	 		<p align="justify" style="margin-top:20px;">
    	 			<font face="segoeui" style="font-size: 9px;">For inquiries, update and complaint/s regarding the services rendered by TAGUM COOPERATIVE FUNECARE, the contracting party may contact immediately the office for appropriate action.
    	 			</font>
    	 		</p>
    	 	</div>
			
			<div>
				<font face="segoeui" style="margin-top:5px;"><strong>In Conformity</strong></font>
				
				<table style="width:100%; margin-top:10px;">
	    	 		
	    	 		<tr>
	    	 			<td style="border-bottom:1px solid black; text-align:center;" ><font face="segoeui" style="font-size: 14px;"><?php echo $accounts[0]->fname." ".$accounts[0]->mname." ".$accounts[0]->lname; ?></font></td> 
	    	 		</tr>
	    	 		<tr>
	    	 			<td style="text-align:center;"><font face="segoeui"><strong>Contracting Party</strong></font></td>   	 			
	    	 		</tr>
	    	 		<tr>
	    	 			<td style="text-align:center;"><font face="segoeui"><strong>and</strong></font></td>   	 			
	    	 		</tr>
    	 		</table>
    	 		
    	 		<table style="width:100%; margin-top:10px;">
	    	 		<tr>
	    	 			<td style="border-bottom:1px solid black; text-align:center;" ><font face="segoeui" style="font-size: 14px;"><?php echo $accounts[0]->guarantor_name; ?></font></td> 
	    	 		</tr>
	    	 		<tr>
	    	 			<td style="text-align:center;"><font face="segoeui"><strong>Guarantor</strong></font></td>   	 			
	    	 		</tr>

    	 		</table>
			</div>
    	 </div>
    </body>
</html>
