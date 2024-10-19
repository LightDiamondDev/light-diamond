<script setup lang="ts">
import {computed, type PropType} from 'vue'
import {RouterLink} from 'vue-router'
import {getFullPresentableDate, getRelativeDate} from '@/helpers'

import {type Post} from '@/types'

import UserAvatar from '@/components/user/UserAvatar.vue'
import PostActionBar from '@/components/post/PostActionBar.vue'

const props = defineProps({
    post: {
        type: Object as PropType<Post>,
        required: true
    },
    showAuthor: {
        type: Boolean,
        default: true
    }
})

const wasUpdated = computed(() => props.post!.updated_at !== props.post!.created_at)
const isFirstVersion = computed(() => props.post!.updated_at === props.post!.created_at)
</script>

<template>
    <div class="post-version-card ld-primary-border-top flex sm:flex-row flex-col">
        <div class="flex w-full gap-2 p-2">
            <RouterLink
                class="preview-wrap ld-primary-border flex
                    sm:h-[112px] sm:max-w-[196px] sm:min-w-[196px]
                    xs:h-[76px] xs:max-w-[132px] xs:min-w-[132px]
                    h-[58px] max-w-[100px] min-w-[100px]
                    overflow-hidden"
                :to="{ name: 'post', params: {slug: post.slug} }"
            >
                <img alt="" class="preview flex w-full full-locked duration-500" :src="post.version?.cover_url">
            </RouterLink>
            <div class="description-wrap flex flex-col w-full">
                <RouterLink class="post-title-wrap border-0" :to="{ name: 'post', params: {slug: post.slug} }">
                    <h1 class="post-title md:text-[14px] text-[12px]">{{ post.version?.title }}</h1>
                </RouterLink>
                <p class="description xs:flex hidden md:text-[10px] text-[8px] opacity-80 mb-0.5">{{ post.version?.description }}</p>
                <div class="flex flex-wrap items-center md:text-[12px] text-[10px] gap-2 mt-0.5">
                    <RouterLink v-if="post.version?.author" class="author-wrap flex flex-wrap border-0 gap-1" :to="{ name: 'home' }">
                        <UserAvatar
                            border-class-list="h-7 w-7"
                            icon-class-list="h-5 w-5"
                            :user="post.version?.author"
                        />
                        <p class="author-username flex items-center">{{ post.version?.author.username }}</p>
                    </RouterLink>
                    <div v-else class="author-wrap flex flex-wrap border-0 gap-1">
                        <span class="icon-border-profile icon flex"/>
                        <p class="author-username flex items-center">Некто</p>
                    </div>
                    <p
                        class="time-ago time flex items-center md:text-[10px] text-[8px] h-fit gap-2 locked tooltip whitespace-nowrap"
                        v-tooltip.top="(wasUpdated ? 'Обновлено ' : 'Создано ') + getFullPresentableDate(post!.updated_at!)"
                    >
                        <span class="opacity-80">{{ getRelativeDate(post!.updated_at!) }}</span>
                    </p>
                </div>
                <PostActionBar class="ld-primary-background-container sm:flex hidden gap-4 mt-2" :post="post"/>
            </div>
        </div>
        <div class="sm-wrap ld-tinted-background sm:hidden flex xs:flex-row flex-col p-1">
            <div class="flex justify-between items-center sm:w-fit w-full">
                <PostActionBar class="ld-secondary-background-container gap-4" :post="post"/>
            </div>
        </div>
    </div>
</template>

<style>
.tooltip::before {
    top: -2rem;
}
.moder.tooltip::before {
    height: 2.5rem;
}
</style>

<style scoped>
.preview-wrap,
.preview {
    object-position: center;
    aspect-ratio: 16/9;
    object-fit: cover;
}
.post-version-card:hover .preview {
    transform: scale(1.2);
}
</style>
