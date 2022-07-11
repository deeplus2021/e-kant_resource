import request from '@/utils/request'

export function getFieldInfoList(params) {
    return request({
        url: 'api/v1/field-master/get-field-info-list',
        method: 'get',
        params: params
    })
}

export function getFieldInfo(params) {
    return request({
        url: 'api/v1/field-master/get-field-info',
        method: 'get',
        params: params
    })
}

export function addField(data) {
    return request({
        url: 'api/v1/field-master/add-field',
        method: 'post',
        data: data
    })
}

export function updateField(data) {
    return request({
        url: 'api/v1/field-master/update-field',
        method: 'post',
        data: data
    })
}

export function uploadFiles(data) {
    return request.post('api/v1/field-master/upload-files',
        data,
        {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        }
    )
}

export function deleteFields(data) {
    return request({
        url: 'api/v1/field-master/delete-fields',
        method: 'post',
        data: data
    })
}

export function getStaffList(params) {
    return request({
        url: 'api/v1/field-master/get-staff-list',
        method: 'get',
        params: params
    })
}

export function getFieldList(params) {
    return request({
        url: 'api/v1/field-master/get-field-list',
        method: 'get',
        params: params
    })
}


export function downloadFieldFile(data) {
    return request({
        url: 'api/v1/field-master/download-field-file',
        method: 'get',
        params: data,
        responseType: 'blob'
    })
}
