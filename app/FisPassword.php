<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisPassword extends Model
{
    protected $table = 'SystemUser'; //table name of the model
    protected $primaryKey = 'UserName';
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
}
