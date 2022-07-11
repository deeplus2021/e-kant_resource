<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2019/10/31
 * Time: 15:11
 */

namespace App\Http\Controllers\Api\System;

use App\Common\ApiPageResponseData;
use App\Http\Controllers\Controller;
use App\Services\System\RoleService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Common\ApiResponseData;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->service = app(RoleService::class);
    }

    public function getStaffRoleList(Request $request){
        $params = $request->all();

        $validator = Validator::make($params,
            [
                'page' => ['required', 'integer', 'min:1'],
                'limit' => ['required', 'integer', 'min:1'],
            ]);

        $responseData = new ApiPageResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ", $validator->messages()->all());
            return response()->json($responseData);
        }

        $result = $this->service->getStaffRoleList($params);

        $responseData->total = $result->total();
        $responseData->result = $result->items();
        $responseData->status = self::SUCCESS;

        return response()->json($responseData);
    }

    public function getStaffRole(Request $request){
        $input = $request->all();
        $validator = Validator::make($input,
            [
                'id' => ['required', 'exists:t_staff_roles,id'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $menu = $this->service->getStaffRole($input['id']);

        $responseData->result = $menu;
        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function getRoleTree(Request $request)
    {
        $responseData = new ApiResponseData($request);

        $menu = $this->service->getRoleTree();

        $responseData->result = $menu->children;
        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function addStaffRole(Request $request){
        $input = $request->all();
        $validator = Validator::make($input,
            [
                'name' => ['required', 'unique:t_staff_roles,name', 'string', 'max:64'],
                'desc' => ['nullable', 'string', 'max:256'],
                'page_menu_ids' => ['required', 'array'],
                'page_menu_ids.*' => ['required', 'distinct', 'exists:page_menu,id']
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $role = $this->service->addStaffRole($input['name'], $input['desc'], $input['page_menu_ids']);

        $responseData->result = $role;
        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function deleteStaffRoles(Request $request){
        $input = $request->all();
        $validator = Validator::make($input,
            [
                'ids' => ['required', 'array'],
                'ids.*' => ['required', 'exists:t_staff_roles,id'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $this->service->Staff($input['ids']);

        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function updateStaffRole(Request $request){
        $input = $request->all();
        $validator = Validator::make($input,
            [
                'id' => ['required', 'exists:t_staff_roles,id'],
                'name' => ['required', 'unique:t_staff_roles,name,'.$input['id'].',id', 'string', 'max:64'],
                'desc' => ['nullable', 'string', 'max:256'],
                'is_active' => ['required', 'in:0,1'],
                'page_menu_ids' => ['required', 'array'],
                'page_menu_ids.*' => ['required', 'distinct', 'exists:page_menu,id']
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $this->service->updateStaffRole($input['id'], $input['name'], $input['desc'], $input['page_menu_ids'], $input['is_active']);

        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }
}