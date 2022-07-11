import request from '@/utils/request'

export function getStaffRoleList() {
    return request({
        url: 'api/v1/value-list/get-staff-role-list',
        method: 'get'
    })
}
