<script setup lang="ts">

import PostCard from '@/components/catalog/PostCard.vue'
</script>

<template>
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
