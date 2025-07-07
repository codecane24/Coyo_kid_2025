<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{   
    protected $fillable = [
        'name',
        'parent_id',
        'guard_name',
        'is_perm_deleted'
    ];
    
    public function parent()
    {
        return $this->belongsTo(Permission::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Permission::class, 'parent_id');
    }
}
