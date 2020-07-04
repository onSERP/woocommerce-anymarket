<template>
  <div class="app-about">
    <div class="container mb-8">
      <img class="border-0" src="@images/logo-onserp-azul.png" alt="" />
    </div>
    <div class="container">
      <p class="title mb-4">Sobre este plugin</p>

      <main style="max-width: 600px;" class="wysiwyg">
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
    </div>
  </div>
</template>

<script>
import 'wysiwyg.css'

export default {
  name: 'About',
  data() {
    return {}
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
</style>
