<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PdcModel;
use Illuminate\Support\Facades\Validator;

class PdcController extends Controller
{
    // List all PDCs
    public function index()
    {
        $data = PdcModel::all();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    // Show a single PDC
    public function show($id)
    {
        $pdc = PdcModel::find($id);
        if (!$pdc) {
            return response()->json(['status' => false, 'message' => 'PDC not found'], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $pdc
        ]);
    }

    // Store multiple PDCs for a student
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer',
            'pdc' => 'required|array|min:1',
            'pdc.*.bank_name' => 'required|string|max:255',
            'pdc.*.account_holder_name' => 'required|string|max:255',
            'pdc.*.cheque_number' => 'required|string|max:100',
            'pdc.*.amount' => 'required|numeric',
            'pdc.*.cheque_date' => 'required|date',
            'pdc.*.branch_name' => 'nullable|string|max:255',
            'pdc.*.account_number' => 'nullable|string|max:100',
            'pdc.*.remarks' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $created = [];
        foreach ($request->pdc as $item) {
            $data = array_merge(
                $item,
                ['student_id' => $request->student_id]
            );
            $created[] = PdcModel::create($data);
        }

        return response()->json([
            'status' => true,
            'message' => 'PDC(s) created successfully',
            'data' => $created
        ], 201);
    }

    // Update multiple PDCs for a student (delete old and insert new)
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer',
            'pdc' => 'required|array|min:1',
            'pdc.*.bank_name' => 'required|string|max:255',
            'pdc.*.account_holder_name' => 'required|string|max:255',
            'pdc.*.cheque_number' => 'required|string|max:100',
            'pdc.*.amount' => 'required|numeric',
            'pdc.*.cheque_date' => 'required|date',
            'pdc.*.branch_name' => 'nullable|string|max:255',
            'pdc.*.account_number' => 'nullable|string|max:100',
            'pdc.*.remarks' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Remove all existing PDCs for this student
        PdcModel::where('student_id', $request->student_id)->delete();

        $updated = [];
        foreach ($request->pdc as $item) {
            $data = array_merge(
                $item,
                ['student_id' => $request->student_id]
            );
            $updated[] = PdcModel::create($data);
        }

        return response()->json([
            'status' => true,
            'message' => 'PDC(s) updated successfully',
            'data' => $updated
        ]);
    }

    // Delete a PDC
    public function destroy($id)
    {
        $pdc = PdcModel::findOrFail($id);
        $pdc->delete();

        return response()->json([
            'status' => true,
            'message' => 'PDC deleted successfully'
        ]);
    }
}
