<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisCashDeposit extends Model
{
	protected $table = '_fis_cash_deposit'; //table name of the model
	protected $primaryKey = 'id';
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.

	
	// public function itemsales()
	// {
	// 	return $this->hasMany('App\FisItemSales', 'contract_id', 'contract_id');
	// }
	
	// public function servicesales()
	// {
	// 	return $this->hasMany('App\FisServiceSales', 'fk_contract_id', 'contract_id');
	// }

	public function remittancedetails()
	{
		return $this->hasMany('App\FisRemittanceDetails', 'fk_cash_deposit_id', 'id');
	}

	public function signee()
	{
		return $this->hasOne('App\FisMemberData', 'id', 'fk_signee_id');
	}
}	
