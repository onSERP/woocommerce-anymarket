<template>
  <div class="app-about container">
    <div class="mb-8">
      <img class="border-0" src="@images/logo-onserp-azul.png" alt="" />
    </div>
    <div class="px-4">
      <p class="title mb-4">Sobre este plugin</p>
      <p class="text-lg mb-4">Woocommerce Anymarket</p>
      <main class="wysiwyg">
        <p>
          Este plugin é desenvolvido e mantido por
          <a href="https://onserp.com.br" target="_blank"> onSERP Marketing </a>
        </p>

        <p>
          Ao usar este plugin você aceita e concorda com os nossos
          <a href="https://onserp.com.br/anymarket-plugin-termos">
            termos de uso
          </a>
          e com a nossa
          <a href="https://onserp.com.br/politica-de-privacidade">
            política de privacidade
          </a>
        </p>
      </main>

      <p class="title my-4">Changelog</p>
      <div class="changelog wysiwyg" v-html="changelog"></div>
    </div>
  </div>
</template>

<script>
import 'wysiwyg.css'
import changelog from '@changelog'

export default {
  name: 'About',
  data() {
    return {
      changelog: changelog,
    }
  },
}
</script>

<style lang="scss">
.app-about {
  main {
    p {
      @apply text-base;
    }
  }
  .wysiwyg {
    a {
      @apply text-blue-600;
    }
  }

  .changelog {
    max-height: 320px;

    p,
    ul,
    li {
      @apply text-sm;
    }

    h1 {
      @apply text-xl;
    }

    h2 {
      @apply text-base;
    }
    @apply font-mono overflow-y-scroll bg-gray-300 rounded mb-8 py-6 px-4;
  }
}
</style>
