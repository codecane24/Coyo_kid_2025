<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ResponseController;
use Illuminate\Validation\Rule;
use App\Models\UserModel as User;
use App\Models\UserBranch;  
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class UserController extends ResponseController
{


    public function login(Request $request)
    {
     
        if (!$request->isBranch || $request->isBranch != 1) {
            // Validate request
            $request->validate([
                'username' => 'required',
                'password' => 'required',
            ]);

            // Determine login field (email, username, mobile, or code)
            $loginInput = $request->username;
            $findField = 'username'; // default
            
            if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                $findField = 'email';
            } elseif (is_numeric($loginInput)) {
                // Check if it's a mobile number (assuming mobile is stored as string)
                $findField = 'mobile';
            } else {
                // Check if it matches a user code pattern (adjust as needed)
                $findField = 'code';
            }

            // Find user by any of the possible fields
            $user = User::where(function($query) use ($loginInput) {
                $query->where('email', $loginInput)
                    ->orWhere('username', $loginInput)
                    ->orWhere('mobile', $loginInput)
                    ->orWhere('code', $loginInput);
            })->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404);
            }

            // Check IP address restriction
            $clientIp = $request->ip();
            if ($user->assigned_ip_address && $clientIp !== $user->assigned_ip_address) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Login not allowed from this IP address',
                ], 403);
            }

      

            // Check if user has multiple branches
            $userBranches = UserBranch::where('user_id', $user->id)->count();
            if ($userBranches > 1) {
                $encryptedUserId = Crypt::encryptString($user->id);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Multiple branches detected, please select a branch',
                    'data' => [
                        'user_id' => $encryptedUserId,
                        'branches' => UserBranch::where('user_id', $user->id)
                            ->with('branch')
                            ->get()
                            ->map(function ($branch) {
                                return [
                                    'id' => $branch->branch->id,
                                    'name' => $branch->branch->name,
                                    'code' => $branch->branch->code,
                                ];
                            }),
                    ],
                ], 200);
            }

            // Authenticate user
            if ($user && Hash::check($request->password, $user->password)) {
                // Log in the user and generate a token
                Auth::login($user, $request->remember ?? false);
                $token = token_generator();

                // Update last login
                $user->last_login = Carbon::now()->toDateTimeString();
                $user->save();

                // Fetch branch details
                $branch = Branch::where('id', $user->branch_id)->first();
                if (!$branch) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Branch not found',
                    ], 404);
                }

                // Get financial year data
                $fydata = $this->getFinancialYearId();

                // Prepare user data for response
                $userData = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'code' => $user->code,
                    'username' => $user->username,
                    'email' => $user->email,
                    'type' => $user->type,
                    'branch_id' => $user->branch_id,
                    'branch_name' => $branch->name,
                    'fyear' => $fydata,
                    'role' => $user->role, // Assuming role is a field in User model
                    'profile_image' => $user->profile_image,
                ];

                // Check if company exists
                if (Company::count() == 0) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'No company found, redirect to company creation',
                        'data' => [
                            'token' => $token,
                            'user' => $userData,
                            //'redirect' => route('admin.company.create'),
                        ],
                    ], 200);
                }
                // Determine dashboard based on user type
                // $dashboardRoute = ($user->type == 'admin' || $user->type == 'subadmin')
                //     ? route('user.dashboard')
                //     : route($this->getDashboardRouteName());

                return response()->json([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'data' => [
                        'token' => $token,
                        'user' => $userData,
                        'branches' => UserBranch::where('user_id', $user->id)
                            ->with('branch')
                            ->get()
                            ->map(function ($branch) {
                                return [
                                    'id' => $branch->branch->id,
                                    'name' => $branch->branch->name,
                                    'code' => $branch->branch->code,
                                ];
                            }),
                        'redirect' => 'Dashboard', // $dashboardRoute
                    ],
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials',
                ], 401);
            }
        } else {
            // Branch-specific login (unchanged)
            $request->validate([
                'userId' => 'required',
                'branch_id' => 'required|exists:branches,id',
            ]);

            $user = User::where('id', $request->userId)->first();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404);
            }

            // Log in the user and generate a token
            Auth::login($user);
            $token = token_generator();

            // Fetch branch details
            $branch = Branch::find($request->branch_id);
            if (!$branch) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Branch not found',
                ], 404);
            }

            // Get financial year data
            $fydata = $this->getFinancialYearId();

            // Prepare user data for response
            $userData = [
                'user_id' => $user->id,
                'name' => $user->name,
                'code' => $user->code,
                'username' => $user->username,
                'email' => $user->email,
                'type' => $user->type,
                'branch_id' => $request->branch_id,
                'branch_name' => $branch->name,
                'fyear' => $fydata,
            ];

            // Check if company exists
            if (Company::count() == 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No company found, redirect to company creation',
                    'data' => [
                        'token' => $token,
                        'user' => $userData,
                        'redirect' => route('admin.company.create'),
                    ],
                ], 200);
            }

            // Determine dashboard based on user type
            // $dashboardRoute = ($user->type == 'customer' || $user->type == 'Customer')
            //     ? route('user.dashboard')
            //     : route($this->getDashboardRouteName());

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => $userData,
                    'branches' => UserBranch::where('user_id', $user->id)
                        ->with('branch')
                        ->get()
                        ->map(function ($branch) {
                            return [
                                'id' => $branch->branch->id,
                                'name' => $branch->branch->name,
                                'code' => $branch->branch->code,
                            ];
                        }),
                    'redirect' => '',//$dashboardRoute,
                ],
            ], 200);
        }
    }

    
    public function ClearCache()
    {
        Artisan::call('optimize:clear');
        return "Cleared!";
    }


    private function getFinancialYearId()
    {
        $financialYear = \App\Models\FinancialYear::where('status', 1)->first();
        return $financialYear ? $financialYear : null;
    }

    public function getProfile()
    {
        $this->sendResponse(200, __('api.succ'), $this->get_user_data());
    }

    public function logout(Request $request)
    {
        DeviceToken::where('token', get_header_auth_token())->delete();
        $this->sendResponse(200, __('api.succ_logout'), false);
    }

    public function update_name(Request $request)
    {
        $user_data = $request->user();
        $this->directValidation([
            'first_name' => ['required', 'max:100'],
            'last_name' => ['required', 'max:100'],
        ]);
        $user_data->update([
            'name' => $request->first_name . ' ' . $request->last_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);
        $this->sendResponse(200, __('api.succ_name_update'), $this->get_user_data());
    }

    public function update_email(Request $request)
    {
        $user_data = $request->user();
        $this->directValidation([
            'email' => ['required', 'email', Rule::unique('users')->ignore($user_data->id)->whereNull('deleted_at')],
        ]);
        $user_data->update([
            'email' => $request->email,
        ]);
        $this->sendResponse(200, __('api.succ_email_update'), $this->get_user_data());
    }

    public function update_mobile_number(Request $request)
    {
        $user_data = $request->user();
        $this->directValidation([
            'mobile' => ['required', 'integer', Rule::unique('users')->where('country_code', $request->country_code)->ignore($user_data->id)->whereNull('deleted_at')],
            'country_code' => ['required'],
        ], [
            'mobile.unique' => __('api.err_mobile_is_exits'),
        ]);
        $user_data->update([
            'mobile' => $request->mobile,
            'country_code' => $request->country_code,
        ]);
        $this->sendResponse(200, __('api.succ_number_update'), $this->get_user_data());
    }

    public function update_profile_image(Request $request)
    {
        $user_data = $request->user();
        $this->directValidation([
            'profile_image' => ['required', 'file', 'image'],
        ]);
        $up = $this->upload_file('profile_image', 'user_profile_image');
        if ($up) {
            un_link_file($user_data->profile_image);
            $user_data->update(['profile_image' => $up]);
            $this->sendResponse(200, __('api.succ_profile_picture_update'), $this->get_user_data());
        } else {
            $this->sendError(412, __('api.errr_fail_to_upload_image'));
        }
    }

}
