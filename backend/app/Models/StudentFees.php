<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFees extends Model
{
    use HasFactory;

    protected $table = 'student_fees';

    protected $fillable = [
        'student_id',
        'class_id',
        'feestype_id',
        'feesmaster_id',
        'amount',
        'due_date',
        'created_by',
        'branch_id',
        'company_id',
        'academicyear_id',
    ];

    // Relationships (optional, if you have these models)
    public function student() {
        return $this->belongsTo(Student::class, 'student_id');
    }
    public function class() {
        return $this->belongsTo(Classes::class, 'class_id');
    }
    public function feestype() {
        return $this->belongsTo(FeesType::class, 'feestype_id');
    }
    public function feesmaster() {
        return $this->belongsTo(FeesMaster::class, 'feesmaster_id');
    }
}