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
		'email',
		'aadhar',
		'aadhar_file',
		'qualiffication',
		'relation',
		'occupation',
		'itr_no',
		'itr_file',
		'image',
		'docfolder_name',
		'status'
	];

	protected $hidden = [
		'created_at',
		'updated_at'
	];
}
