<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [
        'father_id',
        'mother_id',
        'academic_year',
        'admission_no',
        'roll_no',
        'doj',
        'status',
        'first_name',
        'last_name',
        'class_id',
        'gender',
        'dob',
        'blood_group',
        'house',
        'religion',
        'category',
        'phone',
        'email',
        'caste',
        'mother_tongue',
        'languages',
        'profile_image',
        'address',
        'area',
        'landmark',
        'city_name',
        'state_name',
        'pincode',
        'address_2',
        'area_2',
        'city_name_2',
        'state_name_2',
        'pincode_2',
        'transport_allow',
        'docfolder_name',
        'added_by'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'dob' => 'date',
        'doj' => 'date',
        'languages' => 'array',
        'transport_allow' => 'boolean',
    ];

    /**
     * Get the father parent record
     */
    public function father(): BelongsTo
    {
        return $this->belongsTo(StudentParent::class, 'father_id');
    }

    /**
     * Get the mother parent record
     */
    public function mother(): BelongsTo
    {
        return $this->belongsTo(StudentParent::class, 'mother_id');
    }

    /**
     * Get all parents (both father and mother)
     */
    public function parents()
    {
        return StudentParent::whereIn('id', [$this->father_id, $this->mother_id])->get();
    }

    /**
     * Get all guardians (excluding parents)
     */
    public function guardians(): HasMany
    {
        return $this->hasMany(StudentParent::class, 'student_id')
                   ->whereNotIn('relation', ['father', 'mother']);
    }

    /**
     * Get all siblings
     */
    public function siblings(): HasMany
    {
        return $this->hasMany(StudentSibling::class);
    }

    /**
     * Get the medical history
     */
    public function medicalHistory(): HasOne
    {
        return $this->hasOne(StudentMedicalHistory::class);
    }

    /**
     * Get the previous education record
     */
    public function previousEducation(): HasOne
    {
        return $this->hasOne(StudentPreviousEducation::class);
    }

    /**
     * Get all documents
     */
    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    /**
     * Get the full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the age attribute
     */
    public function getAgeAttribute(): ?int
    {
        return $this->dob ? $this->dob->age : null;
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}