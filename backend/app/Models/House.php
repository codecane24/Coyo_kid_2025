<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
	protected $table = 'houses';

	// hidden attributes
	protected $hidden = [
		'created_at',
		'updated_at'
	];
	
	

}
