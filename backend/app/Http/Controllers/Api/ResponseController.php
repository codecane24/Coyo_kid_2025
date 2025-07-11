<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public $errors;

    public function __construct()
    {
        $this->errors = null;
    }

    public function apiValidation($rules, $messages = [], $data = null)
    {
        $data = ($data) ? $data : request()->all();
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            $this->errors = $validator->errors()->first();
            return false;
        }
        return true;
    }

    public function directValidation($rules, $messages = [], $direct = true, $data = null)
    {
        $data = ($data) ? $data : request()->all();
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            $this->errors = $validator->errors()->first();
            if ($direct) {
                return $this->sendError($this->errors);
            }
            return false;
        }
        return $validator->valid();
    }

    public function sendError($message = null, $array = true)
    {
        $message = ($this->errors) ? $this->errors : ($message ? $message : __('api.err_something_went_wrong'));
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $array ? [] : new \stdClass()
        ], 412);
    }

    public function sendResponse($status, $message, $result = null, $extra = null)
    {
        $response = [
            'status' => $status == 200 ? 'success' : 'error',
            'message' => $message,
            'data' => $result ?? new \stdClass()
        ];

        if ($extra) {
            $response['data'] = array_merge((array)$response['data'], (array)$extra);
        }

        return response()->json($response, $status);
    }

    public function get_user_data($token = null)
    {
        $user_data = auth()->user();
        return [
            'user_id' => $user_data->id,
            'username' => $user_data->username,
            'name' => $user_data->name,
            'first_name' => $user_data->first_name,
            'last_name' => $user_data->last_name,
            'email' => $user_data->email,
            'country_code' => $user_data->country_code,
            'mobile' => $user_data->mobile,
            'profile_image' => $user_data->profile_image,
            'type' => $user_data->type,
            'token' => $token ?? get_header_auth_token(),
        ];
    }
}