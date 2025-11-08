<script setup lang="ts">
import {onBeforeMount, type Ref, ref, watch} from "vue";
import LoadingSpin from "@/components/LoadingSpin.vue";
import FetchOops from "@/components/FetchOops.vue";

const props = defineProps({
  query: URLSearchParams,
  novel_id: Number,
});

// 0: Loading
// 1: failed/Not found
// 2: success
const state = ref(0);
const data: Ref<any> = ref({});

const funcOnBeforeMount = () => {
  state.value = 0;
  fetch(`/api/novels/${props?.novel_id}/revisions?${props.query?.toString()}`, {
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

watch(() => props.novel_id, () => {
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

  if (keepCommitId && props.query?.has('commit_id')) {
    const commit_id = props.query?.get('commit_id') ?? '';
    if (!queryBuilder.has('commit_id'))
      queryBuilder.append('commit_id', commit_id);
  }

  return `${endpoint}?${queryBuilder.toString()}`;
};
</script>

<template>
  <LoadingSpin v-if="state === 0" />
  <FetchOops v-else-if="state === 1" />
  <main v-else-if="state === 2">
    <div class="container p-4">
      <div class="row">
        <div class="col">
          <div class="text-center">
            <h1><small class="heading-prefix">Revisions of</small> {{ data?.title }}</h1>
          </div>
          <div class="text-end">{{ data?.author }}</div>
        </div>
      </div>
    </div>
    <table data-toggle="table" class="table table-striped">
      <thead>
      <tr>
        <th>コミットID</th>
        <th>コミットメッセージ</th>
        <th>コミット日</th>
      </tr>
      </thead>
      <tbody>
        <template v-for="(revision, id) in data?.revisions" :key="id">
          <tr>
            <th scope="row">
              <router-link :to="urlGen(`/novels/${novel_id}`, { 'commit_id': revision.commit_id }, undefined, false)">
                {{revision.commit_id}}
              </router-link>
            </th>
            <td>{{ revision.commit_message }}</td>
            <td>{{ new Date(revision.commit_date).toLocaleString() }}</td>
          </tr>
        </template>
      </tbody>
    </table>
    <div>
      全{{ data?.revisions.length.toLocaleString() }}件
    </div>
  </main>
</template>

<style scoped>
pre {
  white-space: pre-wrap;
}
</style>