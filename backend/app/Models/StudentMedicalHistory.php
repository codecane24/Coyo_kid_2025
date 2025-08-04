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
        'serious_disease',
        'serious_injuries',
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