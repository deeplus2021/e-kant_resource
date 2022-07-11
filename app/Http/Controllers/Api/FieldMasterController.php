<?php


namespace App\Http\Controllers\Api;


use App\Common\ApiPageResponseData;
use App\Common\ApiResponseData;
use App\Http\Controllers\Controller;
use App\Models\FieldFile;
use App\Services\FieldMasterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FieldMasterController extends Controller
{
    public function __construct()
    {
        $this->service = app(FieldMasterService::class);
    }

    public function getFieldInfoList(Request $request)
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

        $records = $this->service->getFieldInfoList($params);

        $responseData->status = self::SUCCESS;
        $responseData->total = $records->total();
        $responseData->result = $records->items();

        return response()->json($responseData);
    }

    public function getFieldInfo(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'field_id' => ['required', 'string', 'max:32'],
        ]);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $result = $this->service->getFieldInfo($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $result;

        return response()->json($responseData);
    }

    public function getFieldList(Request $request)
    {
        $params = $request->all();

        $responseData = new ApiResponseData($request);

        $result = $this->service->getFieldList($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $result;

        return response()->json($responseData);
    }

    public function addField(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'name' => '現場名',
            'furigana' => 'ふりがな',
            'tel' => '電話番号',
            'address' => '所在地',
            'latitude' => '緯度',
            'longitude' => '経度',
            's_time' => '開始時間',
            'e_time' => '終了時間',
            'holidays' => '定休日',
            'files' => 'マニアル等',
            'cstaffs' =>'現場責任者',
            'staffs' =>'スタッフ',
            'estaffs' =>'緊急対応スタッフ',
            'is_active' =>'有効/無効',
        ];
        $validator = Validator::make($params,
            [
                'name' => ['required', 'string', 'max:128', 'regex:/^[ぁ-んァ-ヶ一-龥々０-９ａ-ｚＡ-Ｚー・a-zA-Z0-9 　]+$/u'],
                'furigana' => ['required', 'string', 'max:255', 'regex:/^[あ-ん゛゜ぁ-ぉゃ-ょー「」、 　]+$/u'],
                'tel'=> ['required', 'string', 'regex:/^[0-9]+$/', 'max:11'],
                'address'=> ['required', 'string', 'max:255'],
                'latitude'=> ['required', 'numeric'],
                'longitude'=> ['required', 'numeric'],
                's_time' => ['required', 'integer', 'min:0', 'max:2400'],
                'e_time' => ['required', 'integer', 'min:0', 'max:2400', 'gte:s_time'],
                'holidays' => ['nullable', 'array'],
                'holidays.*' => ['required_with:holidays', 'date_format:Y-m-d'],
                'files' => ['nullable', 'array'],
                'files.*' => ['required_with:files', 'exists:t_field_file,id'],
                'cstaffs' => ['nullable', 'array'],
                'cstaffs.*' => ['required_with:cstaffs', 'exists:t_staff,id'],
                'staffs' => ['nullable', 'array'],
                'staffs.*' => ['required_with:staffs', 'exists:t_staff,id'],
                'estaffs' => ['nullable', 'array'],
                'estaffs.*' => ['required_with:estaffs', 'exists:t_staff,id'],
                'is_active' => ['required', 'in:0,1'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->addField($request, $params);
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

    public function updateField(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'name' => '現場名',
            'furigana' => 'ふりがな',
            'tel' => '電話番号',
            'address' => '所在地',
            'latitude' => '緯度',
            'longitude' => '経度',
            's_time' => '開始時間',
            'e_time' => '終了時間',
            'holidays' => '定休日',
            'files' => 'マニアル等',
            'cstaffs' =>'現場責任者',
            'staffs' =>'スタッフ',
            'estaffs' =>'緊急対応スタッフ',
            'is_active' =>'有効/無効',
        ];
        $validator = Validator::make($params,
            [
                'id' => ['required', 'exists:t_field,id'],
                'name' => ['required', 'string', 'max:128', 'regex:/^[ぁ-んァ-ヶ一-龥々０-９ａ-ｚＡ-Ｚー・a-zA-Z0-9 　]+$/u'],
                'furigana' => ['required', 'string', 'max:255', 'regex:/^[あ-ん゛゜ぁ-ぉゃ-ょー「」、 　]+$/u'],
                'tel'=> ['required', 'string', 'regex:/^[0-9]+$/', 'max:11'],
                'address'=> ['required', 'string', 'max:255'],
                'latitude'=> ['required', 'numeric'],
                'longitude'=> ['required', 'numeric'],
                's_time' => ['required', 'integer', 'min:0', 'max:2400'],
                'e_time' => ['required',  'integer', 'min:0', 'max:2400', 'gte:s_time'],
                'holidays' => ['nullable', 'array'],
                'holidays.*' => ['required_with:holidays', 'date_format:Y-m-d'],
                'files' => ['nullable', 'array'],
                'files.*' => ['required_with:files', 'exists:t_field_file,id'],
                'cstaffs' => ['nullable', 'array'],
                'cstaffs.*' => ['required_with:cstaffs', 'exists:t_staff,id'],
                'staffs' => ['nullable', 'array'],
                'staffs.*' => ['required_with:staffs', 'exists:t_staff,id'],
                'estaffs' => ['nullable', 'array'],
                'estaffs.*' => ['required_with:estaffs', 'exists:t_staff,id'],
                'is_active' => ['required', 'in:0,1'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->updateField($request, $params);
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

    public function uploadFiles(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'files' => ['required', 'array'],
                'files.*' => ['required', 'file', 'max:10240']
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();
        $result = null;
        try {
            $result = $this->service->uploadFiles($request);
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
        $responseData->result = $result;

        return response()->json($responseData);
    }

    public function deleteFields(Request $request)
    {
        $params = $request->all();
        $admin = Auth::user();
        $admin_fields = isset($admin->staffFields) ? $admin->staffFields->pluck("field_id")->toArray() : null;
        $validator = Validator::make($params,
            [
                'field_ids' => ['required', 'array'],
                'field_ids.*' => ['required', 'exists:t_field,id', $admin_fields ? Rule::notIn($admin_fields) : '']
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->deleteFields($params);
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


    public function getStaffList(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'field_id' => ['nullable', 'exists:t_field,id'],
        ]);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getStaffList($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $records;

        return response()->json($responseData);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function downloadFieldFile(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'file_id' => ['required', 'exists:t_field_file,id'],
        ]);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $file = app(FieldFile::class)->find($params["file_id"]);

        return Storage::download($file->path);
    }
}
