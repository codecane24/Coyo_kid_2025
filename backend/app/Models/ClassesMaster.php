<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ClassesMastrer extends Model
{
	protected $table = 'classes';

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
