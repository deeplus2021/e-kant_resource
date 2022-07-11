import {
    asyncRouterMap,
    constantRouterMap,
} from '@/router/index';

//判断是否有权限访问该菜单
function hasPermission(menus, route) {
    if (route.name) {
        let currMenu = getMenu(route.name, menus);
        if (currMenu != null) {
            //设置菜单的标题、图标和可见性
            if (currMenu.name != null && currMenu.name !== '') {
                route.meta.title = currMenu.name;
            }
            if (currMenu.image_url != null && currMenu.image_url !== '') {
                route.meta.icon = currMenu.image_url;
            }
            if (currMenu.hidden != null) {
                route.hidden = currMenu.hidden !== 0;
            }
            if (currMenu.order != null && currMenu.order !== '') {
                route.sort = currMenu.order;
            }
            return true;
        } else {
            route.sort = 0;
            if (route.hidden !== undefined && route.hidden === true) {
                return true;
            } else {
                return false;
            }
        }
    } else {
        return true
    }
}

//根据路由名称获取菜单
function getMenu(name, menus) {
    for (let i = 0; i < menus.length; i++) {
        let menu = menus[i];
        if (name === menu.code) {
            return menu;
        }
        if (menu.children && menu.children.length > 0) {
            let menuc = menu.children;
            let match = getMenu(name, menuc)
            if (match !== null) {
                return match
            }
        }
    }
    return null;
}

//对菜单进行排序
function sortRouters(accessedRouters) {
    for (let i = 0; i < accessedRouters.length; i++) {
        let router = accessedRouters[i];
        if (router.children && router.children.length > 0) {
            router.children.sort(compare("sort"));
        }
    }
    accessedRouters.sort(compare("sort"));
}

//降序比较函数
function compare(p) {
    return function (m, n) {
        let a = m[p];
        let b = n[p];
        return b - a;
    }
}

const permission = {
    state: {
        routers: constantRouterMap,
        addRouters: [],
    },
    mutations: {
        SET_ROUTERS: (state, data) => {
            state.routers = constantRouterMap.concat(data.all_routers);
            state.addRouters = data.all_routers;
        },
    },
    actions: {
        GenerateRoutes({commit}, menus) {
            return new Promise(resolve => {
                let accessedRouters = asyncRouterMap.filter((v) => {
                    if (hasPermission(menus, v)) {
                        if (v.children && v.children.length > 0) {
                            v.children = v.children.filter(child => {
                                if (hasPermission(menus, child)) {
                                    return child
                                }
                                return false;
                            });
                            return v
                        } else {
                            return v
                        }
                    }
                    return false;
                });
                commit('SET_ROUTERS', {
                    all_routers: accessedRouters,
                });
                resolve();
            })
        },
    }
};

export default permission;
