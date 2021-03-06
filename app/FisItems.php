<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisItems extends Model
{
    protected $table = '_fis_items'; //table name of the model
    protected $primaryKey = 'item_code';
    protected $keyType = 'string';
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
}
