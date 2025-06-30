<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roleuser extends Model
{

    use HasFactory;protected $table ='role_user';
    protected $hidden = ['created_at', 'updated_at'];
    protected $guarded = [];

    public $timestamps = false;

   public function user()
    {
        return $this->belongsTo(UserModel::class,'user_id');
    }
    public function role()
    {
        return $this->belongsTo(Role::class,'role_id');
    }

    public function getUserNameAttribute()
    {
        return $this->user->name;
    }
    public function getRoleNameAttribute()
    {
        return $this->role->name;
    }
}

