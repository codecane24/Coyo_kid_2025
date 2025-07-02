<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    /**
     * Display a listing of the classes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $classes = Classes::select(
            'id', 
            'code', 
            'name', 
            'classmaster_id', 
            'section', 
            'room_no', 
            'status',
            'company_id',
            'branch_id'
        )->get();
        
        return response()->json($classes);
    }

    /**
     * Display the specified class.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $class = Classes::select(
            'id', 
            'code', 
            'name', 
            'classmaster_id', 
            'section', 
            'room_no', 
            'status',
            'company_id',
            'branch_id'
        )->findOrFail($id);
        
        return response()->json($class);
    }

    /**
     * Store a newly created class in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'classmaster_id' => 'required|integer|exists:classes_master,id',
            'section' => 'nullable|string|max:50',
            'room_no' => 'nullable|string|max:50',
            'status' => 'required|boolean',
        ]);

        /*
        'code' => 'required|string|unique:classes,code|max:50',
        'company_id' => 'required|integer|exists:companies,id',
        'branch_id' => 'required|integer|exists:branches,id',
        'user_id' => 'required|integer|exists:branches,id',
            
        */

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $class = Classes::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Class created successfully',
            'data' => $class
        ], 201);
    }

    /**
     * Update the specified class in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $class = Classes::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classes')->ignore($class->id)
            ],
            'name' => 'required|string|max:255',
            'classmaster_id' => 'required|integer|exists:classmasters,id',
            'section' => 'nullable|string|max:100',
            'room_no' => 'nullable|string|max:50',
            'status' => 'required|boolean',
            'company_id' => 'required|integer|exists:companies,id',
            'branch_id' => 'required|integer|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $class->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Class updated successfully',
            'data' => $class
        ]);
    }

    /**
     * Remove the specified class from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $class = Classes::findOrFail($id);
        $class->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class deleted successfully'
        ]);
    }
}