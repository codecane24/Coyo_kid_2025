<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ClassMaster extends Model
{
	protected $table = 'classes_master';

	protected $fillable = [
		'code',
		'name',
		'status'
	];


	
	public function classes()
	{
		return $this->hasMany(Classes::class, 'classmaster_id');
	}


}
