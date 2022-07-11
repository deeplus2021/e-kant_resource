<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/10/29
 * Time: 11:25
 */

return [
    'system' => [
        'late_time' => 1,
        'early_time' => 1,
        'over_time' => 15,
        'start_distance' => 0.01 // km
    ],
    'staff_roles' => [
        'super_admin' => 1,
        'field_admin' => 2,
        'staff' => 3,
        'e_staff' => 4,
    ],
    'staff_status' => [
        'already' => 1,
        'started' => 2,
        'arrived' => 3,
        'leaved' => 4,
        'space' => 5,
        'warning' => 6,
    ],
    'admin_notify' => [
        'confirm_yesterday' => 1,
        'confirm_today' => 2,
        'early_leave' => 3,
        'rest' => 4,
        'over_time' => 5,
        'alt_date' => 6,
        'late' => 7,
        'arrive' => 8,
        'leave' => 9,
        'break' => 10,
        'night_break' => 11,
    ],
    'admin_confirm_type' => [
        'reject' => 0,
        'admission' => 1,
        'edited_admission' => 2
    ],
];