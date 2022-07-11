<?php

use App\Http\Controllers\Api\AttendanceMasterController;
use App\Http\Controllers\Api\Client\AppController;
use App\Http\Controllers\Api\Client\ConfirmController;
use App\Http\Controllers\Api\Client\RequestController;
use App\Http\Controllers\Api\Client\ShiftController;
use App\Http\Controllers\Api\OpenStatusController;
use App\Http\Controllers\Api\PostMasterController;
use App\Http\Controllers\Api\ShiftMasterController;
use App\Http\Controllers\Api\StaffMasterController;
use App\Http\Controllers\Api\WorkMasterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\System\MenuController;
use App\Http\Controllers\Api\System\RoleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FieldMasterController;
use App\Http\Controllers\Api\ValueListController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function(){
    Route::post('login', [AuthController::class, 'login'])->name('api.staff.login');
    Route::post('register', [AuthController::class, 'register'])->name('api.staff.register');

    Route::middleware('auth:api')->group(function () {
        Route::get('get-staff', [AuthController::class, 'getStaff'])->name('api.staff.get_staff');
        Route::post('broadcasting-auth', function(Request $request){
            return \Illuminate\Support\Facades\Broadcast::auth($request);
        }
        )->name('api.staff.broadcastingAuth');
        Route::post('logout', [AuthController::class, 'logout'])->name('api.staff.logout');

        Route::prefix('auth')->group(function () {
            Route::post('update', [AuthController::class, 'update'])->name('api.auth.update');
            Route::post('change-password', [AuthController::class, 'changePassword'])->name('api.auth.change_password');
        });

        Route::prefix('system')->group(function() {
            Route::prefix('menu')->group(function () {
                Route::get('get-menu', [MenuController::class, 'getMenu'])->name('api.system.menu.get_menu');
                Route::get('get-editable-tree-menu', [MenuController::class, 'getEditableTreeMenu'])->name('api.system.menu.get_editable_tree_menu');
                Route::post('update-menu', [MenuController::class, 'updateMenu'])->name('api.system.menu.update_menu');
                Route::post('add-menu', [MenuController::class, 'addMenu'])->name('api.system.menu.add_menu');
                Route::post('delete-menu', [MenuController::class, 'deleteMenu'])->name('api.system.menu.delete_menu');
            });
            Route::prefix('role')->group(function () {
                Route::get('get-staff-role', [RoleController::class, 'getStaffRole'])->name('api.system.role.get_staff_role');
                Route::post('add-staff-role', [RoleController::class, 'addStaffRole'])->name('api.system.role.add_staff_role');
                Route::post('update-staff-role', [RoleController::class, 'updateStaffRole'])->name('api.system.role.update_staff_role');
                Route::post('delete-staff-role', [RoleController::class, 'deleteStaffRoles'])->name('api.system.role.delete_staff_roles');
                Route::get('get-staff-role-list', [RoleController::class, 'getStaffRoleList'])->name('api.system.role.get_staff_roles');
                Route::get('get-role-tree', [RoleController::class, 'getRoleTree'])->name('api.system.role.get_role_tree');
            });
        });

        Route::prefix('value-list')->group(function() {
            Route::get('get-staff-role-list', [ValueListController::class, 'getStaffRoleList'])->name('api.value_list.getStaffRoleList');
            Route::get('get-staff-status-list', [ValueListController::class, 'getStaffStatusList'])->name('api.value_list.getStaffStatusList');
        });

        Route::prefix('field-master')->group(function() {
            Route::get('get-field-info-list', [FieldMasterController::class, 'getFieldInfoList'])->name('api.field_master.getFieldInfoList');
            Route::get('get-field-info', [FieldMasterController::class, 'getFieldInfo'])->name('api.field_master.getFieldInfo');
            Route::post('add-field', [FieldMasterController::class, 'addField'])->name('api.field_master.addField');
            Route::post('update-field', [FieldMasterController::class, 'updateField'])->name('api.field_master.updateField');
            Route::post('upload-files', [FieldMasterController::class, 'uploadFiles'])->name('api.field_master.uploadFiles');
            Route::post('delete-fields', [FieldMasterController::class, 'deleteFields'])->name('api.field_master.deleteFields');
            Route::get('get-staff-list', [FieldMasterController::class, 'getStaffList'])->name('api.field_master.getStaffList');
            Route::get('get-field-list', [FieldMasterController::class, 'getFieldList'])->name('api.field_master.getFieldList');
            Route::get('download-field-file', [FieldMasterController::class, 'downloadFieldFile'])->name('api.field_master.downloadFieldFile');
        });

        Route::prefix('staff-master')->group(function() {
            Route::get('get-staff-info-list', [StaffMasterController::class, 'getStaffInfoList'])->name('api.staff_master.getStaffInfoList');
            Route::get('get-staff-info', [StaffMasterController::class, 'getStaffInfo'])->name('api.staff_master.getStaffInfo');
            Route::post('add-staff', [StaffMasterController::class, 'addStaff'])->name('api.staff_master.addStaff');
            Route::post('update-staff', [StaffMasterController::class, 'updateStaff'])->name('api.staff_master.updateStaff');
            Route::post('delete-staffs', [StaffMasterController::class, 'deleteStaffs'])->name('api.staff_master.deleteStaffs');
            Route::get('export-staffs', [StaffMasterController::class, 'exportStaffs'])->name('api.staff_master.exportStaffs');
            Route::post('import-staffs', [StaffMasterController::class, 'importStaffs'])->name('api.staff_master.importStaffs');
            Route::post('update-device-token', [StaffMasterController::class, 'updateDeviceToken'])->name('api.staff_master.updateDeviceToken');
            Route::get('get-staff-address', [StaffMasterController::class, 'getStaffAddress'])->name('api.staff_master.getStaffAddress');
            Route::post('update-staff-address', [StaffMasterController::class, 'updateStaffAddress'])->name('api.staff_master.updateStaffAddress');
        });

        Route::prefix('shift-master')->group(function() {
            Route::get('get-field-list', [ShiftMasterController::class, 'getFieldList'])->name('api.shift_master.getFieldList');
            Route::get('get-shift-info-list', [ShiftMasterController::class, 'getShiftInfoList'])->name('api.shift_master.getShiftInfoList');
            Route::get('get-shift-month-info', [ShiftMasterController::class, 'getShiftMonthInfo'])->name('api.shift_master.getShiftMonthInfo');
            Route::get('get-shift-week-info', [ShiftMasterController::class, 'getShiftWeekInfo'])->name('api.shift_master.getShiftWeekInfo');
            Route::get('get-staff-list', [ShiftMasterController::class, 'getStaffList'])->name('api.shift_master.getStaffList');
            Route::get('get-post-times', [ShiftMasterController::class, 'getPostTimes'])->name('api.shift_master.getPostTimes');
            Route::get('get-shift-info', [ShiftMasterController::class, 'getShiftInfo'])->name('api.shift_master.getShiftInfo');
            Route::post('add-shift', [ShiftMasterController::class, 'addShift'])->name('api.shift_master.addShifts');
            Route::post('update-shift', [ShiftMasterController::class, 'updateShift'])->name('api.shift_master.updateShifts');
            Route::post('delete-shifts', [ShiftMasterController::class, 'deleteShifts'])->name('api.shift_master.deleteShifts');
            Route::get('get-shift-list', [ShiftMasterController::class, 'getShiftList'])->name('api.shift_master.getShiftList');
            Route::post('update-shift-list', [ShiftMasterController::class, 'updateShiftList'])->name('api.shift_master.updateShiftList');
            Route::post('check-staff-holiday', [ShiftMasterController::class, 'checkStaffHoliday'])->name('api.shift_master.checkStaffHoliday');
            Route::get('export-week-shifts', [ShiftMasterController::class, 'exportWeekShifts'])->name('api.shift_master.exportWeekShifts');
            Route::get('export-month-shifts', [ShiftMasterController::class, 'exportMonthShifts'])->name('api.shift_master.exportMonthShifts');
            Route::get('export-day-shifts', [ShiftMasterController::class, 'exportDayShifts'])->name('api.shift_master.exportDayShifts');
        });

        Route::prefix('post-master')->group(function() {
            Route::get('get-field-info-list', [PostMasterController::class, 'getFieldInfoList'])->name('api.post_master.getFieldInfoList');
            Route::get('get-post-info-list', [PostMasterController::class, 'getPostInfoList'])->name('api.post_master.getPostInfoList');
            Route::get('get-post-info', [PostMasterController::class, 'getPostInfo'])->name('api.post_master.getPostInfo');
            Route::post('add-post', [PostMasterController::class, 'addPost'])->name('api.post_master.addPost');
            Route::post('update-post', [PostMasterController::class, 'updatePost'])->name('api.post_master.updatePost');
            Route::post('delete-posts', [PostMasterController::class, 'deletePosts'])->name('api.post_master.deletePosts');
        });

        Route::prefix('work-master')->group(function() {
            Route::get('get-field-list', [WorkMasterController::class, 'getFieldList'])->name('api.work_master.getFieldList');
            Route::get('get-work-info-list', [WorkMasterController::class, 'getWorkInfoList'])->name('api.work_master.getWorkInfoList');
        });

        Route::prefix('open-status')->group(function() {
            Route::get('get-field-list', [OpenStatusController::class, 'getFieldList'])->name('api.open_status.getFieldList');
            Route::get('get-field-status', [OpenStatusController::class, 'getFieldStatus'])->name('api.open_status.getFieldStatus');
        });

        Route::prefix('attendance-master')->group(function() {
            Route::get('get-attendance-list', [AttendanceMasterController::class, 'getAttendanceList'])->name('api.attendance_master.getAttendanceList');
            Route::get('get-staff-attendance-list', [AttendanceMasterController::class, 'getStaffAttendanceList'])->name('api.attendance_master.getStaffAttendanceList');
            Route::post('confirm-request-late', [AttendanceMasterController::class, 'confirmRequestLate'])->name('api.attendance_master.confirmRequestLate');
            Route::post('confirm-request-early-leave', [AttendanceMasterController::class, 'confirmRequestEarlyLeave'])->name('api.attendance_master.confirmRequestEarlyLeave');
            Route::post('confirm-request-rest', [AttendanceMasterController::class, 'confirmRequestRest'])->name('api.attendance_master.confirmRequestRest');
            Route::post('confirm-request-over-time', [AttendanceMasterController::class, 'confirmRequestOverTime'])->name('api.attendance_master.confirmRequestOverTime');
            Route::post('confirm-request-alt-date', [AttendanceMasterController::class, 'confirmRequestAltDate'])->name('api.attendance_master.confirmRequestAltDate');
            Route::post('confirm-all-request', [AttendanceMasterController::class, 'confirmAllRequest'])->name('api.attendance_master.confirmAllRequest');
            Route::post('confirm-request-arrive', [AttendanceMasterController::class, 'confirmRequestArrive'])->name('api.attendance_master.confirmRequestArrive');
            Route::post('confirm-request-leave', [AttendanceMasterController::class, 'confirmRequestLeave'])->name('api.attendance_master.confirmRequestLeave');
            Route::post('confirm-request-break', [AttendanceMasterController::class, 'confirmRequestBreak'])->name('api.attendance_master.confirmRequestBreak');
            Route::post('confirm-request-night-break', [AttendanceMasterController::class, 'confirmRequestNightBreak'])->name('api.attendance_master.confirmRequestNightBreak');
            Route::get('export-attendance', [AttendanceMasterController::class, 'exportAttendance'])->name('api.attendance_master.exportAttendance');
            Route::get('get-attendance-info', [AttendanceMasterController::class, 'getAttendanceInfo'])->name('api.attendance_master.getAttendanceInfo');
        });
    });

    Route::prefix('client')->group(function() {
        Route::post('login', [AppController::class, 'login'])->name('api.client.login');

        Route::middleware('auth:api')->group(function () {
            Route::post('logout', [AppController::class, 'logout'])->name('api.client.logout');
            Route::post('update-device-token', [AppController::class, 'updateDeviceToken'])->name('api.client.updateDeviceToken');

            Route::get('get-shift-list', [ShiftController::class, 'getShiftList'])->name('api.client.getShiftList');
            Route::post('update-shift-list', [ShiftController::class, 'updateShiftList'])->name('api.client.updateShiftList');
            Route::post('register-status', [ShiftController::class, 'registerStatus'])->name('api.client.registerStatus');
            Route::post('change-check-time', [ShiftController::class, 'changeCheckTime'])->name('api.client.changeCheckTime');

            Route::post('confirm-yesterday', [ConfirmController::class, 'confirmYesterday'])->name('api.client.confirmYesterday');
            Route::post('confirm-today', [ConfirmController::class, 'confirmToday'])->name('api.client.confirmToday');
            Route::post('confirm-start', [ConfirmController::class, 'confirmStart'])->name('api.client.confirmStart');
            Route::post('confirm-arrive', [ConfirmController::class, 'confirmArrive'])->name('api.client.confirmArrive');
            Route::post('confirm-leave', [ConfirmController::class, 'confirmLeave'])->name('api.client.confirmLeave');
            Route::post('confirm-break', [ConfirmController::class, 'confirmBreak'])->name('api.client.confirmBreak');

            Route::post('request-early-leave', [RequestController::class, 'requestEarlyLeave'])->name('api.client.requestEarlyLeave');
            Route::post('request-rest', [RequestController::class, 'requestRest'])->name('api.client.requestRest');
            Route::post('request-over-time', [RequestController::class, 'requestOverTime'])->name('api.client.requestOverTime');
            Route::post('request-alt-date', [RequestController::class, 'requestAltDate'])->name('api.client.requestAltDate');
        });
    });
 });
