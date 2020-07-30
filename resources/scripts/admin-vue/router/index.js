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
      path: '/export',
      name: 'Export',
      component: () =>
        import(
          /* webpackChunkName: "anymarket_export" */ '@scripts/admin-vue/pages/Export.vue'
        ),
      redirect: '/export/products',

      children: [
        {
          path: 'products',
          name: 'ExportProducts',
          component: () =>
            import(
              /* webpackChunkName: "anymarket_export_roducts" */ '@scripts/admin-vue/pages/Export/ExportProducts.vue'
            ),
        },
        {
          path: 'categories',
          name: 'ExportCategories',
          component: () =>
            import(
              /* webpackChunkName: "anymarket_export_categories" */ '@scripts/admin-vue/pages/Export/ExportCategories.vue'
            ),
        },
        {
          path: 'brands',
          name: 'ExportBrands',
          component: () =>
            import(
              /* webpackChunkName: "anymarket_export_brands" */ '@scripts/admin-vue/pages/Export/ExportBrands.vue'
            ),
        },
      ],
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
