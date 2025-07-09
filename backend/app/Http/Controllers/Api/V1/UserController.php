<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\DeviceToken;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ResponseController;
use Illuminate\Validation\Rule;
use App\User;
use App\Models\UserBranch;  
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;


class UserController extends ResponseController
{


    public function login(Request $request)
    {
        
        // Step 1: Initial login or multi-branch re-auth
        if (!$request->filled('branchid')) {
            // First login attempt
            $request->validate([
                'username' => 'required',
                'password' => 'required',
            ]);
          $loginInput = $request->username;
            $findField = 'username';

            if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                $findField = 'email';
            } elseif (is_numeric($loginInput)) {
                $findField = 'mobile';
            } else {
                $findField = 'code';
            }

            $user = User::where($findField, $loginInput)
                ->with('permissions:name')
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials',
                ], 401);
            }

            if ($user->status !== 'active') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account is not active. Please contact support.',
                ], 403);
            }

            // SUPERADMIN or ADMIN: login directly without branch
            if (in_array($user->type, ['superadmin', 'admin'])) {
                return $this->finalizeLogin($request, $user, null);
            }

            // Other users: check branches
            $branches = UserBranch::where('user_id', $user->id)
                ->with('branch:id,name,code')
                ->get();

            if ($branches->count() > 1) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Multiple branches found. Please select one to continue.',
                    'data' => [
                        'user_id' => $user->id,
                        'username' => $request->username,
                        'password' => $request->password, // optionally remove this for security
                        'branches' => $branches->map(function ($branch) {
                            return [
                                'id' => $branch->branch->id,
                                'name' => $branch->branch->name,
                                'code' => $branch->branch->code,
                            ];
                        }),
                    ]
                ]);
            }

            // Only one branch, continue login
            $request->merge([
                'userId' => $user->id,
                'branch_id' => $branches->first()->branch_id ?? null,
            ]);
        }

        // Step 2: Re-authenticate with branchid present
        if ($request->branchid >= 1) {
            $request->validate([
                'username' => 'required',
                'password' => 'required',
                'branchid' => 'nullable|exists:branches,id',
            ]);

            $loginInput = $request->username;
            $findField = 'username';

            if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                $findField = 'email';
            } elseif (is_numeric($loginInput)) {
                $findField = 'mobile';
            } else {
                $findField = 'code';
            }

            $user = User::where($findField, $loginInput)
                ->with('permissions:name')
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials',
                ], 401);
            }

            //== check branch id belongs to user
            $branch = UserBranch::where('user_id', $user->id)
                ->where('branch_id', $request->branchid)
                ->first();

            if (!$branch) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Branch not found for this user.',
                ], 404);
            }

            if ($user->status !== 'active') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account is not active. Please contact support.',
                ], 403);
            }

            return $this->finalizeLogin($request, $user, $request->branchid);
        }
    }


    private function finalizeLogin($request, $user, $branchid = null)
    {
        Auth::login($user);

        $token = token_generator();

        if ($token) {
            DeviceToken::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'device_token' => $token,
                ],
                [
                    'type' => $request->device_type ?? 'web',
                ]
            );
        }

        $branch = $branch_id ? Branch::find($branchid) : null;
        $fydata = $this->getFinancialYearId();

        $userData = [
            'user_id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'code' => $user->code,
            'mobile' => $user->mobile,
            'email' => $user->email,
            'type' => $user->type,
            'branch_id' => $branch?->id,
            'branch_name' => $branch?->name,
            'fyear' => $fydata,
            'permissions' => $user->permissions->pluck('name')->toArray(),
        ];

        if (Company::count() == 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'No company found. Please create one.',
                'data' => [
                    'token' => $token,
                    'user' => $userData,
                    'redirect' => route('admin.company.create'),
                ],
            ]);
        }

        $branches = UserBranch::where('user_id', $user->id)
            ->with('branch:id,name,code')
            ->get()
            ->map(function ($branch) {
                return [
                    'id' => $branch->branch->id,
                    'name' => $branch->branch->name,
                    'code' => $branch->branch->code,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => $userData,
                'branches' => $branches,
                'redirect' => '', // can be filled based on user type if needed
            ],
        ]);
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
