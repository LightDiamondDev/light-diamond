<script setup lang="ts">
import {computed, type PropType, ref} from 'vue'
import {RouterLink} from 'vue-router'
import {getFullPresentableDate, getRelativeDate} from '@/helpers'

import {PostVersionActionType as ActionType, type PostVersion, PostVersionStatus, UserRole} from '@/types'

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
    <div class="post-version-card ld-primary-border-top flex sm:flex-row flex-col">
        <div class="flex w-full gap-2 p-2">
            <RouterLink
                class="preview-wrap ld-primary-border flex
                    sm:h-[112px] sm:max-w-[196px] sm:min-w-[196px]
                    xs:h-[76px] xs:max-w-[132px] xs:min-w-[132px]
                    h-[58px] max-w-[100px] min-w-[100px]
                    overflow-hidden"
                :to="{ name: 'post-version', params: {id: postVersion.id} }"
            >
                <img alt="" class="preview flex w-full full-locked duration-500" :src="postVersion.cover_url">
            </RouterLink>
            <div class="description-wrap flex flex-col w-full">
                <RouterLink class="post-title-wrap border-0" :to="{ name: 'post-version', params: {id: postVersion.id} }">
                    <h1 class="post-title md:text-[14px] text-[12px]">{{ postVersion.title }}</h1>
                </RouterLink>
                <p class="description xs:flex hidden md:text-[10px] text-[8px] opacity-80 mb-0.5">{{ postVersion.description }}</p>
                <div class="flex flex-wrap items-center md:text-[12px] text-[10px] gap-2 mt-0.5">
                    <RouterLink v-if="postVersion.author" class="author-wrap flex flex-wrap border-0 gap-1" :to="{ name: 'home' }">
                        <UserAvatar
                            border-class-list="h-7 w-7"
                            icon-class-list="h-5 w-5"
                            :user="postVersion.author"
                        />
                        <p class="author-username flex items-center">{{ postVersion.author!.username }}</p>
                    </RouterLink>
                    <div v-else class="author-wrap flex flex-wrap border-0 gap-1">
                        <span class="icon-border-profile icon flex"/>
                        <p class="author-username flex items-center">Некто</p>
                    </div>
                    <p
                        class="time-ago time flex items-center md:text-[10px] text-[8px] h-fit gap-2 locked tooltip whitespace-nowrap"
                        v-tooltip.top="(wasUpdated ? 'Обновлено ' : 'Создано ') + getFullPresentableDate(postVersion!.updated_at!)"
                    >
                        <span class="opacity-80">{{ getRelativeDate(postVersion!.updated_at!) }}</span>
                    </p>
                </div>
                <PostVersionAction
                    v-if="[ActionType.ACCEPT, ActionType.REJECT, ActionType.REQUEST_CHANGES].includes(lastAction?.type!)"
                    class="sm-show w-full mt-0.5"
                    :action="lastAction"
                    :minimized="true"
                />
            </div>
        </div>
        <div class="sm-wrap flex xs:flex-row flex-col">
            <div class="flex justify-between items-center sm:w-fit w-full">
                <PostVersionAction
                    v-if="[ActionType.ACCEPT, ActionType.REJECT, ActionType.REQUEST_CHANGES].includes(lastAction?.type!)"
                    class="sm-hidden m-2"
                    :action="lastAction"
                    :minimized="true"
                />
                <div v-if="postVersion.assigned_moderator" class="flex relative m-2">
                    <span
                        :class="{
                            'icon-charoit-crown': postVersion.assigned_moderator?.role === UserRole.ADMIN,
                            'icon-emerald-dagger': postVersion.assigned_moderator?.role === UserRole.MODERATOR
                        }"
                        class="icon-role icon flex absolute
                            sm:h-8 h-6 sm:w-8 w-6 z-[1] sm:right-[-10px]
                            sm:top-[-10px] right-[-8px] top-[-8px]"
                    />
                    <UserAvatar
                        v-if="postVersion.status !== PostVersionStatus.DRAFT"
                        v-tooltip.top="`Закреплено за ${postVersion.assigned_moderator.username}`"
                        :user="postVersion.assigned_moderator"
                        class="moder"
                        border-class-list="sm:h-10 sm:w-10 h-7 w-7"
                        icon-class-list="sm:h-7 sm:w-7 h-5 w-5"
                    />
                </div>
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
.sm-show {
    display: none;
}
.sm-wrap {
    background: var(--tinted-bg-color);
}

/* =============== [ Медиа-Запрос { 640px < ?px } ] =============== */

@media screen and (min-width: 640px) {
    .sm-wrap {
        background: none;
    }
    .sm-hidden {
        display: none;
    }
    .sm-show {
        display: flex;
    }
}
</style>
