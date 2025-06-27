<?php

namespace App\Http\Controllers\General;

use App\GeneralSettings;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\Admin\General\PasswordUpdateRequest;
use App\Models\Branch;
use App\Models\Department;
use App\Models\UserModel as User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Models\UserBranch;
use App\Models\FinancialYear;
use Carbon\Carbon;


class GeneralController extends WebController
{
    public function Panel_Login()
    {
        return view('general.login', [
            'header_panel' => false,
            'title' => __('admin.lbl_login'),
        ]);
    }

    public function login(Request $request)
    {
        
        if ($request->isBranch != 1 || $request->isBranch == null) {
          
            $remember = ($request->remember) ? true : false;
            $request->validate(['username' => 'required', 'password' => 'required']);

            $find_field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? "email" : "username";
            $creds = [$find_field => $request->username, 'password' => $request->password, 'status' => 'active'];
            $user = User::where('email', $request->username)->first();
           
            if (!$user) {
                session()->flash('error', 'User Not Found');
                return redirect()->back();
            }

            $clientIp = $request->ip();
            if ($user->assigned_ip_address && $clientIp !== $user->assigned_ip_address) {
                session()->flash('error', 'Login not allowed from this IP address.');
                return redirect()->back();
            }

             $currentTime = now()->format('H:i:s');
            if ($user->login_start_time && $user->login_end_time) {
                if ($currentTime < $user->login_start_time || $currentTime > $user->login_end_time) {
                    $startTime = date('h:i A', strtotime($user->login_start_time));
                    $endTime = date('h:i A', strtotime($user->login_end_time));
                    session()->flash('error', "Login not allowed at this time. Allowed time is between $startTime and $endTime.");
                    return redirect()->back();
                }
            }


             $userBranches = UserBranch::where('user_id', $user->id)->count();

            if ($userBranches > 1) {
                $encryptedUserId = Crypt::encryptString($user->id);
                return redirect()->route('admin.select_branch', ['user' => $encryptedUserId]);
            }

            if ($user && Hash::check($request->password, $user->password)) {

                Auth::login($user, $remember);

                $user->last_login=Carbon::now()->toDateTimeString();
                $user->save();
                $branch = Branch::where('id', $user->branch_id)->first();
                //====Assing Financial Year Id in Auth=====
                $fydata = $this->getFinancialYearId();
                $request->session()->regenerate();
                session(['fyear' => $fydata]);
                Auth::user()->setAttribute('fyear', $fydata);
                Auth::user()->setAttribute('branch_id', $user->branch_id);
                Auth::user()->setAttribute('branch_name', $branch->name);
                Session::put('branch_id', $user->branch_id);
                Session::put('branch_name', $branch->name);

                if (Company::count() == 0) {
                    return redirect()->route('admin.company.create');
                }
                
                // Redirect based on user type
                if ( ($user->type == "customer" || $user->type == "Customer")  || ($user->type == 'supplier')) {
                    return redirect()->route('user.dashboard'); // Redirect client users to user dashboard
                } else {
                    return redirect()->route(getDashboardRouteName()); // Default admin dashboard
                }
            } else {
                if ($find_field == "username") {
                    flash_session('error', 'Please enter a valid username or password');
                } else {
                    flash_session('error', 'Please enter a valid email or password');
                }
            }
        } else {

            $remember = ($request->remember) ? true : false;
            $user = User::where('id', $request->userId)->first();
            if ($user) {
                Auth::login($user);
                if (Company::count() == 0) {
                    return redirect()->route('admin.company.create');
                }
                $branch = Branch::find($request->branch_id);
                Session::put('branch_id', $request->branch_id);
                Session::put('branch_name', $branch->name);

                //====Assign Financial Year Id in Auth=====
                $fydata = $this->getFinancialYearId();
                session(['fyear' => $fydata]);
                Auth::user()->setAttribute('fyear', $fydata);
                Auth::user()->setAttribute('branch_id', $request->branch_id);
                Auth::user()->setAttribute('branch_name', $branch->name);

                // Redirect based on user type
                if ($user->type == "Customer" || $user->type == "customer") {
                    return redirect()->route('user.dashboard'); // Redirect client users to user dashboard
                } else {
                    return redirect()->route(getDashboardRouteName()); // Default admin dashboard
                }
            } else {
                flash_session('error', 'Please enter a valid email or password');
            }
        }



        return redirect()->back();
    }


    public function SelectBranch(Request $request)
    {
        if ($request->has('user')) {
            try {
                $userId = Crypt::decryptString($request->query('user'));
                $user = User::findOrFail($userId); // Fetch user from the database
                $encryptedUserId = $userId;
                $userBranches = UserBranch::with('branch')->where('user_id', $user->id)->get();
                return view('admin.branch.select', [
                    'title' => 'Select Branch',
                    'user' => $user,
                    'userBranches' => $userBranches,
                    'encryptedUserId' => $encryptedUserId,
                    'breadcrumb' => breadcrumb([
                        'Select Branch' => route('admin.select_branch'),
                    ]),
                ]);
            } catch (\Exception $e) {
                return redirect()->route('admin.dashboard')->with('error', 'Invalid user data.');
            }
        }

        return redirect()->route('admin.dashboard')->with('error', 'User not found.');
    }

    public function Panel_Pass_Forget()
    {
        return view('general.password_reset', [
            'header_panel' => false,
            'title' => __('admin.lbl_forgot_password'),
        ]);
    }

    public function ForgetPassword(Request $request)
    {
        User::password_reset($request->email);
        return redirect()->back();
    }


    public function totalusers()
    {
        $users = User::select(DB::raw('date(created_at) as userdate'), 'created_at', DB::raw('count(id) as totaluser'))->where('type', 'user')->groupBy('userdate')->get();

        $maindata = [];
        // echo $current_date_time = Carbon::now()->toDateTimeString();

        // die;


        if (count($users) > 0) {
            foreach ($users as $user) {

                $detail = [];
                $detail[] = strtotime($user->userdate . ' 23:00:00') * 1000;
                $detail[] = $user->totaluser;
                $maindata[] = $detail;
            }
        }
        return $maindata;
    }


    public function Admin_dashboard(Request $request)
    {

        //  return Auth::user();
        $user_data = $request->user();
        $user_selected_country = $user_data->country;
        return view('admin.general.dashboard', [
            'title' => __('admin.lbl_dashboard'),
            'user_count' => User::where(['type' => 'user'])->count(),
        ]);
    }


    public function get_update_password(Request $request)
    {
        $title = 'Change Password';
        $user_data = $request->user();
        $view = ($user_data->type == "vendor") ? 'vendor.general.update_password' : 'admin.general.update_password';
        return view($view, [
            'title' => $title,
            'breadcrumb' => breadcrumb([
                $title => route('admin.get_update_password'),
            ]),
        ]);
    }

    public function get_site_settings()
    {
        $title = 'Site setting';
        return view('admin.general.site_settings', [
            'title' => $title,
            'fields' => GeneralSettings::where('status', 'active')->orderBy('order_number', 'DESC')->get(),
            'breadcrumb' => breadcrumb([
                $title => route('admin.get_site_settings'),
            ]),
        ]);
    }

    public function site_settings(Request $request)
    {
        $all_req = $request->except('_token');
        foreach ($all_req as $key => $value) {
            $setting = GeneralSettings::find($key);
            if ($request->hasFile($key)) {
                $up = $this->upload_file($key, 'admin_upload');
                if ($up) {
                    un_link_file($setting->value);
                    $setting->update(['value' => $up]);
                }
            } else {
                $setting->update(['value' => $value]);
            }
        }
        success_session(__('admin.site_setting_updated'));
        return redirect()->route('admin.get_site_settings');
    }

    public function update_password(PasswordUpdateRequest $request)
    {
        $request->update_pass();
        return redirect()->back();
    }

    public function get_profile(Request $request)
    {
        $user_data = $request->user();

        // Group permissions by parent_id or self if no parent_id
        $groupedPermissions = Permission::all()->groupBy(function ($permission) {
            if (is_null($permission->parent_id)) {
                // Group by its own ID if no parent_id
                return $permission->id;
            }

            // Check if parent exists
            $parentExists = Permission::find($permission->parent_id);
            return $parentExists ? $permission->parent_id : $permission->id;
        });

        $userPermissions = $user_data->permissions->pluck('name')->toArray();
        $userRole = $user_data->roles->first();
        // dd($userRole);

        // Determine view based on user type
        $view = ($user_data->type == "vendor") ? 'vendor.general.profile' : 'general.profile';

        $branch = Branch::where('id', $user_data->branch_id)->first();

        $department = Department::where('id', $user_data->department_id)->first();
        return view($view, [
            'title' => 'Profile',
            'groupedPermissions' => $groupedPermissions,
            'userPermissions' => $userPermissions,
            'user' => $user_data,
            'branch' => $branch,
            'department' => $department,
            'userRole' => $userRole,
            'breadcrumb' => breadcrumb([
                'Profile' => route('admin.profile'),
            ]),
        ]);
    }


    public function logout()
    {
        $name = getDashboardRouteName();
        Auth::logout();
        Session::flush();
        return redirect()->route($name);
    }

    public function availability_checker(Request $request)
    {
        $count = 0;
        $type = $request->type;
        $val = $request->val;
        $user_id = Auth::id() ?? 0;
        if ($type == "username" || $type == "email") {
            $count = User::where($type, $val)->where('id', '!=', $user_id)->count();
        }
        return $count ? "false" : "true";
    }

    public function user_availability_checker(Request $request)
    {
        $id = $request->id ?? 0;
        $query = User::where('id', '!=', $id);
        if ($request->username) {
            $query = $query->where('username', $request->username);
        } elseif ($request->email) {
            $query = $query->where('email', $request->email);
        } elseif ($request->number && $request->country_code) {
            $query = $query->where(['mobile' => $request->number, 'country_code' => $request->country_code]);
        } else {
            return 'false';
        }

        return $query->count() ? "false" : "true";
    }

    public function post_profile(Request $request)
    {
        $user_data = $request->user();

        // Validation rules
        $rules = [
            'profile_image' => ['file', 'image'],
            'name' => ['required', 'max:255'],
            'email' => [
                'required',
                'max:255',
                Rule::unique('users')->ignore($user_data->id)->whereNull('deleted_at'),
            ],
            // Optional: Validate permissions (if sent in the request)
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'], // Ensure permissions exist in the database
        ];

        // Validate request
        $req = $request->validate($rules);

        // Update user basic info
        $user_data->update([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
        ]);

        // Handle profile image update
        $profile_image = $user_data->getRawOriginal('profile_image');
        if ($request->hasFile('profile_image')) {
            $up = upload_file('profile_image', 'user_profile_image');
            if ($up) {
                un_link_file($profile_image); // Delete old image
                $profile_image = $up;
                $user_data->update(['profile_image' => $profile_image]);
            }
        }

        // Update user permissions (if provided)
        if ($request->filled('permissions')) {
            // Retrieve permissions by name
            $permissions = \Spatie\Permission\Models\Permission::whereIn('name', $request->input('permissions'))->get();
            // Sync permissions (passing permission objects or names)
            $user_data->syncPermissions($permissions);
        }

        // Success message and redirect
        success_session('Profile and permissions updated successfully');
        return redirect()->back();
    }


    public function forgot_password_view($token)
    {
        //        $user = User::where(['status' => 'active', 'reset_token' => $token])->first();
//        if ($user) {
        return view('general.reset_password', [
            'token' => $token,
            'header_panel' => false,
            'title' => 'Password reset',
        ]);
        //        }
//        error_session('Invalid password token');
//        return redirect()->route('admin.login');
    }

    public function forgot_password_post(Request $request)
    {
        $request->validate([
            'reset_token' => ['required', Rule::exists('users', 'reset_token')->whereNull('deleted_at')],
            'password' => ['required'],
        ], [
            'reset_token.exists' => 'Invalid password token',
        ]);
        $user = User::where('reset_token', $request->reset_token)->first();
        $user->update(['reset_token' => null, 'password' => $request->password]);
        success_session('Password Updated successfully');
        return redirect()->back();
    }

    public function ClearCache()
    {
        Artisan::call('optimize:clear');
        return "Cleared!";
    }


    private function getFinancialYearId()
    {
        $financialYear = FinancialYear::where('status', 1)->first();
        return $financialYear ? $financialYear : null;
    }

}
