<template>
  <div class="home container">
    <loading :active.sync="isLoading" :is-full-page="true"> </loading>
    <div class="mt-8 mb-8 sm:mb-16 px-4">
      <div class="flex flex-wrap justify-between items-center">
        <img
          class="border-0"
          src="@images/logo-anymarket.png"
          alt="Anymarket"
        />
        <div>
          <div class="select-menu">
            <div class="btn btn-base mt-6 sm:mt-0">Exportar em massa</div>
            <ul>
              <li>
                <a
                  href="/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product"
                >
                  1º PASSO - Exportar Categorias
                </a>
              </li>
              <li>
                <a
                  href="/wp-admin/edit.php?post_type=product&page=product_attributes"
                >
                  2º PASSO - Exportar Atributos
                </a>
              </li>
              <li>
                <a href="/wp-admin/edit.php?post_type=product">
                  3º PASSO - Exportar Produtos
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <StatusBar />
    <div class="px-4 mt-16">
      <div class="flex mb-8">
        <p class="title">Configurações</p>
      </div>
      <form class="flex flex-col">
        <div class="form-group">
          <label for="anymarketToken">Token da ANYMARKET</label>
          <div class="input">
            <input
              v-model="options.anymarketToken"
              id="anymarketToken"
              name="anymarketToken"
              type="text"
              required
            />
          </div>
        </div>
        <div class="form-group">
          <label for="anymarketOI">OI</label>
          <div class="input">
            <input
              v-model="options.anymarketOI"
              id="anymarketOI"
              name="anymarketOI"
              type="text"
              required
            />
          </div>
        </div>
        <div class="form-group">
          <label for="env">Ambiente</label>
          <div class="input relative">
            <toggle-button
              id="env"
              v-model="options.isDevEnv"
              sync
              :color="{
                checked: '#3366FF',
                unchecked: '#555770',
                disabled: '#CCCCCC',
              }"
            />
            <span class="absolute left-0 ml-16">
              Usar ambiente de homologação
            </span>
            <div class="help-text">
              Marque a opção se o integrador informar que seu ambiente é de
              Testes (Sandbox)
            </div>
          </div>
        </div>
        <div class="form-group">
          <label for="showLogs">Logs</label>
          <div class="input relative">
            <toggle-button
              id="showLogs"
              v-model="options.showLogs"
              sync
              :color="{
                checked: '#3366FF',
                unchecked: '#555770',
                disabled: '#CCCCCC',
              }"
            />
            <span class="absolute left-0 ml-16"> Habilitar logs </span>
            <div class="help-text">
              Registra os eventos do plugin.
              <a href="/wp-admin/admin.php?page=wc-status&tab=logs">Ver Logs</a>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label for="useOrder">Pedido</label>
          <div class="input relative">
            <toggle-button
              id="useOrder"
              v-model="options.useOrder"
              sync
              :color="{
                checked: '#3366FF',
                unchecked: '#555770',
                disabled: '#CCCCCC',
              }"
            />
            <span class="absolute left-0 ml-16"> Integrar pedidos </span>
            <div class="help-text">
              Caso desabilitado, sua loja não receberá pedidos e apenas o
              estoque será descontado.
            </div>
          </div>
        </div>
        <div class="form-group">
          <label for="useOrder">Modo de Edição</label>
          <div class="input relative">
            <toggle-button
              id="useOrder"
              v-model="options.editMode"
              sync
              :color="{
                checked: '#3366FF',
                unchecked: '#555770',
                disabled: '#CCCCCC',
              }"
            />
            <span class="absolute left-0 ml-16"></span>
            <div class="help-text">
              Ative esta opção caso deseje associar livremente um produto no
              Woocommerce a um produto no Anymarket.
              <b>
                ATENÇÃO: Desative após usar. Manter essa opção ativada por muito
                tempo pode gerar comportamentos inesperados na sua loja.
              </b>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label for="callbackURL">URL para Callback</label>
          <div class="input input-copy">
            <input
              type="text"
              id="callbackURL"
              name="callbackURL"
              readonly
              v-model="options.callbackURL"
            />
            <button
              class="btn clipboard"
              @click.prevent="copiedTooltip"
              data-clipboard-target="#callbackURL"
              v-tooltip="{
                content: 'Copiado!',
                trigger: 'manual',
                offset: '10',
                show: copiedTooltipIsOpen,
              }"
            >
              Copiar
            </button>
          </div>
        </div>
        <div class="form-group mt-16">
          <label for=""></label>
          <button
            type="submit"
            @click.prevent="handleForm"
            class="btn btn-base w-32"
          >
            Salvar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import StatusBar from '../components/StatusBar'
import { ToggleButton } from 'vue-js-toggle-button'
import { anymarket } from '../utils/api'

import 'vue-loading-overlay/dist/vue-loading.css'

export default {
  name: 'Home',
  components: {
    StatusBar,
    ToggleButton,
    Loading,
  },
  data: () => {
    return {
      isLoading: false,
      copiedTooltipIsOpen: false,
      options: {
        anymarketToken: '',
        anymarketOI: '',
        isDevEnv: false,
        callbackURL: '',
        showLogs: false,
        useOrder: false,
        editMode: false,
      },
    }
  },
  mounted() {
    anymarket
      .get('options')
      .then((response) => {
        this.options.anymarketToken = response.data.anymarket_token
        this.options.anymarketOI = response.data.anymarket_oi
        this.options.callbackURL = response.data.callback_url

        this.options.isDevEnv = response.data.is_dev_env === 'true'
        this.options.showLogs = response.data.show_logs === 'true'
        this.options.useOrder = response.data.use_order === 'true'
        this.options.editMode = response.data.edit_mode === 'true'
      })
      .catch((err) => {
        console.log(err)
      })
  },
  methods: {
    copiedTooltip(e) {
      this.copiedTooltipIsOpen = true
      e.target.setAttribute('disabled', 'disabled')

      setTimeout(() => {
        this.copiedTooltipIsOpen = false
        e.target.removeAttribute('disabled')
      }, 1000)
    },
    handleForm(e) {
      this.isLoading = true
      anymarket.put('options', this.options).then((response) => {
        if (response.status === 200) {
          this.$toasted.success('Configurações atualizadas!')
        } else {
          this.$toasted.error('Erro ao atualizar as configurações!')
        }
        this.isLoading = false
      })
    },
  },
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped></style>
