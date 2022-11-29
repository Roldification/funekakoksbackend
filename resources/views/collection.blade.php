<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://fonts.googleapis.com/css?family=Raleway&display=swap" rel="stylesheet">

	<style>
		.grid {
			font-size:10;
			border-top: 1px solid #000000;
    		border-bottom: 1px solid #000000;
			border-left: 1px solid #000000;
			border-right: 1px solid #000000;
			padding-top:5px;
			padding-left:5px;
			padding-right:5px;
			padding-bottom: 5px;
			font-family:'verdana';
		}

		.people div {
			font-size:12;
			font-weight: bold;
			margin-top:20px;
		}

		td {
			text-align: center;
		}

		table {
			border-collapse: separate;
  			border-spacing: 0.2px;
		}

		span {
			font-size:10;
		}

		.bold {
			font-weight: bold;
		}
	</style>
</head>
<body>
	@php
		$totalAmount = 0;
		$totalDenom = 0;
	@endphp

        <header>
			<img src="../public/images/header-soa.jpg" style="width: 100%;">         
   		</header>
        <div class="row padding-top">
         	<table style="width:100%;">
         		<tr>
         			<td style="text-align:center;"><h4>DAILY COLLECTION SUMMARY</h4></td>
         		</tr>
         	</table>
         </div>

		 <div style="padding: 15px;">
			 <table style="width:70%; margin-top:15px;">
				 <thead>
					 <tr>
						 <th class="grid" style="font-size:10;">Date</th>
						 <th class="grid">Stub No.</th>
						 <th class="grid">OR No.</th>
						 <th class="grid">Amount</th>
						 <th class="grid">Paid by (for the late: deceased)/(purpose)</th>
						 <th class="grid">SC No.</th>
					 </tr>
				 </thead>
				 <tbody>
					 @foreach ($collection[0]['remittancedetails'] as $coll)
					 	@php
							$totalAmount += (double)$coll->amount;
						@endphp
					 <tr>
						 @if($coll->scpayments)
								<td class="grid">{{date('m/d/Y', strtotime($coll->scpayments->payment_date))}}</td>
								<td class="grid">{{$coll->stub_no}}</td>
								<td class="grid">{{$coll->scpayments->reference_no}}</td>
								<td class="grid">{{number_format((double)$coll->scpayments->AR_Credit, 2, '.', ',')}}</td>
								<td class="grid">
									{{$coll->scpayments->header->signeeprofile->lastname}}, {{$coll->scpayments->header->signeeprofile->firstname}} {{$coll->scpayments->header->signeeprofile->middlename}}(
									@foreach($coll->scpayments->header->itemsales as $items)
										{{$items->items->item_name}}, 
									@endforeach)
								</td>
								<td class="grid">{{$coll->scpayments->header->contract_no}}</td>
						 @elseif($coll->miscpayments)
								<td class="grid">{{date('m/d/Y', strtotime($coll->miscpayments->payment_date))}}</td>
								<td class="grid">{{$coll->stub_no}}</td>
								<td class="grid">{{$coll->miscpayments->reference_no}}</td>
								<td class="grid">{{number_format((double)$coll->miscpayments->AR_Credit, 2, '.', ',')}}</td>
								<td class="grid">
									{{$coll->miscpayments->header->client}}(
									@foreach($coll->miscpayments->header->itemsales as $items)
										{{$items->items->item_name}}, 
									@endforeach
									@foreach($coll->miscpayments->header->servicesales as $services)
										{{$services->services->service_name}}, 
									@endforeach
									)
								</td>
								<td class="grid">-</td>
						  @elseif($coll->cashdeposits)
								<td class="grid">{{date('m/d/Y', strtotime($coll->cashdeposits->date_transacted))}}</td>
								<td class="grid">{{$coll->stub_no}}</td>
								<td class="grid">{{$coll->cashdeposits->reference}}</td>
								<td class="grid">{{number_format((double)$coll->cashdeposits->amount, 2, '.', ',')}}</td>
								<td class="grid">
									{{$coll->cashdeposits->signee->lastname}}, {{$coll->cashdeposits->signee->firstname}} {{$coll->cashdeposits->signee->middlename}}(Cash Deposit)
								</td>
								<td class="grid">-</td>
						  @endif
						 
					 </tr>
					 @endforeach
					 <tr>
						<td class="grid"></td>
						<td class="grid bold" colspan="2">TOTAL:</td>
						<td class="grid bold">{{number_format((double)$totalAmount, 2, '.', ',')}}</td>
						<td class="grid"></td>
						<td class="grid"></td>
					 </tr>
				 </tbody>
			 </table>

			 <div class="people">
				 <div>
					 Prepared by: Harold Calio
				 </div>
				 <div>
					Checked by: Harold Calio
				</div>
				<div>
					Cashier by: MARIA APRIL V. BANDALA
				</div>
			 </div>
		 </div>

		 <div style="width:210px; height:500px; position:absolute; left:580px; top: 150px; text-align:right;">
				<span>Date: {{date('F d, Y', strtotime($collection[0]['date_transacted']))}}</span>
				<table style="width:100%;">
					<thead>
						<tr>
							<th class="grid">Denomination</th>
							<th class="grid">No. of</th>
							<th class="grid">Total</th>
						</tr>
					</thead>
					<tbody>
						@foreach($denominations as $denomination)
							@php
								$totalDenom += (double)$denomination->quantity * $denomination->denomination;
							@endphp
						<tr>
							<td class="grid">{{number_format((double)$denomination->denomination, 2, '.', ',')}}</td>
							<td class="grid">{{$denomination->quantity}}</td>
							<td class="grid">{{number_format((double)$denomination->quantity * $denomination->denomination, 2, '.', ',')}}</td>
						</tr>
						@endforeach
						<tr>
							<td class="grid bold">Total</td>
							<td class="grid"></td>
							<td class="grid bold">{{number_format((double)$totalDenom, 2, '.', ',')}}</td>
						</tr>
					</tbody>
				</table>
		 </div>




</body>
</html>