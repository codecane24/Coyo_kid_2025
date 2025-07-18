<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FeesGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FeesGroupController extends Controller
{
    /**
     * Display a listing of the fees groups.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = FeesGroup::select('id', 'code', 'name', 'status', 'description')->get();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully',
            'data' => $data
        ], 200);
    }

    /**
     * Display the specified fees group.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $feesGroup = FeesGroup::select('id', 'code', 'name', 'status', 'description')
            ->findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => $feesGroup
        ], 200);
    }

    /**
     * Store a newly created fees group in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:50|unique:fees_groups,code',
            'status' => 'required|boolean',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $code = getNewSerialNo('fees_group');
        $request->merge([
            'code' => $code,
        ]);

        $feesGroup = FeesGroup::create($request->all());

        increaseSerialNo('fees_group');

        return response()->json([
            'status' => true,
            'message' => 'Fees group created successfully',
            'data' => $feesGroup
        ], 201);
    }

    /**
     * Update the specified fees group in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $feesGroup = FeesGroup::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('fees_groups', 'code')->ignore($feesGroup->id)
            ],
            'status' => 'required|boolean',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $feesGroup->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Fees group updated successfully',
            'data' => $feesGroup
        ], 200);
    }

    /**
     * Remove the specified fees group from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $feesGroup = FeesGroup::findOrFail($id);
        $feesGroup->delete();

        return response()->json([
            'status' => true,
            'message' => 'Fees group deleted successfully'
        ], 200);
    }
}