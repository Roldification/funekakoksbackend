<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisPaymentType extends Model
{
	protected $table = '_fis_paymenttype'; //table name of the model
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
	protected $primaryKey = 'typeid';
	
}
