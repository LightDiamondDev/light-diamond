<script setup lang="ts">
import axios, {type AxiosError, type AxiosResponse} from 'axios'
import {getErrorMessageByCode} from '@/helpers'
import {RouterLink} from 'vue-router'

import Button from '@/components/elements/Button.vue'
import EffectIcon from '@/components/elements/EffectIcon.vue'
import {useAuthStore} from '@/stores/auth'
import {useGlobalModalStore} from '@/stores/global-modal'
import {useToastStore} from '@/stores/toast'
import type {PropType} from 'vue'
import type {Post} from '@/types'

const props = defineProps({
    post: {
        type: Object as PropType<Post>,
        required: true,
    }
})

const authStore = useAuthStore()
const globalModalStore = useGlobalModalStore()
const toastStore = useToastStore()

function toggleLike() {
    props.post.is_liked = !props.post.is_liked
    props.post.like_count += props.post.is_liked ? 1 : -1
}

function toggleFavourite() {
    props.post.is_favourite = !props.post.is_favourite
    props.post.favourite_count += props.post.is_favourite ? 1 : -1
}

function onLikeClick() {
    if (!authStore.isAuthenticated) {
        globalModalStore.isAuth = true
        return
    }
    toggleLike()
    const apiUrl = `/api/posts/${props.post.id}/likes`
    const responseCallback = function (response: AxiosResponse) {
        if (!response.data.success) {
            toggleLike()
            toastStore.error(response.data.message)
        }
    }
    const errorCallback = function (error: AxiosError) {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }

    if (props.post.is_liked) {
        axios.post(apiUrl).then(responseCallback).catch(errorCallback)
    } else {
        axios.delete(apiUrl).then(responseCallback).catch(errorCallback)
    }
}

function onFavouriteClick() {
    if (!authStore.isAuthenticated) {
        globalModalStore.isAuth = true
        return
    }
    toggleFavourite()
    const apiUrl = `/api/posts/${props.post.id}/favourites`
    const responseCallback = function (response: AxiosResponse) {
        if (!response.data.success) {
            toggleFavourite()
            toastStore.error(response.data.message)
        }
    }
    const errorCallback = function (error: AxiosError) {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }

    if (props.post.is_favourite) {
        axios.post(apiUrl).then(responseCallback).catch(errorCallback)
    } else {
        axios.delete(apiUrl).then(responseCallback).catch(errorCallback)
    }
}
</script>

<template>
    <div class="flex sm:text-[12px] text-[10px]">
        <button :class="{ 'active': post.is_liked }" class="set-mark flex items-center" @click="onLikeClick">
            <EffectIcon icon="icon-heart"/>
            <span class="counter flex p-1">{{ post.like_count }}</span>
        </button>
        <button :class="{ 'active': post.is_favourite }" class="set-mark flex items-center" @click="onFavouriteClick">
            <EffectIcon icon="icon-diamond"/>
            <span class="counter flex p-1">{{ post.favourite_count }}</span>
        </button>
        <RouterLink
            :to="{name: 'post', params: {slug: post.slug}, hash: '#comments'}"
            class="set-mark flex items-center"
        >
            <span class="icon icon-comment flex opacity-85 sm:h-[32px] h-[24px] sm:w-[32px] w-[24px]"/>
            <span class="counter flex p-1">{{ post.comment_count }}</span>
        </RouterLink>
        <RouterLink
            :to="{name: 'post', params: {slug: post.slug}}"
            class="set-mark flex items-center mini"
        >
            <span class="icon icon-eye flex opacity-85 sm:h-[32px] h-[24px] sm:w-[32px] w-[24px]"/>
            <span class="counter flex p-1">{{ post.view_count }}</span>
        </RouterLink>
        <RouterLink
            :to="{name: 'post', params: {slug: post.slug}}"
            class="set-mark flex items-center mini"
        >
            <span class="icon icon-download flex opacity-85 sm:h-[32px] h-[24px] sm:min-w-[32px] min-w-[24px]"/>
            <span class="counter flex p-1">1,1K</span>
        </RouterLink>
    </div>
</template>

<style scoped>

</style>
