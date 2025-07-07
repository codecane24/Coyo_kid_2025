<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionApiController extends Controller
{
    

    public function index(Request $request)
    {
      
        try {
            $search = $request->get('search', '');
            $withChildren = $request->get('with_children', false);

            $query = Permission::with('children')->whereNull('parent_id')
            ->orderBy('name');

            if (!empty($search)) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhereHas('children', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    });
            }

            $permissions = $query->get();

            return response()->json($permissions);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'permissions' => 'array|nullable',
                'child_name' => 'nullable|string|max:100',
                'parent_id' => 'nullable|exists:permissions,id',
                'allow_delete' => 'nullable|boolean'
            ]);

            $allow_delete = $request->input('allow_delete', false);

            if ($request->has('name') && !empty($request->name)) {
                $parentPermission = Permission::create([
                    'name' => $request->name,
                    'parent_id' => null,
                    'guard_name' => 'web',
                    'is_perm_deleted' => $allow_delete
                ]);

                if ($request->has('permissions')) {
                    foreach ($request->permissions as $action) {
                        Permission::create([
                            'name' => "{$request->name}_{$action}",
                            'parent_id' => $parentPermission->id,
                            'guard_name' => 'web',
                            'is_perm_deleted' => $allow_delete
                        ]);
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Parent permission created successfully',
                    'data' =>  $query = Permission::with('children')
                                ->where('parent_id',$parentPermission->id)
                                ->orderBy('name')->first(),
                ], 201);
            }

            if ($request->has('child_name') && !empty($request->child_name)) {
                if (!$request->has('parent_id')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Parent Permission is required for Child Permission.'
                    ], 422);
                }

                $parentPermission = Permission::find($request->parent_id);

                if ($parentPermission) {
                    $childPermission = Permission::create([
                        'name' => $request->child_name,
                        'parent_id' => $request->parent_id,
                        'guard_name' => 'web',
                        'is_perm_deleted' => $allow_delete
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Child permission created successfully',
                        'data' => new PermissionResource($childPermission->load('parent'))
                    ], 201);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Selected Parent Permission does not exist.'
                ], 404);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'No valid permission data provided'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error creating permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $permission = Permission::with('children', 'parent')->findOrFail($id);
            return new PermissionResource($permission);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => ['required', 'max:255'],
                'allow_delete' => ['nullable', 'boolean']
            ]);

            $permission = Permission::findOrFail($id);
            
            // Prevent updating parent_id for child permissions
            if ($permission->parent_id && $request->has('parent_id')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot change parent of a child permission'
                ], 422);
            }

            $permission->name = $request->name;
            $permission->is_perm_deleted = $request->input('allow_delete', $permission->is_perm_deleted);
            $permission->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Permission updated successfully',
                'data' => new PermissionResource($permission)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            
            // Check if this permission has children
            if ($permission->children()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete permission with child permissions'
                ], 422);
            }
            
            if ($permission->is_perm_deleted) {
                $permission->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Permission deleted successfully'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'This permission cannot be deleted'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function permissionTree()
    {
        try {
            $permissions = Permission::with('children')
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get();

            return new PermissionCollection($permissions);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching permission tree',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}