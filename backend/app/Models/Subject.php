<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
	protected $table = 'subjects';

	protected $fillable = [
		'code',
		'name',
		'subject_code',
		'subject_type',
		'status',
	];

	protected $hidden = [
		'created_at',
		'updated_at'
	];

}
