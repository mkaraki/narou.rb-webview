<script setup lang="ts">
import {onBeforeMount, type Ref, ref, watch} from "vue";
import ReaderPageController from "@/components/ReaderPageController.vue";
import LoadingSpin from "@/components/LoadingSpin.vue";
import FetchOops from "@/components/FetchOops.vue";

const props = defineProps({
  query: URLSearchParams,
  novel_id: String,
  story_id: String,
});

// 0: Loading
// 1: failed/Not found
// 2: success
const state = ref(0);
const data: Ref<any> = ref({});

const funcOnBeforeMount = () => {
  state.value = 0;
  fetch(`/api/novels/${props?.novel_id}/subtitles/${props?.story_id}?${props.query?.toString()}`, {
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

watch(() => props.story_id, () => {
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
  <div class="container p-4 read-container" v-else-if="state === 2">
    <div class="row">
      <div class="col">
        <div class="text-center">
          <div class="novel-title">{{ data?.reader_info?.novel_title }}</div>
          <div class="novel-info">{{ data?.chapter ?? '' }}</div>
          <div class="novel-info">{{ data?.subchapter ?? '' }}</div>
          <div class="novel-info">{{ data?.subtitle ?? '' }}</div>
        </div>
        <div class="text-end">
          {{ data?.reader_info?.novel_author ?? '' }}
        </div>
        <hr />
      </div>
      <ReaderPageController
          :nextUrl="urlGen(`/novels/${novel_id}/subtitles/${parseInt(story_id ?? '-1') + 1}`, {}, query)"
          :prevUrl="urlGen(`/novels/${novel_id}/subtitles/${parseInt(story_id ?? '1') - 1}`, {}, query)"
          :tocUrl="urlGen(`/novels/${novel_id}`)"
          :storyNo="parseInt(story_id ?? '0')" :totalStoryNo="data?.reader_info?.novel_total_subtitles"
      />
      <div class="row">
        <div class="col">
          <div class="novelview novelintro" v-html="data?.reader_info.element?.introduction"></div>
          <hr />
          <div class="novelview body" v-html="data?.reader_info?.element?.body"></div>
          <hr />
          <div class="novelview novelpost" v-html="data?.reader_info?.element?.postscript"></div>
        </div>
      </div>
    </div>
    <ReaderPageController
        :nextUrl="urlGen(`/novels/${novel_id}/subtitles/${parseInt(story_id ?? '-1') + 1}`, {}, query)"
        :prevUrl="urlGen(`/novels/${novel_id}/subtitles/${parseInt(story_id ?? '1') - 1}`, {}, query)"
        :tocUrl="urlGen(`/novels/${novel_id}`)"
        :storyNo="parseInt(story_id ?? '0')" :totalStoryNo="data?.reader_info?.novel_total_subtitles"
    />
  </div>
</template>

<style scoped>
</style>