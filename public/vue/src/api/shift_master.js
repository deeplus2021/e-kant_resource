import request from '@/utils/request'

export function getFieldList(params) {
    return request({
        url: 'api/v1/shift-master/get-field-list',
        method: 'get',
        params: params
    })
}

export function getShiftInfoList(params) {
    return request({
        url: 'api/v1/shift-master/get-shift-info-list',
        method: 'get',
        params: params
    })
}

export function getShiftMonthInfo(params) {
    return request({
        url: 'api/v1/shift-master/get-shift-month-info',
        method: 'get',
        params: params
    })
}

export function getShiftWeekInfo(params) {
    return request({
        url: 'api/v1/shift-master/get-shift-week-info',
        method: 'get',
        params: params
    })
}

export function getPostTimes(params) {
    return request({
        url: 'api/v1/shift-master/get-post-times',
        method: 'get',
        params: params
    })
}

export function getStaffList(params) {
    return request({
        url: 'api/v1/shift-master/get-staff-list',
        method: 'get',
        params: params
    })
}

export function getShiftInfo(params) {
    return request({
        url: 'api/v1/shift-master/get-shift-info',
        method: 'get',
        params: params
    })
}

export function getShiftList(params) {
    return request({
        url: 'api/v1/shift-master/get-shift-list',
        method: 'get',
        params: params
    })
}

export function addShift(data) {
    return request({
        url: 'api/v1/shift-master/add-shift',
        method: 'post',
        data: data
    })
}

export function updateShift(data) {
    return request({
        url: 'api/v1/shift-master/update-shift',
        method: 'post',
        data: data
    })
}

export function deleteShifts(data) {
    return request({
        url: 'api/v1/shift-master/delete-shifts',
        method: 'post',
        data: data
    })
}
export function deleteAllShifts(data) {
  return request({
    url: 'api/v1/shift-master/delete-all-shifts',
    method: 'post',
    data: data
  })
}

export function updateShiftList(data) {
    return request({
        url: 'api/v1/shift-master/update-shift-list',
        method: 'post',
        data: data
    })
}

export function checkStaffHoliday(data) {
    return request({
        url: 'api/v1/shift-master/check-staff-holiday',
        method: 'post',
        data: data
    })
}

export function exportMonthShifts(data) {
    return request({
        url: 'api/v1/shift-master/export-month-shifts',
        method: 'get',
        params: data,
        responseType: 'blob'
    })
}

export function exportWeekShifts(data) {
    return request({
        url: 'api/v1/shift-master/export-week-shifts',
        method: 'get',
        params: data,
        responseType: 'blob'
    })
}

export function exportDayShifts(data) {
    return request({
        url: 'api/v1/shift-master/export-day-shifts',
        method: 'get',
        params: data,
        responseType: 'blob'
    })
}

