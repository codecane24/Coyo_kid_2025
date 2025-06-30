<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FyearBranchFilter;
use App\Traits\ValidatesFinancialYear;

class PushLog extends Model
{
    use HasFactory, FyearBranchFilter, ValidatesFinancialYear;  
    protected $guarded = [];

    public static function add_log($user_id = 0, $from_user_id = 0, $push_type = 0, $push_status = "", $push_data = '', $firebase_log = '')
    {
        PushLog::create([
            'user_id' => $user_id,
            'from_user_id' => $from_user_id,
            'push_type' => $push_type,
            'message' => $push_status,
            'push_data' => $push_data,
            'firebase_log' => $firebase_log,
        ]);
    }

}
