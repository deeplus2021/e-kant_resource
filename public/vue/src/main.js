import Vue from 'vue'

import 'normalize.css/normalize.css'// A modern alternative to CSS resets

import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import locale from 'element-ui/lib/locale/lang/ja' // lang i18n
import ViewUI from 'view-design'
import echarts from 'echarts'
import utils from "@/utils";
import '../my-theme/index.less';
import ilocale from 'view-design/dist/locale/ja-JP';

Vue.prototype.$echarts = echarts
Vue.prototype.$utils = utils;
import '@/styles/index.scss' // global css
import components from './components/' //加载公共组件
import App from './App'
import router from './router'
import store from './store'
//import './assets/font_bq5d7efeso7/iconfont.css';//调用自定义矢量图标
import '@/permission'
import '@/icons'

import mDatePicker from 'vue-multi-date-picker'


Vue.use(ElementUI, {locale})
Vue.use(ViewUI, {locale: ilocale})
Vue.use(mDatePicker)

Vue.config.productionTip = false
Object.keys(components).forEach((key) => {
    var name = key.replace(/(\w)/, (v) => v.toUpperCase())
    Vue.component('v' + name, components[key])
})

router.onError((error) => {
    const pattern = /Loading chunk (\d)+ failed/g;
    const isChunkLoadFailed = error.message.match(pattern);
    const targetPath = router.history.pending.fullPath;
    if (isChunkLoadFailed) {
        router.replace(targetPath);
    }
});
new Vue({
    el: '#app',
    router,
    store,
    template: '<App/>',
    components: {App}
})
