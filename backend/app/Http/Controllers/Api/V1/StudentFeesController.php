<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StudentFees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentFeesController extends Controller
{
    // List all student fees
    public function index()
    {
        $data = StudentFees::with(['student', 'class', 'feestype', 'feesmaster'])->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully',
            'data' => $data
        ], 200);
    }

    // Show a single student fee
    public function show($id)
    {
        $fee = StudentFees::with(['student', 'class', 'feestype', 'feesmaster'])->findOrFail($id);
        return response()->json($fee);
    }

    // Store a new student fee
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'class_id' => 'required|integer|exists:classes,id',
            'feestype_id' => 'required|integer|exists:fees_type_master,id',
            'feesmaster_id' => 'required|integer|exists:fees_master,id',
            'amount' => 'required|numeric',
            'due_date' => 'nullable|date',
            'created_by' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
            'company_id' => 'nullable|integer',
            'academicyear_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $studentFee = StudentFees::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Student Fee created successfully',
            'data' => $studentFee
        ], 201);
    }

    // Update a student fee
    public function update(Request $request, $id)
    {
        $studentFee = StudentFees::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'class_id' => 'required|integer|exists:classes,id',
            'feestype_id' => 'required|integer|exists:fees_type_master,id',
            'feesmaster_id' => 'required|integer|exists:fees_master,id',
            'amount' => 'required|numeric',
            'due_date' => 'nullable|date',
            'created_by' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
            'company_id' => 'nullable|integer',
            'academicyear_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $studentFee->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Student Fee updated successfully',
            'data' => $studentFee
        ]);
    }

    // Delete a student fee
    public function destroy($id)
    {
        $fee = StudentFees::findOrFail($id);
        $fee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student Fee deleted successfully'
        ]);
    }
}