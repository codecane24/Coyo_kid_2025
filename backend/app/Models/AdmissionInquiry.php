<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionInquiry extends Model
{
    use HasFactory;
    protected $table = 'admission_inquiries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'academic_year',
        'date_of_enquiry',
        'first_name',
        'middle_name',
        'last_name',
        'class_id',
        'gender',
        'date_of_birth',
        'primary_contact',
        'email',
        'suitable_batch',
        'father_name',
        'father_email',
        'father_phone',
        'father_aadhar',
        'father_occupation',
        'father_profile_image',
        'father_aadhar_image',
        'mother_name',
        'mother_phone',
        'mother_email',
        'mother_aadhar',
        'mother_occupation',
        'mother_profile_image',
        'mother_aadhar_image',
        'sibling_same_school',
        'sibling_ids',
        'permanent_address',
        'current_address',
        'previous_school_name',
        'previous_school_address',
        'added_by',
        'status',
        'remarks',
        'created_at',
        'updated_at',
        'student_id',
        'branch_id',
        'company_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date_of_enquiry' => 'datetime',
        'date_of_birth' => 'datetime',
        'sibling_ids' => 'array',
        'permanent_address' => 'array',
        'current_address' => 'array',
        'father_profile_image' => 'string',
        'father_aadhar_image' => 'string',
        'mother_profile_image' => 'string',
        'mother_aadhar_image' => 'string',
    ];
}