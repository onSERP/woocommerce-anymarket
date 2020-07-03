import Vue from 'vue'
import Router from 'vue-router'
import Home from 'admin/pages/Home.vue'
import About from 'admin/pages/About.vue'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'Home',
      component: Home,
    },
    {
      path: '/about',
      name: 'About',
      component: About,
    },
  ],
})
