<template>
  <div class="app-about container">
    <div class="mb-8">
      <img class="border-0" src="@images/logo-onserp-azul.png" alt="" />
    </div>
    <div class="px-4">
      <p class="title mb-4">Sobre este plugin</p>
      <p class="text-lg mb-4">Woocommerce Anymarket v1.0.0</p>
      <main class="wysiwyg">
        <p>
          Este plugin é desenvolvido e mantido por
          <a href="https://onserp.com.br" target="_blank">
            onSERP Marketing
          </a>
        </p>

        <p>
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Quos aliquid
          facere soluta veniam similique voluptatibus nostrum vel sed minus
          culpa a harum ratione inventore necessitatibus, laboriosam repellat
          explicabo exercitationem odit.
        </p>

        <p>
          Lorem ipsum dolor, sit amet consectetur adipisicing elit.
          Exercitationem tempora quos doloribus id, at consequatur aliquid
          ducimus cum aut, explicabo, cupiditate doloremque reprehenderit eos
          asperiores modi totam accusantium! Voluptatibus tenetur reprehenderit
          sunt quis. Praesentium illo culpa consequuntur placeat adipisci nihil
          doloremque consectetur velit asperiores pariatur, voluptas nam qui at
          delectus.
        </p>
      </main>

      <p class="title my-4">
        Changelog
      </p>
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

<style lang="scss" scoped>
.wysiwyg {
  p {
    @apply text-base;
  }

  a {
    @apply text-blue-600;
  }
}

.changelog {
  max-height: 320px;
  @apply font-mono text-sm overflow-y-scroll bg-gray-300 rounded mb-8 py-6 px-4;
}
</style>
