<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WebController;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Spatie\Permission\Models\Permission;

class PermissionController extends WebController
{

    public $perm_obj;
    public function __construct()
    {
        $this->perm_obj = new Permission();
    }
    public function index()
    {
        if (!hasPermission('permission_view')) {
            return redirect()->back();
        }
        return view('admin.permission.index', [
            'title' => 'Permission',
            'breadcrumb' => breadcrumb([
                'Permission' => route('admin.permission.index'),
            ]),
        ]);
    }

    public function create()
    {
        if (!hasPermission('permission_create')) {
            return redirect()->back();
        }
        $categories = $this->perm_obj->where('parent_id', null)->get();
        return view('admin.permission.create', [
            'title' => "Create Permission",
            'categories' => $categories,
            'breadcrumb' => breadcrumb([
                'Permission' => route('admin.permission.index')
            ]),
        ]);
    }


    public function listing(Request $request)
    {
        try {
            // Get pagination and search parameters
            $perPage = $request->get('length', 25);
            $searchValue = $request->get('search')['value'] ?? '';

            // Base query with search functionality
            $query = Permission::with('children')
                    ->whereNull('parent_id')
                    ->orderBy('name');

            if (!empty($searchValue)) {
                $query->where('name', 'LIKE', "%{$searchValue}%")
                    ->orWhereHas('children', function ($q) use ($searchValue) {
                        $q->where('name', 'LIKE', "%{$searchValue}%");
                    });
            }

            $permissions = $query->paginate($perPage);
            $data = [];

            foreach ($permissions as $key => $permission)
            {
                // Add parent permission
                $data[] = [
                    'id' => $permission->id,
                    'no' => $permissions->perPage() * ($permissions->currentPage() - 1) + $key + 1,
                    'name' =>[
                                'display' => $permission->name,
                                'type' => 'parent',
                                'id' => $permission->id
                            ],
                    'action' => $this->generateActionButtons($permission)
                ];

                // Add child permissions under the parent
                foreach ($permission->children as $child) {
                    $data[] = [
                        'id' => $child->id,
                        'no' => '',  // Empty for hierarchy
                        'name' => [
                            'display' => $child->name,
                            'type' => 'child',
                            'parent_id' => $permission->id
                        ],
                        'action' => $this->generateActionButtons($child)
                    ];
                }
            }

            return response()->json([
                'draw' => $request->get('draw', 1),
                'recordsTotal' => Permission::count(), // Total records in DB
                'recordsFiltered' => $query->count(), // Filtered count
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('Permission Listing Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    private function generateActionButtons($permission)
    {
        $buttons = [];

        if (auth()->user()->hasPermissionTo('permission_edit')) {
            $buttons[] = [
                'type' => 'edit',
                'url' => route('admin.permission.edit', $permission->id),
                'tooltip' => 'Edit Permission'
            ];
        }

        if (auth()->user()->hasPermissionTo('permission_delete') && $permission->is_perm_delete == 1) {
            $buttons[] = [
                'type' => 'delete',
                'url' => route('admin.permission.destroy', $permission->id),
                'tooltip' => 'Delete Permission'
            ];
        }

        return $buttons;
    }


    public function store(Request $request)
    {
        $request->validate([
            'main_name' => 'nullable|string|max:255',
            'child_name' => 'nullable|string|max:255',
            'permissions' => 'nullable|array'
        ]);

        $allow_delete = $request->has('allow_delete') ? 1 : 0;

        if ($request->has('main_name') && !empty($request->main_name)) {
            $parentPermission = Permission::create([
                'name' => $request->main_name,
                'parent_id' => null,
                'guard_name' => 'web',
                'is_perm_deleted' => $allow_delete
            ]);

            // Create selected child permissions
            if ($request->has('permissions')) {
                foreach ($request->permissions as $action) {
                    Permission::create([
                        'name' => "{$request->main_name}_{$action}",
                        'parent_id' => $parentPermission->id,
                        'guard_name' => 'web',
                        'is_perm_deleted' => $allow_delete
                    ]);
                }
            }
        }

        if ($request->has('child_name') && !empty($request->child_name)) {
            if (!$request->has('parent_id')) {
                return redirect()->back()->withErrors(['message' => 'Parent Permission is required for Child Permission.']);
            }

            $parentPermission = Permission::where('id', $request->parent_id)->first();

            if ($parentPermission) {
                Permission::create([
                    'name' => $request->child_name,
                    'parent_id' => $request->parent_id,
                    'guard_name' => 'web',
                ]);
            } else {
                return redirect()->back()->withErrors(['message' => 'Selected Parent Permission does not exist.']);
            }
        }

        return redirect()->route('admin.permission.index')->with('success', 'Permission created successfully!');
    }



    public function edit($id)
    {
        if (!hasPermission('permission_edit')) {
            return redirect()->back();
        }
        $data = $this->perm_obj->find($id);
        $categories = $this->perm_obj->where('parent_id', null)->get();
        if (isset($data) && !empty($data)) {
            return view('admin.permission.create', [
                'title' => 'Category Update',
                'categories' => $categories,
                'breadcrumb' => breadcrumb([
                    'Category' => route('admin.permission.index'),
                    'edit' => route('admin.permission.edit', $id),
                ]),
            ])->with(compact('data'));
        }
        return redirect()->route('admin.permission.index');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'child_name' => ['required', 'max:255'],
        ]);

        if ($request->allow_delete) {
            $allow_delete = 1;
        } else {
            $allow_delete = 0;
        }
        $data = $this->perm_obj::find($id);
        if (isset($data) && !empty($data)) {
            $data->name = $request->child_name;
            $data->is_perm_deleted = $allow_delete;
            $data->save();

            success_session('Permission updated successfully');
        } else {
            error_session('Permission not found');
        }
        return redirect()->route('admin.permission.index');
    }

    public function destroy($id)
    {
        if (!hasPermission('permission_delete')) {
            return redirect()->back();
        }
        $data = $this->perm_obj::where('id', $id)->delete();
        if ($data) {
            success_session('Permission deleted successfully');
        } else {
            error_session('Permission not found');
        }
        return redirect()->route('admin.permission.index');
    }
}
