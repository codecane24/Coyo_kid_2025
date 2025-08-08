<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdcModel extends Model
{
    protected $table = 'student_pdc';

    protected $fillable = [
        'student_id',
        'payment_id',
        'bank_name',
        'account_holder_name',
        'cheque_number',
        'amount',
        'cheque_date',
        'branch_name',
        'account_number',
        'status',
        'cleared_date',
        'remarks',
    ];
}
