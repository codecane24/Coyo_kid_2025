<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\User as UserModel;
use App\Models\UserBranch;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Artisan;

class UserController extends Controller
{
    /**
     * Handle user login and store device token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        if (!$request->isBranch || $request->isBranch != 1) {
            // Validate request
            $request->validate([
                'username' => 'required',
                'password' => 'required',
                'device_token' => 'nullable|string|max:255', // Added device_token validation
            ]);

            // Determine login field (email, username, mobile, or code)
            $loginInput = $request->username;
            $findField = 'username'; // default
            
            if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                $findField = 'email';
            } elseif (is_numeric($loginInput)) {
                $findField = 'mobile';
            } else {
                $findField = 'code';
            }

            // Find user by any of the possible fields
            $user = UserModel::where(function($query) use ($loginInput) {
                $query->where('email', $loginInput)
                    ->orWhere('username', $loginInput)
                    ->orWhere('mobile', $loginInput)
                    ->orWhere('code', $loginInput);
            })->first();

            if (!$user) {
                return $this->sendError(404, __('api.err_user_not_found'));
            }

            // Check IP address restriction
            $clientIp = $request->ip();
            if ($user->assigned_ip_address && $clientIp !== $user->assigned_ip_address) {
                return $this->sendError(403, __('api.err_ip_restricted'));
            }

            // Check if user has multiple branches
            $userBranches = UserBranch::where('user_id', $user->id)->count();
            if ($userBranches > 1) {
                $encryptedUserId = Crypt::encryptString($user->id);
                return $this->sendResponse(200, __('api.succ_multiple_branches'), [
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
                ]);
            }

            // Authenticate user
            if ($user && Hash::check($request->password, $user->password)) {
                // Log in the user and generate a token
                Auth::login($user, $request->remember ?? false);
                $token = token_generator();

                // Store device token if provided
                if ($request->filled('device_token')) {
                    DeviceToken::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'token' => $request->device_token,
                        ],
                        [
                            'user_id' => $user->id,
                            'token' => $request->device_token,
                        ]
                    );
                }

                // Update last login
                $user->last_login = Carbon::now()->toDateTimeString();
                $user->save();

                // Fetch branch details
                $branch = Branch::where('id', $user->branch_id)->first();
                if (!$branch) {
                    return $this->sendError(404, __('api.err_branch_not_found'));
                }

                // Get financial year data
                $fydata = $this->getFinancialYearId();

                // Prepare user data for response
                $userData = [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'code' => $user->code,
                    'email' => $user->email,
                    'type' => $user->type,
                    'branch_id' => $user->branch_id,
                    'branch_name' => $branch->name,
                    'fyear' => $fydata,
                    'role' => $user->role,
                    'profile_image' => $user->profile_image,
                    'device_token' => $request->device_token ?? null, // Include device token in response
                ];

                // Check if company exists
                if (Company::count() == 0) {
                    return $this->sendResponse(200, __('api.succ_no_company'), [
                        'token' => $token,
                        'user' => $userData,
                    ]);
                }

                return $this->sendResponse(200, __('api.succ_login'), [
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
                    'redirect' => 'Dashboard',
                ]);
            } else {
                return $this->sendError(401, __('api.err_invalid_credentials'));
            }
        } else {
            // Branch-specific login
            $request->validate([
                'userId' => 'required',
                'branch_id' => 'required|exists:branches,id',
                'device_token' => 'nullable|string|max:255', // Added device_token validation
            ]);

            $user = UserModel::where('id', $request->userId)->first();
            if (!$user) {
                return $this->sendError(404, __('api.err_user_not_found'));
            }

            // Log in the user and generate a token
            Auth::login($user);
            $token = token_generator();

            // Store device token if provided
            if ($request->filled('device_token')) {
                DeviceToken::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'token' => $request->device_token,
                    ],
                    [
                        'user_id' => $user->id,
                        'token' => $request->device_token,
                    ]
                );
            }

            // Fetch branch details
            $branch = Branch::find($request->branch_id);
            if (!$branch) {
                return $this->sendError(404, __('api.err_branch_not_found'));
            }

            // Get financial year data
            $fydata = $this->getFinancialYearId();

            // Prepare user data for response
            $userData = [
                'user_id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'code' => $user->code,
                'email' => $user->email,
                'type' => $user->type,
                'branch_id' => $request->branch_id,
                'branch_name' => $branch->name,
                'fyear' => $fydata,
                'device_token' => $request->device_token ?? null, // Include device token in response
            ];

            // Check if company exists
            if (Company::count() == 0) {
                return $this->sendResponse(200, __('api.succ_no_company'), [
                    'token' => $token,
                    'user' => $userData,
                    'redirect' => route('admin.company.create'),
                ]);
            }

            return $this->sendResponse(200, __('api.succ_login'), [
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
                'redirect' => '',
            ]);
        }
    }

    /**
     * Clear application cache.
     *
     * @return string
     */
    public function ClearCache()
    {
        Artisan::call('optimize:clear');
        return "Cleared!";
    }

    /**
     * Get authenticated user's profile data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile()
    {
        return $this->sendResponse(200, __('api.succ'), $this->get_user_data());
    }

    /**
     * Log out the authenticated user and delete device token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        DeviceToken::where('token', get_header_auth_token())->delete();
        return $this->sendResponse(200, __('api.succ_logout'), false);
    }

    /**
     * Update authenticated user's name.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
        return $this->sendResponse(200, __('api.succ_name_update'), $this->get_user_data());
    }

    /**
     * Update authenticated user's email.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_email(Request $request)
    {
        $user_data = $request->user();
        $this->directValidation([
            'email' => ['required', 'email', Rule::unique('users')->ignore($user_data->id)->whereNull('deleted_at')],
        ]);
        $user_data->update([
            'email' => $request->email,
        ]);
        return $this->sendResponse(200, __('api.succ_email_update'), $this->get_user_data());
    }

    /**
     * Update authenticated user's mobile number.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
        return $this->sendResponse(200, __('api.succ_number_update'), $this->get_user_data());
    }

    /**
     * Update authenticated user's profile image.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
            return $this->sendResponse(200, __('api.succ_profile_picture_update'), $this->get_user_data());
        } else {
            return $this->sendError(412, __('api.errr_fail_to_upload_image'));
        }
    }

    /**
     * Get financial year ID.
     *
     * @return mixed
     */
    private function getFinancialYearId()
    {
        $financialYear = \App\Models\FinancialYear::where('status', 1)->first();
        return $financialYear ? $financialYear : null;
    }

    /**
     * Helper method to generate token.
     * Placeholder: Implement as needed.
     *
     * @return string
     */
    private function token_generator()
    {
        // Implement your token generation logic here (e.g., Sanctum, JWT)
        return \Str::random(60); // Placeholder
    }

    /**
     * Get authenticated user data.
     * Placeholder: Implement as needed.
     *
     * @return array
     */
    private function get_user_data()
    {
        $user = Auth::user();
        return [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'mobile' => $user->mobile,
            'country_code' => $user->country_code,
            'profile_image' => $user->profile_image,
            'type' => $user->type,
            'role' => $user->role,
        ];
    }

    /**
     * Placeholder for upload_file method.
     *
     * @param string $field
     * @param string $directory
     * @return string|null
     */
    private function upload_file($field, $directory)
    {
        // Implement your file upload logic here
        return request()->file($field)->store($directory, 'public'); // Placeholder
    }

    /**
     * Placeholder for un_link_file method.
     *
     * @param string $path
     * @return void
     */
    private function un_link_file($path)
    {
        if ($path && \Storage::disk('public')->exists($path)) {
            \Storage::disk('public')->delete($path);
        }
    }
}