<script setup lang="ts">
import {ref, useTemplateRef} from "vue";

const searchType = ref('title-grep');
const searchQuery = ref('');
const searchBtn = useTemplateRef('searchBtn');

const getSearchUrl = () => {
  switch (searchType.value) {
    case "title-grep":
      return `/novels?title_like=${encodeURIComponent(searchQuery.value)}`;
    case "author-grep":
      return `/novels?author_like=${encodeURIComponent(searchQuery.value)}`;
    case "novel-index":
      return `/search/novel?query=${encodeURIComponent(searchQuery.value)}`;
    case "story-index":
      return `/search/story?query=${encodeURIComponent(searchQuery.value)}`;
  }
};
const goSearch = () => {
  searchBtn.value?.click();
};
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
        <form class="d-flex" @submit.prevent="goSearch">
          <select class="form-select" v-model="searchType">
            <option value="title-grep" selected>作品名検索</option>
            <option value="author-grep" selected>作者名検索</option>
            <option value="novel-index">作品検索</option>
            <option value="story-index">話検索</option>
          </select>
          <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" v-model="searchQuery">
          <router-link class="btn btn-outline-success" ref="searchBtn" :to="getSearchUrl()" role="button">検索</router-link>
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
