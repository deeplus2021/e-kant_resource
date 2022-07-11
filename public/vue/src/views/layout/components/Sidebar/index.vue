<template>
    <scroll-bar>
        <div style="height: 60px;font-size: 16px;color: #FFFFFF;position: relative">
            <p style="position: absolute; margin: 0; top: 50%;left: 50%; margin-right: -50%; transform: translate(-50%, -50%)"
               v-show="!isCollapse">e_KANT</p>
        </div>
        <el-menu
                mode="vertical"
                :show-timeout="200"
                :default-active="$route.path"
                :collapse="isCollapse"
                text-color="#bfcbd9"
                active-text-color="#FFFFFF"
        >
            <sidebar-item :routes="routes"></sidebar-item>
        </el-menu>
    </scroll-bar>
</template>

<script>
    import {mapGetters} from 'vuex'
    import SidebarItem from './SidebarItem'
    import ScrollBar from '@/components/ScrollBar'

    export default {
        components: {SidebarItem, ScrollBar},
        computed: {
            ...mapGetters([
                'sidebar',
                'routers',
            ]),
            routes() {
                // return this.$router.options.routes
                return this.routers
            },
            isCollapse() {
                return !this.sidebar.opened
            },
        }
    }
</script>
