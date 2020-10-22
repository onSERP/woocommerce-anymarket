import Vue from 'vue'
import App from './App.vue'
import domReady from '@wordpress/dom-ready'
// import the styles

Vue.config.productionTip = false

//vue
domReady(function () {
  new Vue({
    el: '#notAnElement',
    render: (h) => h(App),
  })
})
