<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/10/21
 * Time: 15:32
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Services\OpenStatusService;
use App\Common\ApiPageResponseData;
use App\Common\ApiResponseData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OpenStatusController extends Controller
{
    public function __construct()
    {
        $this->service = app(OpenStatusService::class);
    }

    public function getFieldList(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'shift_date' => '日付',
        ];
        $validator = Validator::make($params, [
            'shift_date' => ['required', 'date_format:Y-m-d'],
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1'],
        ], $messages, $customAttributes);

        $responseData = new ApiPageResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getFieldList($params);

        $responseData->status = self::SUCCESS;
        $responseData->total =  $records->total();
        $responseData->result = $records->items();

        return response()->json($responseData);
    }

    public function getFieldStatus(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'field_id' => '現場',
            'shift_date' => '日付',
        ];
        $validator = Validator::make($params, [
            'field_id' => ['required', 'exists:t_field,id'],
            'shift_date' => ['required', 'date_format:Y-m-d'],
        ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getFieldStatus($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $records;

        return response()->json($responseData);
    }
}
