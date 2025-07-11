<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
	protected $table = 'classes';

	protected $fillable = [
		'code',
		'name',
		'classmaster_id',
		'room_no',
		'section',
		'status'
	];

	protected $hidden = [
		'created_at',
		'updated_at'
	];

	
	public function students()
	{
		//return $this->hasMany(Student::class, 'class_id');
	}

	public function classmaster()
	{
		return $this->belongsTo(ClassMaster::class, 'classmaster_id');
	}


}
