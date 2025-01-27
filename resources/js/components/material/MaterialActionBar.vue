<script setup lang="ts">
import axios, {type AxiosError, type AxiosResponse} from 'axios'
import {computed, type PropType} from 'vue'
import {RouterLink} from 'vue-router'

import {useAuthStore} from '@/stores/auth'
import useCategoryRegistry from '@/categoryRegistry'
import {useGlobalModalStore} from '@/stores/global-modal'
import {useToastStore} from '@/stores/toast'
import {getCounterLabel, getErrorMessageByCode} from '@/helpers'

import Button from '@/components/elements/Button.vue'
import EffectIcon from '@/components/elements/EffectIcon.vue'
import type {Material} from '@/types'

const props = defineProps({
    material: {
        type: Object as PropType<Material>,
        required: true
    }
})

const authStore = useAuthStore()
const categoryRegistry = useCategoryRegistry()
const globalModalStore = useGlobalModalStore()
const toastStore = useToastStore()

const materialRoute = computed(() => ({
    name: 'material',
    params: {
        edition: props.material!.edition?.toLowerCase(),
        category: useCategoryRegistry().get(props.material!.category)!.slug,
        slug: props.material!.slug
    }
}))

function toggleLike() {
    props.material.is_liked = !props.material.is_liked
    props.material.likes_count += props.material.is_liked ? 1 : -1
}

function toggleFavourite() {
    props.material.is_favourite = !props.material.is_favourite
    props.material.favourites_count += props.material.is_favourite ? 1 : -1
}

function onLikeClick() {
    if (!authStore.isAuthenticated) {
        globalModalStore.authModal = true
        return
    }
    if (!authStore.hasVerifiedEmail) {
        globalModalStore.notVerifiedEmailModal = true
        return
    }
    toggleLike()
    const apiUrl = `/api/materials/${props.material.id}/likes`
    const responseCallback = function (response: AxiosResponse) {
        if (!response.data.success) {
            toggleLike()
            toastStore.error(response.data.message)
        }
    }
    const errorCallback = function (error: AxiosError) {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }

    if (props.material.is_liked) {
        axios.post(apiUrl).then(responseCallback).catch(errorCallback)
    } else {
        axios.delete(apiUrl).then(responseCallback).catch(errorCallback)
    }
}

function onFavouriteClick() {
    if (!authStore.isAuthenticated) {
        globalModalStore.authModal = true
        return
    }
    if (!authStore.hasVerifiedEmail) {
        globalModalStore.notVerifiedEmailModal = true
        return
    }
    toggleFavourite()
    const apiUrl = `/api/materials/${props.material.id}/favourites`
    const responseCallback = function (response: AxiosResponse) {
        if (!response.data.success) {
            toggleFavourite()
            toastStore.error(response.data.message)
        }
    }
    const errorCallback = function (error: AxiosError) {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }

    if (props.material.is_favourite) {
        axios.post(apiUrl).then(responseCallback).catch(errorCallback)
    } else {
        axios.delete(apiUrl).then(responseCallback).catch(errorCallback)
    }
}
</script>

<template>
    <div class="flex text-[12px]">
        <button :class="{ 'active': material.is_liked }" class="set-mark flex items-center" @click="onLikeClick">
            <EffectIcon icon="icon-heart"/>
            <span class="counter flex p-1">{{ getCounterLabel(material.likes_count) }}</span>
        </button>
        <button :class="{ 'active': material.is_favourite }" class="set-mark flex items-center"
                @click="onFavouriteClick">
            <EffectIcon icon="icon-diamond"/>
            <span class="counter flex p-1">{{ getCounterLabel(material.favourites_count) }}</span>
        </button>
        <RouterLink
            :to="{...materialRoute, hash: '#comments'}"
            class="set-mark flex items-center text-[12px]"
        >
            <span class="icon icon-comment flex opacity-85"/>
            <span class="counter flex p-1">{{ getCounterLabel(material.comments_count) }}</span>
        </RouterLink>
        <RouterLink
            :to="materialRoute"
            class="set-mark flex items-center mini text-[12px]"
        >
            <span class="icon icon-eye flex opacity-85"/>
            <span class="counter flex p-1">{{ getCounterLabel(material.views_count) }}</span>
        </RouterLink>
        <RouterLink
            v-if="useCategoryRegistry().get(material.category).isDownloadable"
            :to="{...materialRoute, hash: '#download'}"
            class="set-mark flex items-center mini text-[12px]"
        >
            <span class="icon icon-download flex opacity-85"/>
            <span class="counter flex p-1">{{ getCounterLabel(material.downloads_count) }}</span>
        </RouterLink>
    </div>
</template>
