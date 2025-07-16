<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\StudentSibling;
use App\Models\StudentDocument;
use App\Models\StudentMedicalHistory;
use App\Models\StudentPreviousSchool;

class StudentController extends ResponseController
{

    public function index(){
        $students = Student::with(['parent', 'siblings', 'documents', 'medicalHistory', 'previousSchool'])
            ->orderBy('created_at', 'desc')
            ->get();

        $studentData = $students->map(function ($student) {
            return $this->get_student_data($student);
        });

        return $this->sendResponse(200, __('api.succ_students_list'), $studentData);
    }
    
    /**
     * Show the details of a specific student.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $student = Student::with(['parent', 'siblings', 'documents', 'medicalHistory', 'previousSchool'])
            ->find($id);

        if (!$student) {
            return $this->sendError(__('api.err_student_not_found'));
        }

        $studentData = $this->get_student_data($student);
        return $this->sendResponse(200, __('api.succ_student_details'), $studentData);
    }

    public function create(Request $request)
    {
        if ($request->has('step1')){
            validate , create, returnse response with student data with student encripted id 
        }
        if ($request->has('step1')){
            validate , create, returnse response with student data with student encripted id 
        }

        $rules = [
            // Personal Information
            'academic_year' => ['required'],
            'admission_number' => ['required', 'string', 'max:50', 'unique:students,admission_number'],
            'admission_date' => ['required', 'date'],
            'roll_number' => ['required', 'string', 'max:50'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'class' => ['required', Rule::in(['I', 'II', 'III', 'IV', 'V'])],
            'section' => ['required', Rule::in(['A', 'B'])],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'date_of_birth' => ['required', 'date'],
            'blood_group' => ['nullable', Rule::in(['O +ve', 'B +ve', 'B -ve'])],
            'house' => ['nullable', Rule::in(['Red'])],
            'religion' => ['nullable', Rule::in(['Christianity', 'Buddhism', 'Irreligion'])],
            'category' => ['nullable', Rule::in(['OBC', 'BC'])],
            'primary_contact_number' => ['required', 'string', 'max:15'],
            'email' => ['required', 'email', 'max:255', 'unique:students,email'],
            'caste' => ['nullable', 'string', 'max:100'],
            'mother_tongue' => ['nullable', Rule::in(['English', 'Spanish'])],
            'languages_known' => ['nullable', 'array'],
            'profile_image' => ['nullable', 'file', 'image', 'max:4096'], // 4MB max

            // Parents & Guardian Information
            'father.name' => ['nullable', 'string', 'max:100'],
            'father.email' => ['nullable', 'email', 'max:255'],
            'father.phone' => ['nullable', 'string', 'max:15'],
            'father.occupation' => ['nullable', 'string', 'max:100'],
            'father.image' => ['nullable', 'file', 'image', 'max:4096'],
            'mother.name' => ['nullable', 'string', 'max:100'],
            'mother.email' => ['nullable', 'email', 'max:255'],
            'mother.phone' => ['nullable', 'string', 'max:15'],
            'mother.occupation' => ['nullable', 'string', 'max:100'],
            'mother.image' => ['nullable', 'file', 'image', 'max:4096'],
            'guardian_type' => ['required', Rule::in(['Parents', 'Guardian', 'Others'])],
            'guardian.name' => ['required_if:guardian_type,Guardian,Others', 'string', 'max:100'],
            'guardian.relation' => ['required_if:guardian_type,Guardian,Others', 'string', 'max:100'],
            'guardian.phone' => ['required_if:guardian_type,Guardian,Others', 'string', 'max:15'],
            'guardian.email' => ['nullable', 'email', 'max:255'],
            'guardian.occupation' => ['nullable', 'string', 'max:100'],
            'guardian.address' => ['nullable', 'string', 'max:255'],
            'guardian.image' => ['nullable', 'file', 'image', 'max:4096'],

            // Siblings
            'siblings' => ['nullable', 'array'],
            'siblings.*.name' => ['required_with:siblings', 'string', 'max:100'],
            'siblings.*.roll_no' => ['required_with:siblings', 'string', 'max:50'],
            'siblings.*.admission_no' => ['required_with:siblings', 'string', 'max:50'],
            'siblings.*.class' => ['required_with:siblings', Rule::in(['I', 'II', 'III', 'IV', 'V'])],

            // Address
            'current_address' => ['required', 'string', 'max:255'],
            'permanent_address' => ['required', 'string', 'max:255'],

            // Transport Information
            'transport_enabled' => ['boolean'],
            'transport.route' => ['required_if:transport_enabled,true', Rule::in(['Newyork', 'Denver', 'Chicago'])],
            'transport.vehicle_number' => ['required_if:transport_enabled,true', Rule::in(['AM 54548', 'AM 64528', 'AM 123548'])],
            'transport.pickup_point' => ['required_if:transport_enabled,true', Rule::in(['Cincinatti', 'Illinois', 'Morgan'])],

            // Hostel Information
            'hostel_enabled' => ['boolean'],
            'hostel.name' => ['required_if:hostel_enabled,true', Rule::in(['Phoenix Residence', 'Tranquil Haven', 'Radiant Towers', 'Nova Nest'])],
            'hostel.room_no' => ['required_if:hostel_enabled,true', Rule::in(['20', '22', '24', '26'])],

            // Documents
            'medical_condition_document' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
            'transfer_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],

            // Medical History
            'medical_condition' => ['required', Rule::in(['Good', 'Bad', 'Others'])],
            'allergies' => ['nullable', 'array'],
            'medications' => ['nullable', 'array'],

            // Previous School Details
            'previous_school.name' => ['nullable', 'string', 'max:255'],
            'previous_school.address' => ['nullable', 'string', 'max:255'],

            // Other Details
            'bank.name' => ['nullable', 'string', 'max:100'],
            'bank.branch' => ['nullable', 'string', 'max:100'],
            'bank.ifsc' => ['nullable', 'string', 'max:50'],
            'other_information' => ['nullable', 'string', 'max:1000'],
        ];


        $validatedData = $this->directValidation($rules);
        if ($validatedData === false) {
            return $this->sendError(null);
        }

        try {
            // Handle file uploads
            $profileImage = $request->hasFile('profile_image') 
                ? $this->upload_file('profile_image', 'student_profiles') 
                : null;

            $fatherImage = $request->hasFile('father.image') 
                ? $this->upload_file('father.image', 'parent_profiles') 
                : null;

            $motherImage = $request->hasFile('mother.image') 
                ? $this->upload_file('mother.image', 'parent_profiles') 
                : null;

            $guardianImage = $request->hasFile('guardian.image') 
                ? $this->upload_file('guardian.image', 'guardian_profiles') 
                : null;

            $medicalDocument = $request->hasFile('medical_condition_document') 
                ? $this->upload_file('medical_condition_document', 'student_documents') 
                : null;

            $transferCertificate = $request->hasFile('transfer_certificate') 
                ? $this->upload_file('transfer_certificate', 'student_documents') 
                : null;

            // Create Student
            $student = Student::create([
                'academic_year' => $request->academic_year,
                'admission_number' => $request->admission_number,
                'admission_date' => $request->admission_date,
                'roll_number' => $request->roll_number,
                'status' => $request->status,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'class' => $request->class,
                'section' => $request->section,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'blood_group' => $request->blood_group,
                'house' => $request->house,
                'religion' => $request->religion,
                'category' => $request->category,
                'primary_contact_number' => $request->primary_contact_number,
                'email' => $request->email,
                'caste' => $request->caste,
                'mother_tongue' => $request->mother_tongue,
                'languages_known' => $request->languages_known ? json_encode($request->languages_known) : null,
                'profile_image' => $profileImage,
                'current_address' => $request->current_address,
                'permanent_address' => $request->permanent_address,
                'transport_enabled' => $request->transport_enabled ?? false,
                'transport_route' => $request->transport_enabled ? $request->transport['route'] : null,
                'transport_vehicle_number' => $request->transport_enabled ? $request->transport['vehicle_number'] : null,
                'transport_pickup_point' => $request->transport_enabled ? $request->transport['pickup_point'] : null,
                'hostel_enabled' => $request->hostel_enabled ?? false,
                'hostel_name' => $request->hostel_enabled ? $request->hostel['name'] : null,
                'hostel_room_no' => $request->hostel_enabled ? $request->hostel['room_no'] : null,
            ]);

            // Create Parent Information
            if ($request->filled('father.name') || $request->filled('mother.name')) {
                StudentParent::create([
                    'student_id' => $student->id,
                    'father_name' => $request->father['name'],
                    'father_email' => $request->father['email'],
                    'father_phone' => $request->father['phone'],
                    'father_occupation' => $request->father['occupation'],
                    'father_image' => $fatherImage,
                    'mother_name' => $request->mother['name'],
                    'mother_email' => $request->mother['email'],
                    'mother_phone' => $request->mother['phone'],
                    'mother_occupation' => $request->mother['occupation'],
                    'mother_image' => $motherImage,
                    'guardian_type' => $request->guardian_type,
                    'guardian_name' => $request->guardian['name'],
                    'guardian_relation' => $request->guardian['relation'],
                    'guardian_phone' => $request->guardian['phone'],
                    'guardian_email' => $request->guardian['email'],
                    'guardian_occupation' => $request->guardian['occupation'],
                    'guardian_address' => $request->guardian['address'],
                    'guardian_image' => $guardianImage,
                ]);
            }

            // Create Siblings
            if ($request->siblings) {
                foreach ($request->siblings as $sibling) {
                    StudentSibling::create([
                        'student_id' => $student->id,
                        'name' => $sibling['name'],
                        'roll_no' => $sibling['roll_no'],
                        'admission_no' => $sibling['admission_no'],
                        'class' => $sibling['class'],
                    ]);
                }
            }

            // Create Documents
            if ($medicalDocument || $transferCertificate) {
                StudentDocument::create([
                    'student_id' => $student->id,
                    'medical_condition_document' => $medicalDocument,
                    'transfer_certificate' => $transferCertificate,
                ]);
            }

            // Create Medical History
            StudentMedicalHistory::create([
                'student_id' => $student->id,
                'medical_condition' => $request->medical_condition,
                'allergies' => $request->allergies ? json_encode($request->allergies) : null,
                'medications' => $request->medications ? json_encode($request->medications) : null,
            ]);

            // Create Previous School Details
            if ($request->filled('previous_school.name')) {
                StudentPreviousSchool::create([
                    'student_id' => $student->id,
                    'school_name' => $request->previous_school['name'],
                    'address' => $request->previous_school['address'],
                ]);
            }

            // Create Other Details
            if ($request->filled('bank.name') || $request->filled('other_information')) {
                $student->update([
                    'bank_name' => $request->bank['name'],
                    'bank_branch' => $request->bank['branch'],
                    'bank_ifsc' => $request->bank['ifsc'],
                    'other_information' => $request->other_information,
                ]);
            }

            return $this->sendResponse(200, __('api.succ_student_created'), [
                'student_id' => $student->id,
                'admission_number' => $student->admission_number,
            ]);

        } catch (\Exception $e) {
            return $this->sendError(__('api.err_something_went_wrong'));
        }
    }

    // Helper method to get student data (similar to get_user_data)
    private function get_student_data($student)
    {
        return [
            'student_id' => $student->id,
            'admission_number' => $student->admission_number,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'email' => $student->email,
            'primary_contact_number' => $student->primary_contact_number,
            'class' => $student->class,
            'section' => $student->section,
            'status' => $student->status,
            'profile_image' => $student->profile_image,
        ];
    }
}