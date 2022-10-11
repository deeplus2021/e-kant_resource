import router from './router'
import store from './store'
import NProgress from 'nprogress' // Progress 进度条
import 'nprogress/nprogress.css'// Progress 进度条样式
import {Message} from 'element-ui'
import {getToken} from '@/utils/auth' // 验权
import {getMenu} from '@/api/login'

const whiteList = ['/login'] // 不重定向白名单
router.beforeEach((to, from, next) => {
    NProgress.start()
    if (getToken()) {
        if (to.path === '/login') {
            next()
            NProgress.done() // if current page is dashboard will not trigger	afterEach hook, so manually handle it
        } else {
            if (store.getters.roles === 0) {
                store.dispatch('GetInfo').then(res => { // 拉取用户信息
                    getMenu().then(res => {
                        let menus = res.data.result.children;
                        store.dispatch('GenerateRoutes', menus).then(() => { // 生成可访问的路由表
                            router.addRoutes(store.getters.addRouters); // 动态添加可访问路由表
                            next({...to, replace: true})
                        })
                    }).catch((err) => {
                        next({path: '/404'})
                    })
                }).catch((err) => {
                    store.dispatch('FedLogOut').then(() => {
                        Message.error(err || 'Verification failed, please login again')
                        next({path: '/404'})
                    })
                })
            } else {
                next()
            }
        }
    } else {
        if (whiteList.indexOf(to.path) !== -1) {
            next()
        } else {
            next('/login')
            NProgress.done()
        }
    }
})

router.afterEach(() => {
    NProgress.done() // 结束Progress
})


