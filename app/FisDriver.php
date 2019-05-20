<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisDriver extends Model
{
    protected $table = '_fis_settings_driver'; //table name of the model
    protected $primaryKey = 'driver_id';
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
}
