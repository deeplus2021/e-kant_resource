import request from '@/utils/request'

export function getFieldList(params) {
    return request({
        url: 'api/v1/open-status/get-field-list',
        method: 'get',
        params: params
    })
}

export function getFieldStatus(params) {
    return request({
        url: 'api/v1/open-status/get-field-status',
        method: 'get',
        params: params
    })
}
