<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassFees extends Model
{
    protected $table = 'class_fees_master';

    protected $fillable = [
        'class_id',
        'feestype_id',
        'amount',
        'due_date',
        'created_by',
        'branch_id',
        'company_id',
        'academicyear_id',
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function feestype()
    {
        return $this->belongsTo(FeesType::class, 'feestype_id');
    }
}
