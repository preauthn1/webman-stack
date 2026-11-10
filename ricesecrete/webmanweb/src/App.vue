<script setup>
import { RouterView } from 'vue-router'
import Tabbar from "@/components/Tabbar.vue"
import { ref, onMounted, onUnmounted, watch, nextTick, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useConfigStore } from './stores/config'
import { Push } from '@/utils/push-vue'
import { useUserStore } from '@/stores/user';
const userStore = useUserStore();
const route = useRoute();
const configStore = useConfigStore();
const showTabbar = computed(() => route.meta.showTabbar);
//先获取系统配置在展示router-view
const isready = ref(false);

onMounted(async () => {
  try {
    await configStore.getConfigFn();

    isready.value = true;
  } catch (error) {
    console.log('系统配置获取失败:', error);
    isready.value = true;
  }
});

watch(() => userStore.is_login, (newVal) => {
  if (newVal) {
    nextTick(() => {
      initialPush();
    })
  }
}, { immediate: true })

</script>

<template>
  <RouterView class="container mx-auto px-4 md:px-0 md:w-[1440px]" />
</template>

<style scoped></style>
