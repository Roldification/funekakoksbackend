<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisPendingContract extends Model
{
    protected $table = '_fis_service_contract_status'; //table name of the model
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
	protected $primaryKey = 'contract_id';
}
