<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $table = 'financial_years';

    protected $fillable = [
        'code',
        'name',
        'start_date',
        'end_date',
        'parent_year',
        'previous_year',
        'status',
        'closed_on',
        'closed_by',
        'description',
        'created_by',
        'company_id',
        'branch_id',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'closed_on',
        'created_at',
        'updated_at',
    ];
}
