<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AcademicYearController extends Controller
{
    public function index()
    {
        $years = AcademicYear::all();
        return response()->json([
            'status' => 'success',
            'data' => $years
        ]);
    }

    public function show($id)
    {
        $year = AcademicYear::find($id);
        if (!$year) {
            return response()->json(['status' => 'error', 'message' => 'Academic Year not found'], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $year
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:financial_years,code',
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'parent_year' => 'nullable|integer|exists:financial_years,id',
            'previous_year' => 'nullable|integer|exists:financial_years,id',
            'status' => 'required|integer|in:0,1,2,3',
            'closed_on' => 'nullable|date',
            'closed_by' => 'nullable|integer',
            'description' => 'nullable|string',
            'created_by' => 'nullable|integer',
            'company_id' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $year = AcademicYear::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Academic Year created successfully',
            'data' => $year
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $year = AcademicYear::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:financial_years,code,' . $year->id,
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'parent_year' => 'nullable|integer|exists:financial_years,id',
            'previous_year' => 'nullable|integer|exists:financial_years,id',
            'status' => 'required|integer|in:0,1,2,3',
            'closed_on' => 'nullable|date',
            'closed_by' => 'nullable|integer',
            'description' => 'nullable|string',
            'created_by' => 'nullable|integer',
            'company_id' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $year->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Academic Year updated successfully',
            'data' => $year
        ]);
    }

    public function destroy($id)
    {
        $year = AcademicYear::findOrFail($id);
        $year->delete();

        return response()->json([
            'status' => true,
            'message' => 'Academic Year deleted successfully'
        ]);
    }
}
