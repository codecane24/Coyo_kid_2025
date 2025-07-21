<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentSibling extends Model
{
	protected $table = 'student_sibilings';

	protected $fillable = [
		'student_id',
		'sibiling_student_id',
		'status',
	];

	protected $hidden = [
		'created_at',
		'updated_at'
	];
}
