// eslint-disable-next-line no-unused-vars
import config from '@config'
import '@styles/admin'

import Vue from 'vue'
import App from './App.vue'
import router from './router'
import VTooltip from 'v-tooltip'

import menuFix from './utils/admin-menu-fix'
import ClipboardJS from 'clipboard'

Vue.config.productionTip = false

Vue.use(VTooltip)

new Vue({
  el: '#anymarket__app',
  router,
  render: (h) => h(App),
})

new ClipboardJS('.btn.clipboard')

// fix the admin menu for the slug "anymarket"
menuFix('anymarket')
