<script setup lang="ts">
const props = defineProps({
  prevUrl: String,
  nextUrl: String,
  tocUrl: String,
  storyNo: Number,
  totalStoryNo: Number,
});
</script>

<template>
  <div class="row">
    <div class="col-4 text-center">
      <router-link :to="prevUrl ?? ''" v-if="(storyNo ?? 1) > 0">前へ</router-link>
    </div>
    <div class="col-4 text-center">
      <router-link :to="tocUrl ?? ''">目次</router-link>

    </div>
    <div class="col-4 text-center">
      <router-link :to="nextUrl ?? ''" v-if="(storyNo ?? 0) < (totalStoryNo ?? 1)">次へ</router-link>
    </div>
  </div>
</template>

<style scoped>

</style>