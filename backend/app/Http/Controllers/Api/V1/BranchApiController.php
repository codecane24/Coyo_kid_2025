<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchApiController extends Controller
{
    /**
     * Display a listing of the branches.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // if (!$this->hasPermission('branch_view')) {
        //     return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        // }

       $userid = Auth::id();
        $branches = Branch::get();

        $data = $branches->map(function ($branch) {
            return [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'address' => $branch->address,
                'company_id' => $branch->company_id,
                'actions' => $this->generateActionLinks($branch),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }

    /**
     * Store a newly created branch in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!$this->hasPermission('branch_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code',
            'address' => 'required|string|max:500',
        ]);

        $branch = Branch::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'address' => $validated['address'],
            'company_id' => Auth::user()->company_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Branch created successfully.',
            'data' => [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'address' => $branch->address,
                'company_id' => $branch->company_id,
            ],
        ], 201);
    }

    /**
     * Display the specified branch.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!$this->hasPermission('branch_view')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $branch = Branch::where('company_id', Auth::user()->company_id)->find($id);
        if (!$branch) {
            return response()->json(['status' => 'error', 'message' => 'Branch not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'address' => $branch->address,
                'company_id' => $branch->company_id,
            ],
        ], 200);
    }

    /**
     * Update the specified branch in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (!$this->hasPermission('branch_edit')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $branch = Branch::where('company_id', Auth::user()->company_id)->find($id);
        if (!$branch) {
            return response()->json(['status' => 'error', 'message' => 'Branch not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('branches')->ignore($id)],
            'address' => 'required|string|max:500',
        ]);

        $branch->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'address' => $validated['address'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Branch updated successfully.',
            'data' => [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'address' => $branch->address,
                'company_id' => $branch->company_id,
            ],
        ], 200);
    }

    /**
     * Remove the specified branch from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!$this->hasPermission('branch_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $branch = Branch::where('company_id', Auth::user()->company_id)->find($id);
        if (!$branch) {
            return response()->json(['status' => 'error', 'message' => 'Branch not found'], 404);
        }

        $branch->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Branch deleted successfully',
        ], 200);
    }

    /**
     * Helper method to check permissions.
     *
     * @param string $permission
     * @return bool
     */
    private function hasPermission($permission)
    {
        //return Auth::user()->hasPermissionTo($permission);
    }

    /**
     * Helper method to generate action links.
     *
     * @param Branch $branch
     * @return array
     */
    private function generateActionLinks($branch)
    {
        $actions = [];
        if ($this->hasPermission('branch_view')) {
            $actions['view'] = route('api.v1.branches.show', $branch->id);
        }
        if ($this->hasPermission('branch_edit')) {
            $actions['edit'] = route('api.v1.branches.update', $branch->id);
        }
        if ($this->hasPermission('branch_delete')) {
            $actions['delete'] = route('api.v1.branches.destroy', $branch->id);
        }
        return $actions;
    }
}