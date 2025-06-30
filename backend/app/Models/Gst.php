<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Gst extends Model
{
	protected $table = 'tbl_gst';

	public function categories()
	{
		return $this->hasMany(Category::class, 'tax_id', 'id')->select('id','code','name','tax_id');
	}
}
