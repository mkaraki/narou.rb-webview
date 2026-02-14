<script setup lang="ts">
import 'bootstrap/dist/js/bootstrap.min.js';
import 'bootstrap/scss/bootstrap.scss';
import './styles/global.scss';
import NavBar from "@/components/NavBar.vue";
</script>

<template>
  <NavBar />
  <router-view v-slot="{ Component }">
    <keep-alive :include="['List', 'NovelSearch', 'Revisions', 'Stories', 'Story', 'StorySearch']">
      <component :is="Component" />
    </keep-alive>
  </router-view>
</template>

<style scoped></style>
