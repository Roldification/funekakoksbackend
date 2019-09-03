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
		font-size: 14px;
		text-align: left;
		font-family: 'Ubuntu', sans-serif;
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
	  	margin-top: 10px;
	  	width: 700px;
	}

	th{
		border: 1px solid #000000;
		padding: 10px; 
	}

	td{
		height: 20px;
		border: 1px solid #000000;
  		padding: 10px;
	}
</style>
</head>
<body>
	<div id="wrapper">
		<div class="main-title">
			<strong>PACKAGE NAME:</strong> <?php echo  $package[0]->package_name;?>
		</div>
		<div class="main-title">
			<strong>STANDARD PRICE:</strong> <?php echo number_format((double)$package[0]->standardPrice, 2, '.', ','); ?>
		</div>
		<div class="main-title">
			<strong>DISCOUNT:</strong> <?php echo  $package[0]->discount;?>%
		</div>
		<div class="main-title">
			<strong>SALES PRICE:</strong> <?php echo number_format((double)$package[0]->salesPrice, 2, '.', ','); ?>
		</div>
		
		<table>	
			<tr>
				<th style="width: 311px;"><strong>ITEM NAME</strong></th>
				<th style="width: 100px;"><strong>QUANTITY</strong></th>
				<th style="width: 100px;"><strong>PRICE</strong></th>
			</tr>
			<?php foreach ($items as $row) { ?>
				<tr>
					<td style="width: 311px;"><?php echo $row->item_name; ?></td>
					<td style="width: 100px;"><?php echo $row->quantity; ?> <?php echo $row->type_duration; ?></td>
					<td style="width: 100px;"><?php echo number_format((double)$row->total_amount); ?></td>
				</tr>
			<?php } ?>
	
			<tr>
				<th style="width: 300px;"><strong>SERVICE NAME</strong></th>
				<th style="width: 100px;"><strong>LENGTH</strong></th>
				<th style="width: 100px;"><strong>PRICE</strong></th>
			</tr>
			<?php foreach ($service as $row) { ?>
				<tr>
					<td style="width: 311px;"><?php echo $row->service_name; ?></td>
					<td style="width: 100px;"><?php echo $row->duration; ?> <?php echo $row->type_duration; ?></td>
					<td style="width: 100px;"><?php echo number_format((double)$row->total_amount); ?></td>
				</tr>
			<?php } ?>
		</table>
	</div>
</body>
</html>