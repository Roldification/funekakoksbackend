<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessTokens extends Model
{
	protected $table = '_fis_access_tokens';
	protected $guarded = []; //set all the fields fillable. fillable means the ones to be supplied for inserting data

}
