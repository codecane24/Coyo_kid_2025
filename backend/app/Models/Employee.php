<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'users';
    protected $hidden = ['created_at', 'updated_at'];
    protected $guarded = [];

    protected $appends = ['role_id', 'role_name']; // Append custom attributes

    public function assignInquery()
    {    
        return $this->hasMany(Inquery::class, 'salesman_id', 'id');
    } 

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    // Custom Accessors
    public function getRoleIdAttribute()
    {
        return $this->roles->pluck('id')->first();
    }

    public function getRoleNameAttribute()
    {
        return $this->roles->pluck('name')->first();
    }
}
