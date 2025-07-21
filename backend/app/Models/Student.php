<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
	protected $table = 'students';

	 protected $fillable = [
        'father_id',
        'mother_id'
	 ];

	protected $hidden = [
		'created_at',
		'updated_at'
	];

	public function parent(){
		//
	}

	public function gaurdians(){
		//
	}

	public function siblings(){
		//
	}

	public function medicalhistory(){
		//
	}

	public function previousSchool(){
		//
	}


}
