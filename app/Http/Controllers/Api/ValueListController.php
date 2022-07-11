<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\StaffStatus;
use App\Services\System\RoleService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Common\ApiResponseData;

class ValueListController extends Controller
{
    public function getStaffRoleList(Request $request)
    {
        $responseData = new ApiResponseData($request);

        $records = StaffRole::getKeyValueList(1);

        $responseData->result = $records;
        $responseData->status = self::SUCCESS;

        return response()->json($responseData);
    }

    public function getStaffStatusList(Request $request)
    {
        $responseData = new ApiResponseData($request);

        $records = StaffStatus::getKeyValueList();

        $responseData->result = $records;
        $responseData->status = self::SUCCESS;

        return response()->json($responseData);
    }
}
