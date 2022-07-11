const getters = {
    sidebar: state => state.app.sidebar,
    device: state => state.app.device,
    token: state => state.staff.token,
    avatar: state => state.staff.avatar,
    name: state => state.staff.name,
    roles: state => state.staff.roles,
    staff_info: state => state.staff.staff_info,
    addRouters: state => state.permission.addRouters,
    routers: state => state.permission.routers,
    screenHeight: state => state.app.screenHeight,
    showFullScreen: state => state.app.showFullScreen,
    staff_role_list: state => state.value_list.staff_role_list,
    googleMapApi: state => state.value_list.googleMapApi,
}
export default getters
