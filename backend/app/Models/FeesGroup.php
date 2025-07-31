<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FeesGroup extends Model
{
	protected $table = 'fees_group_master';

	protected $fillable = [
		'code',
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
