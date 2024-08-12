<script setup lang="ts">
import {ref} from 'vue'
import {getRandomColor, getRandomSplash} from '@/stores/splashes'
import {useAuthStore} from '@/stores/auth'
import Posts from '@/components/catalog/Posts.vue'

const authStore = useAuthStore()

const isBedrock = ref(true)
const isFresh = ref(true)
const isFilters = ref(false)

const timePeriod = [
    'За всё время',
    'За сутки',
    'За неделю',
    'За месяц',
    'За 6 месяцев',
    'За год'
]
let timePeriodCounter = 0;
let currentTimePeriod = ref('За всё время');

function switchTimePeriod() {
    timePeriodCounter++
    if (timePeriodCounter == timePeriod.length) timePeriodCounter = 0;
    currentTimePeriod.value = timePeriod[timePeriodCounter]
}

const activeSplash = getRandomSplash()
const activeColor = getRandomColor()
</script>

<template>
<div class="catalog-banner flex justify-center w-full">
    <div class="banner flex justify-center items-end">
        <div class="title flex flex-col justify-center items-center">
            <RouterLink
                class="logo-wrap flex items-center full-locked"
                :to="{ name: 'catalog' }"
            >
                <img alt="Logo" class="h-[48px] xs:h-full md:h-[100px]" src="/images/elements/light-diamond-materials-logo.png"/>
                <span v-if="authStore.isAuthenticated" class="base">
                    <span
                        class="splash flex justify-center"
                        :class="{
                            'bottom-splash': activeSplash.length > 20,
                            'large-splash': activeSplash.length > 30,
                            'small-splash': activeSplash.length < 31
                        }"
                        :style="{ 'color': activeColor }"
                    >
                        {{ activeSplash }}
                    </span>
                </span>
            </RouterLink>
            <h1 class="text-center absolute opacity-0">Каталог Light Diamond</h1>
        </div>
    </div>
</div>
<div class="catalog-container flex justify-center w-full">
    <section class="catalog flex flex-col items-center w-full">
        <form class="catalog-panel flex flex-col w-full self-center" name="catalog">

            <nav class="flex flex-col">

                <div class="line flex flex-wrap justify-between gap-4 p-4">
                    <div class="sub-line flex flex-wrap gap-4">
                        <button
                            :class="{ 'active': isFresh }"
                            class="flex items-center option"
                            type="button"
                            @click="isFresh = true"
                        >
                            <span class="press flex">
                                <span class="preset flex items-center gap-1">
                                    <span class="icon icon-clock"/>
                                    <span>Свежие</span>
                                </span>
                            </span>
                        </button>
                        <button
                            :class="{ 'active': !isFresh }"
                            class="flex items-center option"
                            type="button"
                            @click="isFresh = false"
                        >
                            <span class="press flex">
                                <span class="preset flex items-center gap-1">
                                    <span class="icon icon-crown"/>
                                    <span>Популярные</span>
                                </span>
                            </span>
                        </button>
                        <button
                            v-if="!isFresh"
                            class="active flex items-center option w-[200px]"
                            type="button"
                            @click="switchTimePeriod()"
                        >
                            <span class="press flex">
                                <span class="preset flex items-center gap-1">
                                    <span class="icon icon-crown"/>
                                    <span>{{ currentTimePeriod }}</span>
                                </span>
                            </span>
                        </button>
                    </div>
                    <div class="sub-line flex flex-wrap justify-center gap-4">
                        <button class="flex items-center option edition" type="button" @click="isBedrock = !isBedrock">
                            <span class="press flex">
                                <span class="preset flex items-center gap-1 min-w-[172px]">
                                    <span
                                        :class="{ 'icon-bedrock-dev-small': isBedrock, 'icon-minecraft-materials': !isBedrock }"
                                        class="icon"
                                    />
                                    <span>{{ isBedrock ? 'Bedrock' : 'Java' }}</span>
                                </span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="menu-separator flex self-center"></div>

                <div class="line flex flex-wrap gap-4 p-4">

                    <RouterLink class="flex items-center" :to="{ name: 'home' }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-news"/>
                                <span>Новости</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="flex items-center" :to="{name: 'home'}">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-axolotl-bucket"/>
                                <span>Ресурс-Паки</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="flex items-center" :to="{name: 'home'}">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-spawn-egg"/>
                                <span>Аддоны</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="flex items-center" :to="{name: 'home'}">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-skin"/>
                                <span>Скины</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="flex items-center" :to="{name: 'home'}">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-map"/>
                                <span>Карты</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="flex items-center" :to="{name: 'home'}">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-script"/>
                                <span>Статьи</span>
                            </span>
                        </span>
                    </RouterLink>
                </div>

                <div class="menu-separator"></div>

                <button
                    class="filters-button line flex items-center gap-4 p-4"
                    @click="isFilters = !isFilters"
                    type="button">
                    <span :class="{ 'down-arrow-up': isFilters }" class="icon icon-down-arrow"></span>
                    <span>Фильтры</span>
                </button>

                <div v-if="isFilters" class="filters gap-4 p-4">
                    <p>Фильтры всякие там да</p>
                    <p>Ну вот Ресус Пики да</p>
                    <p>ДА Резус Факи да</p>
                    <p>Всякие там да филтры</p>
                    <p>А когда ВЫайП?</p>
                    <p>АбоБс, Это тебе не Сервер</p>
                    <p>А да Фильтры всякие там да</p>
                </div>

            </nav>
        </form>

        <Posts/>

    </section>
</div>
</template>

<style scoped>
.catalog-container {
    position: relative;
    margin-top: 208px;
}
.catalog-banner {
    overflow: hidden;
    position: fixed;
    height: 280px;
    top: 0;
}
.banner {
    background-image: url('/images/elements/catalog-banner1.png');
    background-repeat: no-repeat;
    height: 280px;
    width: 1920px;
}
.banner .title {
    position: relative;
    line-height: 1.1;
    font-size: 3rem;
    height: 208px
}
.banner .title h1 {
    text-shadow: 4px 4px 48px black;
}
.catalog-banner .base {
    transform: rotate(-15deg);
    position: absolute;
    height: 10px;
    width: 10px;
    right: 35%;
    bottom: 0;
}

.catalog-banner .splash {
    animation: splash-animation 1s infinite;
    text-shadow: 2px 2px black;
    position: absolute;
    text-align: center;
    width: 250px;
    bottom: 10px;
}
.catalog-banner .splash.bottom-splash {
    bottom: 0;
}
.large-splash {
    font-size: .8rem;
}
.small-splash {
    font-size: .9rem;
}
section.catalog {
    max-width: 1280px;
}
.catalog-panel .line a,
.catalog-panel .line button {
    max-width: 400px;
    flex-grow: 1;
    border: none;
}
.catalog-panel .line .option {
    flex-grow: 0;
}
.catalog-panel .line a .press,
.catalog-panel .line button .press {
    width: 100%;
}
.catalog-panel .line a .preset,
.catalog-panel .line button .preset {
    justify-content: center;
    padding: 0 1.5rem;
    height: 40px;
    width: 100%;
}
.catalog-panel .line a:hover .press .icon,
.catalog-panel .line button:hover .press .icon {
    animation: icon-trigger-up-animation .3s ease;
}
.menu-separator {
    width: 98%;
}
.catalog-panel .filters-button {
    font-size: 1.1rem;
}
.catalog-panel .filters {
    height: 240px;
}
.icon-down-arrow {
    transition: .5s;
}
.down-arrow-up {
    transform: rotate(-180deg);
}

/* =============== [ Анимации ] =============== */

.smooth-settings-switch-enter-active,
.smooth-auth-switch-leave-active {
    transition: .8s ease;
    position: absolute;
}

.smooth-settings-switch-enter-from {
    transform: translateY(100%);
    transition: .8s;
    opacity: 0;
}

.smooth-settings-switch-leave-to {
    transform: translateY(-100%);
    transition: .8s;
    opacity: 0;
}

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px) {
    .catalog-panel .line .option,
    .catalog-panel .sub-line {
        flex-grow: 1;
    }
    .banner .title {
        font-size: 2.5rem;
    }
    .menu-separator {
        width: 95%;
    }
}

/* =============== [ Медиа-Запрос { ?px < 426px } ] =============== */

@media screen and (max-width: 450px) {
    .catalog-container {
        margin-top: 102px;
    }
    .catalog-banner {
        height: 178px;
    }
    .banner {
        height: 178px;
    }
    .banner .title {
        font-size: 1.8rem;
        max-width: 214px;
        height: 126px
    }
    .catalog-banner .splash {
        max-width: 150px;
        bottom: 18px;
    }
    .catalog-banner .splash.bottom-splash {
        bottom: 0;
    }
    .large-splash {
        font-size: .5rem;
    }
    .small-splash {
        font-size: .6rem;
    }
    .catalog-panel .line a,
    .catalog-panel .line button {
        max-width: 180px;
    }
    .catalog-panel .line a .preset,
    .catalog-panel .line button .preset {
        padding: 0 .2rem;
        height: 36px;
    }
    .catalog-panel .line .preset span {
        font-size: .8rem;
    }
    .menu-separator {
        width: 95%;
    }
}
</style>
