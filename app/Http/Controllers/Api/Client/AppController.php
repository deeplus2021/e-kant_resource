<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/10/26
 * Time: 10:26
 */

namespace App\Http\Controllers\Api\Client;


use App\Http\Controllers\Controller;
use App\Services\Client\AppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Common\ApiResponseData;

class AppController extends Controller
{
    public function __construct()
    {
        $this->service = app(AppService::class);
    }

    public function login(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input,
            [
                'email' => ['required', 'exists:t_staff,email'],
                'password' => ['required', 'string'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('email')) {
                $responseData->message =  self::ERR_INVALID_USER_EMAIL;
            }
            else {
                $responseData->message =  self::ERR_INVALID_PASSWORD;
            }
            return response()->json($responseData);
        }

        if(Auth::attempt(['email' => $input['email'], 'password' => $input['password'], 'is_active' => 1])){
            $user = Auth::user();
            $success = array(
                'token' =>  $user->createToken(config('app.name'))-> accessToken,
                'staff_info' => $this->service->getStaffInfo()
            );
            $responseData->result = $success;
            $responseData->message = __("common.response.success");
            $responseData->status = self::SUCCESS;
            return response()->json($responseData);
        } else{
            $responseData->status = self::ERROR;
            $responseData->message = self::ERR_INVALID_PASSWORD;
            return response()->json($responseData);
        }
    }

    public function logout (Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        $this->service->deleteDeviceToken();
        $responseData = new ApiResponseData($request);
        $responseData->message = __("common.messages.logout");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function updateDeviceToken(Request $request){
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'fcm_token' => ['required', 'string'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->updateDeviceToken($params);
        }
        catch (\Exception $e) {
            DB::rollback();

            $responseData->status = self::ERROR;
            $responseData->message = $e->getMessage();

            return response()->json($responseData);
        }

        DB::commit();

        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

}