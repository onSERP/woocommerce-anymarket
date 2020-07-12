<template>
  <div>
    <v-data-table
      :loading="loading"
      loadingText="Carregando"
      v-model="selected"
      :headers="headers"
      :items="products"
      :server-items-length="total"
      :options.sync="options"
      item-key="name"
      show-select
      class="shadow-md"
    >
      <template v-slot:item.actions="{ item }">
        <div class="flex justify-center">
          <button class="btn btn-small" @click="exportSingleProduct(item.id)">
            Exportar
          </button>
        </div>
      </template>

      <template v-slot:progress>
        <div class="flex justify-center my-8">
          <loading :active="true" :is-full-page="false"> </loading>
        </div>
      </template>
    </v-data-table>
  </div>
</template>

<script>
import { wooApi } from '../../utils/api'
import Loading from 'vue-loading-overlay'

export default {
  components: {
    Loading,
  },
  data: () => {
    return {
      loading: true,
      selected: [],
      options: {},
      headers: [
        {
          text: 'SKU',
          align: 'start',
          sortable: true,
          value: 'sku',
        },
        { text: 'Nome', sortable: true, value: 'name' },
        { text: 'Ações', value: 'actions', sortable: false },
      ],
      products: [],
      total: 0,
    }
  },
  watch: {
    options: {
      handler() {
        this.getProducts().then((data) => {
          this.products = data.items
          this.total = data.total
        })
      },
      deep: true,
    },
  },
  mounted() {
    this.getProducts()
  },
  methods: {
    /*  getProducts(rows = 10, page = 1) {
      this.loading = true
      wooApi
        .get('products', { per_page: rows, page })
        .then((response) => {
          this.formatProducts(
            response.data,
            parseInt(response.headers['x-wp-total'])
          )
        })
        .catch((err) => console.log(err))
    }, */
    getProducts() {
      return new Promise((resolve, reject) => {
        try {
          let response = wooApi.get('products', {})

          const total = parseInt(response.headers['x-wp-total'])

          let products = response.data.map((item) => {
            return {
              id: item.id,
              name: item.name,
              sku: item.sku,
            }
          })

          const { sortBy, sortDesc, page, itemsPerPage } = this.options

          if (sortBy.length === 1 && sortDesc.length === 1) {
            products = products.sort((a, b) => {
              const sortA = a[sortBy[0]]
              const sortB = b[sortBy[0]]

              if (sortDesc[0]) {
                if (sortA < sortB) return 1
                if (sortA > sortB) return -1
                return 0
              } else {
                if (sortA < sortB) return -1
                if (sortA > sortB) return 1
                return 0
              }
            })
          }

          if (itemsPerPage > 0) {
            items = items.slice((page - 1) * itemsPerPage, page * itemsPerPage)
          }

          setTimeout(() => {
            this.loading = false
            resolve({
              products,
              total,
            })
          }, 1000)
        } catch (err) {
          reject()
          console.error(err)
        }
      })
    },
    exportSingleProduct(product) {
      console.log('Exportou!')
      console.log(product)
    },
    exportMultipleProducts(products) {
      console.log('Exportou Vários!')
      console.log(products)
    },
  },
}
</script>

<style></style>
