<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {computed, type PropType, ref, watch} from 'vue'

import {getErrorMessageByCode} from '@/helpers'
import {getRandomColor, getRandomSplash} from '@/stores/splashes'
import usePreferenceManager from '@/preference-manager'
import {RouterLink, useRoute, useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'

import {GameEdition, type Post} from '@/types'

import Banner from '@/components/elements/Banner.vue'
import PostCard from '@/components/post/PostCard.vue'
import Paginator from '@/components/elements/Paginator.vue'
import ShineButton from '@/components/elements/ShineButton.vue'
import Button from '@/components/elements/Button.vue'
import useCategoryRegistry, {type Category} from '@/categoryRegistry'

const props = defineProps({
    category: Object as PropType<Category>,
    edition: {
        type: String as PropType<GameEdition>,
        required: true
    }
})

interface PostLoadResponseData {
    success: boolean
    message?: string
    errors?: string[][]
    records?: Post[]
    pagination?: {
        total_records: number
        current_page: number
        total_pages: number
    }
}

enum PostSortType {
    LATEST = 'LATEST',
    POPULAR = 'POPULAR'
}

enum PostLoadPeriod {
    DAY = 'DAY',
    WEEK = 'WEEK',
    MONTH = 'MONTH',
    YEAR = 'YEAR',
    ALL_TIME = 'ALL_TIME'
}

const postLoadPeriodItems = [
    {label: 'Всё время', value: PostLoadPeriod.ALL_TIME},
    {label: 'Сутки', value: PostLoadPeriod.DAY},
    {label: 'Неделя', value: PostLoadPeriod.WEEK},
    {label: 'Месяц', value: PostLoadPeriod.MONTH},
    {label: 'Год', value: PostLoadPeriod.YEAR}
]

const authStore = useAuthStore()
const toastStore = useToastStore()
const categoryRegistry = useCategoryRegistry()

const bannerImagesSrc = ['/images/elements/general-banner-ancient-city.png']

const posts = ref<Post[]>([])
const router = useRouter()
const route = useRoute()

const totalRecordsCount = ref(0)
const timePeriodCounter = ref(0)

const activeSplash = getRandomSplash()
const activeColor = getRandomColor()

const isLoading = ref(false)
const isFilters = ref(false)
const isHorizontalCards = ref(false)
const currentPageNumber = ref(1)
const sortType = ref(PostSortType.LATEST)
const loadPeriod = ref(PostLoadPeriod.ALL_TIME)

const loadData = computed(() => ({
    edition: props.edition!,
    category: props.category?.type,
    page: currentPageNumber.value,
    period: loadPeriod.value,
    per_page: 15,
    sort_type: sortType.value,
}))

usePreferenceManager().setEdition(props.edition!)

watch(() => [props.edition, props.category], () => {
    loadPosts()
})

function loadPosts() {
    isLoading.value = true

    axios.get('/api/posts', {params: {...loadData.value}}).then((response) => {
        const responseData: PostLoadResponseData = response.data
        if (responseData.success) {
            totalRecordsCount.value = responseData.pagination!.total_records
            posts.value = responseData.records!
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

function changeSortType(type: PostSortType) {
    sortType.value = type
    currentPageNumber.value = 1
    switch (type) {
        case PostSortType.LATEST:
            loadPeriod.value = PostLoadPeriod.ALL_TIME
            break
        case PostSortType.POPULAR:
            loadPeriod.value = PostLoadPeriod.ALL_TIME
            timePeriodCounter.value = 0
            break
    }
    loadPosts()
}

function switchTimePeriod() {
    timePeriodCounter.value++
    if (timePeriodCounter.value === 5) timePeriodCounter.value = 0
    loadPeriod.value = postLoadPeriodItems[timePeriodCounter.value].value
    loadPosts()
}

function switchEdition() {
    const edition = props.edition === GameEdition.BEDROCK ? GameEdition.JAVA : GameEdition.BEDROCK
    usePreferenceManager().setEdition(edition)
    router.push({name: 'catalog'})
}

function scrollToBottom() {
    window.scrollTo(0, document.body.scrollHeight)
}

loadPosts()
</script>

<template>
    <Banner class="md:h-[208px] h-[178px]" :images-src="bannerImagesSrc">
        <template v-slot:banner-content>
            <div class="title flex flex-col justify-center items-center w-full">
                <RouterLink
                    class="logo-wrap flex items-center full-locked relative orange"
                    :to="{ name: 'catalog' }"
                >
                    <h1 class="flex justify-center w-full absolute invisible">Каталог Light Diamond</h1>
                    <img alt="Logo" class="posts-logo h-[48px] md:h-[100px]"
                         src="/images/elements/light-diamond-catalog-logo.png"/>
                    <span class="base flex justify-center items-center">
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
            </div>
        </template>
    </Banner>
    <div class="catalog-container ld-secondary-background flex justify-center w-full">

        <section class="catalog flex flex-col items-center w-full">
            <form class="catalog-panel ld-primary-background ld-primary-border flex flex-col w-full self-center"
                  name="catalog">

                <nav class="flex flex-col">

                    <div class="line flex flex-wrap justify-between gap-3 p-3">
                        <div class="sub-line flex justify-center flex-wrap gap-3">
                            <ShineButton
                                class="flex items-center"
                                class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                                :class="{ 'active': sortType === PostSortType.LATEST }"
                                label="Свежие"
                                icon="icon-clock"
                                @click="changeSortType(PostSortType.LATEST)"
                            />
                            <ShineButton
                                class="flex items-center"
                                class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                                :class="{ 'active': sortType === PostSortType.POPULAR }"
                                label="Популярные"
                                icon="icon-crown"
                                @click="changeSortType(PostSortType.POPULAR)"
                            />
                            <ShineButton
                                v-if="sortType === PostSortType.POPULAR"
                                class="active flex items-center option w-[200px]"
                                class-preset="ld-title-font justify-center sm:text-[16px]
                                    xs:text-[14px] text-[12px] gap-1 px-6 py-0.5 whitespace-nowrap"
                                :label="postLoadPeriodItems[timePeriodCounter].label"
                                icon="icon-crown"
                                @click="switchTimePeriod()"
                            />
                        </div>
                        <div class="sub-line flex flex-wrap justify-center gap-3">
                            <ShineButton
                                class="flex items-center option edition xl:min-w-[158px]"
                                class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                                :label="edition === GameEdition.BEDROCK ? 'Bedrock' : 'Java'"
                                :icon="edition === GameEdition.BEDROCK ? 'icon-bedrock-flat' : 'icon-grass-flat'"
                                @click="switchEdition"
                            />
                        </div>
                    </div>

                    <div class="menu-separator flex self-center"></div>

                    <div class="line flex flex-wrap sm:justify-start justify-center gap-3 p-3">
                        <ShineButton
                            as="RouterLink"
                            class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                            :class="{'active': props.category === undefined}"
                            label="Все"
                            icon="icon-brilliant"
                            :to="{ name: 'catalog-of', params: {edition: edition.toLowerCase()} }"
                        />

                        <template v-for="category in categoryRegistry.getByEdition(edition)">
                            <ShineButton
                                as="RouterLink"
                                class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                                :class="{'active': props.category?.type === category.type}"
                                :label="category.name"
                                :icon="category.icon"
                                :to="{ name: 'catalog-of', params: {edition: edition.toLowerCase(), category: category.slug} }"
                            />
                        </template>
                    </div>

                    <div class="menu-separator flex self-center"/>

                    <div class="flex">
                        <button
                            class="filters-button line flex items-center w-full gap-3 p-3"
                            @click="isFilters = !isFilters"
                            type="button"
                        >
                            <span :class="{ 'down-arrow-up': isFilters }" class="icon icon-down-arrow"/>
                            <span>Фильтры</span>
                        </button>
                        <button
                            class="catalog-anchor flex justify-center items-center self-center"
                            @click="scrollToBottom"
                            type="button"
                        >
                            <span class="icon icon-down-arrow"/>
                        </button>
                        <ShineButton
                            :icon="isHorizontalCards ?
                            'icon-display-detail max-h-[28px] max-w-[28px] min-w-[1rem]' :
                            'icon-display-grid max-h-[28px] max-w-[28px] min-w-[1rem]'"
                            class-preset="p-1"
                            class="m-2.5"
                            @click="isHorizontalCards = !isHorizontalCards"
                        />
                    </div>

                    <div v-if="isFilters" class="filters gap-3 p-3">
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

            <div v-if="isLoading && posts.length === 0" class="w-full">
                <div class="posts flex flex-wrap w-full gap-2">

                    <div
                        v-for="i in 6"
                        class="post-card ld-primary-background flex"
                        :class="{
                            'flex-col xl:max-w-[421px] lg:max-w-[32.8%] sm:max-w-[49.3%] max-w-full': !isHorizontalCards,
                            'horizontal md:flex-row flex-col': isHorizontalCards
                        }"
                        style="flex: 1 0"
                    >
                        <div
                            class="flex flex-grow"
                            :class="{ 'flex-col': !isHorizontalCards, 'xs:flex-row flex-col': isHorizontalCards }"
                        >
                            <div
                                class="skeleton transfusion transfusion flex w-full full-locked duration-200"
                                :class="{ 'max-h-[234px] max-w-[366px]': isHorizontalCards }"
                                style="aspect-ratio: 16/9; object-fit: cover"
                            />
                            <div class="description-wrap flex flex-col flex-grow justify-between text-[12px] w-full">
                                <div class="flex flex-col">
                                    <div class="post-title-wrap transfusion h-[2rem]"/>
                                    <div
                                        class="description ld-tinted-background flex flex-col gap-2 p-2"
                                        :class="{ 'md:flex hidden': isHorizontalCards }"
                                    >
                                        <div class="skeleton transfusion flex h-3 w-full"/>
                                        <div class="skeleton transfusion flex h-3 w-[80%]"/>
                                        <div class="skeleton transfusion flex h-3 w-[60%]"/>
                                    </div>
                                </div>
                                <div class="flex flex-col">
                                    <div
                                        class="flex"
                                        :class="{
                                            'flex-wrap flex-row-reverse lg:justify-between justify-end items-center': isHorizontalCards,
                                            'flex-col': !isHorizontalCards
                                        }"
                                    >
                                        <div
                                            class="post-info flex flex-wrap justify-between px-2"
                                            :class="{ 'sm:flex hidden w-full gap-8': isHorizontalCards }"
                                        >
                                            <div class="flex gap-3">
                                                <div class="skeleton transfusion flex h-[24px] w-[10rem]"/>
                                            </div>
                                            <div class="skeleton transfusion flex h-[24px] w-[64px]"/>
                                        </div>
                                        <div
                                            class="author-info flex justify-between p-2"
                                            :class="{ 'sm:flex-row xs:flex-col flex-row w-full sm:gap-8 gap-2': isHorizontalCards }"
                                        >
                                            <div class="flex flex-wrap items-center gap-1">
                                                <div class="skeleton transfusion h-8 min-w-8"/>
                                                <div class="skeleton transfusion flex h-4 w-[6rem]"/>
                                            </div>
                                            <div
                                                class="flex items-center text-end opacity-80 cursor-pointer ml-1"
                                            >
                                                <div class="skeleton transfusion flex h-4 w-[6rem]"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="menu-separator flex self-center w-[95%]"
                                        :class="{ 'md:flex hidden w-[98%]': isHorizontalCards }"
                                    />
                                    <div
                                        class="actions ld-primary-background-container flex gap-2 p-2 overflow-x-auto overflow-y-hidden"
                                        :class="{ 'md:flex hidden': isHorizontalCards }"
                                        style="scrollbar-width: thin"
                                    >
                                        <div v-for="i in 5" class="flex items-center gap-2">
                                            <div class="skeleton transfusion h-8 min-w-8"/>
                                            <div class="skeleton transfusion flex h-4 w-[2rem]"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="isHorizontalCards"
                            class="actions ld-primary-background ld-primary-background-container ld-tinted-background ld-primary-border-bottom
                                ld-primary-border-right ld-primary-border-left gap-2 p-2 overflow-x-auto overflow-y-hidden md:hidden flex"
                            style="scrollbar-width: thin"
                        >
                            <div v-for="i in 5" class="flex items-center gap-2">
                                <div class="skeleton transfusion h-8 min-w-8"/>
                                <div class="skeleton transfusion flex h-4 w-[2rem]"/>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="ld-primary-background ld-primary-border h-[48px] w-full mb-2"/>
            </div>
            <template v-else>
                <div v-if="posts.length === 0" class="unavailable-post flex justify-center items-center w-full">
                    <div class="unavailable-post-container flex flex-col items-center p-8">
                        <h1 class="ld-title-font text-center sm:text-[2rem] text-[1.2rem]">Посты не найдены</h1>
                        <div class="mob phantom flex justify-center items-center
                            sm:h-[200px] h-[100px] max-w-[320] w-full full-locked"
                        >
                            <div
                                class="animation-flying-phantom sm:h-[160px]
                                    h-[80px] sm:w-[320px] w-[160px]"
                                style="background-size: 100% 100%"
                            />
                        </div>
                    </div>
                </div>
                <div v-else class="posts flex flex-wrap max-w-full w-full gap-2" :class="{'paginator-refresh': isLoading}">
                    <PostCard
                        v-for="post in posts"
                        class="xl:max-w-[421px] lg:max-w-[32.8%] sm:max-w-[49.3%] max-w-full duration-200"
                        :is-horizontal-direction="isHorizontalCards"
                        :post="post"
                    />
                </div>
            </template>
            <Paginator
                v-model="currentPageNumber"
                class="ld-primary-background ld-primary-border h-[48px] w-full mb-2"
                :class="{'hidden': posts.length === 0}"
                :records-at-page="loadData.per_page"
                :total-records="totalRecordsCount"
                @update:model-value="loadPosts"
            />
        </section>

    </div>
</template>

<style scoped>
.catalog-container {
    background-attachment: fixed;
}

.title {
    position: relative;
    line-height: 1.1;
    height: 208px
}

.title {
    overflow: hidden;
    height: 208px
}

.base {
    transform: rotate(-15deg);
    transform-origin: center;
    position: absolute;
    height: 10px;
    width: 10px;
    bottom: 0;
    right: 5%;
}

.horizontal {
    min-width: 100%;
}

.splash {
    animation: splash-animation 1s infinite;
    text-shadow: 2px 2px black;
    position: absolute;
    text-align: center;
    width: 240px;
}

.splash.bottom-splash {
    bottom: -1.5rem;
    width: 230px;
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
    max-width: 268px;
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

.catalog-panel .filters {
    height: 240px;
}

.icon-down-arrow {
    transition: .5s;
}

.down-arrow-up {
    transform: rotate(-180deg);
}

.catalog-anchor .icon-down-arrow {
    animation: catalog-anchor-icon-animation 1s infinite;
}

@keyframes catalog-anchor-icon-animation {
    0% {
        margin-top: -10px;
    }
    50% {
        margin-top: 10px;
    }
    100% {
        margin-top: -10px;
    }
}

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px) {
    .logo-wrap .posts-logo {
        height: 72px;
    }

    .catalog-container .base {
        bottom: -12%;
        right: 5%;
    }

    .catalog-container .splash {
        width: 230px;
    }

    .catalog-container .splash.bottom-splash {
        bottom: -2rem;
    }

    .catalog-panel .line .option,
    .catalog-panel .sub-line {
        flex-grow: 1;
    }

    .menu-separator {
        width: 95%;
    }
}

.posts {
    padding: .5rem 0;
}

/* =============== [ Медиа-Запрос { ?px < 1280px } ] =============== */

@media screen and (max-width: 1279px) {
    .posts {
        padding: .5em;
    }
}

/* =============== [ Медиа-Запрос { ?px < 451px } ] =============== */

@media screen and (max-width: 450px) {
    .catalog-container .title {
        height: 104px;
    }

    .catalog-container .splash {
        max-width: 150px;
    }

    .catalog-container .splash.bottom-splash {
        bottom: -1rem;
        right: -80px;
    }

    .logo-wrap .posts-logo {
        height: 48px;
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

    .menu-separator {
        width: 95%;
    }
}
</style>
