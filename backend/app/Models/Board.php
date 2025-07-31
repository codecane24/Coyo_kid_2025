<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
	protected $table = 'education_boards';

	protected $fillable = [
		'name',
		'description',
		'board_type',
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
