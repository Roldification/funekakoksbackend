<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransferPlan extends Model
{
    protected $table = '_fis_cares_plan_transfer'; //table name of the model
	protected $primaryKey = 'fk_profile_id';
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
}
