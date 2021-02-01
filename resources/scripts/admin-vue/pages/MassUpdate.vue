<template>
  <div class="home container">
    <div class="mt-8 mb-8 sm:mb-16 px-4">
      <div class="flex flex-wrap justify-between items-center">
        <img
          class="border-0"
          src="@images/logo-anymarket.png"
          alt="Anymarket"
        />
      </div>
      <div class="px-4 mt-16">
        <div class="title">Atualizar em massa</div>
        <div class="mt-8 text-lg">Selecione os items que deseja atualizar.</div>
        <div class="mt-16 flex gap-4 justify-center">
          <div class="form-group">
            <div class="relative">
              <toggle-button
                id="price"
                v-model="options.price"
                sync
                :color="{
                  checked: '#3366FF',
                  unchecked: '#555770',
                  disabled: '#CCCCCC',
                }"
              />
              <span class="absolute left-0 ml-16"> Preços </span>
            </div>
          </div>
          <div class="form-group">
            <div class="relative">
              <toggle-button
                id="stock"
                v-model="options.stock"
                sync
                :color="{
                  checked: '#3366FF',
                  unchecked: '#555770',
                  disabled: '#CCCCCC',
                }"
              />
              <span class="absolute left-0 ml-16"> Estoque </span>
            </div>
          </div>
          <div class="form-group">
            <div class="relative">
              <toggle-button
                id="images"
                v-model="options.images"
                sync
                :color="{
                  checked: '#3366FF',
                  unchecked: '#555770',
                  disabled: '#CCCCCC',
                }"
              />
              <span class="absolute left-0 ml-16"> Imagens </span>
            </div>
          </div>
        </div>

        <div class="mt-16 flex justify-center text-center w-full">
          <a
            :href="buildUrl"
            class="btn btn-base mt-6 sm:mt-0 mb-4 cursor-pointer"
            @click="handleButtonClick"
          >
            Atualizar produtos
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ToggleButton } from 'vue-js-toggle-button'
import { anymarket } from '../utils/api'

import 'vue-loading-overlay/dist/vue-loading.css'

export default {
  name: 'MassUpdate',
  components: {
    ToggleButton,
  },
  data: () => {
    return {
      actionURL: '/wp-admin/?anymarket_action=update_all',
      options: {
        price: false,
        stock: false,
        images: false,
      },
    }
  },
  computed: {
    buildUrl: {
      get() {
        if (this.options.price || this.options.stock || this.options.images) {
          const url = `${
            this.actionURL
          }&images=${this.options.images.toString()}&price=${this.options.price.toString()}&stock=${this.options.stock.toString()}`
          return url
        }
      },
    },
  },
  methods: {
    handleButtonClick(e) {
      if (e.target.href === '') {
        e.preventDefault()
        this.$toasted.error('Selecione pelo menos um dos itens')
      }
    },
  },
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped></style>
