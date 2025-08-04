<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdmissionInquiry;
use Illuminate\Support\Facades\Validator;

class AdmissionInquiryController extends Controller
{
    // List all inquiries
    public function index()
    {
        $inquiries = AdmissionInquiry::all();
        return response()->json(['status' => true, 'data' => $inquiries]);
    }

    // Store a new inquiry
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'academic_year' => 'required|string|max:50',
            'date_of_enquiry' => 'required|date',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'class_id' => 'required|string|max:50',
            'gender' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'primary_contact' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'suitable_batch' => 'nullable|string|max:50',
            'father_name' => 'nullable|string|max:100',
            'father_email' => 'nullable|email|max:100',
            'father_phone' => 'nullable|string|max:20',
            'father_occupation' => 'nullable|string|max:100',
            'mother_name' => 'nullable|string|max:100',
            'mother_phone' => 'nullable|string|max:20',
            'mother_email' => 'nullable|email|max:100',
            'mother_occupation' => 'nullable|string|max:100',
            'sibling_same_school' => 'nullable|string|max:10',
            'sibling_ids' => 'nullable|array',
            'permanent_address' => 'nullable|array',
            'current_address' => 'nullable|array',
            'previous_school_name' => 'nullable|string|max:255',
            'previous_school_address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        // Generate unique admission inquiry ID
        $data['code'] = getNewSerialNo('admission_inquiry');
        $data['created_at'] = now();
        $data['updated_at'] = now();
        $data['added_by'] = auth()->user()->id ?? null; // Assuming you have user authentication
        $data['status'] = '0'; // 0:pending(new) | 1: picked |2:reply |3:admission| 4:closed

        // JSON encode array fields for DB if needed
        $data['sibling_ids'] = isset($data['sibling_ids']) ? json_encode($data['sibling_ids']) : json_encode([]);
        $data['permanent_address'] = isset($data['permanent_address']) ? json_encode($data['permanent_address']) : json_encode([]);
        $data['current_address'] = isset($data['current_address']) ? json_encode($data['current_address']) : json_encode([]);

        $inquiry = AdmissionInquiry::create($data);
        if (!$inquiry) {
            return response()->json(['status' => false, 'message' => 'Inquiry could not be created'], 500);
        }

        // function to increase the serial number
        increaseSerialNo('admission_inquiry');
        return response()->json(['status' => true, 'data' => $inquiry]);
    }

    // Show a single inquiry
    public function show($id)
    {
        $inquiry = AdmissionInquiry::find($id);
        if (!$inquiry) {
            return response()->json(['status' => false, 'message' => 'Inquiry not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $inquiry]);
    }

    // Update an inquiry
    public function update(Request $request, $id)
    {
        $inquiry = AdmissionInquiry::find($id);
        if (!$inquiry) {
            return response()->json(['status' => false, 'message' => 'Inquiry not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'academic_year' => 'sometimes|required|string|max:50',
            'date_of_enquiry' => 'sometimes|required|date',
            'first_name' => 'sometimes|required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'class_id' => 'sometimes|required|string|max:50',
            'gender' => 'sometimes|required|string|max:20',
            'date_of_birth' => 'sometimes|required|date',
            'primary_contact' => 'sometimes|required|string|max:20',
            'email' => 'nullable|email|max:100',
            'suitable_batch' => 'nullable|string|max:50',
            'father_name' => 'nullable|string|max:100',
            'father_email' => 'nullable|email|max:100',
            'father_phone' => 'nullable|string|max:20',
            'father_occupation' => 'nullable|string|max:100',
            'mother_name' => 'nullable|string|max:100',
            'mother_phone' => 'nullable|string|max:20',
            'mother_email' => 'nullable|email|max:100',
            'mother_occupation' => 'nullable|string|max:100',
            'sibling_same_school' => 'nullable|string|max:10',
            'sibling_ids' => 'nullable|array',
            'permanent_address' => 'nullable|array',
            'current_address' => 'nullable|array',
            'previous_school_name' => 'nullable|string|max:255',
            'previous_school_address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        // JSON encode array fields for DB if needed
        if (isset($data['sibling_ids'])) {
            $data['sibling_ids'] = json_encode($data['sibling_ids']);
        }
        if (isset($data['permanent_address'])) {
            $data['permanent_address'] = json_encode($data['permanent_address']);
        }
        if (isset($data['current_address'])) {
            $data['current_address'] = json_encode($data['current_address']);
        }

        $inquiry->update($data);

        return response()->json(['status' => true, 'data' => $inquiry]);
    }

    // Delete an inquiry
    public function destroy($id)
    {
        $inquiry = AdmissionInquiry::find($id);
        if (!$inquiry) {
            return response()->json(['status' => false, 'message' => 'Inquiry not found'], 404);
        }
        $inquiry->delete();
        return response()->json(['status' => true, 'message' => 'Inquiry deleted successfully']);
    }
}