<!doctype html>
<html>
    <head>
    	<link href="https://fonts.googleapis.com/css?family=Ubuntu&display=swap" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Open+Sans|Ubuntu&display=swap" rel="stylesheet">
    	<style>
    	 @page{
    	 	margin: 0px 30px;
    	 }

    	 body {
    		font-family: 'Ubuntu', sans-serif;
    		font-size: 11px; 
    	}
    	 		 
    	.row {
	    	 width: 100%;
	    	 display:blocked;
    	}

    	.title {
    		font-family: 'Ubuntu', sans-serif;
    		font-size: 20px; 
    		font-weight: bold; 
    		text-align: center;
    	}

    	.col-1 {
    		width: 50%;
    		float:  left;
    	}

    	</style>
    </head>
    <body>
    			
		<div>
		 <font style="font-size:5;">&nbsp;</font>
		</div>
         <div style="height:100px;">

			
         </div>			    	 	  
    	 <div class="row" style="margin-top:5px;  width: 100%;">
    	 	<div class="title">
    	 		FUNERAL SERVICE AGREEMENT
    	 	</div>
    	 </div>
    	 
    	 <div class="row">
    	 	<p style="text-align: justify; text-justify: inter-word;">&emsp;&emsp;&emsp;This Service Agreement is made and entered to this <u><?php echo date('jS'); ?></u> day of <u><?php echo date('F Y'); ?></u> at Tagum City, Province of Davao del Norte between:</p>
    	 </div>

    	 <div class="row">
    	 	<p style="text-align: justify; text-justify: inter-word;">&emsp;&emsp;&emsp;<strong>TAGUM COOPERATIVE</strong>, a cooperative duly organized and existing by virtue of Philippine laws, with principal office at Dalisay Road, Magugpo West, Tagum City, Davao del Norte, represented herein by <strong><u>JURIS D. PEREZ, CPA, MBA,</u></strong> it's Chief Executive Officer, of legal age, Filipino, married and a resident of Tagum City, hereinafter referred as <strong>"FIRST PARTY"</strong></p>
    	 </div>

    	 <div class="row">
    	 	<p>&emsp;&emsp;&emsp;<strong><u><?php echo $accounts[0]->fname." ".$accounts[0]->mname." ".$accounts[0]->lname; ?></u></strong>, of legal age, Filipino, married/s single and a resident of <u><?php echo $accounts[0]->signee_address; ?></u> hereinafter reffered as <strong>"SECOND PARTY"</strong></p>
    	 </div>

    	 <div class="row">
    	 	<p style="text-align: center;"><strong>W I T N E S S E T H:</strong></p>
    	 	<p style="text-align: justify; text-justify: inter-word;">&emsp;&emsp;&emsp;WHEREAS, the <strong>FIRST PARTY</strong> is multipurpose cooperative engaged in funeral services as one of its allied business/services offered to its member and non-members;</p>
    	 	<p style="text-align: justify; text-justify: inter-word;">&emsp;&emsp;&emsp;WHEREAS, the <strong>FIRST PARTY</strong> has a program dubbed as <strong>"Free Paupers Burial"</strong> for decendent aged one (1) year old and below resident in Tagum City, Davao del Norte;</p>
    	 	<p style="text-align: justify; text-justify: inter-word;">&emsp;&emsp;&emsp;WHEREAS, the <strong>FIRST PARTY</strong> thru <strong>TAGUM COOPERATIVE FUNECARE</strong> is the service provider of the said program to be offered to the <strong>SECOND PARTY</strong>;</p>
    	 	<p style="text-align: justify; text-justify: inter-word;">&emsp;&emsp;&emsp;WHEREAS, the <strong>SECOND PARTY</strong> would like to avail the funeral services of the <strong>FIRST PARTY</strong> for the benefit of the hereunder decedent;</p>
    	 </div>

    	 <div class="row" style="width: 80%; border: 1px solid #000000; padding-left: 10px; padding-right: 10px; margin-left: 70px;">
    	 	<p style="line-height: 0px; font-size: 14px;">
    	 		Name: <strong><u><?php echo $accounts[0]->firstname." ".$accounts[0]->middlename." ".$accounts[0]->lastname; ?></u></strong> <br/>
    	 		Date of Birth: <u><?php echo date('F d, Y', strtotime($accounts[0]->birthday)); ?></u> Age: <u><?php echo $accounts[0]->deceased_age; ?></u> <br/>
    	 		Date Died: <u><?php echo date('F d, Y', strtotime($accounts[0]->date_died)); ?></u> Time: <u><?php echo date('h:i A', strtotime($accounts[0]->date_died)); ?></u> <br/>
    	 		Place of Death: <u><?php echo $accounts[0]->deceased_address; ?></u> <br/>
    	 		Cause of Death: <u><?php echo $accounts[0]->causeOfDeath; ?></u>
    	 	</p>
    	 </div>

    	 <br/>

    	 <div class="row" style="width: 83%; margin-left: 70px;">
	    	 <div style="width: 50%; height: 160px; border: 1px solid #000000; padding-left: 10px; padding-right: 10px; float: left;">
	    	 	<p style="line-height: 0px;">
	    	 		services rendered to the decedent amounting to Three Thousand Pesos (P 3,000.00) with the hereunder inclusions, to wit;

	    	 		<ul style="line-height: 0px">
	    	 			<li>Retrieval/Delivery of Cadaver</li>
	    	 			<li>Pauper Casket</li>
	    	 			<li>3 days Embalming</li>
	    	 			<li>Wake Equipment</li>
	    	 			<li>Funeral Hearse</li>
	    	 			<li>Pauper Kit (Coffee, Biscuit, and etc.)</li>
	    	 		</ul>
	    	 	</p>
	    	 </div>

	    	 <div style="width: 42%; height: 167px; border: 1px solid #000000; padding-left: 10px; padding-right: 10px; float: right;">
	    	 	<p style="line-height: 0px;">
	    	 		requirements to the FIRST PARTY on or before the interment schedule, to wit;
	    	 		<ul style="line-height: 0px">
	    	 			<li>Death Certificate Certified True Copy or Police Burial Blotter</li>
	    	 		</ul>
	    	 	</p>
	    	 </div>
    	 </div>

    	 <div class="row">
    	 	<p style="text-align: justify; text-justify: inter-word;">&emsp;&emsp;&emsp;WHEREAS, the parties shall comply with the terms and conditions of this agreement;</p>

    	 	<p style="text-align: justify; text-justify: inter-word;">&emsp;&emsp;&emsp;IN WITNESS WHEREOF, the parties hereto have hereunto set their hands this <u><?php echo date('jS'); ?></u> day of <u><?php echo date('F Y'); ?></u> at Tagum City, Davao del Norte, Philippines.</p>
    	 </div>

    	 <div class="row">
    	 	<div class="col-1">
    	 		<p>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<strong>FIRST PARTY</strong></p>
    	 		<p>&emsp;&emsp;By:</p>
    	 		<p>&emsp;&emsp;&emsp;&emsp;&emsp;<u><strong>JURIS D. PEREZ, CPA, MBA</strong></u><br/>
    	 			&emsp;&emsp;&emsp;&emsp;&emsp;Chief Executive Officer <br/>
    	 			&emsp;&emsp;&emsp;&emsp;&emsp;Valid ID No.: <u>P1980021B (DFA/Passport)</u></p>
    	 	</div>

    	 	<div class="col-1">
    	 		<p>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<strong>SECOND PARTY</strong></p>
    	 		<p>By:</p>
    	 		<p>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;<u><strong><?php echo $accounts[0]->fname." ".$accounts[0]->mname." ".$accounts[0]->lname; ?></strong></u> <br/>
    	 			&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;Family Representative <br/>
    	 			&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;Valid ID No.: <u><?php echo $id_number ?></u></p>
    	 	</div>
    	 </div>

    	 <div class="row">
    	 	<p style="text-align: center;"><strong>SIGNED IN THE PRESENCE OF:</strong></p>
    	 	<p style="text-align: center;"><strong><u><?php echo $first_present ?></u></strong> & <strong><u><?php echo $second_present ?></strong></u></strong></p>
    	 </div>

    	 <div class="row">
    	 	<div class="col-1">
    	 	</div>
    	 	<div class="col-1" style="float: right;">
	    	 	<p style="text-transform: uppercase;">Prepared By: &emsp;<strong><u><?php echo $accounts[0]->created_by; ?></u></strong></p>
	    	 	<p>Noted By: &emsp;&emsp;&emsp;&nbsp;<strong><u>MARIA APRIL V. BANDALA</u></strong> <br/>
	    	 	&emsp;&emsp;&emsp;&nbsp;&emsp;&emsp;&emsp;&nbsp;&emsp;&emsp;FuneCare Services Department Manager <br/><br/>
	    	 	&emsp;&emsp;&emsp;&nbsp;&emsp;&emsp;&emsp;&nbsp;&emsp;&emsp;<strong><u>VIVELYN D. FRONTERAS</u></strong> <br/>
	    	 	&emsp;&emsp;&emsp;&nbsp;&emsp;&emsp;&emsp;&nbsp;&emsp;&emsp;Chief Operating Officer
	    	 	</p>


	    	</div>
    	 </div>


    </body>
</html>
