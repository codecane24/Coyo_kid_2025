<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
	protected $table ='teachers';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'first_name',
        'last_name',
        'class_id',
        'subject_id',
        'gender',
        'phone',
        'email',
        'blood_group',
        'date_of_joining',
        'father_name',
        'mother_name',
        'dob',
        'marital_status',
        'languages_known',
        'qualification',
        'work_experience',
        'previous_school',
        'previous_school_address',
        'previous_school_phone',
        'address',
        'permanent_address',
        'pan_number',
        'status',
        'epf_no',
        'basic_salary',
        'contract_type',
        'work_shift',
        'work_location',
        'date_of_leaving',
        'medical_leaves',
        'casual_leaves',
        'maternity_leaves',
        'sick_leaves',
        'account_name',
        'account_number',
        'bank_name',
        'ifsc_code',
        'branch_name',
        'route_id',
        'vehicle_number_id',
        'pickup_point_id',
        'hostel_id',
        'hostel_room_no',
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'youtube_url',
        'twitter_url',
        'resume_file',
        'joining_letter_file',
        'company_id',
        'branch_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'languages_known' => 'array',
        'date_of_joining' => 'date',
        'date_of_birth' => 'date',
        'date_of_leaving' => 'date',
        'status' => 'boolean',
        'basic_salary' => 'decimal:2',
        'medical_leaves' => 'integer',
        'casual_leaves' => 'integer',
        'maternity_leaves' => 'integer',
        'sick_leaves' => 'integer',
    ];

    /**
     * Get the class that the teacher is associated with.
     */
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Get the subject that the teacher is associated with.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Get the company that the teacher is associated with.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the branch that the teacher is associated with.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Get the route that the teacher is associated with.
     */
    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id');
    }

    /**
     * Get the vehicle number that the teacher is associated with.
     */
    public function vehicleNumber()
    {
        return $this->belongsTo(VehicleNumber::class, 'vehicle_number_id');
    }

    /**
     * Get the pickup point that the teacher is associated with.
     */
    public function pickupPoint()
    {
        return $this->belongsTo(PickupPoint::class, 'pickup_point_id');
    }

    /**
     * Get the hostel that the teacher is associated with.
     */
    public function hostel()
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }
}