<template>
  <div class="w-full sm:w-1/2 md:w-56 mt-4 xl:mt-0 px-2">
    <div class="flex flex-col bg-white py-4 shadow-lg rounded">
      <div class="text-base text-center mb-4">
        {{ title }}
      </div>
      <div
        v-if="loaded"
        class="text-xl font-bold text-center"
        :class="{
          'text-green-500': alertColor === 'green',
          'text-red-400': alertColor === 'red',
        }"
      >
        {{ dataText }}
      </div>
      <ContentLoader
        :width="150"
        :height="12"
        viewBox="0 0 150 12"
        v-else
        backgroundColor="#dadada"
        foregroundColor="#c4c1c1"
      >
        <rect x="25" y="0" rx="3" ry="3" width="100" height="12" />
      </ContentLoader>
    </div>
  </div>
</template>

<script>
import { ContentLoader } from 'vue-content-loader'

export default {
  components: {
    ContentLoader,
  },
  props: {
    loaded: Boolean,
    title: String,
    dataText: String,
    alertColor: String,
  },
}
</script>

<style></style>
