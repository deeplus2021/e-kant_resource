<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/10/16
 * Time: 16:43
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PostMasterService;
use App\Common\ApiPageResponseData;
use App\Common\ApiResponseData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PostMasterController extends Controller
{
    public function __construct()
    {
        $this->service = app(PostMasterService::class);
    }

    public function getFieldInfoList(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'name' => ['nullable', 'string', 'max:255'],
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


    public function getPostInfoList(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'field_id' => ['required', 'exists:t_field,id'],
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1'],
        ]);

        $responseData = new ApiPageResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getPostInfoList($params);

        $responseData->status = self::SUCCESS;
        $responseData->total = $records->total();
        $responseData->result = $records->items();

        return response()->json($responseData);
    }

    public function getPostInfo(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'post_id' => ['nullable', 'exists:t_post,id'],
            'field_id' => ['nullable', 'required_without:post_id', 'exists:t_field,id'],
        ]);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $result = $this->service->getPostInfo($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $result;

        return response()->json($responseData);
    }

    public function addPost(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'field_id' => '現場',
            'p_weeks' => '該当曜日',
            's_date' => '開始日',
            'e_date' => '終了日',
            'post_times' => '時間帯',
        ];
        $validator = Validator::make($params,
            [
                'field_id' => ['required', 'exists:t_field,id'],
                'p_weeks'=> ['nullable', 'array'],
                'p_weeks.*'=> ['nullable', 'required_with:p_weeks', 'in:1,2,3,4,5,6,7'],
                's_date'=> ['nullable', 'required_without:p_weeks', 'date_format:Y-m-d', 'unique:t_post,s_date,NULL,id,field_id,'.$params["field_id"]],
                'e_date'=> ['nullable', 'required_without:p_weeks', 'date_format:Y-m-d', 'after_or_equal:s_date', 'unique:t_post,e_date,NULL,id,field_id,'.$params["field_id"]],
                'post_times' => ['required', 'array'],
                'post_times.*.s_time'=> ['required', 'integer', 'min:0', 'max:2400'],
                'post_times.*.e_time'=> ['required', 'integer', 'min:0', 'max:2400'],
                'post_times.*.number'=> ['required', 'integer'],
                'special_date_list.*' => ['nullable', 'array'],
                'special_date_list.*.s_date' => ['nullable', 'required_with:special_date_list.*.e_date', 'date_format:Y-m-d',],
                'special_date_list.*.e_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:special_date_list.*.s_date',],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        if(isset($params['s_date']) && isset($params['e_date'])){
            $intersect = Post::where('field_id', $params["field_id"])
                ->where(function($query) use ($params) {
                    $query->where([
                        ['s_date', '<=', $params['s_date']],
                        ['e_date', '>=', $params['s_date']]
                        ])
                        ->orWhere([
                            ['s_date', '<=', $params['e_date']],
                            ['e_date', '>=', $params['e_date']]
                        ]);
                })
                ->first();
            if(isset($intersect)){
                $responseData->status = self::ERROR;
                $responseData->message = '日付区間が交差されました。';
                return response()->json($responseData);
            }
        }


        DB::beginTransaction();

        try {
            $this->service->addPost($params);
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

    public function updatePost(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'field_id' => '現場',
            'p_week' => '該当曜日',
            's_date' => '開始日',
            'e_date' => '終了日',
            'post_times' => '時間帯',
        ];
        $validator = Validator::make($params,
            [
                'id' => ['required', 'exists:t_post,id'],
                'field_id' => ['required', 'exists:t_field,id'],
                'p_weeks'=> ['nullable', 'array'],
                'p_weeks.*'=> ['nullable', 'required_with:p_weeks', 'in:1,2,3,4,5,6,7'],
                's_date'=> ['nullable', 'required_without:p_weeks', 'date_format:Y-m-d', 'unique:t_post,s_date,'.$params["id"].',id,field_id,'.$params["field_id"]],
                'e_date'=> ['nullable', 'required_without:p_weeks', 'date_format:Y-m-d', 'after_or_equal:s_date', 'unique:t_post,e_date,'.$params["id"].',id,field_id,'.$params["field_id"]],
                'post_times' => ['required', 'array'],
                'post_times.*.s_time'=> ['required', 'integer', 'min:0', 'max:2400'],
                'post_times.*.e_time'=> ['required', 'integer', 'min:0', 'max:2400'],
                'post_times.*.number'=> ['required', 'integer'],
                'special_date_list.*' => ['nullable', 'array'],
                'special_date_list.*.s_date' => ['nullable', 'required_with:special_date_list.*.e_date', 'date_format:Y-m-d',],
                'special_date_list.*.e_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:special_date_list.*.s_date',],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        if(isset($params['s_date']) && isset($params['e_date'])){
            $intersect = Post::where('field_id', $params["field_id"])
                ->where(function($query) use ($params) {
                    $query->where([
                        ['s_date', '<=', $params['s_date']],
                        ['e_date', '>=', $params['s_date']]
                    ])
                        ->orWhere([
                            ['s_date', '<=', $params['e_date']],
                            ['e_date', '>=', $params['e_date']]
                        ]);
                })
                ->where("id", "<>", $params['id'])
                ->first();
            if(isset($intersect)){
                $responseData->status = self::ERROR;
                $responseData->message = '日付区間が交差されました。';
                return response()->json($responseData);
            }
        }

        DB::beginTransaction();

        try {
            $this->service->updatePost($params);
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

    public function deletePosts(Request $request){
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'post_ids' => ['required', 'array'],
                'post_ids.*' => ['required', 'exists:t_post,id']
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->deletePosts($params);
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
