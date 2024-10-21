<script setup lang="ts">
import axios, {type AxiosError, type AxiosResponse} from 'axios'

import {computed, type PropType, reactive} from 'vue'

import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'
import {RouterLink, useRouter} from 'vue-router'

import { getErrorMessageByCode } from '@/helpers'

import type {Post} from '@/types'

import Button from '@/components/elements/Button.vue'

const props = defineProps({
    post: {
        type: Object as PropType<Post>,
        required: true
    }
})

const authStore = useAuthStore()
const toastStore = useToastStore()
const post = reactive(props.post!)
const router = useRouter()

const icon = computed(() => {
    return `${post.is_liked ? 'fa-solid' : 'fa-regular'} fa-heart`
})

function toggleLike() {
    post.is_liked = !post.is_liked
    post.like_count += post.is_liked ? 1 : -1
}

function onLikeClick() {
    if (!authStore.isAuthenticated) {
        // modalStore.auth = true
        return
    }

    toggleLike()

    const apiUrl = `/api/posts/${post.id}/likes`
    const responseCallback = function (response: AxiosResponse) {
        if (!response.data.success) {
            toggleLike()
            toastStore.error(response.data.message)
        }
    }
    const errorCallback = function (error: AxiosError) {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }

    if (post.is_liked) {
        axios.post(apiUrl).then(responseCallback).catch(errorCallback)
    } else {
        axios.delete(apiUrl).then(responseCallback).catch(errorCallback)
    }
}
</script>

<template>
    <div class="flex gap-3">
        <div class="flex items-center">
            <Button
                :title="post.is_liked ? 'Больше не нравится' : 'Нравится'"
                :severity="post.is_liked ? 'danger' : 'secondary'"
                :icon="`fa-heart ${post.is_liked ? 'fa-solid' : 'fa-regular'}`"
                @click="onLikeClick"
                class="-ml-2.5"
            />
            <p class="text-sm" :class="{'p-error': post.is_liked}">{{ post.like_count }}</p>
        </div>

        <div class="flex items-center">
            <RouterLink :to="{name: 'post', params: {slug: post.slug}, hash: '#comments'}">
                <Button
                    rounded
                    title="Комментарии"
                    severity="secondary"
                    icon="fa-regular fa-comment"
                />
            </RouterLink>

            <p class="text-sm">{{ post.comment_count }}</p>
        </div>

        <div class="flex items-center ml-auto gap-2">
            <span title="Просмотры" class="fa-regular fa-eye text-[var(--text-color-secondary)]"/>
            <p class="text-sm">{{ post.view_count }}</p>
        </div>
    </div>
</template>

<style scoped>

</style>
