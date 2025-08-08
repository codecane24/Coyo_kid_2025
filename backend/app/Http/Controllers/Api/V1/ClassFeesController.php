<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassFees;
use Illuminate\Support\Facades\Validator;

class ClassFeesController extends Controller
{
    // List all class fees
    public function index()
    {
        $data = ClassFees::with(['class', 'feestype'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    // Show a single class fee
    public function show($id)
    {
        $fee = ClassFees::with(['class', 'feestype'])->find($id);
        if (!$fee) {
            return response()->json(['status' => false, 'message' => 'Class Fee not found'], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $fee
        ]);
    }

    // Store new class fee(s)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'classid' => 'required|array|min:1',
            'classid.*' => 'required|integer|exists:class_master,id',
            'feestypes' => 'required|array|min:1',
            'feestypes.*.feestype_id' => 'required|integer|exists:fees_type_master,id',
            'feestypes.*.amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $created = [];
        foreach ($request->classid as $class_id) {
            foreach ($request->feestypes as $feestype) {
                $data = [
                    'class_id' => $class_id,
                    'feestype_id' => $feestype['feestype_id'],
                    'amount' => $feestype['amount'],
                    // Add other fields if needed, e.g. due_date, created_by, etc.
                ];
                $created[] = ClassFees::create($data);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Class Fees created successfully',
            'data' => $created
        ], 201);
    }

    // Update class fee
    public function update(Request $request, $id)
    {
        $fee = ClassFees::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|integer|exists:class_master,id',
            'feestype_id' => 'required|integer|exists:fees_type_master,id',
            'amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $fee->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Class Fee updated successfully',
            'data' => $fee
        ]);
    }

    // Delete class fee
    public function destroy($id)
    {
        $fee = ClassFees::findOrFail($id);
        $fee->delete();

        return response()->json([
            'status' => true,
            'message' => 'Class Fee deleted successfully'
        ]);
    }
}
