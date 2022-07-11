<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StaffMasterService;
use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Common\ApiResponseData;

class AuthController extends Controller
{
    //TODO
    public function register(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input,
            [
                'email' => ['required', 'string', 'max:64', 'unique:t_staff,email'],
                'name' => ['required', 'string', 'max:64'],
                'password' => ['required', 'string', 'min:6', 'max:64'],
                'c_password' => ['required', 'same:password'],
                'staff_role_id' => ['required', 'exists:t_staff_roles,id']
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }
        $input['password'] = Hash::make($input['password']);
        $user = Staff::create($input);
        $success['token'] =  $user->createToken(config('app.name'))->accessToken;
        $responseData->result = $success;
        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }


    public function login(Request $request)
    {
        $input = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'email' => 'メールアドレス',
            'password' => 'パスワード',
        ];
        $validator = Validator::make($input,
            [
                'email' => ['required', 'exists:t_staff,email'],
                'password' => ['required', 'string'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message =  implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }


        if(Auth::attempt(['email' => $input['email'], 'password' => $input['password'], 'is_active' => 1])){
            $user = Auth::user();
            $success['token'] =  $user->createToken(config('app.name'))-> accessToken;
            $responseData->result = $success;
            $responseData->message = __("common.response.success");
            $responseData->status = self::SUCCESS;
            return response()->json($responseData);
        } else{
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }

    public function getStaff(Request $request)
    {
        $responseData = new ApiResponseData($request);
        $user = Auth::user();
        $params = array();
        $params["staff_id"] = $user->id;
        $user = app(StaffMasterService::class)->getStaffInfo($params);

        $responseData->result = $user;
        $responseData->status = self::SUCCESS;

        return response()->json($responseData);
    }

    public function logout (Request $request)
    {

        $token = $request->user()->token();
        $token->revoke();
        $responseData = new ApiResponseData($request);
        $responseData->message = __("common.messages.logout");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    //TODO
    public function update(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();
        $validator = Validator::make($input,
            [
                'name' => ['required', 'string', 'max:64'],
                'furigana' => ['nullable', 'string', 'max:64'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $user->fill([
            'name' => $input['name']
        ])->save();

        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function changePassword(Request $request)
    {
        $input = $request->all();
        $user = Auth::user();
        $messages = [
        ];
        $customAttributes = [
            'old_password' => '以前のパスワード',
            'new_password' => '新しいパスワード',
            'c_password' => 'パスワードを認証',
        ];
        $validator = Validator::make($input,
            [
                'old_password' => ['required', 'string', 'max:64'],
                'new_password' => ['required', 'string', 'min:6', 'max:64'],
                'c_password' => ['required', 'same:new_password'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        if (Hash::check($input['old_password'], $user->password)) {
            $user->fill([
                'password' => Hash::make($input['new_password'])
            ])->save();

        } else {
            $responseData->message = __("パスワードが一致しません");
            $responseData->status = self::ERROR;
            return response()->json($responseData);
        }

        $token = $request->user()->token();
        $token->revoke();

        $success['token'] =  $user->createToken(config('app.name'))-> accessToken;
        $responseData->result = $success;
        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function broadcastingAuth(Request $request)
    {
        return response()->json(Auth::user());
    }
}
