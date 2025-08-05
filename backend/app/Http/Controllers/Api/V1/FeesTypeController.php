<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FeesType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeesTypeController extends Controller
{
    // List all fee types
    public function index()
    {
        $data = FeesType::with(['feesgroup:id,name'])->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully',
            'data' => $data
        ], 200);
    }

    // Show a single fee type
    public function show($id)
    {
        $type = FeesType::findOrFail($id);
        if (!$type) {
            return response()->json(['status' => false, 'message' => 'Fee Type not found'], 404);
        }
        $type->load('feesgroup:id,name'); // Load related fees group
        return response()->json([
            'status' => 'success',
            'data' => $type
        ], 200);
  
    }

    // Store a new fee type
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feesgroup_id' => 'required|integer|exists:fees_group_master,id',
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:100|unique:fees_type_master,name',
            'description' => 'nullable|string|max:100',
            'status' => 'required|boolean',
            // 'branch_id' => 'nullable|integer',
            // 'company_id' => 'nullable|integer',
            // 'created_by' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $feeType = FeesType::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Fee Type created successfully',
            'data' => $feeType
        ], 201);
    }

    // Update a fee type
    public function update(Request $request, $id)
    {
        $feeType = FeesType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'feesgroup_id' => 'required|integer|exists:fees_group_master,id',
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:100|unique:fees_type_master,name,' . $feeType->id,
            'description' => 'nullable|string|max:100',
            'status' => 'required|boolean',
            // 'branch_id' => 'nullable|integer',
            // 'company_id' => 'nullable|integer',
            // 'created_by' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $feeType->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Fee Type updated successfully',
            'data' => $feeType
        ]);
    }

    // Delete a fee type
    public function destroy($id)
    {
        $type = FeesType::findOrFail($id);
        $type->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Fee Type deleted successfully'
        ]);
    }
}