import request from '@/utils/request'

export function getStaffInfoList(params) {
    return request({
        url: 'api/v1/staff-master/get-staff-info-list',
        method: 'get',
        params: params
    })
}

export function getStaffInfo(params) {
    return request({
        url: 'api/v1/staff-master/get-staff-info',
        method: 'get',
        params: params
    })
}

export function addStaff(data) {
    return request({
        url: 'api/v1/staff-master/add-staff',
        method: 'post',
        data: data
    })
}

export function updateStaff(data) {
    return request({
        url: 'api/v1/staff-master/update-staff',
        method: 'post',
        data: data
    })
}

export function deleteStaffs(data) {
    return request({
        url: 'api/v1/staff-master/delete-staffs',
        method: 'post',
        data: data
    })
}

export function importStaffs(data) {
    return request({
        url: 'api/v1/staff-master/import-staffs',
        method: 'post',
        data: data
    })
}

export function exportStaffs(data) {
    return request({
        url: 'api/v1/staff-master/export-staffs',
        method: 'get',
        params: data,
        responseType: 'blob'
    })
}


export function updateDeviceToken(data) {
    return request({
        url: 'api/v1/staff-master/update-device-token',
        method: 'post',
        data: data
    })
}


export function getStaffAddress(params) {
    return request({
        url: 'api/v1/staff-master/get-staff-address',
        method: 'get',
        params: params
    })
}

export function updateStaffAddress(data) {
    return request({
        url: 'api/v1/staff-master/update-staff-address',
        method: 'post',
        data: data
    })
}

