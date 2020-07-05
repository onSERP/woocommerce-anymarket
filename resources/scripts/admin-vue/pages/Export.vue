<template>
  <div class="app-export">
    <div class="container mt-8 mb-8 sm:mb-16 px-4">
      <div class="flex flex-wrap justify-between items-center">
        <img
          class="border-0"
          src="@images/logo-anymarket.png"
          alt="Anymarket"
        />
      </div>
    </div>

    <div class="container">
      <div class="flex">
        <div class="w-3/12 px-4">
          <div
            class="pb-4 border border-gray-100 shadow bg-white rounded-md sticky"
            style="top: 60px;"
          >
            <div class="my-4 text-2xl font-bold text-center">
              Exportar
            </div>
            <ul class="menu">
              <li>
                <router-link :to="{ name: 'ExportProducts' }">
                  Produtos
                </router-link>
              </li>
              <li>
                <router-link :to="{ name: 'ExportCategories' }">
                  Categorias
                </router-link>
              </li>
              <li>
                <router-link :to="{ name: 'ExportBrands' }">
                  Marcas
                </router-link>
              </li>
            </ul>
          </div>
        </div>

        <div class="w-9/12 px-4">
          <router-view></router-view>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Export',
}
</script>

<style lang="css" scoped></style>
