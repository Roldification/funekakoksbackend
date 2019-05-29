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
    	</style>
    </head>
    <body>
    			
    	 <img src="<?php echo public_path()?>/images/t1c.jpg" alt="Logo" style="height:300mm;"/>
    	 
    	 <div class="row" style="margin-top:5px;">
    	 	<table style="width:100%;">
    	 		<tr>
    	 			<td style="width:250px;"> </td>
    	 			<td style="width:300px;"> </td>
    	 			<td><font face="segoeui"><strong>Date:</strong> May 6, 2019</font></td>
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
						<td style="width:13%;"><font face="segoeui"><strong>Cause of Death:</strong></font></td>
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
    	 
    	 <div class="row" style="border:1px solid black; padding:5px 15px 5px 15px; height:300px;">
    	 		<table style="width:100%;">
	    	 		<tr>
	    	 			<td style="width:70%;"><font face="segoeui"><strong>Particulars</strong></font></td>
	    	 			<td style="width:10%;"><font face="segoeui"><strong>Unit Price</strong></font></td>
	    	 			<td style="width:10%;"><font face="segoeui"><strong>Discount</strong></font></td>
	    	 			<td style="width:10%;"><font face="segoeui"><strong>Total</strong></font></td>   	 			
	    	 		</tr>
	    	 		<tr>
	    	 			<td><font face="segoeui" style="font-size:14px;">Casket Including Services</font></td>
	    	 			<td><font face="segoeui" style="font-size:14px;"><?php echo number_format((double)$accounts[0]->grossPrice, 2, '.', ','); ?></font></td>
	    	 			<td><font face="segoeui" style="font-size:14px;"><?php echo number_format((double)$accounts[0]->discount, 2, '.', ','); ?></font></td>
	    	 			<td><font face="segoeui" style="font-size:14px;"><?php echo number_format((double)$accounts[0]->contract_amount, 2, '.', ','); ?></font></td>
	    	 		</tr>
	    	 		<tr>
	    	 			<td><font face="segoeui">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Package Inclusions: Nova</font></td>
	    	 		</tr>
	    	 		
	    	 		
	    	 		<?php 
	    	 		foreach ($inclusions as $row)
	    	 		{
	    	 			?>
	    	 			
	    	 		 <tr>
	    	 			<td><font face="segoeui">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $row->inclusionname; ?></font></td>
	    	 		 </tr>
	    	 			
	    	 			<?php 
	    	 		}
	    	 		?>
    	 		</table>
    	 </div>
    </body>
</html>
