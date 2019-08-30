<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisChapelServInc extends Model
{
    protected $table = '_fis_chapel_inclusions'; //table name of the model
	protected $primaryKey = 'service_id';
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
}
