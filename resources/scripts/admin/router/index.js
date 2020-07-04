import Vue from 'vue'
import Router from 'vue-router'
import Home from 'admin/pages/Home.vue'
import About from 'admin/pages/About.vue'
import Export from 'admin/pages/Export.vue'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'Home',
      component: Home,
    },
    {
      path: '/export',
      name: 'Export',
      component: Export,
    },
    {
      path: '/about',
      name: 'About',
      component: About,
    },
  ],
})
