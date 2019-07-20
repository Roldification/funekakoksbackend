<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisCaresPackage extends Model
{
    protected $table = '_fis_cares_package'; //table name of the model
	protected $primaryKey = 'package_code';
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
}
