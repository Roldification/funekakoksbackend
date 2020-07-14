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
		font-size: 17px;
		text-align: center;
		font-family: 'Ubuntu', sans-serif;
		margin-bottom: 30px;
	}
	.sub-title{
		font-size: 14px;
		text-align: left;
		font-family: 'Open Sans', sans-serif;
		float: left;
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

	th{
		padding: 5px;
		text-align: left;
		border: 1px solid #000000;
	}

	td{
		text-align: left;
	}

	.row{
		width: 100%;
	}


</style>
</head>
<body>

<div id="wrapper">
	<div class="main-title"><strong>YEAR - END PERFORMANCE BONUS</strong></div>

	<div class="row">
	<div class="sub-title" style="width: 50%; float: left;"> Name of Informant: <?php echo " ".$report[0]->informant_name ?></div>
	<div class="sub-title" style="width: 50%; text-align: right;"> 
		<?php echo date('F d, Y', strtotime($date))?></div>
	</div>

	<table style="margin-top: 10px;">
		<tr>
			<th style="width: 300px;">Month</th>
			<th style="width: 200px;">QCI No.</th>
			<th style="width: 200px;">Total Package Availed</th>
			<th style="width: 200px;">Incentives</th>
		</tr>

		<?php 
		 $sumbasic = 0;
		 $sumincentives = 0;
		foreach ($report as $row) { 
					$sumbasic = $sumbasic + $row->basic_inc;
					$sumincentives = $sumincentives + $row->amount;
			?>
		<tr>
			<td>
			<?php echo date('F', strtotime($row->date))?>
			</td>
			<td>
			<?php echo " ".$row->qci_no ?>
			</td>
			<td>
			<?php echo " ".$row->basic_inc ?>
			</td>
			<td>
			<?php echo " ".$row->amount ?>
			</td>
		</tr>
		<?php } ?>
	</table>

	<table style="margin-top: 5px;">
		<tr>
		<td style="width: 300px;"></td>
		<td style="width: 200px; text-align: right; font-size: 14px;"><strong> Total Incentives: </strong></td>
		<td style="width: 200px; text-align: left; font-size: 14px;"><strong><?php echo number_format((double)$sumbasic, 2,'.','');?> </strong> </td>

		<td style="width: 200px; text-align: left; font-size: 14px;"><strong><?php echo number_format((double)$sumincentives, 2,'.','');?> </strong></td>
		</tr>
	</table>
	
	<table style="margin-top: 5px;">
		<tr>
		<td style="width: 500px; font-size: 14px;"><strong>Additional Monthly Cash Incentives Rate</strong></td>
		<td style="width: 300px; font-size: 14px;"><strong>40%</strong></td>
		</tr>

		<tr>
		<td style="width: 500px; font-size: 14px;"><strong>Additional Monthly Cash Incentives</td>
		<?php 
		$inc = 0;
		$inc = $sumincentives * 0.40 ?>
		<td style="width: 300px; font-size: 14px;"><strong><?php echo number_format((double)$inc, 2,'.','');?> </strong></td>
		</tr>
	</table>

	<table style="margin-top: 10px;">
		<tr>
			<td style="width: 400; font-size: 12px;">Prepared by:</td>
			<td style="width: 300; font-size: 12px;">Checked by:</td>
		</tr>

		<tr>
			<td style="width: 400; font-size: 12px; text-decoration: underline;"><strong>Lourence T. Caga-anan</strong></td>
			<td style="width: 300; font-size: 12px; text-decoration: underline;"><strong>Sarah Jane Responso</strong></td>
		</tr>

		<tr>
			<td style="width: 400; font-size: 12px;">OIC-Funecare Operation</td>
			<td style="width: 300; font-size: 12px;">Funecare Accounting Clerk</td>
		</tr>
	</table>


	<table style="margin-top: 10px;">
		<tr>
			<td style="width: 400; font-size: 12px; font-weight: 600;">Approved by:</td>
			<td style="width: 300; font-size: 12px;">Received by:</td>
		</tr>

		<tr>
			<td style="width: 400; font-size: 12px; text-decoration: underline;"><strong>Maria April V. Villanueva</strong></td>
			<td style="width: 300; font-size: 12px;"><strong>___________________________________</strong></td>
		</tr>

		<tr>
			<td style="width: 400; font-size: 12px;">Funecare Service Manager</td>
			<td style="width: 300; font-size: 12px;">Name and Signature/Date</td>
		</tr>
	</table>

	<div class="container">
		<p style="text-align: right; font-size: 14px;">
			YPB No. <u><?php echo " ".$ypb ?></u>
		</p>
	</div>

</div>

</body>
</html>