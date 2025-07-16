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

class StudentController extends Controller
{

    public function index(){
        // $students = Student::with(['parent', 'siblings', 'documents', 'medicalhistory', 'previousschool'])
        //     ->orderBy('created_at', 'desc')
        //     ->get();
        $studentData = Student::get();
        // $studentData = $students->map(function ($student) {
        //     return $this->get_student_data($student);
        // });

        return response()->json([
            'status' => 'true',
            'data' => $studentData,
           // 'recordsTotal' => $totalRecords,
            //'recordsFiltered' => $filteredRecords,
           // 'mytoken' => request()->headers->get('MyToken'),
        ], 200);
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
        $responses = [];

       
        $step='';
        $stepName1 = "Student Information";
        if($request->has('step')){
            $step=$request->step;
        };


         // Step 1: Validate and Create Student Record
        if($step=='step_1')
        {
            try {
                    $studentRules = 
                    [
                       
                        'admission_date' => ['required', 'date'],
                        'status' => ['required'],
                        'first_name' => ['required', 'string', 'max:50'],
                        'last_name' => ['required', 'string', 'max:50'],
                        'class' => ['required'],
                        'section' => ['required'],
                        'gender' => ['required', Rule::in(['male', 'female', 'other'])],
                        'date_of_birth' => ['required', 'date'],
                        'blood_group' => ['nullable'],
                        'house' => ['nullable'],
                        'religion' => ['nullable', Rule::in(['Christianity', 'Buddhism', 'Irreligion'])],
                        'category' => ['nullable', Rule::in(['OBC', 'BC'])],
                        'primary_contact_number' => ['required', 'string', 'max:15'],
                        'email' => ['required', 'email', 'max:255', 'unique:students,email'],
                        'caste' => ['nullable', 'string', 'max:100'],
                        'mother_tongue' => ['nullable', Rule::in(['English', 'Spanish'])],
                        'languages_known' => ['nullable', 'array'],
                        'profile_image' => ['nullable', 'file', 'image', 'max:4096'],
                        'current_address' => ['required', 'string', 'max:255'],
                        'permanent_address' => ['required', 'string', 'max:255'],
                        'transport_enabled' => ['boolean'],
                        'transport.route' => ['required_if:transport_enabled,true', Rule::in(['Newyork', 'Denver', 'Chicago'])],
                        'transport.vehicle_number' => ['required_if:transport_enabled,true', Rule::in(['AM 54548', 'AM 64528', 'AM 123548'])],
                        'transport.pickup_point' => ['required_if:transport_enabled,true', Rule::in(['Cincinatti', 'Illinois', 'Morgan'])],
                        'hostel_enabled' => ['boolean'],
                        'hostel.name' => ['required_if:hostel_enabled,true', Rule::in(['Phoenix Residence', 'Tranquil Haven', 'Radiant Towers', 'Nova Nest'])],
                        'hostel.room_no' => ['required_if:hostel_enabled,true', Rule::in(['20', '22', '24', '26'])],
                    ];
                    //$request->validate($studentRules);

                    $profileImage = $request->hasFile('profile_image') 
                        ? $this->upload_file('profile_image', 'student_profiles') 
                        : null;

                     //status : '0:inactive |1:active |2:incomplete |3:cancelled | 4:left | 5:deleted'
                    $student = Student::create([
                        'academic_year' => $request->academic_year,
                        'admission_no' => $request->admission_number,
                        'doj' => $request->admission_date,
                        'role_no' => $request->roll_number,
                        'status' => 2, 
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'class' => $request->class,
                        'gender' => $request->gender,
                        'dob' => $request->date_of_birth,
                        'blood_group' => $request->blood_group,
                        'house' => $request->house,
                        'religion' => $request->religion,
                        'category' => $request->category,
                        'caste' => $request->caste,
                        'phone' => $request->primaryContact,
                        'email' => $request->email,
                        'mother_tongue' => $request->mother_tongue,
                        'languages' => $request->languages ? json_encode($request->languages) : null,
                        'profile_image' => $profileImage,
                        
                    ]);

                    $responses[] = [
                        'step' => 'Step 1',
                        'name' => $stepName1,
                        'status' => 'success',
                        'message' => "Step 1: $stepName1 completed successfully",
                        'data' => [
                            'student_id' => $student->id,
                            'admission_number' => $student->admission_number,
                        ],
                    ];
                } catch (\Exception $e) {
                    return $this->sendError("Step 1: $stepName1 failed");
                }
        }


        // Step 2: Validate and Create Parent/Guardian Record
        if($step=='step_2')
        {
            $stepName2 = "Validate and Create Parent/Guardian Record";
            try {
                    $parentRules = [
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

                        'siblings' => ['nullable', 'array'],
                        'siblings.*.name' => ['required_with:siblings', 'string', 'max:100'],
                        'siblings.*.roll_no' => ['required_with:siblings', 'string', 'max:50'],
                        'siblings.*.admission_no' => ['required_with:siblings', 'string', 'max:50'],
                        'siblings.*.class' => ['required_with:siblings', Rule::in(['I', 'II', 'III', 'IV', 'V'])],
                    ];
                  
                    $request->validate($parentRules);

                    $fatherImage = $request->hasFile('father.image') 
                        ? $this->upload_file('father.image', 'parent_profiles') 
                        : null;
                    $motherImage = $request->hasFile('mother.image') 
                        ? $this->upload_file('mother.image', 'parent_profiles') 
                        : null;
                    $guardianImage = $request->hasFile('guardian.image') 
                        ? $this->upload_file('guardian.image', 'guardian_profiles') 
                        : null;

                    $parentCreated = false;

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
                        $parentCreated = true;
                    }

                   
              

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
                        $siblingsCreated = true;
                    }

                     $responses[] = [
                        'step' => 'Step 2',
                        'name' => $stepName2,
                        'status' => 'success',
                        'message' => "Step 2: $stepName2 completed " . ($parentCreated ? 'successfully' : 'skipped (no parent data provided)'),
                        'data' => [
                            'student_id' => $student->id,
                        ],
                    ];
                    
                } catch (\Exception $e) {
                    return $this->sendError("Step 3: $stepName3 failed");
                }
        }

        // setp 3 : Address====
        if($step=='step_3'){
            $studentAddressUpdate = Student::where('id', $request->studentId)->update([
                'current_address' => $request->current_address,
                'permanent_address' => $request->permanent_address,
                'transport_enabled' => $request->transport_enabled ?? false,
                'transport_route' => $request->transport_enabled ? ($request->transport['route'] ?? null) : null,
                'transport_vehicle_number' => $request->transport_enabled ? ($request->transport['vehicle_number'] ?? null) : null,
                'transport_pickup_point' => $request->transport_enabled ? ($request->transport['pickup_point'] ?? null) : null,
                'hostel_enabled' => $request->hostel_enabled ?? false,
                'hostel_name' => $request->hostel_enabled ? ($request->hostel['name'] ?? null) : null,
                'hostel_room_no' => $request->hostel_enabled ? ($request->hostel['room_no'] ?? null) : null,
            ]);
        }


        // Step 4 : Other information
        if($step=='step_4'){
                $stepName4 = "Validate and Create Medical History Record";
                try {
                    $medicalRules = [
                        'medical_condition' => ['required', Rule::in(['Good', 'Bad', 'Others'])],
                        'allergies' => ['nullable', 'array'],
                        'medications' => ['nullable', 'array'],
                    ];
                    $request->validate($medicalRules);

                    StudentMedicalHistory::create([
                        'student_id' => $student->id,
                        'medical_condition' => $request->medical_condition,
                        'allergies' => $request->allergies ? json_encode($request->allergies) : null,
                        'medications' => $request->medications ? json_encode($request->medications) : null,
                    ]);

                    $responses[] = [
                        'step' => 'Step 4',
                        'name' => $stepName4,
                        'status' => 'success',
                        'message' => "Step 4: $stepName4 completed successfully",
                        'data' => [
                            'student_id' => $student->id,
                        ],
                    ];
                } catch (\Exception $e) {
                    return $this->sendError("Step 5: $stepName5 failed");
                }
        }

        // Step 5: Validate and Create Document Records
        if($step=='step_5')
        {
            $stepName5 = "Validate and Create Document Records";
            try {
                    $documentRules = [
                        'medical_condition_document' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
                        'transfer_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
                    ];
                    $request->validate($documentRules);

                    $medicalDocument = $request->hasFile('medical_condition_document') 
                        ? $this->upload_file('medical_condition_document', 'student_documents') 
                        : null;
                    $transferCertificate = $request->hasFile('transfer_certificate') 
                        ? $this->upload_file('transfer_certificate', 'student_documents') 
                        : null;

                    $documentsCreated = false;
                    if ($medicalDocument || $transferCertificate) {
                        StudentDocument::create([
                            'student_id' => $student->id,
                            'medical_condition_document' => $medicalDocument,
                            'transfer_certificate' => $transferCertificate,
                        ]);
                        $documentsCreated = true;
                    }

                    $responses[] = [
                        'step' => 'Step 5',
                        'name' => $stepName4,
                        'status' => 'success',
                        'message' => "Step 5: $stepName4 completed " . ($documentsCreated ? 'successfully' : 'skipped (no documents provided)'),
                        'data' => [
                            'student_id' => $student->id,
                        ],
                    ];
                } catch (\Exception $e) {
                    return $this->sendError("Step 5: $stepName5 failed");
                }
        }

        // Step 6: Payment Details
        if($step=='step_6'){

            $stepName6 = "Validate and Update Other Details";
            try {
                $otherRules = [
                    'bank.name' => ['nullable', 'string', 'max:100'],
                    'bank.branch' => ['nullable', 'string', 'max:100'],
                    'bank.ifsc' => ['nullable', 'string', 'max:50'],
                    'other_information' => ['nullable', 'string', 'max:1000'],
                ];
                $request->validate($otherRules);

                $otherDetailsUpdated = false;
                if ($request->filled('bank.name') || $request->filled('other_information')) {
                    $student->update([
                        'bank_name' => $request->bank['name'],
                        'bank_branch' => $request->bank['branch'],
                        'bank_ifsc' => $request->bank['ifsc'],
                        'other_information' => $request->other_information,
                    ]);
                    $otherDetailsUpdated = true;
                }

                $responses[] = [
                    'step' => 'Step 6',
                    'name' => $stepName6,
                    'status' => 'success',
                    'message' => "Step 6: $stepName6 completed " . ($otherDetailsUpdated ? 'successfully' : 'skipped (no other details provided)'),
                    'data' => [
                        'student_id' => $student->id,
                    ],
                ];
            } catch (\Exception $e) {
                return $this->sendError("Step 6: $stepName6 failed");
            }


        }
        
        
        
        // Step 7: Finalize Process
        if($step=='step_7'){
             $stepName8 = "Finalize Process";
                try {
                        $responses[] = [
                            'step' => 'Step 8',
                            'name' => $stepName8,
                            'status' => 'success',
                            'message' => "Step 8: $stepName8 completed successfully",
                            'data' => [
                                'student_id' => $student->id,
                                'admission_number' => $student->admission_number,
                            ],
                        ];

                        return $this->sendResponse(200, __('api.succ_student_created'), [
                            'steps' => $responses,
                            'student_id' => $student->id,
                            'admission_number' => $student->admission_number,
                        ]);
                } catch (\Exception $e) {
                    return $this->sendError("Step 7: $stepName8 failed");
                }
            
        }

         return response()->json([
            'step' =>'step_1',
            'status' => 'error',
            'message' => 'Unable to complete the process!',
        ], 200);
       
    }

    protected function upload_file($field, $directory)
    {
        $file = request()->file($field);
        return $file->store($directory, 'public');
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