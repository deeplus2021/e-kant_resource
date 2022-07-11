<?php


namespace App\Http\Controllers\Api\Client;


use App\Common\ApiResponseData;
use App\Http\Controllers\Controller;
use App\Services\Client\ConfirmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConfirmController extends Controller
{
    public function __construct()
    {
        $this->service = app(ConfirmService::class);
    }



    public function confirmYesterday(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'staff_address_id' => ['nullable', 'exists:t_staff_address,id'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('shift_id')) {
                $responseData->message =  self::ERR_INVALID_SHIFT_ID;
            }
            else {
                $responseData->message =  self::ERR_INVALID_UNKNOWN;
            }
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmYesterday($params);
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

    public function confirmToday(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'health_status' => ['required', 'in:1,0'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('shift_id')) {
                $responseData->message =  self::ERR_INVALID_SHIFT_ID;
            }
            else if ($errors->has('health_status')) {
                $responseData->message =  self::ERR_INVALID_HEALTH_STATUS;
            }
            else {
                $responseData->message =  self::ERR_INVALID_UNKNOWN;
            }
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmToday($params);
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

    public function confirmStart(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'latitude' => ['required', 'numeric'],
                'longitude' => ['required', 'numeric'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('shift_id')) {
                $responseData->message =  self::ERR_INVALID_SHIFT_ID;
            }
            else {
                $responseData->message =  self::ERR_INVALID_LOCATION;
            }
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $check_location = $this->service->confirmStart($params);
        }
        catch (\Exception $e) {
            DB::rollback();

            $responseData->status = self::ERROR;
            $responseData->message = $e->getMessage();

            return response()->json($responseData);
        }

        DB::commit();

        $responseData->message = __("common.response.success");
        $responseData->result = $check_location;
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function confirmBreak(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'break_time' => ['required', 'integer', 'in:15,30,45,60,75,90,105,120'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('shift_id')) {
                $responseData->message =  self::ERR_INVALID_SHIFT_ID;
            }
            else if ($errors->has('break_time')) {
                $responseData->message =  self::ERR_INVALID_BREAK_TIME;
            }
            else {
                $responseData->message =  self::ERR_INVALID_UNKNOWN;
            }
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmBreak($params);
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

    public function confirmArrive(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'arrive_time' => ['nullable', 'integer', 'min:0', 'max:2400']
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('shift_id')) {
                $responseData->message =  self::ERR_INVALID_SHIFT_ID;
            }
            else {
                $responseData->message =  self::ERR_INVALID_UNKNOWN;
            }
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmArrive($params);
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

    public function confirmLeave(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'leave_time' => ['nullable', 'integer', 'min:0', 'max:2400']
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('shift_id')) {
                $responseData->message =  self::ERR_INVALID_SHIFT_ID;
            }
            else {
                $responseData->message =  self::ERR_INVALID_UNKNOWN;
            }
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmLeave($params);
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
