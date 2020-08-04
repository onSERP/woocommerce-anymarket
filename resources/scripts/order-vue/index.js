import Vue from 'vue'
import App from './App.vue'
import VueGoodTablePlugin from 'vue-good-table'

// import the styles
import 'vue-good-table/dist/vue-good-table.css'

Vue.config.productionTip = false

//vue
wp.domReady(function () {
  Vue.use(VueGoodTablePlugin)

  new Vue({
    el: '#carbon_fields_container_order_data_vue .inside',
    render: (h) => h(App),
  })
})
