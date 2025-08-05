<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FeesType extends Model
{
	protected $table = 'fees_type_master';

	protected $fillable = [
		'code',
		'feesgroup_id',
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

	public function feesgroup()
	{
		return $this->belongsTo(FeesGroup::class, 'feesgroup_id');
	}

	public function createby()
	{
		return $this->belongsTo(User::class, 'created_by');
	}

}
