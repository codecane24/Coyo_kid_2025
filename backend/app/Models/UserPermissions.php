<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermissions extends Model
{
    protected $table = 'permission_user';
    protected $fillable = [
        'user_id',
        'permission_id',
    ];
    protected $casts = [
        'user_id' => 'integer',
        'permission_id' => 'integer',
    ];
     //pivot table does not have timestamps by default
    //if you want timestamps, you can add them in migration or set $timestamps to true

    public $timestamps = false;
    

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
