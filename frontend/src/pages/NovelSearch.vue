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
  if (state.value !== 2)
  state.value = 0;
  fetch(`/api/search/novel?${props.query?.toString()}`, {
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
        <th>タイトル</th>
        <th>更新日</th>
        <th>作者</th>
        <th>掲載サイト</th>
      </tr>
      </thead>
      <tbody>
        <tr v-for="novel in (data ?? [])" :key="novel.id">
          <th scope="row"><router-link :to="urlGen(`/novels/${novel.id}`)">{{ novel.title }}</router-link></th>
          <td>{{ new Date(novel.general_lastup).toLocaleString() }}</td>
          <td>
            <router-link :to="`/novels?author_exact=${encodeURIComponent(novel.author)}`">{{ novel.author }}</router-link>
          </td>
          <td><a :href="novel.toc_url" target="_blank" rel="noopener noreferrer">{{ novel.sitename }}</a></td>
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
