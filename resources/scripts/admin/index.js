// eslint-disable-next-line no-unused-vars
import config from '@config'
import '@styles/admin'

import Vue from 'vue'
import App from './App.vue'
import router from './router'

import menuFix from './utils/admin-menu-fix'

Vue.config.productionTip = false

new Vue({
  el: '#anymarket__app',
  router,
  render: (h) => h(App),
})

// fix the admin menu for the slug "anymarket"
menuFix('anymarket')
