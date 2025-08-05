<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeesMaster extends Model
{
    use HasFactory;

    protected $table = 'fees_master';

    protected $fillable = [
        'feestype_id',
        'rate',
        'rate_type',
        'amount',
        'due_date',
        'created_by',
        'branch_id',
        'company_id',
    ];

    public function feestype()
    {
        return $this->belongsTo(FeesType::class, 'feestype_id');
    }
}