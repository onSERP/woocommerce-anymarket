// eslint-disable-next-line no-unused-vars
import config from '@config'
import '@styles/admin'

import Vue from 'vue'
import App from './App.vue'
import router from './router'
import VTooltip from 'v-tooltip'

Vue.config.productionTip = false

Vue.use(VTooltip)

//import data-tables
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import { DataTables, DataTablesServer } from 'vue-data-tables'
Vue.use(ElementUI)
Vue.use(DataTables)
Vue.use(DataTablesServer)

// set language to EN
import lang from 'element-ui/lib/locale/lang/en'
import locale from 'element-ui/lib/locale'
locale.use(lang)

new Vue({
  el: '#anymarket__app',
  router,
  render: (h) => h(App),
})

import ClipboardJS from 'clipboard'
new ClipboardJS('.btn.clipboard')

import menuFix from './utils/admin-menu-fix'
// fix the admin menu for the slug "anymarket"
menuFix('anymarket')
