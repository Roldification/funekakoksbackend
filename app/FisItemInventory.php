<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisItemInventory extends Model
{
	protected $table = '_fis_item_inventory'; //table name of the model
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
}
