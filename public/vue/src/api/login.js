import request from '@/utils/request'

export function login(email, password) {
    return request({
        url: 'api/v1/login',
        method: 'post',
        data: {
            email,
            password
        }
    })
}

export function logout(data) {
    return request({
        url: 'api/v1/logout',
        method: 'post',
        data: data
    })
}

export function getInfo() {
    return request({
        url: 'api/v1/get-staff',
        method: 'get',
    })
}

export function getMenu() {
    return request({
        url: 'api/v1/system/menu/get-menu',
        method: 'get',
    })
}

export function changePassword(data) {
    return request({
        url: 'api/v1/auth/change-password',
        method: 'post',
        data: data
    })
}
