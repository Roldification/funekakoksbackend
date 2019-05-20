<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MiscTransactionDetail extends Model
{
	protected $table = 'MiscTransactionDetail'; //table name of the model
	//protected $primaryKey = 'contract_id';
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
}
