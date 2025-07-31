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
use App\Models\StudentPreviousEducation; // Ensure this model exists and is used if previous school data is handled
use Illuminate\Support\Facades\Log; // Import Log facade for error logging
use Illuminate\Support\Facades\Storage; // Import Storage facade for file uploads
use Auth;
use Illuminate\Support\Str;
class StudentController extends Controller
{
    /**
     * Display a listing of the students.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // For a simple list, eager loading all relations might be too heavy.
        // If you need more detailed data for the index, uncomment the eager loading below.
        // $students = Student::with(['parent', 'siblings', 'documents', 'medicalHistory', 'previousSchool'])
        //     ->orderBy('created_at', 'desc')
        //     ->get();

        // If you just need basic student data, the current line is fine.
        $studentData = Student::get();

        return response()->json([
            'status' => 'success',
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
        // Eager load all related models for a complete student profile.
        $student = Student::with([
            'parent',
            'siblings',
            'documents',
            'medicalHistory',
            'previousSchool' // Ensure this relationship is defined in your Student model
        ])->find($id);

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => __('api.err_student_not_found'),
                'data' => null,
            ], 404); // 404 Not Found
        }

        $studentData = $this->get_student_data($student);
        return response()->json([
            'status' => 'success',
            'message' => __('api.succ_student_details'),
            'data' => $studentData,
        ], 200); // 200 OK
    }

    /**
     * Store a newly created student (Step 1).
     * This method initializes the student record.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $stepName1 = "Student Information";

        // Validation rules for Step 1
        $studentRules = [
            'academic_year' => ['nullable', 'string', 'max:50'],
            'admission_no' => ['required', 'string', 'max:50', 'unique:students,admission_no'],
            'roll_number' => ['nullable', 'string', 'max:50'],
            'admission_date' => ['required', 'date'],
           // 'status' => ['required', Rule::in([0, 1, 2, 3, 4, 5])], // Assuming numeric status codes
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'class' => ['required', 'max:50'], // Changed to string, adjust if it's an ID
           // 'section' => ['required', 'string', 'max:50'], // Added, as it's a required field in your model for 'show'
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'dob' => ['required', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'house' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', Rule::in(['Christianity', 'Buddhism', 'Irreligion', 'Hinduism', 'Islam', 'Sikhism', 'Jainism'])], // Added more common religions
            'category' => ['nullable', Rule::in(['OBC', 'BC', 'General', 'SC', 'ST'])], // Added more common categories
            'primary_contact' => ['required', 'string', 'max:15'], // Changed from 'phone' to match request
            'email' => ['nullable', 'email', 'max:255', 'unique:students,email'],
            'caste' => ['nullable', 'string', 'max:100'],
            'mother_tongue' => ['nullable', Rule::in(['English', 'Spanish', 'Hindi', 'Gujarati', 'Marathi'])], // Added more common languages
            'languages_known' => ['nullable', 'array'],
            'profile_image' => ['nullable', 'file', 'image', 'max:4096'],
        ];

        // Perform validation
        $validator = Validator::make($request->all(), $studentRules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Validation failed',
                'errors' => $validator->errors(),
                'data' => null,
            ], 422); // 422 Unprocessable Entity for validation errors
        }

        try {
            $stcode=getNewSerialNo('student');
            // Handle profile image upload
            $profileImage = $request->hasFile('profile_image')
                ? $this->upload_file('profile_image', $stcode)
                : null;

            // Create a new Student instance and fill its attributes
            $student = new Student();
            $student->code = $stcode;
            $student->academic_year = $request->academic_year;
            $student->admission_no = $request->admission_number;
            $student->doj = $request->admission_date;
            $student->role_no = $request->roll_number;
            $student->status = 2; // Set to incomplete for multi-step registration
            $student->first_name = $request->first_name;
            $student->last_name = $request->last_name;
            $student->class_id = $request->class_id;
            $student->gender = $request->gender;
            $student->dob = $request->dob;
            $student->blood_group = $request->blood_group;
            $student->house = $request->house;
            $student->religion = $request->religion;
            $student->category = $request->category;
            $student->caste = $request->caste;
            $student->phone = $request->primary_contact; // Ensure this matches your column name
            $student->email = $request->email;
            $student->mother_tongue = $request->mother_tongue;
            $student->languages = $request->languages_known ? json_encode($request->languages_known) : null;
            $student->profile_image = $profileImage;
            $student->added_by=$request->user_id ?? 0;
            $student->docfolder_name = getNewSerialNo('student');
            $student->save();

            increaseSerialNo('student');
            // Return success response for Step 1
            return response()->json([
                'status' => 'success',
                'message' => "Step 1: $stepName1 completed successfully",
                'student_id' => $student->id,
                'data' => $student,
            ], 201); // 201 Created for successful resource creation

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error("Student creation (Step 1) failed: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
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
     * @param int $id The student ID to update
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Retrieve the student instance
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' =>'Student Not found',
                'data' => null,
            ], 404); // 404 Not Found
        }

        $step = $request->input('step'); // Get the step from the request

        switch ($step) {
            case 'step_1': // update student information
                $stepName1 = "Student Information update";
                $studentRules = [
                    'academic_year' => ['nullable', 'string', 'max:50'],
                    'admission_no' => ['required', 'string', 'max:50', 'unique:students,admission_no'],
                    'roll_number' => ['nullable', 'string', 'max:50'],
                    'admission_date' => ['required', 'date'],
                // 'status' => ['required', Rule::in([0, 1, 2, 3, 4, 5])], // Assuming numeric status codes
                    'first_name' => ['required', 'string', 'max:50'],
                    'last_name' => ['required', 'string', 'max:50'],
                    'class' => ['required', 'max:50'], // Changed to string, adjust if it's an ID
                // 'section' => ['required', 'string', 'max:50'], // Added, as it's a required field in your model for 'show'
                    'gender' => ['required', Rule::in(['male', 'female', 'other'])],
                    'dob' => ['required', 'date'],
                    'blood_group' => ['nullable', 'string', 'max:10'],
                    'house' => ['nullable', 'string', 'max:100'],
                    'religion' => ['nullable', Rule::in(['Christianity', 'Buddhism', 'Irreligion', 'Hinduism', 'Islam', 'Sikhism', 'Jainism'])], // Added more common religions
                    'category' => ['nullable', Rule::in(['OBC', 'BC', 'General', 'SC', 'ST'])], // Added more common categories
                    'primary_contact' => ['required', 'string', 'max:15'], // Changed from 'phone' to match request
                    'email' => ['nullable', 'email', 'max:255', 'unique:students,email,'.$id],
                    'caste' => ['nullable', 'string', 'max:100'],
                    'mother_tongue' => ['nullable', Rule::in(['English', 'Spanish', 'Hindi', 'Gujarati', 'Marathi'])], // Added more common languages
                    'languages_known' => ['nullable', 'array'],
                    'profile_image' => ['nullable', 'file', 'image', 'max:4096'],
                ];

                $validator = Validator::make($request->all(), $studentRules);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data Validation failed',
                        'errors' => $validator->errors(),
                        'data' => null,
                    ], 422);
                }

                $student =Student::find($id);

                $student->academic_year = $request->academic_year;
                $student->admission_no = $request->admission_number;
                $student->doj = $request->admission_date;
                $student->role_no = $request->roll_number;
                $student->status = 2; // Set to incomplete for multi-step registration
                $student->first_name = $request->first_name;
                $student->last_name = $request->last_name;
                $student->class_id = $request->class_id;
                $student->gender = $request->gender;
                $student->dob = $request->dob;
                $student->blood_group = $request->blood_group;
                $student->house = $request->house;
                $student->religion = $request->religion;
                $student->category = $request->category;
                $student->caste = $request->caste;
                $student->phone = $request->primary_contact; // Ensure this matches your column name
                $student->email = $request->email;
                $student->mother_tongue = $request->mother_tongue;
                $student->languages = $request->languages_known ? json_encode($request->languages_known) : null;
                $student->added_by=$request->user_id ?? 0;

                // Handle profile image upload
                if($request->hasFile('profile_image')){
                    $profileImage = $this->upload_file('profile_image', $student->docfolder_name,$student->profile_image);
                    $student->profile_image = $profileImage;
                }   

            // Create a new Student instance and fill its attributes
                $student->save();

                return response()->json([
                    'status' => 'success',
                    'message' => "Step 1: $stepName1 completed successfully",
                    'student_id' => $student->id,
                    'data' => $student,
                ], 201); // 201 Created for successful resource creation

           case 'step_2': // Parent/Guardian and Sibling Information
                $stepName2 = "Parent/Guardian and Sibling Record";

                $parentRules = [
                    'father_name' => ['required', 'string', 'max:100'],
                    'father_phone' => ['nullable', 'string', 'max:15'],
                    'father_email' => ['nullable', 'email'],
                    'father_aadhar' => ['nullable', 'string', 'max:12'],
                    'father_occupation' => ['nullable', 'string', 'max:100'],
                    'father_qualification' => ['nullable', 'string', 'max:100'],
                    'father_aadhar_image' => ['nullable', 'file', 'image', 'max:4096'],
                    'father_image' => ['nullable', 'file', 'image', 'max:4096'],
                    'father_itr_no' => ['nullable', 'string', 'max:50'],
                    'father_itr_file' => ['nullable', 'file', 'max:4096'],
                    'mother_name' => ['required', 'string', 'max:100'],
                    'mother_email' => ['nullable', 'email'],
                    'mother_phone' => ['nullable', 'string', 'max:15'],
                    'mother_aadhar' => ['nullable', 'string', 'max:12'],
                    'mother_occupation' => ['nullable', 'string', 'max:100'],
                    'mother_qualification' => ['nullable', 'string', 'max:100'],
                    'mother_aadhar_image' => ['nullable', 'file', 'image', 'max:4096'],
                    'mother_image' => ['nullable', 'file', 'image', 'max:4096'],
                    'mother_itr_no' => ['nullable', 'string', 'max:50'],
                    'mother_itr_file' => ['nullable', 'file', 'max:4096'],
                    'sibling_same_school' => ['required', 'string', 'in:yes,no'],
                 //   'sibling_student_ids' => ['nullable', 'array'],
                 //   'sibling_student_ids.*' => ['string', 'max:50'],
                    'guardians' => ['nullable', 'array'],
                    'guardians.*.name' => ['required', 'string', 'max:100'],
                    'guardians.*.phone' => ['nullable', 'string', 'max:15'],
                    'guardians.*.email' => ['nullable', 'email'],
                    'guardians.*.aadhar' => ['nullable', 'string', 'max:12'],
                    'guardians.*.occupation' => ['nullable', 'string', 'max:100'],
                    'guardians.*.qualification' => ['nullable', 'string', 'max:100'],
                    'guardians.*.relation' => ['required', 'string', 'max:50'],
                    'guardians.*.profile_image' => ['nullable', 'file', 'image', 'max:4096'],
                    'guardians.*.aadhar_image' => ['nullable', 'file', 'image', 'max:4096'],
                    'guardians.*.itr_no' => ['nullable', 'string', 'max:50'],
                    'guardians.*.itr_file' => ['nullable', 'file', 'max:4096'],
                ];

                $validator = Validator::make($request->all(), $parentRules);

                if ($validator->fails())
                {
                    return response()->json([
                        'status' => 'error',
                        'message' => __('api.err_validation_failed'),
                        'errors' => $validator->errors(),
                        'data' => null,
                    ], 422);
                }

                try {
                    // Verify student exists
                    if (!$request->input('student_id')) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Student ID is required',
                            'data' => null,
                        ], 400);
                    }

                    $student = Student::findOrFail($request->input('student_id'));
                    $fatherId = null;
                    $motherId = null;
                    $guardiansToAttach = [];

                    // Handle Father
                    $fatherDetails = [
                        'relation' => 'father',
                        'name' => $request->input('father_name'),
                        'phone' => $request->input('father_phone'),
                        'email' => $request->input('father_email'),
                        'aadhar' => $request->input('father_aadhar'),
                        'qualification' => $request->input('father_qualification') ?? '',
                        'occupation' => $request->input('father_occupation') ?? '',
                        'itr_no' => $request->input('father_itr_no') ?? null,
                        'docfolder_name' => $student->docfolder_name,
                    ];

                    if ($request->hasFile('father_aadhar_image')) {
                        $fatherDetails['aadhar_file'] = $this->upload_file('father_aadhar_image', $student->docfolder_name);
                        //$request->file('father_aadhar_image')->store('relation_profiles');
                    }
                    if ($request->hasFile('father_image')) {
                        $fatherDetails['image'] = $this->upload_file('father_image', $student->docfolder_name);
                    }
                    if ($request->hasFile('father_itr_file')) {
                        $fatherDetails['itr_file'] = $this->upload_file('father_itr_file', $student->docfolder_name);
                    }

                    $father = StudentParent::firstOrNew([
                        'phone' => $fatherDetails['phone'],
                        'aadhar' => $fatherDetails['aadhar'],
                    ]);

                    if (!$father->exists && $fatherDetails['phone']) {
                        $father = StudentParent::firstOrNew(['phone' => $fatherDetails['phone']]);
                    } elseif (!$father->exists && $fatherDetails['aadhar']) {
                        $father = StudentParent::firstOrNew(['aadhar' => $fatherDetails['aadhar']]);
                    }

                    $father->fill($fatherDetails)->save();
                    $fatherId = $father->id;

                    // Handle Mother
                    $motherDetails = [
                        'relation' => 'mother',
                        'name' => $request->input('mother_name'),
                        'phone' => $request->input('mother_phone'),
                        'email' => $request->input('mother_email'),
                        'aadhar' => $request->input('mother_aadhar'),
                        'qualification' => $request->input('mother_qualification') ?? '',
                        'occupation' => $request->input('mother_occupation') ?? '',
                        'itr_no' => $request->input('mother_itr_no') ?? null,
                        'docfolder_name' => $student->docfolder_name,
                    ];

                    if ($request->hasFile('mother_aadhar_image')) {
                        $motherDetails['aadhar_file'] = $this->upload_file('mother_aadhar_image', $student->docfolder_name);
                        //$request->file('mother_aadhar_image')->store('relation_profiles');
                    }
                    if ($request->hasFile('mother_image')) {
                        $motherDetails['image'] = $this->upload_file('mother_image', $student->docfolder_name);
                    }

                    if ($request->hasFile('mother_itr_file')) {
                        $motherDetails['itr_file'] = $this->upload_file('mother_itr_file', $student->docfolder_name);
                    }

                    $mother = StudentParent::firstOrNew([
                        'phone' => $motherDetails['phone'],
                        'aadhar' => $motherDetails['aadhar'],
                    ]);

                    if (!$mother->exists && $motherDetails['phone']) {
                        $mother = StudentParent::firstOrNew(['phone' => $motherDetails['phone']]);
                    } elseif (!$mother->exists && $motherDetails['aadhar']) {
                        $mother = StudentParent::firstOrNew(['aadhar' => $motherDetails['aadhar']]);
                    }

                    $mother->fill($motherDetails)->save();
                    $motherId = $mother->id;

                    // Handle Guardians
                    foreach ($request->input('guardians', []) as $index => $guardianData) {
                        $guardianDetails = [
                            'relation' => $guardianData['relation'],
                            'name' => $guardianData['name'],
                            'phone' => $guardianData['phone'] ?? null,
                            'email' => $guardianData['email'] ?? null,
                            'aadhar' => $guardianData['aadhar'] ?? null,
                            'qualification' => $guardianData['qualification'] ?? '',
                            'occupation' => $guardianData['occupation'] ?? null,
                            'itr_no' => $guardianData['itr_no'] ?? null,
                            'docfolder_name' => $student->docfolder_name,
                        ];

                        if ($request->hasFile("guardians.{$index}.profile_image")) {
                            $guardianDetails['image'] = $this->upload_file("guardians.{$index}.profile_image", $student->docfolder_name);
                            //$request->file("guardians.{$index}.profile_image")->store('relation_profiles');
                        }
                        if ($request->hasFile("guardians.{$index}.aadhar_image")) {
                            $guardianDetails['aadhar_file'] = $this->upload_file("guardians.{$index}.aadhar_image", $student->docfolder_name);
                            //$request->file("guardians.{$index}.aadhar_image")->store('relation_profiles');
                        }
                        if ($request->hasFile("guardians.{$index}.itr_file")) {
                            $guardianDetails['itr_file'] = $this->upload_file("guardians.{$index}.itr_file", $student->docfolder_name);
                            //$request->file("guardians.{$index}.itr_file")->store('relation_profiles');
                        }

                        $guardian = StudentParent::firstOrNew([
                            'phone' => $guardianDetails['phone'],
                            'aadhar' => $guardianDetails['aadhar'],
                        ]);

                        if (!$guardian->exists && $guardianDetails['phone']) {
                            $guardian = StudentParent::firstOrNew(['phone' => $guardianDetails['phone']]);
                        } elseif (!$guardian->exists && $guardianDetails['aadhar']) {
                            $guardian = StudentParent::firstOrNew(['aadhar' => $guardianDetails['aadhar']]);
                        }

                        $guardian->fill($guardianDetails)->save();
                        // $guardiansToAttach[$guardian->id] = [
                        //     'guardian_role' => $guardianData['relation'],
                        // ];
                    }

                    // Update student with father and mother IDs
                    $student->update([
                        'father_id' => $fatherId,
                        'mother_id' => $motherId,
                    ]);

                    // Sync guardians
                    //$student->guardians()->sync($guardiansToAttach);

                    // Handle siblings
                    StudentSibling::where('student_id', $student->id)->delete();
                    $siblingsToCreate = [];
                    if ($request->input('sibling_same_school') === 'yes') {
                        foreach ($request->input('sibling_student_ids', []) as $siblingId) {
                            $siblingsToCreate[] = [
                                'sibling_student_id' => $siblingId,
                                //'same_school' => true,
                                'student_id' =>$student->id,

                            ];
                        }
                    }

                    foreach ($siblingsToCreate as $sibling) {
                        StudentSibling::create(array_merge(['student_id' => $student->id], $sibling));
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
                        'status' => 'error',
                        'message' => "Step 2: $stepName2 failed. " . $e->getMessage(),
                        'data' => null,
                    ], 500);
                }

         
            case 'step_3': // Address and Transport/Hostel
                $stepName3 = "Address and Transport/Hostel Details";
                
                // Validation rules for the address fields
                $addressRules = [
                    'student_id' => ['required', 'numeric', 'exists:students,id'],
                    'permanent_address.address' => ['required', 'string', 'max:255'],
                    'permanent_address.area' => ['required', 'string', 'max:100'],
                    'permanent_address.landmark' => ['required', 'string', 'max:100'],
                    'permanent_address.city' => ['required', 'string', 'max:50'],
                    'permanent_address.state' => ['required', 'string', 'max:50'],
                    'permanent_address.pincode' => ['required', 'string', 'max:10', 'regex:/^\d{6}$/'],
                    'current_address.address' => ['required', 'string', 'max:255'],
                    'current_address.area' => ['required', 'string', 'max:100'],
                    'current_address.landmark' => ['required', 'string', 'max:100'],
                    'current_address.city' => ['required', 'string', 'max:50'],
                    'current_address.state' => ['required', 'string', 'max:50'],
                    'current_address.pincode' => ['required', 'string', 'max:10', 'regex:/^\d{6}$/'],
                ];

                // Custom validation messages
                $messages = [
                    'permanent_address.pincode.regex' => 'Permanent address pincode must be 6 digits',
                    'current_address.pincode.regex' => 'Current address pincode must be 6 digits',
                ];

                $validator = Validator::make($request->all(), $addressRules, $messages);
                
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Address validation failed',
                        'errors' => $validator->errors(),
                        'data' => null,
                    ], 422);
                }

                try {
                    // Find the student
                    $student = Student::findOrFail($request->student_id);

                    // Map fields to database columns - using direct array access for form data
                    $student->address=$request->input('current_address.address');
                    $student->area=$request->input('current_address.area');
                    $student->landmark=$request->input('current_address.landmark');
                    $student->city_name=$request->input('current_address.city');
                    $student->state_name=$request->input('current_address.state');
                    $student->pincode=$request->input('current_address.pincode');

                    $student->address_2=$request->input('permanent_address.address');
                    $student->area_2=$request->input('permanent_address.area');
                    $student->city_name_2=$request->input('permanent_address.city');
                    $student->state_name_2=$request->input('permanent_address.state');
                    $student->pincode_2=$request->input('permanent_address.pincode');
                   

                    // Update the student record
                   $student->save();

                    return response()->json([
                        'status' => 'success',
                        'message' => "Step 3: $stepName3 completed successfully",
                        'data' => [
                            'student_id' => $student->id,
                            'current_address' => [
                                'address' => $student->address,
                                'area' => $student->area,
                                'landmark' => $student->landmark,
                                'city' => $student->city_name,
                                'state' => $student->state_name,
                                'pincode' => $student->pincode,
                            ],
                            'permanent_address' => [
                                'address' => $student->address_2,
                                'area' => $student->area_2,
                                'city' => $student->city_name_2,
                                'state' => $student->state_name_2,
                                'pincode' => $student->pincode_2,
                            ]
                        ],
                    ], 200);

                } catch (\Exception $e) {
                    Log::error("Student address update failed: " . $e->getMessage());
                    return response()->json([
                        'status' => 'error',
                        'message' => "Step 3: $stepName3 failed. " . $e->getMessage(),
                        'data' => null,
                    ], 500);
                } 

                case 'step_4': // Medical History
                $stepName4 = "Medical History Record";
                $medicalRules = [
                   // 'medical_condition' => ['required', Rule::in(['Good', 'Bad', 'Others'])],
                    'allergies' => ['nullable', 'array'],
                    'medications' => ['nullable', 'array'],
                ];

                $validator = Validator::make($request->all(), $medicalRules);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data Validation failed',
                        'errors' => $validator->errors(),
                        'data' => null,
                    ], 422);
                }

                try {
                    $student = Student::findOrFail($request->student_id);
                   
                    // Update or create medical history record
                    StudentMedicalHistory::updateOrCreate(
                        ['student_id' => $student->id],
                        [
                            'serious_disease' => $request->serious_disease ?? 'NA',
                            'medical_condition' => $request->medical_condition ?? 'Good',
                            'serious_injuries' => $request->serious_injuries ? json_encode($request->serious_injuries) : null,
                            'allergies' => $request->allergies ? json_encode($request->allergies) : null,
                            'medications' => $request->medications ? json_encode($request->medications) : null,
                        ]
                    );

                    $student->transport_allow = ($request->transport_service == 'yes') ? 1:0;
                    $student->save();
                    
                    StudentPreviousEducation::updateOrCreate(
                        ['student_id' =>$student->id],
                        [
                            'school_name' =>  $request->previous_school_name ?? '',
                            'address' =>  $request->previous_school_address ?? '',
                        ]
                    );

                    return response()->json([
                        'status' => 'success',
                        'message' => "Step 4: $stepName4 completed successfully",
                        'data' => [
                            'student_id' => $student->id,
                        ],
                    ], 200);

                } catch (\Exception $e) {
                    Log::error("Student update (Step 4) failed: " . $e->getMessage());
                    return response()->json([
                        'status' => 'error',
                        'message' => "Step 4: $stepName4 failed. " . $e->getMessage(),
                        'data' => null,
                    ], 500);
                }

            case 'step_5': // Documents
                $stepName5 = "Document Records";
                $documentRules = [
                    'birth_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2096'], // Added image mimes
                    'aadhar_card' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'], // Added image mimes
                    'transfer_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'], // Added image mimes
                ];

                $validator = Validator::make($request->all(), $documentRules);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data Validation failed',
                        'errors' => $validator->errors(),
                        'data' => null,
                    ], 422);
                }

                try {

                    $student = Student::findOrFail($request->student_id);
                    // Handle document uploads
                    if($request->hasFile('birth_certificate')){
                        $birthCertificate = $this->upload_file('birth_certificate', $student->docfolder_name)  ?? null;
                    
                        StudentDocument::updateOrCreate(
                            [
                                'student_id' =>$student->id,
                                'doc_type' => 'birth_certificate'
                            ],
                            [
                                'doc_file' =>  $birthCertificate,
                            ]
                        );
                    }  
                    
                    if($request->hasFile('aadhar_card')){
                        $aadharcard = $this->upload_file('aadhar_card', $student->docfolder_name)  ?? null;
                    
                        StudentDocument::updateOrCreate(
                            [
                                'student_id' =>$student->id,
                                'doc_type' => 'aadhar_card'
                            ],
                            [
                                'doc_file' =>  $aadharcard,
                            ]
                        );
                    }

                    if($request->hasFile('transfer_certificate')){
                        $transfercertificate = $this->upload_file('transfer_certificate', $student->docfolder_name) ?? null;
                    
                        StudentDocument::updateOrCreate(
                            [
                                'student_id' =>$student->id,
                                'doc_type' => 'tc'
                            ],
                            [
                                'doc_file' =>  $transfercertificate,
                            ]
                        );
                    }

                   
                    return response()->json([
                        'status' => 'success',
                        'message' => "Step 5: $stepName5 completed successfully",
                        'data' => [
                            'student_id' => $student->id,
                        ],
                    ], 200);

                } catch (\Exception $e) {
                    Log::error("Student update (Step 5) failed: " . $e->getMessage());
                    return response()->json([
                        'status' => 'error',
                        'message' => "Step 5: $stepName5 failed. " . $e->getMessage(),
                        'data' => null,
                    ], 500);
                }

          
            case 'step_6': // Finalize Process
                $stepName6 = "Finalize Process";
                

            default:
                // Handle invalid step or missing step parameter
                return response()->json([
                    'status' => 'error',
                    'message' => "Invalid or missing 'step' parameter. Please provide a valid step (e.g., 'step_1', 'step_2').",
                    'data' => null,
                ], 400); // 400 Bad Request
        }
    }

    /**
     * Helper method to upload files to public storage.
     *
     * @param string $field The request field name for the file.
     * @param string $directory The storage directory (within 'public' disk).
     * @return string|null The stored file path relative to the storage disk, or null if no file.
     */
    protected function upload_filehhh($field, $directory)
    {
        // Check if the request has a file for the given field
        if (request()->hasFile($field)) {
            $file = request()->file($field);
            // Store the file and return its path relative to the 'public' disk
            return Storage::disk('public')->putFile('uploads/' . $directory, $file);
        }
        return null;
    }


        protected function upload_file($field, $directory, $oldFilePath = null)
        {
            // Delete old file if provided
            if ($oldFilePath && Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->delete($oldFilePath);
            }

            // Check if new file exists in request
            if (request()->hasFile($field)) {
                $file = request()->file($field);
                return Storage::disk('public')->putFile('uploads/' . $directory, $file);
            }
            
            return null;
        }
    /**
     * Helper method to format student data for API responses, including relationships.
     *
     * @param \App\Models\Student $student The Student model instance.
     * @return array Formatted student data.
     */
    public function getStudentData($id)
    {
        // Find the student by ID, including related models
        return $student = Student::with(['parent', 'siblings', 'documents', 'medicalHistory'])
            ->findOrFail($id);

        // Return the student instance directly if needed
        // return $student;

        // If you need to return a structured array instead, uncomment the following lines
        // and comment out the return $student line below.
  
        
        return $student;
        // Decode languages if it's stored as a JSON string in the database
        $languages = $student->languages ? json_decode($student->languages, true) : [];

        // Return a structured array of student data, including related models
        return [
            'student_id' => $student->id,
            'admission_number' => $student->admission_no, // Corrected to match database column
            'academic_year' => $student->academic_year,
            'admission_date' => $student->doj, // Corrected to match database column
            'roll_number' => $student->role_no, // Corrected to match database column
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'email' => $student->email,
            'primary_contact' => $student->phone, // Corrected to match database column
            'class' => $student->class,
            'section' => $student->section,
            'gender' => $student->gender,
            'dob' => $student->dob, // Corrected to match database column
            'blood_group' => $student->blood_group,
            'house' => $student->house,
            'religion' => $student->religion,
            'category' => $student->category,
            'caste' => $student->caste,
            'mother_tongue' => $student->mother_tongue,
            'languages_known' => $languages,
            // Generate full URL for profile image if it exists
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
            // Include parent data if the relationship is loaded
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
            // Include siblings data if the relationship is loaded
            'siblings' => $student->siblings->map(function ($sibling) {
                return [
                    'name' => $sibling->name,
                    'roll_no' => $sibling->roll_no,
                    'admission_no' => $sibling->admission_no,
                    'class' => $sibling->class,
                ];
            }),
            // Include documents data if the relationship is loaded
            'documents' => $student->documents ? [
                'medical_condition_document' => $student->documents->medical_condition_document ? asset('storage/' . $student->documents->medical_condition_document) : null,
                'transfer_certificate' => $student->documents->transfer_certificate ? asset('storage/' . $student->documents->transfer_certificate) : null,
            ] : null,
            // Include medical history data if the relationship is loaded
            'medical_history' => $student->medicalHistory ? [
                'medical_condition' => $student->medicalHistory->medical_condition,
                'allergies' => $student->medicalHistory->allergies ? json_decode($student->medicalHistory->allergies, true) : [],
                'medications' => $student->medicalHistory->medications ? json_decode($student->medicalHistory->medications, true) : [],
            ] : null,
            // Include previous school data if the relationship is loaded
            'previous_school' => $student->previousSchool, // You might want to format this further if it's an object/model
        ];
    }
}