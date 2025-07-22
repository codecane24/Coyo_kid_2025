<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentMedicalHistory extends Model
{
    use HasFactory;

    protected $table = 'student_medicals';

    protected $fillable = [
        'student_id',
        'serious_indury',
        'serious_indury_detail',
        'medical_condition',
        'disease_history',
        'disease_history_detail',
        'allergies',
        'medications',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the student that owns the medical history.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}