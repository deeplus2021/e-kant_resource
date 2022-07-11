import request from '@/utils/request'

export function getFieldList(params) {
    return request({
        url: 'api/v1/work-master/get-field-list',
        method: 'get',
        params: params
    })
}

export function getWorkInfoList(params) {
    return request({
        url: 'api/v1/work-master/get-work-info-list',
        method: 'get',
        params: params
    })
}
