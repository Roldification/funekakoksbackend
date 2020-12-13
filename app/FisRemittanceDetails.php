<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisRemittanceDetails extends Model
{
	protected $table = '_fis_cash_remittance_details'; //table name of the model
	
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
	
	public function scpayments()
	{
		return $this->belongsTo('App\FisSCPayments', 'fk_sc_payment_id', 'payment_id');
	}
	
	public function miscpayments()
	{
		return $this->belongsTo('App\FisSalesTransaction', 'fk_misc_payment_id');
	}

	
	
}	