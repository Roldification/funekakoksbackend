<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisItemSales extends Model
{
	protected $table = '_fis_item_sales'; //table name of the model
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
	
	public function servicecontract()
	{
		return $this->belongsTo('App\ServiceContract', 'contract_id', 'contract_id');
	}
	
	public function items()
	{
		return $this->belongsTo('App\FisItems', 'product_id', 'item_code');
	}
	
}
