<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisServiceSales extends Model
{
	protected $table = '_fis_service_sales'; //table name of the model
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
	
	public function services()
	{
		return $this->belongsTo('App\FisServices', 'fk_service_id', 'id');
	}
}
