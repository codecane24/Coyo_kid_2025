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

    // Show a single class fee
    public function showClassFees($classid)
    {
        $fees = ClassFees::with(['class', 'feestype'])->where('class_id', $classid)->get();
        if ($fees->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Class Fees not found'], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $fees
        ]);
    }

    // classwise fees
    public function classwiseFees(Request $request)
    {
        // Get all class fees with class and feestype relationships
        $classFees = ClassFees::with(['class', 'feestype.feesgroup'])->get();

        if ($classFees->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No class fees found'], 404);
        }
        // Group by class
        $result = [];
        foreach ($classFees as $fee) {
            $classId = $fee->class_id;
            $className = $fee->class->name ?? '';
            if (!isset($result[$classId])) {
                $result[$classId] = [
                    'class_id' => $classId,
                    'class_name' => $className,
                    'feestypes' => []
                ];
            }
            $result[$classId]['feestypes'][] = [
                'feestype_id' => $fee->feestype_id,
                'fees_type_name' => $fee->feestype->name ?? '',
                'feestype_code' => $fee->feestype->code ?? '',
                'feesgroup_id' => $fee->feestype->feesgroup_id ?? null,
                'feesgroup_name' => $fee->feestype->feesgroup->name ?? null,
                'amount' => $fee->amount
            ];
        }

        // Re-index result as array
        $result = array_values($result);

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

    // Store new class fee(s)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'classid' => 'required|array|min:1',
            'classid.*' => 'required|integer|exists:classes,id',
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

        // check already exists return error with class name and fees type name
        
        foreach ($request->classid as $class_id) {
            $existingFees = ClassFees::where('class_id', $class_id)
                ->whereIn('feestype_id', array_column($request->feestypes, 'feestype_id'))
                ->get();
            if ($existingFees->isNotEmpty()) {
                $className = $existingFees->first()->class->name ?? 'Unknown Class';
                $feeTypeNames = $existingFees->pluck('feestype.name')->implode(', ');
                return response()->json([
                    'status' => false,
                    'message' => "Class Fees already exist for the selected class ($className) and fee type(s): $feeTypeNames",
                ], 409);
            }
        }

        // Create class fees for each class and fee type
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

    // Update class fee(s)
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'classid' => 'required|array|min:1',
            'classid.*' => 'required|integer|exists:classes,id',
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

        // Remove all existing records for the given classid(s)
        foreach ($request->classid as $class_id) {
            ClassFees::where('class_id', $class_id)->delete();
        }

        $updated = [];
        foreach ($request->classid as $class_id) {
            foreach ($request->feestypes as $feestype) {
                $data = [
                    'class_id' => $class_id,
                    'feestype_id' => $feestype['feestype_id'],
                    'amount' => $feestype['amount'],
                    // Add other fields if needed, e.g. due_date, created_by, etc.
                ];
                $updated[] = ClassFees::create($data);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Class Fees updated successfully',
            'data' => $updated
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
