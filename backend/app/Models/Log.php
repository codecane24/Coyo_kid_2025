<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FyearBranchFilter;
use App\Traits\ValidatesFinancialYear;

class Log extends Model
{
    use HasFactory, FyearBranchFilter, ValidatesFinancialYear;
    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'changes',
        'user_id',
    ];

}
