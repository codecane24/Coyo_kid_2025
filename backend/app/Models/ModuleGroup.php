<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ModuleGroup extends Model
{
	protected $table = 'module_group';

	
	
	public function modules()
	{
		return $this->hasMany(Permission::class, 'group_id');
	}

}
