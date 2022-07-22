<?php


namespace App\Services;


use App\Models\Shift;
use App\Models\Staff;
use App\Models\StaffAddress;
use App\Notifications\AdminNotified;
use App\Notifications\AdminStarted;
use App\Notifications\StaffNoChecked;
use App\Notifications\StaffNoStart;
use App\Notifications\StaffRequested;
use Illuminate\Support\Facades\Log;

class AutoNotifyService
{
    public function run()
    {
        $now = \Carbon\Carbon::now();
        $shifts = Shift::whereDate("shift_date", ">=", \Carbon\Carbon::today())
            ->whereDate("shift_date", "<=", \Carbon\Carbon::tomorrow())
            ->whereNull("rest_checked_at")
            ->get();
        $notify_count = 0;
        foreach ($shifts as $shift) {
            $shift_date = \Carbon\Carbon::parse($shift->shift_date);
            $s_time = $shift->s_time;
            $staff = $shift->staff;

            if ($shift_date->eq(\Carbon\Carbon::tomorrow())) {
                if ($staff->yesterday_flag && !isset($shift->yesterday_checked_at)) {
                    for ($i = 0; $i < 3; $i++) {
                        $notify_time = -(8 - $i * 2) * 60;
                        if ($now->gt(\Carbon\Carbon::tomorrow()->addMinutes($notify_time - 1)) && $now->lte(\Carbon\Carbon::tomorrow()->addMinutes($notify_time + 1))) {
                            $type = config('constants.admin_notify.confirm_yesterday');

                            try {
                                if (isset($staff->fcm_token)) $staff->notify(new AdminNotified($type, $shift));
                                Log::debug($staff->email . ' confirm_yesterday:' . $now . "==" . \Carbon\Carbon::tomorrow()->addMinutes($notify_time));
                            } catch (\Exception $e) {
                                Log::debug($staff->email . ' confirm_yesterday: Error ' . $e->getMessage());
                            }


                            $notify_count += 1;
                            if ($i == 2) {
                                $object = array(
                                    'type' => '未確認通知',
                                    'sender' => $staff->name,
                                    'content' => "前日勤怠通知メッセージを確認していません。",
                                    "shift_ids" => [$shift->id]
                                );
                                $super_admins = Staff::where("staff_role_id", config('constants.staff_roles.super_admin'))->get();
                                $field_admins = $shift->field->cstaffs;
                                foreach ($field_admins as $field_admin) {
                                    $field_admin->notify(new StaffNoChecked($object));
                                }
                                foreach ($super_admins as $super_admin) {
                                    $super_admin->notify(new StaffNoChecked($object));
                                }
                            }
                        }
                    }
                }
            } else {
                $staff_address = null;
                if (isset($shift->staff_address_id)) {
                    $staff_address = $shift->staffAddress;
                }
                else{
                    $staff_address = StaffAddress::where("staff_id", $staff->id)
                        ->where("field_id", $shift->field_id)
                        ->orderBy("created_at", "desc")
                        ->first();
                }

                if (isset($staff_address)) {
                    if (isset($staff_address->required_time) && isset($staff_address->email_time)) {
                        if ($staff->today_flag && !isset($shift->today_checked_at)) {
                            $alert_count = max(2, intval($staff_address->email_time / 10)) + 1;
                            for ($i = 0; $i < $alert_count; $i++) {
                                $notify_time = $s_time - $staff_address->required_time - $staff_address->email_time + $i * 10;
                                if ($now->gt(\Carbon\Carbon::today()->addMinutes($notify_time - 1)) && $now->lte(\Carbon\Carbon::today()->addMinutes($notify_time + 1))) {
                                    $type = config('constants.admin_notify.confirm_today');

                                    try {
                                        if (isset($staff->fcm_token)) $staff->notify(new AdminNotified($type, $shift));
                                        Log::debug($staff->email . ' confirm_today:' . $now . '==' . \Carbon\Carbon::today()->addMinutes($notify_time));
                                    } catch (\Exception $e) {
                                        Log::debug($staff->email . ' confirm_today: Error ' . $e->getMessage());
                                    }

                                    $notify_count += 1;
                                    if ($i == $alert_count - 1) {
                                        $object = array(
                                            'type' => '未確認通知',
                                            'sender' => $staff->name,
                                            'content' => "当日勤怠通知メッセージを確認していません。",
                                            "shift_ids" => [$shift->id]
                                        );
                                        $super_admins = Staff::where("staff_role_id", config('constants.staff_roles.super_admin'))->get();
                                        $field_admins = $shift->field->cstaffs;
                                        foreach ($field_admins as $field_admin) {
                                            $field_admin->notify(new StaffNoChecked($object));
                                        }
                                        foreach ($super_admins as $super_admin) {
                                            $super_admin->notify(new StaffNoChecked($object));
                                        }
                                    }
                                }
                            }
                        }
                        if ($now->gt(\Carbon\Carbon::today()->addMinutes($s_time - $staff_address->required_time + 9)) && $now->lte(\Carbon\Carbon::today()->addMinutes($s_time - $staff_address->required_time + 11))) {
                            if (!isset($shift->staff_status_id) || empty($shift->staff_status_id) || $shift->staff_status_id == config('constants.staff_status.already')) {
                                $type = config('constants.admin_notify.confirm_start');
                                try {
                                    if (isset($staff->fcm_token)) $staff->notify(new AdminStarted($type, $shift));
                                    Log::debug($staff->email . ' confirm_start:' . $now . '==' . \Carbon\Carbon::today()->addMinutes($notify_time));
                                } catch (\Exception $e) {
                                    Log::debug($staff->email . ' confirm_today: Error ' . $e->getMessage());
                                }
                            }
                        }
                        if ($now->gt(\Carbon\Carbon::today()->addMinutes($s_time - $staff_address->required_time + 19)) && $now->lte(\Carbon\Carbon::today()->addMinutes($s_time - $staff_address->required_time + 21))) {
                            if (!isset($shift->staff_status_id) || empty($shift->staff_status_id) || $shift->staff_status_id == config('constants.staff_status.already')) {
                                $shift->staff_status_id = config('constants.staff_status.warning');
                                $shift->save();

                                Log::debug('shift_id:' . $shift->id . '   ' . $staff->email . ' Sent Mail: Not Start ');

                                $object = array(
                                    'type' => 'スタッフの状況警告通知',
                                    'sender' => $staff->name,
                                    'content' => "自宅を出発していません。",
                                    "shift_ids" => [$shift->id]
                                );
                                $super_admins = Staff::where("staff_role_id", config('constants.staff_roles.super_admin'))->get();
                                $field_admins = $shift->field->cstaffs;
                                foreach ($field_admins as $field_admin) {
                                    $field_admin->notify(new StaffNoStart($object));
                                }
                                foreach ($super_admins as $super_admin) {
                                    $super_admin->notify(new StaffNoStart($object));
                                }
                            }
                        }
                    }
                }
            }
        }
        return $notify_count;
    }
}
