<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
	protected $table = 'users';

	protected $hidden = [
		'created_at',
		'updated_at'
	];

	
	public function classes()
	{
		return $this->hasMany(Student::class, 'class_id');
	}

	public function classmaster()
	{
		return $this->belongsTo(ClassMaster::class, 'classmaster_id');
	}


}
