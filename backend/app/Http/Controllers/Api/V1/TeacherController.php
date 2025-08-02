<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    /**
     * Display a listing of the teachers.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $teachers = Teacher::select(
            'id',
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
            'profile_image'
        )->with(['class', 'subject'])
         ->get();

        
        return response()->json([
            'status' => 'success',
            'data' => $teachers,
            'message' => 'Teacher retrieved successfully.',
        ], 200);
    }

    /**
     * Display the specified teacher.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $teacher = Teacher::select(
            'id',
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
            'branch_id'
        )->findOrFail($id);

         if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Teacher not found',
                'data' => null,
            ], 404);
        }

         return response()->json([
            'status' => 'success',
            'data' => $teachers,
            'message' => 'Teacher retrieved successfully.',
        ], 200);    
        
    }

    /**
     * Store a newly created teacher in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $request;
        $validator = Validator::make($request->all(), [
            //'code' => 'nullable|string|unique:teachers,code|max:50',
            'first_name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'class_id' => 'nullable|integer|exists:classes,id',
            'subject_id' => 'nullable|integer|exists:subjects,id',
            'gender' => 'required|string|in:male,female,other',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:teachers,email|max:255',
            'blood_group' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'date_of_joining' => 'required|date',
            'father_name' => 'nullable|string|max:50',
            'mother_name' => 'nullable|string|max:50',
            'dob' => 'required|date',
            'marital_status' => 'required|string|in:Single,Married,Divorced,Widowed',
            'languages_known' => 'nullable|array',
            'qualification' => 'required|string|max:100',
            'work_experience' => 'nullable|string|max:100',
            'previous_school' => 'nullable|string|max:100',
            'previous_school_address' => 'nullable|string|max:255',
            'previous_school_phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'permanent_address' => 'required|string|max:255',
            'pan_number' => 'nullable|string|max:50',
            'epf_no' => 'nullable|string|max:50',
            'basic_salary' => 'nullable|numeric',
            'contract_type' => 'nullable|string|in:Full-Time,Part-Time,Contract',
            'work_shift' => 'nullable|string|max:50',
            'work_location' => 'nullable|string|max:100',
            'date_of_leaving' => 'nullable|date',
            'medical_leaves' => 'nullable|integer|min:0',
            'casual_leaves' => 'nullable|integer|min:0',
            'maternity_leaves' => 'nullable|integer|min:0',
            'sick_leaves' => 'nullable|integer|min:0',
            'account_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'ifsc_code' => 'nullable|string|max:50',
            'branch_name' => 'nullable|string|max:100',
            'route_id' => 'nullable|integer|exists:routes,id',
            'vehicle_number_id' => 'nullable|integer|exists:vehicle_numbers,id',
            'pickup_point_id' => 'nullable|integer|exists:pickup_points,id',
            'hostel_id' => 'nullable|integer|exists:hostels,id',
            'hostel_room_no' => 'nullable|string|max:50',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'resume_file' => 'nullable|file|mimes:pdf|max:4096',
            'joining_letter_file' => 'nullable|file|mimes:pdf|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle file uploads
        $data = $request->all();
        if ($request->hasFile('resume_file')) {
            $data['resume_file'] = $request->file('resume_file')->store('documents/resumes', 'public');
        }
        if ($request->hasFile('joining_letter_file')) {
            $data['joining_letter_file'] = $request->file('joining_letter_file')->store('documents/joining_letters', 'public');
        }

        // Generate unique teacher ID
        $data['code'] = getNewSerialNo('teacher');
        
        // Set default company_id and branch_id if not provided
        $data['company_id'] = $request->company_id ?? 1;
        $data['branch_id'] = $request->branch_id ?? 1;

        $teacher = Teacher::create($data);

        // Increment serial number for teacher
        increaseSerialNo('teacher');

        return response()->json([
            'status' => true,
            'message' => 'Teacher created successfully',
            'data' => $teacher
        ], 201);
    }

    /**
     * Update the specified teacher in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('teachers')->ignore($teacher->id)
            ],
            'first_name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'class_id' => 'required|integer|exists:classes,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'gender' => 'required|string|in:Male,Female,Other',
            'phone' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('teachers')->ignore($teacher->id)
            ],
            'blood_group' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'date_of_joining' => 'required|date',
            'father_name' => 'nullable|string|max:50',
            'mother_name' => 'nullable|string|max:50',
            'dob' => 'required|date',
            'marital_status' => 'required|string|in:Single,Married,Divorced,Widowed',
            'languages_known' => 'nullable|array',
            'qualification' => 'required|string|max:100',
            'work_experience' => 'nullable|string|max:100',
            'previous_school' => 'nullable|string|max:100',
            'previous_school_address' => 'nullable|string|max:255',
            'previous_school_phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'permanent_address' => 'required|string|max:255',
            'pan_number' => 'nullable|string|max:50',
            'status' => 'required|boolean',
            'epf_no' => 'nullable|string|max:50',
            'basic_salary' => 'nullable|numeric',
            'contract_type' => 'nullable|string|in:Full-Time,Part-Time,Contract',
            'work_shift' => 'nullable|string|max:50',
            'work_location' => 'nullable|string|max:100',
            'date_of_leaving' => 'nullable|date',
            'medical_leaves' => 'nullable|integer|min:0',
            'casual_leaves' => 'nullable|integer|min:0',
            'maternity_leaves' => 'nullable|integer|min:0',
            'sick_leaves' => 'nullable|integer|min:0',
            'account_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'ifsc_code' => 'nullable|string|max:50',
            'branch_name' => 'nullable|string|max:100',
            'route_id' => 'nullable|integer|exists:routes,id',
            'vehicle_number_id' => 'nullable|integer|exists:vehicle_numbers,id',
            'pickup_point_id' => 'nullable|integer|exists:pickup_points,id',
            'hostel_id' => 'nullable|integer|exists:hostels,id',
            'hostel_room_no' => 'nullable|string|max:50',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'resume_file' => 'nullable|file|mimes:pdf|max:4096',
            'joining_letter_file' => 'nullable|file|mimes:pdf|max:4096',
            'company_id' => 'required|integer|exists:companies,id',
            'branch_id' => 'required|integer|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle file uploads
        $data = $request->all();
        if ($request->hasFile('resume_file')) {
            $data['resume_file'] = $request->file('resume_file')->store('documents/resumes', 'public');
        }
        if ($request->hasFile('joining_letter_file')) {
            $data['joining_letter_file'] = $request->file('joining_letter_file')->store('documents/joining_letters', 'public');
        }

        $teacher->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Teacher updated successfully',
            'data' => $teacher
        ]);
    }

    /**
     * Remove the specified teacher from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Teacher deleted successfully'
        ]);
    }
}