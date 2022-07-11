import Vue from 'vue'
import Vuex from 'vuex'
import app from './modules/app'
import staff from './modules/staff'
import permission from './modules/permission'
import value_list from './modules/value_list'
import getters from './getters'

Vue.use(Vuex)

const store = new Vuex.Store({
    modules: {
        app,
        staff,
        permission,
        value_list
    },
    getters
})

export default store
