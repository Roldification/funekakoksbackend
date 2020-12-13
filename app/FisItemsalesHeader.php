<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisItemsalesHeader extends Model
{
	protected $table = '_fis_itemsales_header'; //table name of the model
	//protected $primaryKey = 'OR_no';
	//public $incrementing = false;
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
	
	public function itemsales()
	{
		return $this->hasMany('App\FisItemSales', 'sales_id', 'id');
	}
	
	public function servicesales()
	{
		return $this->hasMany('App\FisServiceSales', 'sales_id', 'id');
	}
	
}
