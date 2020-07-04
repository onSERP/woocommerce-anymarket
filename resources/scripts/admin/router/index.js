import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'Home',
      component: () =>
        import(/* webpackChunkName: "anymarket_home" */ 'admin/pages/Home.vue'),
    },
    {
      path: '/export',
      name: 'Export',
      component: () =>
        import(
          /* webpackChunkName: "anymarket_export" */ 'admin/pages/Export.vue'
        ),
      redirect: '/export/products',

      children: [
        {
          path: 'products',
          name: 'ExportProducts',
          component: () =>
            import(
              /* webpackChunkName: "anymarket_export_roducts" */ 'admin/pages/Export/ExportProducts.vue'
            ),
        },
        {
          path: 'categories',
          name: 'ExportCategories',
          component: () =>
            import(
              /* webpackChunkName: "anymarket_export_categories" */ 'admin/pages/Export/ExportCategories.vue'
            ),
        },
        {
          path: 'brands',
          name: 'ExportBrands',
          component: () =>
            import(
              /* webpackChunkName: "anymarket_export_brands" */ 'admin/pages/Export/ExportBrands.vue'
            ),
        },
      ],
    },
    {
      path: '/about',
      name: 'About',
      component: () => import('admin/pages/About.vue'),
    },
  ],
})
