<script setup lang="ts">
import {computed, type PropType, ref} from 'vue'
import {RouterLink} from 'vue-router'
import {getFullPresentableDate, getRelativeDate} from '@/helpers'

import {type PostVersion, PostVersionStatus} from '@/types'

import PostVersionAction from '@/components/post/PostVersionAction.vue'
import UserAvatar from '@/components/user/UserAvatar.vue'

const props = defineProps({
    postVersion: {
        type: Object as PropType<PostVersion>,
        required: true
    },
    showAuthor: {
        type: Boolean,
        default: true
    }
})

const wasUpdated = computed(() => props.postVersion!.updated_at !== props.postVersion!.created_at)
const isFirstVersion = computed(() => !props.postVersion!.post || props.postVersion!.updated_at === props.postVersion!.post.created_at)
const lastAction = computed(() => props.postVersion!.actions!.at(props.postVersion!.actions!.length - 1))
</script>

<template>
    <div class="post-version-card ld-primary-border-bottom flex gap-2 p-2">
        <RouterLink
            class="preview-wrap ld-primary-border flex overflow-hidden"
            :to="{ name: 'post-version', params: {id: postVersion.id} }"
        >
            <img alt="" class="preview flex w-[170px] full-locked duration-500" :src="postVersion.cover_url">
        </RouterLink>
        <div class="description-wrap flex flex-col w-full">
            <RouterLink class="post-title-wrap" :to="{ name: 'post-version', params: {id: postVersion.id} }">
                <h1 class="post-title md:text-[14px] text-[12px]">{{ postVersion.title }}</h1>
            </RouterLink>
            <p class="description flex md:text-[10px] text-[8px] opacity-80">{{ postVersion.description }}</p>
            <div class="flex flex-wrap items-center md:text-[12px] text-[10px] gap-2">
                <p
                    :class="{
                        'cancel': postVersion.status === PostVersionStatus.REJECTED,
                        'confirm': postVersion.status === PostVersionStatus.ACCEPTED,
                        'warning': postVersion.status === PostVersionStatus.PENDING
                     }"
                    class="transfusion bordered flex items-center h-[28px] gap-1"
                >
                    <span class="px-2">{{ postVersion.status === PostVersionStatus.ACCEPTED ? 'Принято' :
                            postVersion.status === PostVersionStatus.REJECTED ? 'Отклонено' : 'На рассмотрении' }}
                    </span>
                </p>
                <RouterLink class="author-wrap flex flex-wrap gap-1" :to="{ name: 'home' }">
                    <UserAvatar
                        border-class-list="h-7 w-7"
                        icon-class-list="h-5 w-5"
                        :user="postVersion.author"
                    />
                    <p class="author-username flex items-center">{{ postVersion.author!.username }}</p>
                </RouterLink>
                <p
                    class="time-ago time flex items-center md:text-[10px] text-[8px] h-fit gap-2 locked tooltip whitespace-nowrap"
                    v-tooltip.top="(wasUpdated ? 'Обновлено ' : 'Создано ') + getFullPresentableDate(postVersion!.updated_at!)"
                >
                    <span class="opacity-80">{{ getRelativeDate(postVersion!.updated_at!) }}</span>
                </p>
            </div>
        </div>
        <div class="flex items-center">
            <UserAvatar
                v-if="postVersion.assigned_moderator && postVersion.status !== PostVersionStatus.DRAFT"
                v-tooltip.top="`Закреплено за ${postVersion.assigned_moderator.username}`"
                :user="postVersion.assigned_moderator"
                class="moder"
                border-class-list="h-10 w-10"
                icon-class-list="h-7 w-7"
            />
            <div v-else class="icon-border icon flex justify-center items-center h-10 w-10"
                 v-tooltip.top="'Не закреплено'">
                <span class="icon-faq icon flex h-6 w-6"/>
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
.preview-wrap {
    object-position: center;
    aspect-ratio: 16/9;
    object-fit: cover;
}
.post-version-card:hover .preview {
    transform: scale(1.2);
}
.post-version-card:hover {
    background-color: rgba(255, 255, 255, .08);
}

/* =============== [ Медиа-Запрос { ?px <581px } ] =============== */

@media screen and (max-width: 580px) {
    .post-version-card {
        flex-direction: column;
    }
    .preview {
        width: 100%;
    }
}
</style>
