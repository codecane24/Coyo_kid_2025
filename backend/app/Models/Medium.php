<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Medium extends Model
{
	protected $table = 'education_mediums';

	protected $fillable = [
		'name',
		'description',
		'branch_id',
		'company_id',
		'created_by',
		'status'
	];

	protected $hidden = [
		'created_at',
		'updated_at'
	];


}
