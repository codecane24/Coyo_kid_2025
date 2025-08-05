<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FeesMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeesMasterController extends Controller
{
    // List all fee masters
    public function index()
    {
        $data = FeesMaster::with('feestype')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully',
            'data' => $data
        ], 200);
    }

    // Show a single fee master
    public function show($id)
    {
        $master = FeesMaster::with('feestype')->findOrFail($id);
        return response()->json($master);
    }

    // Store a new fee master
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feestype_id' => 'required|integer|exists:fees_type_master,id',
            'rate' => 'required|numeric',
            'rate_type' => 'required|in:0,1',
            'amount' => 'required|numeric',
            'due_date' => 'nullable|date',
            // 'created_by' => 'nullable|integer',
            // 'branch_id' => 'nullable|integer',
            // 'company_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $feeMaster = FeesMaster::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Fee Master created successfully',
            'data' => $feeMaster
        ], 201);
    }

    // Update a fee master
    public function update(Request $request, $id)
    {
        $feeMaster = FeesMaster::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'feestype_id' => 'required|integer|exists:fees_type_master,id',
            'rate' => 'required|numeric',
            'rate_type' => 'required|in:0,1',
            'amount' => 'required|numeric',
            'due_date' => 'nullable|date',
            // 'created_by' => 'nullable|integer',
            // 'branch_id' => 'nullable|integer',
            // 'company_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $feeMaster->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Fee Master updated successfully',
            'data' => $feeMaster
        ]);
    }

    // Delete a fee master
    public function destroy($id)
    {
        $master = FeesMaster::findOrFail($id);
        $master->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fee Master deleted successfully'
        ]);
    }
}