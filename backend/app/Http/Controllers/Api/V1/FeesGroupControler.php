<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FeesGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FeesGroupControler extends Controller
{
    /**
     * Display a listing of the classes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = FeesGroup::get();
    //     ->map(function ($class) {
    //     $class->name = empty($class->name) ? optional($class->classmaster)->name : $class->name;
    //     return $class;
    // });
       return response()->json([
            'status' => 'success',
            'message' => 'deta fetch succesfully',
            'data' => $data
        ], 201);
     
    }

    /**
     * Display the specified class.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $class = FeesGroup::select(
            'id', 
            'code', 
            'name', 
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
            'code' => [
                'nullable',
                'string',
                'max:50'
            ],
            'name' => 'required|string|max:100|unique:fees_group_master,name',
            'description' => 'nullable|string|max:100',
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
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

      
        // Create the class
        //$code=getNewSerialNo('feegroup');
       // $request->merge([
          //  'code' => $code, // Ensure code is uppercase
          //  'company_id' => $request->company_id ?? 1, // Decrypt company_id
          //  'branch_id' => $request->branch_id ?? 1 // Decrypt branch_id
        //]);

        $feeGroup = FeesGroup::create($request->all());

        // increase class code 
         //increaseSerialNo('feegroup'); 

        return response()->json([
            'status' => true,
            'message' => 'Fee Group created successfully',
            'data' => $feeGroup
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
      
        $feeGroup = FeesGroup::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => [
                'nullable',
                'string',
                'max:50'
            ],
            'name' => 'required|string|max:100|unique:fees_group_master,name,' . $feeGroup->id,
            'description' => 'nullable|string|max:100',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $feeGroup->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Class updated successfully',
            'data' => $feeGroup
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
        $class = FeesGroup::findOrFail($id);
        $class->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class deleted successfully'
        ]);
    }
}