<script setup lang="ts">
import {ref} from "vue";

const searchQuery = ref('');
</script>

<template>
  <nav class="navbar navbar-expand-lg">
    <div class=" container-fluid">
      <router-link class="navbar-brand" to="/novels">Narou.rb Web Viewer</router-link>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li>
            <router-link to="/novels?sort=general_lastup&order=desc" class="nav-link">更新順一覧</router-link>
          </li>
        </ul>
        <form class="d-flex">
          <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" v-model="searchQuery">
          <router-link class="btn btn-outline-success" :to="`/novels?title_like=${encodeURIComponent(searchQuery)}`" role="button">Search</router-link>
        </form>
      </div>
    </div>
  </nav>
</template>

<style scoped>
a:visited {
  color: black;
}
</style>