import {login, logout, getInfo} from '@/api/login'
import {getToken, setToken, removeToken} from '@/utils/auth'

const staff = {
    state: {
        token: getToken(),
        name: '',
        avatar: '',
        roles: 0,
        staff_info: null
    },

    mutations: {
        SET_TOKEN: (state, token) => {
            state.token = token
        },
        SET_NAME: (state, name) => {
            state.name = name
        },
        SET_AVATAR: (state, avatar) => {
            state.avatar = avatar
        },
        SET_ROLES: (state, roles) => {
            state.roles = roles
        },
        SET_STAFF_INFO: (state, staff_info) => {
            state.staff_info = staff_info
        },
    },

    actions: {
        // 获取用户信息
        GetInfo({commit, state}) {
            return new Promise((resolve, reject) => {
                getInfo().then(response => {
                    const data = response.data.result
                    if (data.staff_role_id && data.staff_role_id > 0) { // 验证返回的roles是否是一个非空数组
                        commit('SET_ROLES', data.staff_role_id)
                    } else {
                        reject('提示: 无权限访问!')
                    }
                    commit('SET_NAME', data.name)
                    // commit('SET_AVATAR', data.icon)
                    commit('SET_STAFF_INFO', data)
                    resolve(response)
                }).catch(error => {
                    reject(error)
                })
            })
        },
        // 登出
        // LogOut({ commit, state }) {
        //   return new Promise((resolve, reject) => {
        //     logout(state.token).then(() => {
        //       commit('SET_TOKEN', '')
        //       commit('SET_ROLES', [])
        //       removeToken()
        //       resolve()
        //     }).catch(error => {
        //       reject(error)
        //     })
        //   })
        // },

        // 前端 登出
        FedLogOut({commit}) {
            return new Promise(resolve => {
                commit('SET_TOKEN', '')
                removeToken()
                resolve()
            })
        },
    }
}

export default staff
