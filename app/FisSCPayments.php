<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisSCPayments extends Model
{
	protected $table = '_fis_sc_payments'; //table name of the model
	protected $primaryKey = 'payment_id';
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
	
	
	public function header()
	{
		return $this->belongsTo('App\ServiceContract', 'contract_id', 'contract_id');
	}
	
	public function remittancedetails()
	{
		return $this->hasMany('App\FisRemittanceDetails', 'fk_sc_payment_id', 'payment_id');
	}
	
	
	
}
