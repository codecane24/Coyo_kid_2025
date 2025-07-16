<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentParent extends Model
{
	protected $table = 'student_parents';

	protected $hidden = [
		'created_at',
		'updated_at'
	];
}
