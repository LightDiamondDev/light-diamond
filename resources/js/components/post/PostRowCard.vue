<script setup lang="ts">
import {type PropType, ref} from 'vue'
import type {Toast} from '@/stores/toast'
import EffectIcon from '@/components/elements/EffectIcon.vue'

const props = defineProps ({
    cover: {
        type: String,
        required: true
    },
    title: {
        type: String,
        required: true
    },
    likes: {
        type: Number,
        default: 0
    },
    subscribes: {
        type: Number,
        default: 0
    },
    comments: {
        type: Number,
        default: 0
    },
    downloads: {
        type: Number,
        default: 0
    },
    views: {
        type: Number,
        default: 0
    },
    time: String
})

let likes = props.likes
let subscribes = props.subscribes
let comments = props.comments
let downloads = props.downloads
let views = props.views

const isLiked = ref(false)
const isSubscribed = ref(false)

function like() {
    isLiked.value ? likes-- : likes++
    isLiked.value = !isLiked.value
}

function subscribe() {
    isSubscribed.value ? subscribes-- : subscribes++
    isSubscribed.value = !isSubscribed.value
}
</script>

<template>
    <div class="post-line flex items-center">
        <RouterLink class="cover-wrap flex" :to="{ name: 'home' }">
            <img alt="" class="cover flex w-full full-locked" :src="cover">
        </RouterLink>
        <div class="post-info flex flex-col w-full gap-2">
            <RouterLink class="name flex h-[40px] pt-2" :to="{ name: 'home' }">
                <p class="text-[1.1rem] duration-200">{{ title }}</p>
            </RouterLink>
            <div class="line flex justify-between gap-2 pb-2">
                <div class="sub-line flex flex-wrap gap-2">
                    <button :class="{ 'active': isLiked }" class="flex items-center" @click="like" type="button">
                        <EffectIcon class="set-mark" icon="icon-heart"/>
                        <span class="counter flex p-1">{{ likes }}</span>
                    </button>
                    <button :class="{ 'active': isSubscribed }" class="flex items-center" @click="subscribe" type="button">
                        <EffectIcon class="set-mark" icon="icon-diamond"/>
                        <span class="counter flex p-1">{{ subscribes }}</span>
                    </button>
                    <RouterLink class="set-mark flex items-center" :to="{ name: 'home' }">
                        <span class="icon icon-comment flex"/>
                        <span class="counter flex p-1">{{ comments }}</span>
                    </RouterLink>
                    <RouterLink class="set-mark flex items-center" :to="{ name: 'home' }">
                        <span class="icon icon-download flex"/>
                        <span class="counter flex p-1">{{ downloads }}</span>
                    </RouterLink>
                </div>
                <div class="sub-line flex flex-wrap gap-4">
                    <p class="time flex items-center text-end">{{ time }}</p>
                    <RouterLink class="set-mark flex items-center" :to="{ name: 'home' }">
                        <span class="icon icon-eye flex"/>
                        <span class="counter flex p-1">{{ views }}</span>
                    </RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.cover-wrap {
    overflow: hidden;
    height: 88px;
}
.post-line .cover {
    transition: .2s;
}
.post-line:hover .cover {
    transform: scale(1.1);
}
.post-info .name:hover p {
    color: var(--hover-text-color);
}
.post-info button .counter,
.post-info button .icon {
    color: var(--primary-color);
    filter: grayscale(100%);
    transition: .2s;
}
.post-info button:hover .counter,
.post-info button:hover .icon {
    filter: grayscale(60%);
}
.post-info button.active .counter,
.post-info button.active .icon {
    filter: grayscale(0);
}
</style>
