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
use Illuminate\Support\Facades\Log; // Import Log facade for error logging

class StudentController extends Controller
{
    /**
     * Display a listing of the students.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $studentData = Student::get();

        return response()->json([
            'status' => 'true',
            'data' => $studentData,
            'message' => 'Students retrieved successfully.',
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
        $student = Student::with([
            'parent',
            'siblings',
            'documents',
            'medicalHistory',
            'previousSchool'
        ])->find($id);

        if (!$student) {
            return response()->json([
                'status' => 'false',
                'message' => __('api.err_student_not_found'),
                'data' => null,
            ], 404);
        }

        $studentData = $this->get_student_data($student);
        return response()->json([
            'status' => 'true',
            'message' => __('api.succ_student_details'),
            'data' => $studentData,
        ], 200);
    }

    /**
     * Store a newly created student (Step 1).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $stepName1 = "Student Information";

        $studentRules = [
            'academic_year' => ['required', 'string', 'max:50'],
            'admission_number' => ['required', 'string', 'max:50', 'unique:students,admission_no'],
            'roll_number' => ['nullable', 'string', 'max:50'],
            'admission_date' => ['required', 'date'],
            'status' => ['required', Rule::in([0, 1, 2, 3, 4, 5])],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'class' => ['required', 'string', 'max:50'],
            'section' => ['required', 'string', 'max:50'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['required', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'house' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', Rule::in(['Christianity', 'Buddhism', 'Irreligion', 'Hinduism', 'Islam', 'Sikhism', 'Jainism'])],
            'category' => ['nullable', Rule::in(['OBC', 'BC', 'General', 'SC', 'ST'])],
            'primary_contact_number' => ['required', 'string', 'max:15'],
            'email' => ['required', 'email', 'max:255', 'unique:students,email'],
            'caste' => ['nullable', 'string', 'max:100'],
            'mother_tongue' => ['nullable', Rule::in(['English', 'Spanish', 'Hindi', 'Gujarati', 'Marathi'])],
            'languages_known' => ['nullable', 'array'],
            'profile_image' => ['nullable', 'file', 'image', 'max:4096'],
        ];

        $validator = Validator::make($request->all(), $studentRules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'false',
                'message' => __('api.err_validation_failed'),
                'errors' => $validator->errors(),
                'data' => null,
            ], 422);
        }

        try {
            $profileImage = $request->hasFile('profile_image')
                ? $this->upload_file('profile_image', 'student_profiles')
                : null;

            $student = Student::create([
                'academic_year' => $request->academic_year,
                'admission_no' => $request->admission_number,
                'doj' => $request->admission_date,
                'role_no' => $request->roll_number,
                'status' => 2, // Set to incomplete for multi-step registration
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'class' => $request->class,
                'section' => $request->section,
                'gender' => $request->gender,
                'dob' => $request->date_of_birth,
                'blood_group' => $request->blood_group,
                'house' => $request->house,
                'religion' => $request->religion,
                'category' => $request->category,
                'caste' => $request->caste,
                'phone' => $request->primary_contact_number,
                'email' => $request->email,
                'mother_tongue' => $request->mother_tongue,
                'languages' => $request->languages_known ? json_encode($request->languages_known) : null,
                'profile_image' => $profileImage,
                'current_address' => null,
                'permanent_address' => null,
                'transport_enabled' => false,
                'transport_route' => null,
                'transport_vehicle_number' => null,
                'transport_pickup_point' => null,
                'hostel_enabled' => false,
                'hostel_name' => null,
                'hostel_room_no' => null,
                'bank_name' => null,
                'bank_branch' => null,
                'bank_ifsc' => null,
                'other_information' => null,
            ]);

            return response()->json([
                'status' => 'true',
                'message' => "Step 1: $stepName1 completed successfully",
                'data' => [
                    'student_id' => $student->id,
                    'admission_number' => $student->admission_no,
                    'status' => $student->status,
                ],
            ], 201); // 201 Created for successful resource creation

        } catch (\Exception $e) {
            Log::error("Student creation (Step 1) failed: " . $e->getMessage());
            return response()->json([
                'status' => 'false',
                'message' => "Step 1: $stepName1 failed. " . $e->getMessage(),
                'data' => null,
            ], 500); // 500 Internal Server Error for unexpected exceptions
        }
    }

    /**
     * Update a specific student's details based on steps.
     * This method handles steps 2 through 7.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id The student ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'status' => 'false',
                'message' => __('api.err_student_not_found'),
                'data' => null,
            ], 404);
        }

        $step = $request->input('step');

        switch ($step) {
            case 'step_2': // Parent/Guardian and Sibling Information
                $stepName2 = "Parent/Guardian and Sibling Record";
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
                    'siblings.*.class' => ['required_with:siblings', Rule::in(['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'])],
                ];

                $validator = Validator::make($request->all(), $parentRules);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'false',
                        'message' => __('api.err_validation_failed'),
                        'errors' => $validator->errors(),
                        'data' => null,
                    ], 422);
                }

                try {
                    $fatherImage = $request->hasFile('father.image')
                        ? $this->upload_file('father.image', 'parent_profiles')
                        : null;
                    $motherImage = $request->hasFile('mother.image')
                        ? $this->upload_file('mother.image', 'parent_profiles')
                        : null;
                    $guardianImage = $request->hasFile('guardian.image')
                        ? $this->upload_file('guardian.image', 'guardian_profiles')
                        : null;

                    $parentData = [
                        'student_id' => $student->id,
                        'father_name' => $request->input('father.name'),
                        'father_email' => $request->input('father.email'),
                        'father_phone' => $request->input('father.phone'),
                        'father_occupation' => $request->input('father.occupation'),
                        'father_image' => $fatherImage,
                        'mother_name' => $request->input('mother.name'),
                        'mother_email' => $request->input('mother.email'),
                        'mother_phone' => $request->input('mother.phone'),
                        'mother_occupation' => $request->input('mother.occupation'),
                        'mother_image' => $motherImage,
                        'guardian_type' => $request->input('guardian_type'),
                        'guardian_name' => $request->input('guardian.name'),
                        'guardian_relation' => $request->input('guardian.relation'),
                        'guardian_phone' => $request->input('guardian.phone'),
                        'guardian_email' => $request->input('guardian.email'),
                        'guardian_occupation' => $request->input('guardian.occupation'),
                        'guardian_address' => $request->input('guardian.address'),
                        'guardian_image' => $guardianImage,
                    ];

                    StudentParent::updateOrCreate(['student_id' => $student->id], $parentData);

                    if ($request->has('siblings')) {
                        StudentSibling::where('student_id', $student->id)->delete();
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

                    return response()->json([
                        'status' => 'true',
                        'message' => "Step 2: $stepName2 completed successfully",
                        'data' => [
                            'student_id' => $student->id,
                        ],
                    ], 200);

                } catch (\Exception $e) {
                    Log::error("Student update (Step 2) failed: " . $e->getMessage());
                    return response()->json([
                        'status' => 'false',
                        'message' => "Step 2: $stepName2 failed. " . $e->getMessage(),
                        'data' => null,
                    ], 500);
                }

            case 'step_3': // Address and Transport/Hostel
                $stepName3 = "Address and Transport/Hostel Details";
                $addressRules = [
                    'current_address' => ['required', 'string', 'max:255'],
                    'permanent_address' => ['required', 'string', 'max:255'],
                    'transport_enabled' => ['boolean'],
                    'transport.route' => ['required_if:transport_enabled,true', Rule::in(['Newyork', 'Denver', 'Chicago', 'London', 'Paris', 'Tokyo'])],
                    'transport.vehicle_number' => ['required_if:transport_enabled,true', 'string', 'max:50'],
                    'transport.pickup_point' => ['required_if:transport_enabled,true', Rule::in(['Cincinatti', 'Illinois', 'Morgan', 'Brooklyn', 'Manhattan', 'Shinjuku'])],
                    'hostel_enabled' => ['boolean'],
                    'hostel.name' => ['required_if:hostel_enabled,true', Rule::in(['Phoenix Residence', 'Tranquil Haven', 'Radiant Towers', 'Nova Nest', 'Starfall Dorms', 'Whispering Pines'])],
                    'hostel.room_no' => ['required_if:hostel_enabled,true', 'string', 'max:20'],
                ];

                $validator = Validator::make($request->all(), $addressRules);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'false',
                        'message' => __('api.err_validation_failed'),
                        'errors' => $validator->errors(),
                        'data' => null,
                    ], 422);
                }

                try {
                    $student->update([
                        'current_address' => $request->current_address,
                        'permanent_address' => $request->permanent_address,
                        'transport_enabled' => $request->boolean('transport_enabled'),
                        'transport_route' => $request->boolean('transport_enabled') ? ($request->input('transport.route')) : null,
                        'transport_vehicle_number' => $request->boolean('transport_enabled') ? ($request->input('transport.vehicle_number')) : null,
                        'transport_pickup_point' => $request->boolean('transport_enabled') ? ($request->input('transport.pickup_point')) : null,
                        'hostel_enabled' => $request->boolean('hostel_enabled'),
                        'hostel_name' => $request->boolean('hostel_enabled') ? ($request->input('hostel.name')) : null,
                        'hostel_room_no' => $request->boolean('hostel_enabled') ? ($request->input('hostel.room_no')) : null,
                    ]);

                    return response()->json([
                        'status' => 'true',
                        'message' => "Step 3: $stepName3 completed successfully",
                        'data' => [
                            'student_id' => $student->id,
                        ],
                    ], 200);

                } catch (\Exception $e) {
                    Log::error("Student update (Step 3) failed: " . $e->getMessage());
                    return response()->json([
                        'status' => 'false',
                        'message' => "Step 3: $stepName3 failed. " . $e->getMessage(),
                        'data' => null,
                    ], 500);
                }

            case 'step_4': // Medical History
                $stepName4 = "Medical History Record";
                $medicalRules = [
                    'medical_condition' => ['required', Rule::in(['Good', 'Bad', 'Others'])],
                    'allergies' => ['nullable', 'array'],
                    'medications' => ['nullable', 'array'],
                ];

                $validator = Validator::make($request->all(), $medicalRules);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'false',
                        'message' => __('api.err_validation_failed'),
                        'errors' => $validator->errors(),
                        'data' => null,
                    ], 422);
                }

                try {
                    StudentMedicalHistory::updateOrCreate(
                        ['student_id' => $student->id],
                        [
                            'medical_condition' => $request->medical_condition,
                            'allergies' => $request->allergies ? json_encode($request->allergies) : null,
                            'medications' => $request->medications ? json_encode($request->medications) : null,
                        ]
                    );

                    return response()->json([
                        'status' => 'true',
                        'message' => "Step 4: $stepName4 completed successfully",
                        'data' => [
                            'student_id' => $student->id,
                        ],
                    ], 200);

                } catch (\Exception $e) {
                    Log::error("Student update (Step 4) failed: " . $e->getMessage());
                    return response()->json([
                        'status' => 'false',
                        'message' => "Step 4: $stepName4 failed. " . $e->getMessage(),
                        'data' => null,
                    ], 500);
                }

            case 'step_5': // Documents
                $stepName5 = "Document Records";
                $documentRules = [
                    'medical_condition_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
                    'transfer_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
                ];

                $validator = Validator::make($request->all(), $documentRules);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'false',
                        'message' => __('api.err_validation_failed'),
                        'errors' => $validator->errors(),
                        'data' => null,
                    ], 422);
                }

                try {
                    $medicalDocument = $request->hasFile('medical_condition_document')
                        ? $this->upload_file('medical_condition_document', 'student_documents')
                        : null;
                    $transferCertificate = $request->hasFile('transfer_certificate')
                        ? $this->upload_file('transfer_certificate', 'student_documents')
                        : null;

                    StudentDocument::updateOrCreate(
                        ['student_id' => $student->id],
                        [
                            'medical_condition_document' => $medicalDocument,
                            'transfer_certificate' => $transferCertificate,
                        ]
                    );

                    return response()->json([
                        'status' => 'true',
                        'message' => "Step 5: $stepName5 completed successfully",
                        'data' => [
                            'student_id' => $student->id,
                        ],
                    ], 200);

                } catch (\Exception $e) {
                    Log::error("Student update (Step 5) failed: " . $e->getMessage());
                    return response()->json([
                        'status' => 'false',
                        'message' => "Step 5: $stepName5 failed. " . $e->getMessage(),
                        'data' => null,
                    ], 500);
                }

            case 'step_6': // Payment Details / Other Information
                $stepName6 = "Payment Details and Other Information";
                $otherRules = [
                    'bank.name' => ['nullable', 'string', 'max:100'],
                    'bank.branch' => ['nullable', 'string', 'max:100'],
                    'bank.ifsc' => ['nullable', 'string', 'max:50'],
                    'other_information' => ['nullable', 'string', 'max:1000'],
                ];

                $validator = Validator::make($request->all(), $otherRules);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'false',
                        'message' => __('api.err_validation_failed'),
                        'errors' => $validator->errors(),
                        'data' => null,
                    ], 422);
                }

                try {
                    $student->update([
                        'bank_name' => $request->input('bank.name'),
                        'bank_branch' => $request->input('bank.branch'),
                        'bank_ifsc' => $request->input('bank.ifsc'),
                        'other_information' => $request->other_information,
                    ]);

                    return response()->json([
                        'status' => 'true',
                        'message' => "Step 6: $stepName6 completed successfully",
                        'data' => [
                            'student_id' => $student->id,
                        ],
                    ], 200);

                } catch (\Exception $e) {
                    Log::error("Student update (Step 6) failed: " . $e->getMessage());
                    return response()->json([
                        'status' => 'false',
                        'message' => "Step 6: $stepName6 failed. " . $e->getMessage(),
                        'data' => null,
                    ], 500);
                }

            case 'step_7': // Finalize Process
                $stepName7 = "Finalize Process";
                try {
                    $student->update(['status' => 1]); // Set student status to active/complete

                    return response()->json([
                        'status' => 'true',
                        'message' => __('api.succ_student_created'), // Assuming this message implies final success
                        'data' => [
                            'student_id' => $student->id,
                            'admission_number' => $student->admission_no,
                            'final_status' => $student->status,
                        ],
                    ], 200);
                } catch (\Exception $e) {
                    Log::error("Student update (Step 7) failed: " . $e->getMessage());
                    return response()->json([
                        'status' => 'false',
                        'message' => "Step 7: $stepName7 failed. " . $e->getMessage(),
                        'data' => null,
                    ], 500);
                }

            default:
                return response()->json([
                    'status' => 'false',
                    'message' => "Invalid or missing 'step' parameter.",
                    'data' => null,
                ], 400); // Bad Request for invalid step
        }
    }

    /**
     * Helper method to upload files.
     *
     * @param string $field The request field name for the file.
     * @param string $directory The storage directory (within 'public').
     * @return string|null The stored file path or null.
     */
    protected function upload_file($field, $directory)
    {
        if (request()->hasFile($field)) {
            $file = request()->file($field);
            return $file->store($directory, 'public');
        }
        return null;
    }

    /**
     * Helper method to format student data for responses.
     *
     * @param \App\Models\Student $student
     * @return array
     */
    private function get_student_data($student)
    {
        $languages = $student->languages ? json_decode($student->languages, true) : [];

        return [
            'student_id' => $student->id,
            'admission_number' => $student->admission_no,
            'academic_year' => $student->academic_year,
            'admission_date' => $student->doj,
            'roll_number' => $student->role_no,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'email' => $student->email,
            'primary_contact_number' => $student->phone,
            'class' => $student->class,
            'section' => $student->section,
            'gender' => $student->gender,
            'date_of_birth' => $student->dob,
            'blood_group' => $student->blood_group,
            'house' => $student->house,
            'religion' => $student->religion,
            'category' => $student->category,
            'caste' => $student->caste,
            'mother_tongue' => $student->mother_tongue,
            'languages_known' => $languages,
            'profile_image' => $student->profile_image ? asset('storage/' . $student->profile_image) : null,
            'current_address' => $student->current_address,
            'permanent_address' => $student->permanent_address,
            'transport_enabled' => (bool) $student->transport_enabled,
            'transport' => [
                'route' => $student->transport_route,
                'vehicle_number' => $student->transport_vehicle_number,
                'pickup_point' => $student->transport_pickup_point,
            ],
            'hostel_enabled' => (bool) $student->hostel_enabled,
            'hostel' => [
                'name' => $student->hostel_name,
                'room_no' => $student->hostel_room_no,
            ],
            'bank_details' => [
                'name' => $student->bank_name,
                'branch' => $student->bank_branch,
                'ifsc' => $student->bank_ifsc,
            ],
            'other_information' => $student->other_information,
            'status' => $student->status,
            'parent' => $student->parent ? [
                'father_name' => $student->parent->father_name,
                'father_email' => $student->parent->father_email,
                'father_phone' => $student->parent->father_phone,
                'father_occupation' => $student->parent->father_occupation,
                'father_image' => $student->parent->father_image ? asset('storage/' . $student->parent->father_image) : null,
                'mother_name' => $student->parent->mother_name,
                'mother_email' => $student->parent->mother_email,
                'mother_phone' => $student->parent->mother_phone,
                'mother_occupation' => $student->parent->mother_occupation,
                'mother_image' => $student->parent->mother_image ? asset('storage/' . $student->parent->mother_image) : null,
                'guardian_type' => $student->parent->guardian_type,
                'guardian_name' => $student->parent->guardian_name,
                'guardian_relation' => $student->parent->guardian_relation,
                'guardian_phone' => $student->parent->guardian_phone,
                'guardian_email' => $student->parent->guardian_email,
                'guardian_occupation' => $student->parent->guardian_occupation,
                'guardian_address' => $student->parent->guardian_address,
                'guardian_image' => $student->parent->guardian_image ? asset('storage/' . $student->parent->guardian_image) : null,
            ] : null,
            'siblings' => $student->siblings->map(function ($sibling) {
                return [
                    'name' => $sibling->name,
                    'roll_no' => $sibling->roll_no,
                    'admission_no' => $sibling->admission_no,
                    'class' => $sibling->class,
                ];
            }),
            'documents' => $student->documents ? [
                'medical_condition_document' => $student->documents->medical_condition_document ? asset('storage/' . $student->documents->medical_condition_document) : null,
                'transfer_certificate' => $student->documents->transfer_certificate ? asset('storage/' . $student->documents->transfer_certificate) : null,
            ] : null,
            'medical_history' => $student->medicalHistory ? [
                'medical_condition' => $student->medicalHistory->medical_condition,
                'allergies' => $student->medicalHistory->allergies ? json_decode($student->medicalHistory->allergies, true) : [],
                'medications' => $student->medicalHistory->medications ? json_decode($student->medicalHistory->medications, true) : [],
            ] : null,
            'previous_school' => $student->previousSchool,
        ];
    }
}

