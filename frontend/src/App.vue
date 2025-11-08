<script setup lang="ts">
import 'bootstrap/scss/bootstrap.scss';
import './styles/global.scss';
import NavBar from "@/components/NavBar.vue";
</script>

<template>
  <NavBar />
  <router-view />
</template>

<style scoped></style>
