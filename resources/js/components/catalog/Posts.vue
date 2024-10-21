<script setup lang="ts">

import PostCard from '@/components/post/PostCard.vue'
import Paginator from '@/components/elements/Paginator.vue'
</script>

<template>
    <Paginator
        class="ld-primary-background ld-fixed-background
            ld-primary-border-bottom ld-primary-border-left
            ld-primary-border-right h-[48px] w-full"
        :records-at-page="15"
        :totalRecords="50"
    />
    <div class="posts flex flex-wrap w-full gap-2">
        <PostCard/>
        <PostCard/>
        <PostCard/>
        <PostCard/>
        <PostCard/>
        <PostCard/>
        <PostCard/>
        <PostCard/>
        <PostCard/>
    </div>
    <Paginator
        class="ld-primary-background ld-fixed-background
            ld-primary-border-left ld-primary-border-right
            ld-primary-border-top h-[48px] w-full"
        :records-at-page="15"
        :totalRecords="50"
    />
<!--        v-model="loadRequestData.page"-->
<!--        @page-change="onPageChange"-->
</template>

<style scoped>
.posts {
    padding: .5rem 0;
}
/* =============== [ Медиа-Запрос { ?px < 1280px } ] =============== */

@media screen and (max-width: 1279px) {
    .posts {
        padding: .5em;
    }
}
</style>
