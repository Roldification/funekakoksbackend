<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FisSentlog extends Model
{
    protected $table = 'SentLog'; //table name of the model
    protected $primaryKey = 'Id';
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data
	public $timestamps = false; //disable updated_at and created_at in tables.
}
