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
        $fees = ClassFees::with([
            'class:id,name',
            'feestype:id,name,code,feesgroup_id',
            'feestype.feesgroup:id,name'
        ])
        ->where('class_id', $classid)
        ->select('id', 'class_id', 'feestype_id', 'amount', 'due_date')
        ->get();

        if ($fees->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Class Fees not found'], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $fees
        ]);
    }

    // Show all class fees grouped by class with feestypes array
    public function classwiseFees(Request $request)
    {
        $classFees = ClassFees::with(['class', 'feestype.feesgroup'])->get();

        if ($classFees->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No class fees found'], 404);
        }

        $grouped = [];
        foreach ($classFees as $fee) {
            $class_id = (string) $fee->class_id;
            if (!isset($grouped[$class_id])) {
                $grouped[$class_id] = [
                    "class_id" => $class_id,
                    "class_name" => $fee->class->name.' ('. $fee->class->section .')' ?? '',
                    "feestypes" => []
                ];
            }
            $grouped[$class_id]["feestypes"][] = [
                "feestype_id" => (string) $fee->feestype_id,
                "fees_type_name" => $fee->feestype->name ?? "",
                "feestype_code" => $fee->feestype->code ?? "",
                "feesgroup_id" => isset($fee->feestype->feesgroup) ? (string) $fee->feestype->feesgroup->id : "",
                "feesgroup_name" => $fee->feestype->feesgroup->name ?? "",
                "amount" => number_format((float)$fee->amount, 2, '.', '')
            ];
        }

        return response()->json([
            "status" => "success",
            "data" => array_values($grouped)
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
