<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FyearBranchFilter;
use App\Traits\ValidatesFinancialYear;

class FinancialYear extends Model
{
    use HasFactory, ValidatesFinancialYear;
    protected $table = 'financial_years';

    protected $fillable = [
        'name',
        'is_active',
        'start_date',
        'end_date',
        'code', // Added to allow mass assignment
    ];

    public function parentyear()
    {
        return $this->belongsTo(FinancialYear::class, 'parent_year');
    }
    
    public function getParentyearNameAttribute()
    {
        return $this->parentyear ? $this->parentyear->name : 'self';
    }
}
