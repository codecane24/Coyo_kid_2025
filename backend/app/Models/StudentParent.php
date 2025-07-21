<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentParent extends Model
{
	protected $table = 'student_parents';

	protected $fillable = [
		'code',
		'name',
		'phone',
		'adhar',
		'relation',
		'occoccupation',
		'itr',
		'image',
		'docfolder_name',
		'status'
	];

	protected $hidden = [
		'created_at',
		'updated_at'
	];
}
