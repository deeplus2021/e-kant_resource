import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

/* Layout */
import Layout from '../views/layout/Layout'

/**
 * hidden: true                   if `hidden:true` will not show in the sidebar(default is false)
 * alwaysShow: true               if set true, will always show the root menu, whatever its child routes length
 *                                if not set alwaysShow, only more than one route under the children
 *                                it will becomes nested mode, otherwise not show the root menu
 * redirect: noredirect           if `redirect:noredirect` will no redirct in the breadcrumb
 * name:'router-name'             the name is used by <keep-alive> (must set!!!)
 * meta : {
    title: 'title'               the name show in submenu and breadcrumb (recommend set)
    icon: 'svg-name'             the icon show in the sidebar,
  }
 **/
export const constantRouterMap = [
    {path: '/login', component: () => import('@/views/login/index'), hidden: true},
    {path: '/404', component: () => import('@/views/404'), hidden: true},
    {
        path: '',
        component: Layout,
        redirect: '/open-status-table',
        hidden: true,
    }
]

export const asyncRouterMap = [
    {
        path: '/open-status-table',
        component: Layout,
        redirect: '/open-status-table/index',
        name: 'OpenStatusTable',
        meta: {title: 'オープン状況一覧', icon: 'product'},
        children: [
            {
                path: 'index',
                name: 'OpenStatusTableIndex',
                component: () => import('@/views/master/open_status_table'),
                meta: {title: 'オープン状況一覧', icon: 'product'}
            },
        ]
    },
    {
        path: '/attendance-table',
        component: Layout,
        redirect: '/attendance-table/index',
        name: 'AttendanceTable',
        meta: {title: '勤怠一覧', icon: 'product'},
        children: [
            {
                path: 'index',
                name: 'AttendanceTableIndex',
                component: () => import('@/views/master/attendance_table'),
                meta: {title: '勤怠一覧', icon: 'product'}
            },
        ]
    },
    {
        path: '/work-table',
        component: Layout,
        redirect: '/work-table/index',
        name: 'WorkTable',
        meta: {title: '出勤状況一覧', icon: 'product'},
        children: [
            {
                path: 'index',
                name: 'WorkTableIndex',
                component: () => import('@/views/master/work_table'),
                meta: {title: '出勤状況一覧', icon: 'product'}
            },
        ]
    },
    {
        path: '/post-table',
        component: Layout,
        redirect: '/post-table/index',
        name: 'PostTable',
        meta: {title: '配置ポスト表', icon: 'product'},
        children: [
            {
                path: 'index',
                name: 'PostTableIndex',
                component: () => import('@/views/master/post_table'),
                meta: {title: '配置ポスト表', icon: 'product'}
            },
        ]
    },
    {
        path: '/shift-master',
        component: Layout,
        redirect: '/shift-master/index',
        name: 'ShiftMaster',
        meta: {title: 'シフトマスタ', icon: 'product'},
        children: [
            {
                path: 'index',
                name: 'ShiftMasterIndex',
                component: () => import('@/views/master/shift_master'),
                meta: {title: 'シフトマスタ', icon: 'product'}
            },
        ]
    },
    {
        path: '/staff-master',
        component: Layout,
        redirect: '/staff-master/index',
        name: 'StaffMaster',
        meta: {title: 'スタッフマスタ', icon: 'product'},
        children: [
            {
                path: 'index',
                name: 'StaffMasterIndex',
                component: () => import('@/views/master/staff_master'),
                meta: {title: 'スタッフマスタ', icon: 'product'}
            },
        ]
    },
    {
        path: '/field-master',
        component: Layout,
        redirect: '/field-master/index',
        name: 'FieldMaster',
        meta: {title: '現場マスタ', icon: 'product'},
        children: [
            {
                path: 'index',
                name: 'FieldMasterIndex',
                component: () => import('@/views/master/field_master'),
                meta: {title: '現場マスタ', icon: 'product'}
            },
        ]
    },
]

export default new Router({
    // mode: 'history', //后端支持可开
    scrollBehavior: () => ({y: 0}),
    routes: constantRouterMap
})
