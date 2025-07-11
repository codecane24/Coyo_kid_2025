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
                'group_id' => 'nullable|exists:module_group,id',
                'allow_delete' => 'nullable|boolean'
            ]);

            $allow_delete = $request->input('allow_delete', false);

            if ($request->has('name') && !empty($request->name)) {
                $parentPermission = Permission::create([
                    'name' => $request->name,
                    'group_id' => $request->group_id ?? null,
                    'parent_id' => null,
                    'guard_name' => 'web',
                    'is_perm_deleted' => $allow_delete
                ]);

                if ($request->has('permissions')) {
                    foreach ($request->permissions as $action) {
                        Permission::create([
                            'name' => "{$action}",
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
            // Validate the request
            $request->validate([
                'name' => 'required|string|max:100',
                'permissions' => 'array|nullable',
                'group_id' => 'nullable|exists:module_group,id',
                'allow_delete' => 'nullable|boolean'
            ]);

            // Find the parent permission
            $parentPermission = Permission::findOrFail($id);

            // Get allow_delete value, default to false
            $allow_delete = $request->input('allow_delete', false);

            if ($request->has('name') && !empty($request->name)) {
                // Update the parent permission
                $parentPermission->update([
                    'name' => $request->name,
                    'group_id' => $request->group_id ?? null,
                    'guard_name' => 'web',
                    'is_perm_deleted' => $allow_delete
                ]);

                // Handle child permissions
                if ($request->has('permissions')) {
                    // Get existing child permissions
                    $existingChildren = Permission::where('parent_id', $parentPermission->id)
                    ->pluck('name')->toArray();

                    // New permissions from request
                    $newPermissions = $request->permissions ?? [];

                    // Create or update child permissions
                    foreach ($newPermissions as $action) {
                        Permission::updateOrCreate(
                            [
                                'name' => $action,
                                'parent_id' => $parentPermission->id,
                                'guard_name' => 'web'
                            ],
                            [
                                'is_perm_deleted' => $allow_delete
                            ]
                        );
                    }

                    // Delete child permissions that are no longer in the request
                    $permissionsToDelete = array_diff($existingChildren, $newPermissions);
                    if (!empty($permissionsToDelete)) {
                        Permission::where('parent_id', $parentPermission->id)
                            ->whereIn('name', $permissionsToDelete)
                            ->delete();
                    }
                } else {
                    // If no permissions provided, delete all child permissions
                    //Permission::where('parent_id', $parentPermission->id)->delete();
                }

                // Fetch the updated parent permission with its children
                $updatedPermission = Permission::with('children')
                    ->where('id', $parentPermission->id)
                    ->orderBy('name')
                    ->first();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Permission updated successfully',
                    'data' => $updatedPermission
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'No valid permission data provided'
            ], 422);
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