import request from '@/utils/request'

export function getPostInfoList(params) {
    return request({
        url: 'api/v1/post-master/get-post-info-list',
        method: 'get',
        params: params
    })
}

export function getFieldInfoList(params) {
    return request({
        url: 'api/v1/post-master/get-field-info-list',
        method: 'get',
        params: params
    })
}

export function getPostInfo(params) {
    return request({
        url: 'api/v1/post-master/get-post-info',
        method: 'get',
        params: params
    })
}

export function addPost(data) {
    return request({
        url: 'api/v1/post-master/add-post',
        method: 'post',
        data: data
    })
}

export function updatePost(data) {
    return request({
        url: 'api/v1/post-master/update-post',
        method: 'post',
        data: data
    })
}

export function deletePosts(data) {
    return request({
        url: 'api/v1/post-master/delete-posts',
        method: 'post',
        data: data
    })
}
