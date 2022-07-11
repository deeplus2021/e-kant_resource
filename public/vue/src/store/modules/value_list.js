import {getStaffRoleList} from '@/api/value_list'

export default {
    state: {
        staff_role_list: [],
    },
    mutations: {
        SET_STAFF_ROLE_LIST: (state, data) => {
            state.staff_role_list = data
        },
    },
    actions: {
        setStaffRoleList({commit}) {
            getStaffRoleList().then(res => {
                if (res.data.status == "success") {
                    commit('SET_STAFF_ROLE_LIST', res.data.result)
                }
            })
        },
    }
}
