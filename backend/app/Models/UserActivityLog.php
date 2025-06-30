<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


use App\User;
class UserActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'method',
        'ip_address',
        'user_agent',
        'payload',
        'old_data',
        'new_data',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

   
    public function user()
    {
        return $this->belongsTo(User::class); // Assuming `user_id` is the foreign key
    }

}
