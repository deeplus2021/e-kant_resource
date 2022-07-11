import request from '@/utils/request'

export function getAttendanceList(params) {
    return request({
        url: 'api/v1/attendance-master/get-attendance-list',
        method: 'get',
        params: params
    })
}

export function getStaffAttendanceList(params) {
    return request({
        url: 'api/v1/attendance-master/get-staff-attendance-list',
        method: 'get',
        params: params
    })
}

export function getAttendanceInfo(params) {
    return request({
        url: 'api/v1/attendance-master/get-attendance-info',
        method: 'get',
        params: params
    })
}

export function confirmRequestLate(data) {
    return request({
        url: 'api/v1/attendance-master/confirm-request-late',
        method: 'post',
        data: data
    })
}

export function confirmRequestEarlyLeave(data) {
    return request({
        url: 'api/v1/attendance-master/confirm-request-early-leave',
        method: 'post',
        data: data
    })
}

export function confirmRequestRest(data) {
    return request({
        url: 'api/v1/attendance-master/confirm-request-rest',
        method: 'post',
        data: data
    })
}

export function confirmRequestOverTime(data) {
    return request({
        url: 'api/v1/attendance-master/confirm-request-over-time',
        method: 'post',
        data: data
    })
}

export function confirmRequestAltDate(data) {
    return request({
        url: 'api/v1/attendance-master/confirm-request-alt-date',
        method: 'post',
        data: data
    })
}

export function confirmAllRequest(data) {
    return request({
        url: 'api/v1/attendance-master/confirm-all-request',
        method: 'post',
        data: data
    })
}

export function confirmRequestArrive(data) {
    return request({
        url: 'api/v1/attendance-master/confirm-request-arrive',
        method: 'post',
        data: data
    })
}

export function confirmRequestLeave(data) {
    return request({
        url: 'api/v1/attendance-master/confirm-request-leave',
        method: 'post',
        data: data
    })
}

export function confirmRequestBreak(data) {
    return request({
        url: 'api/v1/attendance-master/confirm-request-break',
        method: 'post',
        data: data
    })
}

export function confirmRequestNightBreak(data) {
    return request({
        url: 'api/v1/attendance-master/confirm-request-night-break',
        method: 'post',
        data: data
    })
}

export function exportAttendance(data) {
    return request({
        url: 'api/v1/attendance-master/export-attendance',
        method: 'get',
        params: data,
        responseType: 'blob'
    })
}

