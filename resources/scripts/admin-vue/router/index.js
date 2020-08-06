import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'Home',
      component: () =>
        import(
          /* webpackChunkName: "anymarket_home" */ '@scripts/admin-vue/pages/Home.vue'
        ),
    },
    {
      path: '/instructions',
      name: 'Instructions',
      component: () =>
        import(
          /* webpackChunkName: "anymarket_instructions" */ '@scripts/admin-vue/pages/Instructions.vue'
        ),
    },
    {
      path: '/about',
      name: 'About',
      component: () =>
        import(
          /* webpackChunkName: "anymarket_about" */ '@scripts/admin-vue/pages/About.vue'
        ),
    },
  ],
})
