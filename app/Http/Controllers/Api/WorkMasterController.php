<?php


namespace App\Http\Controllers\Api;


use App\Common\ApiPageResponseData;
use App\Common\ApiResponseData;
use App\Http\Controllers\Controller;
use App\Services\WorkMasterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkMasterController extends Controller
{
    public function __construct()
    {
        $this->service = app(WorkMasterService::class);
    }

    public function getFieldList(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'name' => ['nullable', 'string', 'max:32'],
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1'],
        ]);

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

    public function getWorkInfoList(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'shift_date' => '日付',
        ];
        $validator = Validator::make($params, [
            'name' => ['nullable', 'string', 'max:32'],
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

        $records = $this->service->getWorkInfoList($params);

        $responseData->status = self::SUCCESS;
        $responseData->total =  $records->total();
        $responseData->result = $records->items();

        return response()->json($responseData);
    }

}
