<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisEmbalmer extends Model
{
    protected $table = '_fis_settings_embalmer'; //table name of the model
    protected $primaryKey = 'embalmer_id';
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
}
