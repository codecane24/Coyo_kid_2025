<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    protected $role_obj;

    public function __construct()
    {
        $this->role_obj = new Role();
    }

    /**
     * Display a listing of the roles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $roles = $this->role_obj::where('status', 1)->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'status' => $role->status ? 'Active' : 'Inactive',
                'permissions' => $role->permissions->pluck('name'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $roles,
        ], 200);
    }

    /**
     * Store a newly created role in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!$this->hasPermission('role_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
            'status' => 'required|in:active,inactive',
        ]);

        $status = $validated['status'] === 'active' ? 1 : 0;

        $role = Role::create([
            'name' => $validated['name'],
            'status' => $status,
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'status' => 'success',
            'message' => 'Role created successfully.',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'status' => $role->status ? 'Active' : 'Inactive',
                'permissions' => $role->permissions->pluck('name'),
            ],
        ], 201);
    }

    /**
     * Display the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!$this->hasPermission('role_view')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $role = $this->role_obj::find($id);
        if (!$role) {
            return response()->json(['status' => 'error', 'message' => 'Role not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'status' => $role->status ? 'Active' : 'Inactive',
                'permissions' => $role->permissions->pluck('name'),
            ],
        ], 200);
    }

    /**
     * Update the specified role in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (!$this->hasPermission('role_edit')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $role = $this->role_obj::find($id);
        if (!$role) {
            return response()->json(['status' => 'error', 'message' => 'Role not found'], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('roles')->ignore($id)],
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
            'status' => 'required|in:active,inactive',
        ]);

        $status = $validated['status'] === 'active' ? 1 : 0;

        $role->update([
            'name' => $validated['name'],
            'status' => $status,
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'status' => 'success',
            'message' => 'Role updated successfully.',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'status' => $role->status ? 'Active' : 'Inactive',
                'permissions' => $role->permissions->pluck('name'),
            ],
        ], 200);
    }

    /**
     * Remove the specified role from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!$this->hasPermission('role_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $role = $this->role_obj::find($id);
        if (!$role) {
            return response()->json(['status' => 'error', 'message' => 'Role not found'], 404);
        }

        if ($role->is_perm_delete != 1) {
            return response()->json(['status' => 'error', 'message' => 'Role cannot be deleted'], 403);
        }

        $role->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Role deleted successfully',
        ], 200);
    }

    /**
     * Get a listing of roles for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listing(Request $request)
    {
        $roles = $this->role_obj::all();

        $data = $roles->map(function ($role) {
            $statusText = $role->status == 1 ? 'Active' : 'Inactive';
            $btnClass = $role->status == 1 ? 'btn-success' : 'btn-danger';

            return [
                'id' => $role->id,
                'name' => $role->name,
                'status' => [
                    'text' => $statusText,
                    'class' => $btnClass,
                    'id' => $role->id,
                    'url' => $this->hasPermission('role_edit') ? route('api.v1.roles.status-update') : null,
                ],
                'action' => $this->generateActionLinks($role),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'recordsTotal' => $roles->count(),
            'recordsFiltered' => $roles->count(),
        ], 200);
    }

    /**
     * Update the status of the specified role.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusUpdate(Request $request)
    {
        if (!$this->hasPermission('role_edit')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $request->validate(['id' => 'required|exists:roles,id']);

        $role = $this->role_obj::find($request->id);
        if (!$role) {
            return response()->json(['status' => 'error', 'message' => 'Role not found'], 404);
        }

        $newStatus = $role->status == 1 ? 0 : 1;
        $role->update(['status' => $newStatus]);

        return response()->json([
            'status' => 'success',
            'message' => 'Role status updated',
            'data' => [
                'new_status' => $newStatus == 1 ? 'Active' : 'Inactive',
                'btn_class' => $newStatus == 1 ? 'btn-success' : 'btn-danger',
            ],
        ], 200);
    }

    /**
     * Get permissions for a specific role.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissionsForRole(Request $request)
    {
        $request->validate(['role_id' => 'required|exists:roles,id']);

        $role = Role::find($request->role_id);
        if (!$role) {
            return response()->json(['status' => 'error', 'message' => 'Role not found', 'permissions' => []], 404);
        }

        $permissions = $role->permissions->pluck('name')->toArray();

        return response()->json([
            'status' => 'success',
            'data' => [
                'permissions' => $permissions,
            ],
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
        return Auth::user()->hasPermissionTo($permission);
    }

    /**
     * Helper method to generate action links.
     *
     * @param Role $role
     * @return array
     */
    private function generateActionLinks($role)
    {
        $actions = [];
        if ($this->hasPermission('role_edit')) {
            $actions['edit'] = route('api.v1.roles.update', $role->id);
        }
        if ($this->hasPermission('role_delete') && $role->is_perm_delete == 1) {
            $actions['delete'] = route('api.v1.roles.destroy', $role->id);
        }
        return $actions;
    }
}