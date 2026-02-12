<script setup lang="ts">
import {onBeforeMount, type Ref, ref, watch} from "vue";
import LoadingSpin from "@/components/LoadingSpin.vue";
import FetchOops from "@/components/FetchOops.vue";

const props = defineProps({
  query: URLSearchParams
});

// 0: Loading
// 1: failed/Not found
// 2: success
const state = ref(0);
const data: Ref<any> = ref({});

const funcOnBeforeMount = () => {
  state.value = 0;
  fetch(`/api/search/story?${props.query?.toString()}`, {
  })
      .then(v => v.json())
      .then(v => {
        data.value = v;
        state.value = 2;
      })
      .catch(e => {
        console.error(e);
        state.value = 1;
      });
};

onBeforeMount(funcOnBeforeMount);

watch(() => props.query, () => {
  funcOnBeforeMount();
})

const urlGen = (endpoint: String, queryParams: Object = {}, baseQueryParams: URLSearchParams|undefined = undefined, keepCommitId: boolean = true) => {
  let queryBuilder = new URLSearchParams(typeof baseQueryParams !== 'undefined' ? baseQueryParams : undefined);
  for (const [key, value] of Object.entries(queryParams)) {
    if (queryBuilder.has(key))
        queryBuilder.set(key, value);
    else
      queryBuilder.append(key, value);
  }

  return `${endpoint}?${queryBuilder.toString()}`;
};
</script>

<template>
  <LoadingSpin v-if="state === 0" />
  <FetchOops v-else-if="state === 1" />
  <main v-else-if="state === 2">
    <table data-toggle="table" class="table table-striped">
      <thead>
      <tr>
        <th>話タイトル/章節情報</th>
        <th>作品名/作者</th>
      </tr>
      </thead>
      <tbody>
        <tr v-for="story in (data ?? [])" :key="`${story.novel_info.id}/${story.index}`">
          <th scope="row">
            <router-link :to="urlGen(`/novels/${story.novel_info.id}/subtitles/${story?.index - 1}`)">{{story?.subtitle}}</router-link>
            <div v-if="story?.chapter"><small>Chapter: {{story?.chapter}}</small></div>
            <div v-if="story?.subchapter"><small>Subchapter: {{story?.subchapter}}</small></div>
          </th>
          <td>
            <router-link :to="urlGen(`/novels/${story.novel_info.id}`)">{{ story.novel_info.title }}</router-link><br />
            <small><router-link :to="`/novels?author_exact=${encodeURIComponent(story.novel_info.author)}`">{{ story.novel_info.author }}</router-link></small>
          </td>
        </tr>
      </tbody>
    </table>
    <div>検索上位{{data?.length}}件を表示中</div>
  </main>
</template>

<style scoped>
nav>ul {
  flex-wrap: wrap;
}
</style>
