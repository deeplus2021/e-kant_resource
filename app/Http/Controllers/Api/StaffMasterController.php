<?php

namespace App\Http\Controllers\Api;


use App\Common\ApiPageResponseData;
use App\Common\ApiResponseData;
use App\ExportImport\ExportStaff;
use App\ExportImport\ImportStaff;
use App\Http\Controllers\Controller;
use App\Services\StaffMasterService;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class StaffMasterController extends Controller
{
    public function __construct()
    {
        $this->service = app(StaffMasterService::class);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStaffInfoList(Request $request)
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

        $records = $this->service->getStaffInfoList($params);

        $responseData->status = self::SUCCESS;
        $responseData->total = $records->total();
        $responseData->result = $records->items();

        return response()->json($responseData);

    }

    public function getStaffInfo(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'staff_id' => ['required', 'exists:t_staff,id'],
        ]);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getStaffInfo($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $records;

        return response()->json($responseData);

    }

    public function addStaff(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'email' => 'メールアドレス',
            'name' => '名前',
            'code' => '社員コード',
            'furigana' => 'ふりがな',
            'tel' => '電話番号',
            'staff_role_id' => '権限',
            'password' => 'パスワード',
            'holiday' => '原則休日登録日',
            'desired_holiday' => '希望休日',
            'yesterday_flag' => '前日確認メール',
            'today_flag' => '当日確認メール',
            'staff_address_list.*.address' => '出発先住所',
            'staff_address_list.*.latitude' => '緯度',
            'staff_address_list.*.longitude' => '経度',
            'staff_address_list.*.field_id' => '現場名',
            'staff_address_list.*.required_time' => '現場までの時間',
            'staff_address_list.*.email_time' => '当日確認メール送信時間',
            'is_active' =>'有効/無効',
        ];
        $validator = Validator::make($params,
            [
                'email' => ['required', 'email', 'unique:t_staff,email'],
                'name' => ['required', 'string', 'max:64', 'regex:/^[ぁ-んァ-ヶ一-龥々０-９ａ-ｚＡ-Ｚー・a-zA-Z0-9 　]+$/u'],
                'code' => ['required', 'string', 'max:64'],
                'furigana' => ['required', 'string', 'max:64', 'regex:/^[あ-ん゛゜ぁ-ぉゃ-ょー「」、 　]+$/u'],
                'tel'=> ['required', 'string', 'regex:/^[0-9]+$/', 'max:11'],
                'staff_role_id' => ['required', 'exists:t_staff_roles,id'],
                'is_active' => ['required', 'in:0,1'],
                'password' => ['required', 'string', 'regex:/^[a-zA-Z0-9!#$%&()*+,.:;=?@\[\]^_{}-]+$/', 'max:64'],
                'holiday' => ['nullable', 'array'],
                'holiday.*' => ['required_with:holiday', 'in:1,2,3,4,5,6,7,8'],
                'desired_holiday' => ['nullable', 'array'],
                'desired_holiday.*' => ['required_with:desired_holiday', 'date_format:Y-m-d'],
                'yesterday_flag' => ['required', 'in:0,1'],
                'today_flag' => ['required', 'in:0,1'],
                'staff_address_list' => ['nullable', 'array'],
                'staff_address_list.*.address' => ['nullable', 'string', 'max:256'],
                'staff_address_list.*.latitude' => ['nullable', 'numeric'],
                'staff_address_list.*.longitude' => ['nullable', 'numeric'],
                'staff_address_list.*.field_id' => ['nullable', 'exists:t_field,id'],
                'staff_address_list.*.required_time' => ['nullable', 'integer', 'min:0', 'max:2400'],
                'staff_address_list.*.email_time' => ['nullable', 'integer', 'min:0', 'max:2400'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->addStaff($params);
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

    public function updateStaff(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'email' => 'メールアドレス',
            'name' => '名前',
            'code' => '社員コード',
            'furigana' => 'ふりがな',
            'tel' => '電話番号',
            'staff_role_id' => '権限',
            'password' => 'パスワード',
            'holiday' => '原則休日登録日',
            'desired_holiday' => '希望休日',
            'yesterday_flag' => '前日確認メール',
            'today_flag' => '当日確認メール',
            'staff_address_list.*.address' => '出発先住所',
            'staff_address_list.*.latitude' => '緯度',
            'staff_address_list.*.longitude' => '経度',
            'staff_address_list.*.field_id' => '現場名',
            'staff_address_list.*.required_time' => '現場までの時間',
            'staff_address_list.*.email_time' => '当日確認メール送信時間',
            'is_active' =>'有効/無効',
        ];
        if(isset($params["staff_address_id"]) && $params["staff_address_id"] < 1){
            $params["staff_address_id"] = null;
        }
        $validator = Validator::make($params,
            [
                'id' => ['required', 'exists:t_staff,id'],
                'email' => ['required', 'email',  'unique:t_staff,email,'.$params["id"].',id'],
                'name' => ['required', 'string', 'max:64', 'regex:/^[ぁ-んァ-ヶ一-龥々０-９ａ-ｚＡ-Ｚー・a-zA-Z0-9 　]+$/u'],
                'code' => ['required', 'string', 'max:64'],
                'furigana' => ['required', 'string', 'max:64', 'regex:/^[あ-ん゛゜ぁ-ぉゃ-ょー「」、 　]+$/u'],
                'tel'=> ['required', 'string', 'regex:/^[0-9]+$/', 'max:11'],
                'staff_role_id' => ['required', 'exists:t_staff_roles,id'],
                'password' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9!#$%&()*+,.:;=?@\[\]^_{}-]+$/', 'max:64'],
                'holiday' => ['nullable', 'array'],
                'holiday.*' => ['required_with:holiday', 'in:1,2,3,4,5,6,7,8'],
                'desired_holiday' => ['nullable', 'array'],
                'desired_holiday.*' => ['required_with:desired_holiday', 'date_format:Y-m-d'],
                'yesterday_flag' => ['required', 'in:0,1'],
                'today_flag' => ['required', 'in:0,1'],
                'staff_address_list' => ['nullable', 'array'],
                'staff_address_list.*.address' => ['nullable', 'string', 'max:256'],
                'staff_address_list.*.latitude' => ['nullable', 'numeric'],
                'staff_address_list.*.longitude' => ['nullable', 'numeric'],
                'staff_address_list.*.field_id' => ['nullable', 'exists:t_field,id'],
                'staff_address_list.*.required_time' => ['nullable', 'integer', 'min:0', 'max:2400'],
                'staff_address_list.*.email_time' => ['nullable', 'integer', 'min:0', 'max:2400'],
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
            $this->service->updateStaff($params);
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

    public function getStaffAddress(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'staff_id' => ['required',  'exists:t_staff,id'],
            'field_id' => ['required',  'exists:t_field,id'],
        ]);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getStaffAddress($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $records;

        return response()->json($responseData);
    }


    public function updateStaffAddress(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'staff_id' =>"スタッフ",
            'field_id' => "現場名",
            'staff_address_list.*.address' => '出発先住所',
            'staff_address_list.*.latitude' => '緯度',
            'staff_address_list.*.longitude' => '経度',
            'staff_address_list.*.field_id' => '現場名',
            'staff_address_list.*.required_time' => '現場までの時間',
            'staff_address_list.*.email_time' => '当日確認メール送信時間',
        ];
        if(isset($params["staff_address_id"]) && $params["staff_address_id"] < 1){
            $params["staff_address_id"] = null;
        }
        $validator = Validator::make($params,
            [
                'staff_id' => ['required',  'exists:t_staff,id'],
                'field_id' => ['required',  'exists:t_field,id'],
                'staff_address_list' => ['nullable', 'array'],
                'staff_address_list.*.address' => ['nullable', 'string', 'max:256'],
                'staff_address_list.*.latitude' => ['nullable', 'numeric'],
                'staff_address_list.*.longitude' => ['nullable', 'numeric'],
                'staff_address_list.*.field_id' => ['nullable', 'exists:t_field,id'],
                'staff_address_list.*.required_time' => ['nullable', 'integer', 'min:0', 'max:2400'],
                'staff_address_list.*.email_time' => ['nullable', 'integer', 'min:0', 'max:2400'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->updateStaffAddress($params);
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


    public function deleteStaffs(Request $request){
        $params = $request->all();
        $admin_id = Auth::user()->id;
        $validator = Validator::make($params,
            [
                'staff_ids' => ['required', 'array'],
                'staff_ids.*' => ['required', 'exists:t_staff,id', Rule::notIn([$admin_id])]
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->deleteStaffs($params);
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

    public function exportStaffs(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'name' => '名前',
        ];
        $validator = Validator::make($params, [
            'name' => ['nullable', 'string', 'max:32'],
        ], $messages, $customAttributes);

        $responseData = new ApiPageResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }
        $export = new ExportStaff($params['name'], 'スタッフ.csv');
        return $export->download();
    }


    public function importStaffs(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params,
            [
                'staff_file' => ['required', 'file', 'max:4096'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ", $validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        $import = new ImportStaff();

        try {
            $import->import(request()->file('staff_file'), null, \Maatwebsite\Excel\Excel::CSV);

            $responseData->status = self::SUCCESS;

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            DB::rollback();

            $failures = $e->failures();

            $message = "";
            foreach ($failures as $failure) {
                $message .= $failure->row() . '行目: '; // row that went wrong
                $message .= implode(",", $failure->errors()) . ' '; // Actual error messages from Laravel validator
                $message .= "\n";
            }

            $responseData->status = self::ERROR;
            $responseData->message = $message;

            return response()->json($responseData);
        }

        DB::commit();

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
