<script setup lang="ts">
import {ref} from 'vue'
import EffectIcon from '@/components/elements/EffectIcon.vue'
import UserAvatar from '@/components/user/UserAvatar.vue'

const isLiked = ref(false)
const isSubscribed = ref(false)

let likes = 585
let subscribes = 365

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
<div class="post-card ld-primary-background flex flex-col">
    <RouterLink class="preview-wrap flex" :to="{ name: 'home' }">
        <img alt="" class="preview flex w-full full-locked" src="/images/users/boba.png">
    </RouterLink>
    <div class="description-wrap flex flex-col text-[12px] w-full">
        <RouterLink class="post-title-wrap transfusion p-2" :to="{ name: 'home' }">
            <h2 class="post-title text-[15px]">Light Diamond Addon: Craft & Survive [0.1.0]</h2>
        </RouterLink>
        <p class="description flex text-[11px] p-2">
            Глобальный Аддон Light Diamond призван улучшить многие аспекты игрового процесса и обновить Ваши
            представления о Ванильной Игре!
        </p>
        <div class="material-info info flex flex-wrap justify-between px-2">
            <div class="types flex flex-wrap gap-3">
                <p class="type flex items-center">Addon</p>
                <p class="type flex items-center">16x16</p>
                <p class="type flex items-center">1.19+</p>
            </div>
            <div class="progress-bar flex relative">
                <div class="progress flex items-center transfusion" style="width: 35%;">
                    <p class="text-[11px] absolute pl-1">35%</p>
                </div>
            </div>
        </div>
        <div class="author-info info flex justify-between">
            <RouterLink class="author-wrap flex flex-wrap gap-1" :to="{ name: 'home' }">
                <UserAvatar
                    border-class-list="h-10 w-10"
                    icon-class-list="h-7 w-7"
                    :user="option"
                />
                <p class="author-username flex items-center">GrostLight15</p>
            </RouterLink>
            <p class="type ago flex items-center text-end">Неделю назад</p>
        </div>


        <div class="menu-separator flex self-center w-[95%]"/>
        <div class="actions ld-primary-background-container flex flex-wrap gap-2 text-[0.7rem]">
            <div class="line basic flex flex-wrap gap-2">
                <button :class="{ 'active': isLiked }" class="set-mark flex items-center" @click="like">
                    <EffectIcon icon="icon-heart"/>
                    <span class="counter flex p-1">{{ likes }}</span>
                </button>
                <button :class="{ 'active': isSubscribed }" class="set-mark flex items-center" @click="subscribe">
                    <EffectIcon icon="icon-diamond"/>
                    <span class="counter flex p-1">{{ subscribes }}</span>
                </button>
                <RouterLink class="set-mark flex items-center" :to="{ name: 'home' }">
                    <span class="icon icon-comment flex"/>
                    <span class="counter flex p-1">78</span>
                </RouterLink>
            </div>

            <div class="menu-separator mini-separator self-center w-[95%]"/>

            <div class="line flex flex-wrap gap-2">
                <RouterLink class="set-mark flex items-center mini" :to="{ name: 'home' }">
                    <span class="icon icon-eye flex"/>
                    <span class="counter flex p-1">1,5K</span>
                </RouterLink>
                <RouterLink class="set-mark flex items-center mini" :to="{ name: 'home' }">
                    <span class="icon icon-download flex"/>
                    <span class="counter flex p-1">1,1K</span>
                </RouterLink>
            </div>
        </div>
    </div>
</div>
</template>

<style scoped>
.post-card {
    min-width: 421px;
    flex: 1 0;
}
.preview-wrap {
    overflow: hidden;
    width: 100%;
}
.preview {
    transition: .2s;
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
.type {
    opacity: .8;
}
.progress-bar {
    height: 24px;
    width: 64px;
}
.description-wrap .actions {
    padding: .5rem;
}
.mini-separator {
    display: none;
}

/* =============== [ Медиа-Запрос { < 1281px } ] =============== */

@media screen and (max-width: 1280px) {
    .post-card {
        min-width: 320px;
    }
    .post-card .line {
        width: 100%;
    }
}

/* =============== [ Медиа-Запрос { < 992px } ] =============== */

@media screen and (max-width: 991px) {
    .post-card .line {
        width: fit-content;
    }
}

/* =============== [ Медиа-Запрос { < 839px } ] =============== */

@media screen and (max-width: 838px) {
    .post-card .line {
        width: fit-content;
        gap: 2rem;
    }
}

/* =============== [ Медиа-Запрос { < 664px } ] =============== */

@media screen and (max-width: 663px) {
    .post-card .line {
        gap: .5rem;
    }
}

/* =============== [ Медиа-Запрос { < 425px } ] =============== */

@media screen and (max-width: 424px) {
    .author-info {
        padding: .5rem .5rem 0 .5rem;
        flex-direction: column;
        gap: .5rem;
    }
    .post-card {
        min-width: 100%;
    }
    .post-card .line {
        gap: 2rem;
    }
    .description-wrap .actions {
        flex-direction: column-reverse;
        align-items: center;
        padding: 0;
        gap: 0;
    }
    .description-wrap .line.basic {
        padding: 4px;
    }
    .description-wrap .mini {
        transform: scale(.8);
    }
    .mini-separator {
        display: flex;
    }
    .type.ago {
        margin-bottom: 1rem;
    }
}

/* =============== [ Медиа-Запрос { < 301px } ] =============== */

@media screen and (max-width: 300px) {
    .author-wrap,
    .type.ago {
        justify-content: center;
    }
    .material-info,
    .post-card .line {
        gap: .5rem;
    }
}
</style>
