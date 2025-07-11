<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $subjects = Subject::select(
            'id',
            'code',
            'name',
            'subject_code',
            'subject_type',
            'status',
            'created_at',
            'updated_at'
        )->get();

        return response()->json($subjects);
    }

    /**
     * Store a newly created subject in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:subjects,code',
            'name' => 'required|string|max:255',
            'subject_code' => 'required|integer|unique:subjects,subject_code',
            'subject_type' => 'required|in:Theory,Practical',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subject = Subject::create($request->only([
            'code',
            'name',
            'subject_code',
            'subject_type',
            'status',
        ]));

        return response()->json(['message' => 'Subject created successfully', 'data' => $subject], 201);
    }

    /**
     * Display the specified subject.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        return response()->json($subject);
    }

    /**
     * Update the specified subject in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:subjects,code,' . $id,
            'name' => 'required|string|max:255',
            'subject_code' => 'required|integer|unique:subjects,subject_code,' . $id,
            'subject_type' => 'required|in:Theory,Practical',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subject->update($request->only([
            'code',
            'name',
            'subject_code',
            'subject_type',
            'status',
        ]));

        return response()->json(['message' => 'Subject updated successfully', 'data' => $subject]);
    }

    /**
     * Remove the specified subject from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        $subject->delete();

        return response()->json(['message' => 'Subject deleted successfully']);
    }
}
