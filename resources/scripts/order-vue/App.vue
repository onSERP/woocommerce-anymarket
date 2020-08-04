<template>
  <div id="carbon_fields_container_order_data_vue">
    <OrderDetails />
  </div>
</template>

<script>
import OrderDetails from './components/OrderDetails'
export default {
  name: 'App',
  components: {
    OrderDetails,
  },
}
</script>

<style lang="scss">
#carbon_fields_container_order_data_vue {
  .toggle-indicator {
    display: none;
  }
}
</style>
