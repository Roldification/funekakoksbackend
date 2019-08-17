<!DOCTYPE html>
<html>
<head>
<link href="https://fonts.googleapis.com/css?family=Ubuntu&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Open+Sans|Ubuntu&display=swap" rel="stylesheet">
<style type="text/css">
	#wrapper{
		width: 100%;
		padding: 0px 10px 0px 10px;
	}
	.main-title{
		text-transform: uppercase;
		font-size: 30px;
		text-align: center;
		font-family: 'Ubuntu', sans-serif;
	}
	.sub-title{
		text-transform: uppercase;
		font-size: 20px;
		text-align: center;
		font-family: 'Open Sans', sans-serif;
	}
	.container{
		width: 100%;
		margin-top: 20px;
	}
	.container .small-title{
		font-family: 'Open Sans', sans-serif;
		text-transform: uppercase;
		font-size: 15px;
		text-align: left;
	}

	.container p{
		font-family: 'Open Sans', sans-serif;
		font-size: 14px;
		text-align: justify;
	}

	table {
	  	border: 1px solid #000000;
	  	border-collapse: collapse;
	}

	th{
		text-align: left;
		font-weight: 400;
		border-left: 1px solid #000000;
		vertical-align: top;
	}

	td{
		height: 20px;
  		vertical-align: bottom;
	}


</style>
</head>
<body>

<div id="wrapper">
	<div class="main-title">TC CARES PLAN</div>
	<div class="sub-title">AGREEMENT</div>

	<div class="container">
	<div style="width: 100%;">
		<div style="float: left; width: 60%;"  class="small-title">KNOW ALL YOUR men by these presents:</div>
		<div style="float: right; width: 30%;" class="small-title">TCP</div>
	</div>
		<p>
			TAGUM COOPERATIVE, a cooperative duly organized under Philippine Laws and duly
			licensed and registered with the proper government agencies, hereinafter called
			<b>TAGUM COOP</b> and the <b>MEMBER-PLANHOLDER</b> named herein have entered
			into this AGREEMENT, subject to the terms and conditions set forth below.
		</p>
	</div>

	<table>
		<tr style="padding: 20px;">
			<th style="width: 350px;">Membership CID* NO:</th>
			<th style="width: 400px;">Date of Issue/Effectivity:</th>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000;">
			<strong><?php echo " ".$accounts[0]->membership_id ?></strong>
			</td>
			<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000;">
			<strong><?php echo date('F d, Y', strtotime($accounts[0]->dateIssue)); ?></strong>
			</td>
		</tr>

		<tr>
			<th>Member/Planholder Name:</th>
			<th>Address:</th>
		</tr>
		<tr>
			<td style="border-right: 1px solid #000000;">
			<strong><?php echo $accounts[0]->lastName.", ".$accounts[0]->firstName." ".$accounts[0]->middleName ?></strong>
			</td>
			<td style="border-right: 1px solid #000000;">
			<strong><?php echo " ".$accounts[0]->address ?></strong>
			</td>
		</tr>
		<tr>
			<th></th>
			<th>Contact Number:</th>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000;"></td>
			<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000;">
			<strong><?php echo " ".$accounts[0]->contact_number ?></strong>
			</td>
		</tr>
	
		<tr>
			<th>Paying Period:</th>
			<th>Mode of Payment:</th>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000;">
			<strong><?php echo " ".$accounts[0]->payingPeriod ?></strong>
			</td>
			<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000;">
			<strong><?php echo " ".$accounts[0]->modePayment ?></strong>
			</td>
		</tr>

		<tr>
			<th>Contract Price:</th>
			<th>Amount of Instalment:</th>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000;">
			<strong><?php echo number_format((double)$accounts[0]->contractPrice); ?></strong>
			</td>
			<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000;">
			<strong><?php echo number_format((double)$accounts[0]->amountInstalment); ?></strong>
			</td>
		</tr>

		<tr>
			<th>Beneficiary:</th>
			<th>Relationship:</th>
		</tr>
		<tr>
			<td style="border-right: 1px solid #000000;">
			<strong><?php echo $accounts[0]->b_lastName.", ".$accounts[0]->b_firstName." ".$accounts[0]->b_middleName ?></strong>
			</td>
			<td style="border-right: 1px solid #000000;">
			<strong><?php echo " ".$accounts[0]->relation ?></strong>
			</td>
		</tr>
		<tr>
			<th></th>
			<th>Contact Number:</th>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000;"></td>
			<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000;">
				<strong><?php echo " ".$accounts[0]->b_contact_number ?></strong>
			</td>
		</tr>

		<tr>
			<th>First Payment:</th>
			<th>Due Date/Paying Date:</th>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000;">
				<strong><?php echo number_format((double)$accounts[0]->firstPayment); ?></strong>
			</td>
			<td style="border-bottom: 1px solid #000000; border-right: 1px solid #000000;">
				<strong><?php echo date('F d, Y', strtotime($accounts[0]->dueDate)); ?></strong>
			</td>
		</tr>
	</table>

	<div class="container">
		<p style="margin-top: 0px;">
			All terms, conditions and provisions contained in this TC Cares Plan Agreement signed by the MEMBER-PLANHOLDER constitute the entire contract between TAGUM COOP and the MEMBER-PLANHOLDER.
		</p>

		<p>
			Signed this day of <u><strong><?php echo date('F d, Y') ?></strong></u> at Tagum City, Davao del Norte, Philippines.
		</p>
	</div>


	<table style="border:none;">
		<tr>
			<th style="width: 350; border: none;">TAGUM COOPERATIVE</th>
		</tr>
		<tr>
			<td style="width: 350;">Represented by:</td>
			<td style="width: 350;">Member-Planholder:</td>
		</tr>
		<tr>
			<td style="width: 350; padding-top: 50px; text-decoration: underline;">
				<strong>JURIS D. PEREZ</strong>
			</td>
			<td style="width: 350; padding-top: 50px; text-decoration: underline;">
				<strong>
				<?php echo $accounts[0]->lastName.", ".$accounts[0]->firstName." ".$accounts[0]->middleName ?>
				</strong>
			</td>
		</tr>
		<tr>
			<td style="width: 350;">General Manager</td>
			<td style="width: 350;">Signature Over Printed Name</td>
		</tr>
	</table>

	<div class="container">
		<p style="font-style: italic; font-size: 12px;">
			* Customer Identification Number
		</p>
	</div>

	<div class="container">
		<p style="font-style: italic; font-size: 12px; text-align: center;">
			Continued on reverse side....
		</p>
	</div>

</div>

</body>
</html>