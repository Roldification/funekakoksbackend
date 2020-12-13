<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisRemittanceHeader extends Model
{
	protected $table = '_fis_cash_remittance_header'; //table name of the model
	
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
	
	
	public function remittancedetails()
	{
		return $this->hasMany('App\FisRemittanceDetails', 'fk_remittance_header_id');
	}

}	