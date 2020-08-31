import Vue from 'vue'
import App from './App.vue'
import router from './router'

import '@styles/admin-vue'
import 'typeface-roboto'

Vue.config.productionTip = false

import VTooltip from 'v-tooltip'
Vue.use(VTooltip)

import Toasted from 'vue-toasted'
Vue.use(Toasted, {
  position: 'bottom-center',
  duration: 3000,
})

//vue
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
