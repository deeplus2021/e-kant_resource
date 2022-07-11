<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2019/10/25
 * Time: 9:02
 */

namespace App\Http\Controllers\Api\System;

use App\Common\ApiResponseData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use App\Services\System\MenuService;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function __construct()
    {
        $this->service = app(MenuService::class);
    }

    public function getMenu(Request $request){
        $user = Auth::user();
        $menu = $this->service->getMenu($user, 0, 1);
        $responseData = new ApiResponseData($request);
        $responseData->result = $menu;
        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function getEditableTreeMenu(Request $request)
    {
        $menu = $this->service->getEditableTreeMenu();
        $responseData = new ApiResponseData($request);
        $responseData->result = $menu->children;
        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function updateMenu(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'id' => ['required', 'exists:page_menu,id'],
            'name' => ['required', 'string', 'max:32'],
            'button_type' => ['required', 'in:0,1,2'],
            'url' => ['nullable', 'string', 'max:256'],
            'code' => ['nullable', 'string', 'max:32'],
            'order' => ['nullable', 'integer', 'max:64']
        ]);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $menu = $this->service->updateMenu($params);
        $responseData->result = $menu;
        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;

        Artisan::call('cache:clear');

        return response()->json($responseData);
    }

    public function addMenu(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'parent_id' => ['required', 'exists:page_menu,id'],
            'name' => ['required', 'string', 'max:32'],
            'button_type' => ['required', 'in:0,1,2'],
            'url' => ['nullable', 'string', 'max:256'],
            'code' => ['nullable', 'string', 'max:32'],
            'order' => ['nullable', 'integer', 'max:64']
        ]);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $menu = $this->service->addMenu($params);
        $responseData->result = $menu;
        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;

        Artisan::call('cache:clear');

        return response()->json($responseData);
    }

    public function deleteMenu(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'id' => ['required', 'exists:page_menu,id'],
        ]);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $menu = $this->service->deleteMenu($params);
        $responseData->result = $menu;
        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;

        Artisan::call('cache:clear');

        return response()->json($responseData);
    }
}
