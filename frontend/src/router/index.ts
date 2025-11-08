import { createRouter, createWebHistory } from 'vue-router'

const List = () => import('../pages/List.vue');
const Stories = () => import('../pages/Stories.vue');
const Revisions = () => import('../pages/Revisions.vue');
const Story = () => import('../pages/Story.vue');

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/novels' },
    {
      path: '/novels', component: List, props: route => ({
        query: new URLSearchParams(Array.from(Object.entries(route.query).values()).map((v: any) => [v[0], v[1] as string])),
      }),
    },
    {
      path: '/novels/:novel_id', component: Stories, props: route => ({
        query: new URLSearchParams(Array.from(Object.entries(route.query).values()).map((v: any) => [v[0], v[1] as string])),
        novel_id: route.params.novel_id,
      }),
    },
    {
      path: '/novels/:novel_id/revisions', component: Revisions, props: route => ({
        query: new URLSearchParams(Array.from(Object.entries(route.query).values()).map((v: any) => [v[0], v[1] as string])),
        novel_id: route.params.novel_id,
      }),
    },
    {
      path: '/novels/:novel_id/subtitles/:story_id', component: Story, props: route => ({
        query: new URLSearchParams(Array.from(Object.entries(route.query).values()).map((v: any) => [v[0], v[1] as string])),
        novel_id: route.params.novel_id,
        story_id: route.params.story_id,
      }),
    },
  ],
})

export default router
