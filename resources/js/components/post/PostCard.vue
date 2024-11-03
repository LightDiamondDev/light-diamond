<script setup lang="ts">
import {type PropType} from 'vue'
import UserAvatar from '@/components/user/UserAvatar.vue'
import {type Post} from '@/types'
import {getFullPresentableDate, getRelativeDate} from '@/helpers'
import PostActionBar from '@/components/post/PostActionBar.vue'

const props = defineProps({
    post: {
        type: Object as PropType<Post>,
        required: true
    }
})

function wasPostUpdated(post: Post) {
    return post.updated_at !== post.created_at
}
</script>

<template>
<div class="post-card ld-primary-background flex flex-col">
    <RouterLink class="preview-wrap flex" :to="{name: 'post', params: {slug: post.slug}}">
        <img alt="" class="preview flex w-full full-locked duration-200" :src="post.version?.cover_url">
    </RouterLink>
    <div class="description-wrap flex flex-col flex-grow justify-between text-[12px] w-full">
        <div class="flex flex-col">
            <RouterLink class="post-title-wrap transfusion p-2" :to="{name: 'post', params: {slug: post.slug}}">
                <h2 class="post-title text-[15px]">{{ post.version.title }}</h2>
            </RouterLink>
            <p class="description ld-tinted-background flex text-[11px] p-2">{{ post.version.description }}</p>
        </div>
        <div class="flex flex-col">
            <div class="material-info info flex flex-wrap justify-between px-2">
                <div class="types flex flex-wrap gap-3 opacity-80">
                    <p class="type flex items-center">{{ post.version.category.name }}</p>
                    <p class="type flex items-center">16x16</p>
                    <p class="type flex items-center">1.19+</p>
                </div>
                <div class="progress-bar flex h-[24px] w-[64px] relative">
                    <div class="progress flex items-center transfusion" style="width: 35%">
                        <p class="text-[11px] absolute pl-1">35%</p>
                    </div>
                </div>
            </div>
            <div class="author-info info flex justify-between">
                <RouterLink class="author-wrap flex flex-wrap gap-1" :to="{ name: 'home' }">
                    <UserAvatar
                        border-class-list="h-10 w-10"
                        icon-class-list="h-7 w-7"
                        :user="post.version?.author"
                    />
                    <p class="author-username flex items-center">{{ post.version.author.username }}</p>
                </RouterLink>
                <p
                    v-tooltip="`${wasPostUpdated(post) ? 'Обновлено' : 'Опубликовано'} ${getFullPresentableDate(post.updated_at)}`"
                    class="type ago flex items-center text-end opacity-80 cursor-pointer"
                >
                    {{ getRelativeDate(post.updated_at) }}
                </p>
            </div>
            <div class="menu-separator flex self-center w-[95%]"/>
            <div
                class="actions ld-primary-background-container flex gap-2 p-2 overflow-x-auto overflow-y-hidden"
                style="scrollbar-width: thin"
            >
                <PostActionBar class="xs:gap-4 gap-2" :post="post"/>
            </div>
        </div>
    </div>
</div>
</template>

<style scoped>
.tooltip::before {
    height: 3.5rem;
    width: 5rem;
    left: 4.5rem;
}
.tooltip::after {
    display: none;
}
.post-card { flex: 1 0; }
.preview-wrap {
    overflow: hidden;
    width: 100%;
}
.preview {
    aspect-ratio: 16/9;
    object-fit: cover;
}
.preview-wrap:focus-visible .preview,
.post-card:hover .preview {
    transform: scale(1.1);
}
.author-info {
    padding: .5rem;
}
.author-username,
.post-title {
    font-weight: normal;
    line-height: 1.2;
    transition: .2s;
}
.author-wrap:focus-visible .author-username,
.author-wrap:hover .author-username,
.post-title-wrap:hover .post-title {
    color: var(--hover-text-color);
}
.author-avatar-frame {
    overflow: hidden;
}
.author-avatar-frame,
.author-avatar {
    transition: .2s;
}
.author-wrap:focus-visible .author-avatar,
.author-wrap:hover .author-avatar {
    transform: scale(1.1);
}
.author-wrap:focus-visible .author-avatar-frame,
.author-wrap:hover .author-avatar {
    transform: scale(1.1);
}

</style>
