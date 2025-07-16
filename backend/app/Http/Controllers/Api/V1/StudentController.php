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
    public function index()
    {
        $studentData = Student::get();
        return response()->json([
            'status' => 'true',
            'data' => $studentData,
        ], 200);
    }

    public function show($id)
    {
        $student = Student::with(['parent', 'siblings', 'documents', 'medicalHistory', 'previousSchool'])
            ->find($id);

        if (!$student) {
            return response()->json([
                'status' => 'false',
                'message' => 'Student not found'
            ], 404);
        }

        $studentData = $this->get_student_data($student);
        return response()->json([
            'status' => 'true',
            'data' => $studentData
        ], 200);
    }

    public function create(Request $request)
    {
        $step = $request->has('step') ? $request->step : '';
        $studentId = $request->studentId ?? null;

        try {
            switch ($step) {
                case 'step_1':
                    return $this->processStep1($request);
                case 'step_2':
                    return $this->processStep2($request, $studentId);
                case 'step_3':
                    return $this->processStep3($request, $studentId);
                case 'step_4':
                    return $this->processStep4($request, $studentId);
                case 'step_5':
                    return $this->processStep5($request, $studentId);
                case 'step_6':
                    return $this->processStep6($request, $studentId);
                case 'step_7':
                    return $this->processStep7($request, $studentId);
                default:
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid step parameter'
                    ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function processStep1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admission_date' => ['required', 'date'],
            'status' => ['required'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'class' => ['required'],
            'section' => ['required'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['required', 'date'],
            'email' => ['required', 'email', 'max:255', 'unique:students,email'],
            'current_address' => ['required', 'string', 'max:255'],
            'permanent_address' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $profileImage = $request->hasFile('profile_image') 
            ? $this->upload_file('profile_image', 'student_profiles') 
            : null;

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
            'email' => $request->email,
            'profile_image' => $profileImage,
            'current_address' => $request->current_address,
            'permanent_address' => $request->permanent_address,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Step 1 completed successfully',
            'student_id' => $student->id,
            'admission_number' => $student->admission_no
        ], 201);
    }

    private function processStep2(Request $request, $studentId)
    {
        $validator = Validator::make($request->all(), [
            'guardian_type' => ['required', Rule::in(['Parents', 'Guardian', 'Others'])],
            'guardian.name' => ['required_if:guardian_type,Guardian,Others', 'string', 'max:100'],
            'guardian.relation' => ['required_if:guardian_type,Guardian,Others', 'string', 'max:100'],
            'guardian.phone' => ['required_if:guardian_type,Guardian,Others', 'string', 'max:15'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $fatherImage = $request->hasFile('father.image') 
            ? $this->upload_file('father.image', 'parent_profiles') 
            : null;

        $motherImage = $request->hasFile('mother.image') 
            ? $this->upload_file('mother.image', 'parent_profiles') 
            : null;

        $guardianImage = $request->hasFile('guardian.image') 
            ? $this->upload_file('guardian.image', 'guardian_profiles') 
            : null;

        StudentParent::create([
            'student_id' => $studentId,
            'father_name' => $request->father['name'] ?? null,
            'father_phone' => $request->father['phone'] ?? null,
            'father_image' => $fatherImage,
            'mother_name' => $request->mother['name'] ?? null,
            'mother_phone' => $request->mother['phone'] ?? null,
            'mother_image' => $motherImage,
            'guardian_type' => $request->guardian_type,
            'guardian_name' => $request->guardian['name'] ?? null,
            'guardian_relation' => $request->guardian['relation'] ?? null,
            'guardian_phone' => $request->guardian['phone'] ?? null,
            'guardian_image' => $guardianImage,
        ]);

        if ($request->siblings) {
            foreach ($request->siblings as $sibling) {
                StudentSibling::create([
                    'student_id' => $studentId,
                    'name' => $sibling['name'],
                    'roll_no' => $sibling['roll_no'],
                    'admission_no' => $sibling['admission_no'],
                    'class' => $sibling['class'],
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Step 2 completed successfully'
        ]);
    }

    private function processStep3(Request $request, $studentId)
    {
        Student::where('id', $studentId)->update([
            'transport_enabled' => $request->transport_enabled ?? false,
            'transport_route' => $request->transport_enabled ? ($request->transport['route'] ?? null) : null,
            'transport_vehicle_number' => $request->transport_enabled ? ($request->transport['vehicle_number'] ?? null) : null,
            'transport_pickup_point' => $request->transport_enabled ? ($request->transport['pickup_point'] ?? null) : null,
            'hostel_enabled' => $request->hostel_enabled ?? false,
            'hostel_name' => $request->hostel_enabled ? ($request->hostel['name'] ?? null) : null,
            'hostel_room_no' => $request->hostel_enabled ? ($request->hostel['room_no'] ?? null) : null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Step 3 completed successfully'
        ]);
    }

    private function processStep4(Request $request, $studentId)
    {
        StudentMedicalHistory::create([
            'student_id' => $studentId,
            'medical_condition' => $request->medical_condition,
            'allergies' => $request->allergies ? json_encode($request->allergies) : null,
            'medications' => $request->medications ? json_encode($request->medications) : null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Step 4 completed successfully'
        ]);
    }

    private function processStep5(Request $request, $studentId)
    {
        $medicalDocument = $request->hasFile('medical_condition_document') 
            ? $this->upload_file('medical_condition_document', 'student_documents') 
            : null;

        $transferCertificate = $request->hasFile('transfer_certificate') 
            ? $this->upload_file('transfer_certificate', 'student_documents') 
            : null;

        if ($medicalDocument || $transferCertificate) {
            StudentDocument::create([
                'student_id' => $studentId,
                'medical_condition_document' => $medicalDocument,
                'transfer_certificate' => $transferCertificate,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Step 5 completed successfully'
        ]);
    }

    private function processStep6(Request $request, $studentId)
    {
        $student = Student::find($studentId);
        $student->update([
            'bank_name' => $request->bank['name'] ?? null,
            'bank_branch' => $request->bank['branch'] ?? null,
            'bank_ifsc' => $request->bank['ifsc'] ?? null,
            'other_information' => $request->other_information ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Step 6 completed successfully'
        ]);
    }

    private function processStep7(Request $request, $studentId)
    {
        Student::where('id', $studentId)->update(['status' => 1]); // Set status to active

        return response()->json([
            'status' => 'success',
            'message' => 'Student registration completed successfully',
            'student_id' => $studentId
        ]);
    }

    protected function upload_file($field, $directory)
    {
        $file = request()->file($field);
        return $file->store($directory, 'public');
    }

    private function get_student_data($student)
    {
        return [
            'student_id' => $student->id,
            'admission_number' => $student->admission_no,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'email' => $student->email,
            'class' => $student->class,
            'status' => $student->status,
            'profile_image' => $student->profile_image,
        ];
    }
}