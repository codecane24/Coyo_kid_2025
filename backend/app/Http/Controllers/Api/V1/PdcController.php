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

    // Store a new PDC
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer',
            'payment_id' => 'nullable|integer',
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'cheque_number' => 'required|string|max:100',
            'amount' => 'required|numeric',
            'cheque_date' => 'required|date',
            'branch_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'cleared_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pdc = PdcModel::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'PDC created successfully',
            'data' => $pdc
        ], 201);
    }

    // Update a PDC
    public function update(Request $request, $id)
    {
        $pdc = PdcModel::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer',
            'payment_id' => 'nullable|integer',
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'cheque_number' => 'required|string|max:100',
            'amount' => 'required|numeric',
            'cheque_date' => 'required|date',
            'branch_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'cleared_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pdc->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'PDC updated successfully',
            'data' => $pdc
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
