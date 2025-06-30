<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use App\Models\Department;
use App\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use Auth;

class UsersController extends WebController
{
    public function index()
    {
        return view('admin.user.index', [
            'title' => 'Users',
            'breadcrumb' => breadcrumb([
                'Users' => route('admin.user.index'),
            ]),
        ]);
    }

    public function listing(Request $request)
    {
        $datatable_filter = datatable_filters();
        $offset = $datatable_filter['offset'];
        $search = $datatable_filter['search'];
        $return_data = [
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        ];

        $userType = $request->get('user_type');

        if(Auth::user()->type=='superadmin'){
            $main = User::whereIn('type', ['superadmin','admin','user','subadmin','customer', 'supplier']);
        }else{
            $main = User::whereIn('type', ['user','subadmin','customer', 'supplier']);
        }
       

        if (!empty($userType) && $userType !== 'all') {
            $main->where('type', $userType);
        }

        $return_data['recordsTotal'] = $main->count();


        if (!empty($search)) {
            $main->where(function ($query) use ($search) {
                $query->AdminSearch($search);
            });
        }


        $return_data['recordsFiltered'] = $main->count();

        $all_data = $main->orderBy($datatable_filter['sort'], $datatable_filter['order'])
            ->offset($offset)
            ->limit($datatable_filter['limit'])
            ->get();

        if (!empty($all_data)) {
            foreach ($all_data as $key => $value) {
                $param = [
                    'id' => $value->id,
                    'url' => [
                        'status' => auth()->user()->hasPermissionTo('user_view')
                            ? route('admin.user.status_update')
                            : null,
                        'edit' => auth()->user()->hasPermissionTo('user_edit')
                            ? route('admin.user.edit', \Crypt::encrypt($value->id))
                            : null,
                        'change_password' => auth()->user()->hasPermissionTo('user_edit')
                            ? route('admin.change-user-password-view', \Crypt::encrypt($value->id))
                            : null,
                        'delete' => auth()->user()->hasPermissionTo('user_delete')
                            ? route('admin.user.destroy', $value->id)
                            : null,
                    ]
                ];

                $statusText = ($value->status == "active") ? "Active" : "Inactive";
                $btnClass = ($value->status == "active") ? "btn-success" : "btn-danger";

                $statusButton = '<button class="btn btn-sm ' . $btnClass . ' toggle-status"
                            data-id="' . $value->id . '"
                            data-url="' . $param['url']['status'] . '">
                            ' . $statusText . '
                         </button>';

                $return_data['data'][] = [
                    'id' => $offset + $key + 1,
                    'code' => $value->code ?? "NaN",
                    'profile_image' => get_fancy_box_html($value['profile_image']),
                    'name' => $value->name,
                    'email' => $value->email,
                    'mobile_number' => $value->country_code . ' ' . $value->mobile,
                    'status' => $statusButton,
                    'action' => $this->generate_actions_buttons($param) .
                        ' <a title="Change Password" href="' . $param['url']['change_password'] . '"
                         data-type="change-password" data-id="' . $param['id'] . '"
                         class="btn btn-sm btn-clean btn-icon btn-icon-md btnChangePassword">
                         <i class="fas fa-key"></i>
                      </a>',
                ];
            }
        }

        return $return_data;
    }


    public function changePassword($id)
    {
        if (!hasPermission('user_edit')) {
            return redirect()->back();
        }
        try {
            $decryptedId = Crypt::decrypt($id); // Decrypt the ID safely
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return back()->with('error', 'Invalid account ID.');
        }
        $data = User::find($decryptedId);
        if (!$data) {
            error_session('User not found');
            return redirect()->route('admin.user.index');
        }

        return view('admin.user.change_password', [
            'title' => 'Change Password',
            'data' => $data,
            'breadcrumb' => breadcrumb([
                'User' => route('admin.user.index'),
                'Change Password' => route('admin.change-user-password-view', $id),
            ]),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'confirm_password' => 'required|min:6',
        ]);

        $user = User::findOrFail($request->user_id);

        $user->update([
            'password' => Hash::make($request->confirm_password),
        ]);
        return redirect()->route('admin.user.index')->with('success', 'Password updated successfully For ' . $user->name);
    }

    public function destroy($id)
    {
        if (!hasPermission('user_delete')) {
            return redirect()->back();
        }

        $data = User::where('id', $id)->first();
        if ($data) {
            // $data->delete();
            success_session('User Deleted successfully');
        } else {
            error_session('User not found');
        }
        return redirect()->route('admin.user.index');
    }

    public function status_update(Request $request)
    {
        $id = $request->id;
        $response = ['status' => 0, 'message' => 'User Not Found'];

        $user = User::find($id);
        if ($user) {
            $newStatus = ($user->status == "active") ? "inactive" : "nactive"; // Toggle status
            $user->update(['status' => $newStatus]);

            $response = [
                'status' => 1,
                'message' => 'User status updated',
                'new_status' => $newStatus == "active" ? "Active" : "Inactive",
                'btn_class' => $newStatus == "active" ? "btn-success" : "btn-danger"
            ];
        }

        return response()->json($response);
    }


    public function show($id)
    {
        if (!hasPermission('user_view')) {
            return redirect()->back();
        }
        try {
            $decryptedId = Crypt::decrypt($id); // Decrypt the ID safely
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return back()->with('error', 'Invalid account ID.');
        }
        $data = User::where(['type' => 'user', 'id' => $decryptedId])->first();
        if ($data) {
            return view('admin.user.view', [
                'title' => 'View user',
                'data' => $data,
                'breadcrumb' => breadcrumb([
                    'user' => route('admin.user.index'),
                    'view' => route('admin.user.show', $id)
                ]),
            ]);
        }
        error_session('user not found');
        return redirect()->route('admin.user.index');
    }


    public function edit($id)
    {
        if (!hasPermission('user_edit')) {
            return redirect()->back();
        }

        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return back()->with('error', 'Invalid account ID.');
        }

        $data = User::find($decryptedId);
        if (!$data) {
            error_session('User not found');
            return redirect()->route('admin.user.index');
        }

        $branches = Branch::all();
        $roles = Role::where('id', '>', 1)->get();
        $departments = Department::all();
        $userBranches = $data->branches->pluck('id')->toArray();
        $userRole = $data->getRoleNames()->first();
        $userPermissions = $data->permissions->pluck('name')->toArray();

        // ✅ Get the only company's assigned permissions
        $company = Company::first(); // Only one company
        $companyPermissionNames = $company->permissions->pluck('name')->toArray();

        // ✅ Filter all permissions that are in company assigned permissions
        $permissions = Permission::whereIn('name', $companyPermissionNames)->get();

        // ✅ Group permissions
         $groupedPermissions = $permissions->groupBy(function ($permission) {
            if (is_null($permission->parent_id)) {
                return $permission->id;
            }

            $parentExists = Permission::find($permission->parent_id);
            return $parentExists ? $permission->parent_id : $permission->id;
        });

        return view('admin.user.edit', [
            'title' => 'Update User',
            'groupedPermissions' => $groupedPermissions,
            'userPermissions' => $userPermissions,
            'departments' => $departments,
            'data' => $data,
            'roles' => $roles,
            'branches' => $branches,
            'userBranches' => $userBranches,
            'userRole' => $userRole,
            'companyPermissionNames' => $companyPermissionNames, // use this in Blade
            'breadcrumb' => breadcrumb([
                'User' => route('admin.user.index'),
                'Edit' => route('admin.user.edit', $data->id),
            ]),
        ]);
    }

    
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'role' => 'required|exists:roles,id',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
            'statusdata' => 'required|in:active,inactive',
        ]);

        $user = User::findOrFail($id);

        if ($request->hasFile('profile_image')) {
            $profile_image = $request->profile_image;
            $up = upload_file('profile_image', 'user_profile_image');
            if ($up) {
                un_link_file($user->profile_image); // Remove old image if exists
                $user->profile_image = $up; // Assign new image path
            }
        }

        $user->fill([
            'email' => $request->input('email'),
            'name' => $request->input('first_name') . ' ' . $request->input('last_name'),
            'status' => $request->input('statusdata'),
            'mobile' => $request->input('mobile'),
            'assigned_ip_address' => $request->input('ipaddress') ?? null,
            'login_start_time' => $request->input('login_start_time') ?? null,
            'login_end_time' => $request->input('login_end_time') ?? null,
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        if ($request->filled('role')) {
            DB::table('role_user')->insertOrIgnore([
                'user_id' => $user->id,
                'role_id' => $request->input('role'),
            ]);
        }

        if ($request->has('branches')) {
            $user->branches()->sync($request->branches); // Sync selected branches
        } else {
            $user->branches()->detach(); // Remove all if none selected
        }

        if ($request->filled('permissions')) {
            $permissionIds = Permission::whereIn('name', $request->permissions)->pluck('id');
            $user->syncPermissions($permissionIds);
        } else {
            $user->syncPermissions([]);
        }

        return redirect()->route('admin.user.index')->with('success', 'User updated successfully!');
    }


    public function create()
    {
        if (!hasPermission('user_create')) {
            return redirect()->back();
        }

        $companySettings  = Company::first();

        $user = User::where('type','user')->count();
        if($user > $companySettings->max_employees ){
            return redirect()->back()->with('error','You Excced Your Employee Creation Limit');
        }
        $permissions = Permission::all(); // Fetch all permissions
        $branches = Branch::all();
        $roles = Role::where('id', '>', 1)->get();
        $departments = Department::all();
        $groupedPermissions = Permission::all()->groupBy(function ($permission) {
            return explode('_', $permission->name)[0]; // Extract category from permission name (first part before '_')
        });

        return view('admin.user.create', compact('groupedPermissions', 'roles', 'branches', 'departments'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|unique:users,mobile|max:20',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            // 'branch_id' => 'required'
        ]);

        // Handle file upload if there's a profile image
        if ($request->hasFile('profile_image')) {
            $profile_image = $request->profile_image;
            $up = upload_file('profile_image', 'user_profile_image');
            if ($up) {
                un_link_file($profile_image); // Remove old profile image if necessary
                $profile_image = $up; // Assign the new profile image path
            }
        }


        $sNo = getNewSerialNo('emp_code');
        $noInc = increaseSerialNo('emp_code');
        // dd($sNo);

        // Create the user
        $user = User::create([
            'name' => $request->input('name'),
            'mobile' => $request->input('mobile'),
            'password' => Hash::make($request->input('password')), // Hash password
            'email' => $request->input('email'),
            'department_id' => $request->input('department'), // Assign the selected department
            'profile_image' => $profile_image ?? "", // If no image, leave empty
            'status' => $request->input('statusData'), // Active or inactive status
            'type' => 'user', // Admin or User type
            'code' => $sNo,
            'assigned_ip_address' => $request->input('ipaddress') ?? null,
            'login_start_time' => $request->input('login_start_time') ?? null,
            'login_end_time' => $request->input('login_end_time') ?? null,
        ]);

        $user->branches()->sync($request->branches);

        // Assign the role to the user using Spatie's helper method
        if ($request->filled('role')) {
            $role = Role::findById($request->input('role')); // Fetch the selected role by ID
            if ($role) {
                $user->assignRole($role); // Assign the role to the user
            }
        }

        // Sync permissions using Spatie's helper method
        if ($request->filled('permissions')) {
            // Fetch permission IDs based on the permission names from the request
            $permissionIds = \Spatie\Permission\Models\Permission::whereIn('name', $request->input('permissions'))->pluck('id');

            // Sync the permissions by their IDs
            $user->syncPermissions($permissionIds);
        }

        // Return success message after creating the user
        return redirect()->route('admin.user.index')->with('success', 'User created successfully!');
    }

    public function checkEmail(Request $request)
    {
        $exists = User::where('email', $request->email)->exists();
        return response()->json(!$exists);
    }
}
