import request from '@/utils/request'

export function get_role_tree() {
    return request({
        url: 'api/v1/system/role/get-role-tree',
        method: 'get'
    })
}

export function system_role_get_staff_role_list(params) {
    return request({
        url: 'api/v1/system/role/get-staff-role-list',
        method: 'get',
        params: params
    })
}

export function system_role_get_staff_role(params) {
    return request({
        url: 'api/v1/system/role/get-staff-role',
        method: 'get',
        params: params
    })
}

export function system_role_delete_staff_roles(data) {
    return request({
        url: 'api/v1/system/role/delete-staff-role',
        method: 'post',
        data: data
    })
}

export function system_role_update_staff_role(data) {
    return request({
        url: 'api/v1/system/role/update-staff-role',
        method: 'post',
        data: data
    })
}

export function system_role_add_staff_role(data) {
    return request({
        url: 'api/v1/system/role/add-staff-role',
        method: 'post',
        data: data
    })
}

export function system_get_editable_tree_menu() {
    return request({
        url: 'api/v1/system/menu/get-editable-tree-menu',
        method: 'get'
    })
}

export function system_delete_menu(data) {
    return request({
        url: 'api/v1/system/menu/delete-menu',
        method: 'post',
        data: data
    })
}

export function system_update_menu(data) {
    return request({
        url: 'api/v1/system/menu/update-menu',
        method: 'post',
        data: data
    })
}

export function system_add_menu(data) {
    return request({
        url: 'api/v1/system/menu/add-menu',
        method: 'post',
        data: data
    })
}
