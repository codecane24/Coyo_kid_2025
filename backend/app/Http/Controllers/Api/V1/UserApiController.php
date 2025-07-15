<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\UserModel as User;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserApiController extends Controller
{
    /**
     * Display a listing of the users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        
        $datatable_filter = $this->datatableFilters($request);
        $offset = $datatable_filter['offset'] ?? 0;
        $search = $datatable_filter['search'];
        $userType = $request->query('user_type');
        $AurhUrerType = Auth::user()->type ?? 'admin';
        $query = $AurhUrerType == 'superadmin'
            ? User::whereIn('type', ['superadmin', 'admin', 'user', 'branch_admin', 'teacher', 'student', 'parent'])
            : User::whereIn('type', ['user','admin','branch_admin', 'teacher', 'student','parent']);

        if (!empty($userType) && $userType !== 'all') {
            $query->where('type', $userType);
        }

        $totalRecords = $query->count();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $users = $query->orderBy('id')
                       ->offset($offset)
                       ->limit($datatable_filter['limit'])
                       ->get();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'encryptid' => Crypt::encrypt($user->id),
                'code' => $user->code ?? 'NaN',
                'profile_image' => $user->profile_image ? asset($user->profile_image) : null,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'status' => $user->status,
                'actions' => $this->generateActionLinks($user),
            ];
        });

        return response()->json([
            'status' => 'true',
            'data' => $data,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
           // 'mytoken' => request()->headers->get('MyToken'),
        ], 200);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!$this->hasPermission('user_create')) {
        //    return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $companySettings = Company::first();
        $userCount = User::where('type', 'user')->count();
        if ($userCount >= $companySettings->max_employees) {
           // return response()->json(['status' => 'error', 'message' => 'Employee creation limit exceeded'], 400);
        }


        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => 'required|string|unique:users,mobile|max:20',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'role' => 'nullable|exists:roles,id',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
            'status' => 'required|in:active,inactive',
        // 'department_id' => 'nullable|exists:departments,id',
        //  'ipaddress' => 'nullable|ip',
        // 'login_start_time' => 'nullable|date_format:H:i',
        // 'login_end_time' => 'nullable|date_format:H:i',
        ]);


        if ($request->hasFile('profile_image')) {
           // $validated['profile_image'] = $this->uploadFile($request->file('profile_image'), 'user_profile_image');
        }

       // $sNo = $this->getNewSerialNo('emp_code');
        $this->increaseSerialNo('emp_code');

        $role = Role::find($validated['role']);
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'mobile' => $validated['mobile'],
            'password' => Hash::make($validated['password']),
            'email' => $validated['email'],
            'department_id' => $validated['department_id'] ?? null,
          //  'profile_image' => $validated['profile_image'] ?? null,
            'status' => $validated['status'],
            'type' => $role->name ?? 'user',
           // 'code' => $sNo,
          //  'assigned_ip_address' => $validated['ipaddress'] ?? null,
           // 'login_start_time' => $validated['login_start_time'] ?? null,
           // 'login_end_time' => $validated['login_end_time'] ?? null,
        ]);

        if (!empty($validated['branches'])) {
            $user->branches()->sync($validated['branches']);
        }

        if (!empty($validated['role'])) {
            $user->assignRole(Role::findById($validated['role']));
        }

        if (!empty($validated['permissions'])) {
            $permissionIds = Permission::whereIn('name', $validated['permissions'])->pluck('id');
            $user->syncPermissions($permissionIds);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully!',
            'data' => $user,
        ], 201);
    }

    /**
     * Display the specified user.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!$this->hasPermission('user_view')) {
           // return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Invalid account ID'], 400);
        }

        $user = User::where(['id' => $decryptedId])
                ->select('id','first_name','last_name','email','mobile','gender','profile_image','status')
                ->first();
            if ($user) {
                $user->branches = $user->branches->select('id')->pluck('id')->toArray();
                //$user->permissions = $user->permissions->pluck('name');
            }
        

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' =>$user,
        ], 200);
    }

    /**
     * Update the specified user in storage.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (!$this->hasPermission('user_edit')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Invalid account ID'], 400);
        }

        $user = User::find($decryptedId);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'role' => 'required|exists:roles,id',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
            'status' => 'required|in:active,inactive',
            'mobile' => ['nullable', 'string', Rule::unique('users')->ignore($user->id), 'max:20'],
        //    'ipaddress' => 'nullable|ip',
        //    'login_start_time' => 'nullable|date_format:H:i',
        //    'login_end_time' => 'nullable|date_format:H:i',
        ]);

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $this->uploadFile($request->file('profile_image'), 'user_profile_image');
            if ($user->profile_image) {
                $this->unlinkFile($user->profile_image);
            }
        }

        $user->update([
            'email' => $validated['email'],
            'name' => $validated['first_name'] . ' ' . ($validated['last_name'] ?? ''),
            'status' => $validated['status'],
            'mobile' => $validated['mobile'] ?? $user->mobile,
            'assigned_ip_address' => $validated['ipaddress'] ?? null,
            'login_start_time' => $validated['login_start_time'] ?? null,
            'login_end_time' => $validated['login_end_time'] ?? null,
            'profile_image' => $validated['profile_image'] ?? $user->profile_image,
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);

        if (!empty($validated['branches'])) {
            $user->branches()->sync($validated['branches']);
        } else {
            $user->branches()->detach();
        }

        if (!empty($validated['role'])) {
            DB::table('model_has_roles')->where('model_id', $user->id)->delete();
            $user->assignRole(Role::findById($validated['role']));
        }

        if (!empty($validated['permissions'])) {
            $permissionIds = Permission::whereIn('name', $validated['permissions'])->pluck('id');
            $user->syncPermissions($permissionIds);
        } else {
            $user->syncPermissions([]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully!',
            'data' => $user,
        ], 200);
    }

    /**
     * Remove the specified user from storage.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!$this->hasPermission('user_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['status' => 'success', 'message' => 'User deleted successfully'], 200);
    }

    /**
     * Update the status of the specified user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusUpdate(Request $request)
    {
        $request->validate(['id' => 'required|exists:users,id']);
        $user = User::find($request->id);

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $newStatus = $user->status == 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return response()->json([
            'status' => 'success',
            'message' => 'User status updated',
            'data' => [
                'new_status' => $newStatus,
                'btn_class' => $newStatus == 'active' ? 'btn-success' : 'btn-danger',
            ],
        ], 200);
    }

    /**
     * Update the password of the specified user.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request, $id)
    {
        if (!$this->hasPermission('user_edit')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Invalid account ID'], 400);
        }

        $user = User::find($decryptedId);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully for ' . $user->name,
        ], 200);
    }

    /**
     * Check if email is available.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'status' => 'success',
            'available' => !$exists,
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
     * Helper method to parse datatable filters.
     *
     * @param Request $request
     * @return array
     */
    private function datatableFilters(Request $request)
    {
        return [
            'offset' => $request->query('start', 0),
            'limit' => $request->query('length', 10),
            'search' => $request->query('search')['value'] ?? null,
         //   'sort' => $request->query('columns')[$request->query('order')[0]['column']]['data'] ?? 'id',
            'order' => $request->query('order')[0]['dir'] ?? 'asc',
        ];
    }

    /**
     * Helper method to generate action links.
     *
     * @param User $user
     * @return array
     */
    private function generateActionLinks($user)
    {
        $actions = [];
        if ($this->hasPermission('user_view')) {
            $actions['view'] = route('api.v1.users.show', Crypt::encrypt($user->id));
        }
        if ($this->hasPermission('user_edit')) {
            $actions['edit'] = route('api.v1.users.update', Crypt::encrypt($user->id));
            $actions['change_password'] = route('api.v1.users.update-password', Crypt::encrypt($user->id));
        }
        if ($this->hasPermission('user_delete')) {
            $actions['delete'] = route('api.v1.users.destroy', $user->id);
        }
        return $actions;
    }

    /**
     * Helper method to upload files.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @return string|null
     */
    private function uploadFile($file, $directory)
    {
        return $file->store($directory, 'public');
    }

    /**
     * Helper method to unlink files.
     *
     * @param string $path
     * @return void
     */
    private function unlinkFile($path)
    {
        if ($path && \Storage::disk('public')->exists($path)) {
            \Storage::disk('public')->delete($path);
        }
    }

    /**
     * Helper method to get new serial number.
     *
     * @param string $key
     * @return string
     */
    private function getNewSerialNo($key)
    {
        // Implement your serial number logic here
        return 'EMP-' . str_pad(User::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Helper method to increase serial number.
     *
     * @param string $key
     * @return void
     */
    private function increaseSerialNo($key)
    {
        // Implement your serial number increment logic here
    }
}