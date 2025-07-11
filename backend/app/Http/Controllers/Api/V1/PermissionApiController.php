<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\ModuleGroup;

class PermissionApiController extends Controller
{
    

    public function index(Request $request)
    {
      
        try {
                $search = $request->get('search', '');
                $withChildren = $request->get('with_children', true);

                // Base query with eager loading
                $query = ModuleGroup::with(['modules' => function($q) use ($withChildren) {
                    $q->whereNull('parent_id');
                    if ($withChildren) {
                        $q->with('children');
                    }
                }])->whereHas('modules');

                // Apply search filter if provided
                if (!empty($search))
                {
                    $query->whereHas('modules', function ($q) use ($search) {
                        $q->whereNull('parent_id')
                        ->where(function($subQuery) use ($search) {
                            $subQuery->where('name', 'LIKE', "%{$search}%")
                                    ->orWhereHas('children', function ($q) use ($search) {
                                        $q->where('name', 'LIKE', "%{$search}%");
                                    });
                        });
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
                            'name' => "{$request->name}_{$action['name']}",
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
                                ->orderBy('name')->first()
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
            'name' => 'required|string|max:100',
            'permissions' => 'array|nullable',
            'permissions.*.id' => 'nullable|integer|exists:permissions,id',
            'permissions.*.name' => 'required|string|max:100',
            'child_name' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:permissions,id',
            'allow_delete' => 'nullable|boolean'
        ]);

        $permission = Permission::findOrFail($id);
        $allow_delete = $request->input('allow_delete', false);

        // Update parent permission
        if (is_null($permission->parent_id)) {
            $permission->update([
                'name' => $request->name,
                'is_perm_deleted' => $allow_delete
            ]);

            // Handle child permissions update
            if ($request->has('permissions')) {
                $existingChildIds = $permission->children()->pluck('id')->toArray();
                $submittedChildIds = [];
                
                foreach ($request->permissions as $permissionData) {
                    // Update existing permission
                    if (!empty($permissionData['id'])) {
                        $childPermission = Permission::where('id', $permissionData['id'])
                            ->where('parent_id', $permission->id)
                            ->firstOrFail();
                            
                        $childPermission->update([
                            'name' => $permissionData['name'],
                            'is_perm_deleted' => $allow_delete
                        ]);
                        
                        $submittedChildIds[] = $permissionData['id'];
                    } 
                    // Create new permission
                    else {
                        Permission::create([
                            'name' => $permissionData['name'],
                            'parent_id' => $permission->id,
                            'guard_name' => 'web',
                            'is_perm_deleted' => $allow_delete
                        ]);
                    }
                }
                
                // Delete permissions that weren't submitted (removed from array)
                // $toDelete = array_diff($existingChildIds, $submittedChildIds);
                // if (!empty($toDelete)) {
                //     Permission::whereIn('id', $toDelete)->delete();
                // }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Parent permission updated successfully',
                'data' => Permission::with('children')
                          ->where('id', $permission->id)
                          ->first()
            ], 200);
        }
        // Update child permission
        else {
            if ($request->has('child_name')) {
                $permission->update([
                    'name' => $request->child_name,
                    'is_perm_deleted' => $allow_delete
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Child permission updated successfully',
                    'data' => new PermissionResource($permission->load('parent'))
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Child name is required for updating child permission'
            ], 422);
        }
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