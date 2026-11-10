<template>
    <div class="">
        <van-tabbar route v-model="active" inactive-color="#fff" active-color="#fff" style="z-index: 999; color: #fff; font-size: 12px;">
            <van-tabbar-item replace to="/home">
                <span>{{ $t("首页") }}</span>
                <template #icon="props">
                    <img class=" w-[30px] h-[auto!important]" :src="props.active ? $getImageUrl('icon_home_ac.png') : $getImageUrl('icon_home.png')" />
                </template>
            </van-tabbar-item>
            <van-tabbar-item  replace to="/video">
                <span>{{ $t("视频") }}</span>
                <template #icon="props">
                    <img class=" w-[30px] h-[auto!important]" :src="props.active ? $getImageUrl('icon_video_ac.png') : $getImageUrl('icon_video.png')" />
                </template>
            </van-tabbar-item>
            <van-tabbar-item  replace to="/financial">
                <span>{{ $t("理财") }}</span>
                <template #icon="props">
                    <img class=" w-[30px] h-[auto!important]" :src="props.active ? $getImageUrl('icon_financial_ac.png') : $getImageUrl('icon_financial.png')" />
                </template>
            </van-tabbar-item>
            <van-tabbar-item  replace to="/introduce">
                <span>{{ $t("介绍") }}</span>
                <template #icon="props">
                    <img class=" w-[30px] h-[auto!important]" :src="props.active ? $getImageUrl('icon_introduce_ac.png') : $getImageUrl('icon_introduce.png')" />
                </template>
            </van-tabbar-item>
            <van-tabbar-item  replace to="/mine">
                <span>{{ $t("我的") }}</span>
                <template #icon="props">
                    <img class=" w-[30px] h-[auto!important]" :src="props.active ? $getImageUrl('icon_mine_ac.png') : $getImageUrl('icon_mine.png')" />
                </template>
            </van-tabbar-item>
        </van-tabbar>
    </div>
</template>

<script setup>
import { ref } from 'vue';
const active = ref(0);
</script>

<style lang="less" scoped>

</style>