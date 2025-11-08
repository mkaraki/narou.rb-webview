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
  fetch(`/api/novels/${props?.novel_id}/subtitles?${props.query?.toString()}`, {
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
            <h1>{{ data?.title }}</h1>
          </div>
          <div class="text-end">{{ data?.author }}</div>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <pre>{{data?.story}}</pre>
        </div>
      </div>
    </div>
    <table data-toggle="table" class="table table-striped">
      <thead>
      <tr>
        <th>タイトル</th>
        <th>掲載日</th>
        <th>更新日</th>
      </tr>
      </thead>
      <tbody>
        <template v-for="(subtitle, id) in data?.subtitles" :key="subtitle.index">
          <tr v-if="subtitle.chapter">
            <td colspan="3"><b>{{subtitle.chapter}}</b></td>
          </tr>
          <tr v-if="subtitle.subchapter">
            <td colspan="3"><i>{{subtitle.subchapter}}</i></td>
          </tr>
          <tr>
            <th scope="row">
              <router-link :to="urlGen(`/novels/${novel_id}/subtitles/${id}`)">{{subtitle?.subtitle}}</router-link>
            </th>
            <td>{{ subtitle.subdate }}</td>
            <td>{{ subtitle.subupdate }}</td>
          </tr>
        </template>
      </tbody>
    </table>
    <div>
      全{{ data?.subtitles.length.toLocaleString() }}話,
      <router-link :to="urlGen(`/novels/${novel_id}/revisions`, {}, undefined, false)">過去ログを見る</router-link>
    </div>
  </main>
</template>

<style scoped>
pre {
  white-space: pre-wrap;
}
</style>