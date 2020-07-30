<template>
  <div class="px-4">
    <div class="flex flex-wrap">
      <StatusBox
        title="Status da integração"
        :dataText="active === true ? 'ATIVA' : 'INATIVA'"
        :alert-color="active === true ? 'green' : 'red'"
      />
      <StatusBox
        title="Produtos exportados"
        :dataText="`${exportedProducts}/${totalProducts}`"
      />
      <StatusBox
        title="Categorias exportadas"
        :dataText="`${exportedCategories}/${totalCategories}`"
      />
      <StatusBox
        title="Marcas exportadas"
        :dataText="`${exportedBrands}/${totalBrands}`"
      />
    </div>
  </div>
</template>

<script>
import StatusBox from './shared/StatusBox'
import { anymarket } from '../utils/api'

export default {
  components: {
    StatusBox,
  },
  data: () => {
    return {
      active: false,
      totalProducts: 0,
      totalCategories: 0,
      totalBrands: 0,
      exportedProducts: 0,
      exportedCategories: 0,
      exportedBrands: 0,
    }
  },
  mounted() {
    this.getStatus()
  },
  methods: {
    getStatus() {
      anymarket.get('status').then((response) => {
        this.active = response.data.isValidToken
        this.totalProducts = response.data.totalProducts
        this.totalCategories = response.data.totalCategories
        this.totalBrands = response.data.totalBrands
        this.exportedProducts = response.data.exportedProducts
        this.exportedCategories = response.data.exportedCategories
        this.exportedBrands = response.data.exportedBrands

        if (!response.data.isValidToken)
          this.$toasted.error('Erro ao validar o Token')
      })
    },
  },
}
</script>

<style></style>
