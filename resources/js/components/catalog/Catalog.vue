<script setup lang="ts">
import axios, {type AxiosError} from 'axios'

import {type PropType, reactive, ref, watch} from 'vue'
import {getErrorMessageByCode, getFullDate, getRelativeDate} from '@/helpers'
import {getRandomColor, getRandomSplash} from '@/stores/splashes'
import usePreferenceManager from '@/preference-manager'
import {useRouter, useRoute, RouterLink} from 'vue-router'
import {useAuthStore} from '@/stores/auth'
import {usePostCategoryStore} from '@/stores/postCategory'
import {useToastStore} from '@/stores/toast'

import {GameEdition, type Post} from '@/types'

import Banner from '@/components/elements/Banner.vue'
import PostCard from '@/components/post/PostCard.vue'
import Paginator from '@/components/elements/Paginator.vue'
import ShineButton from '@/components/elements/ShineButton.vue'
import UserAvatar from '@/components/user/UserAvatar.vue'
import PostActionBar from '@/components/post/PostActionBar.vue'
import Button from '@/components/elements/Button.vue'

const props = defineProps({
    additionalLoadData: Object,
    categorySlug: String,
    edition: {
        type: String as PropType<GameEdition>
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
    { label: 'Всё время', value: PostLoadPeriod.ALL_TIME },
    { label: 'Сутки', value: PostLoadPeriod.DAY },
    { label: 'Неделя', value: PostLoadPeriod.WEEK },
    { label: 'Месяц', value: PostLoadPeriod.MONTH },
    { label: 'Год', value: PostLoadPeriod.YEAR }
]

defineExpose({
    getTotalRecordsCount
})

const authStore = useAuthStore()
const categoryStore = usePostCategoryStore()
const toastStore = useToastStore()

const bannerImagesSrc = [ '/images/elements/general-banner-ancient-city.png' ]

const edition = ref(props.edition === GameEdition.BEDROCK ? GameEdition.JAVA : GameEdition.BEDROCK)

const posts = ref<Post[]>([])
const router = useRouter()
const route = useRoute()

const totalRecordsCount = ref(0)
const timePeriodCounter = ref(0);
const currentTimePeriod = ref(PostLoadPeriod.ALL_TIME)

const activeSplash = getRandomSplash()
const activeColor = getRandomColor()

const isLoading = ref(false)
const isFresh = ref(true)
const isFilters = ref(false)

const loadData = reactive({
    page: 1,
    per_page: 15,
    sort_type: PostSortType.LATEST,
    period: PostLoadPeriod.ALL_TIME,
})

watch(route, () => { loadPosts() })

function loadPosts() {
    isLoading.value = true

    axios.get('/api/posts', {params: {...loadData, ...props.additionalLoadData}}).then((response) => {
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

function onPageChange(event: PageState) {
    const selectedPage = event.page + 1
    if (selectedPage !== loadData.page) {
        loadData.page = selectedPage
        loadPosts()
    }
}

function onSortTypeSelect(type: PostSortType) {
    loadData.sort_type = type
    loadData.page = 1
    switch (type) {
        case PostSortType.LATEST:
            loadData.period = PostLoadPeriod.ALL_TIME
            break
        case PostSortType.POPULAR:
            loadData.period = currentTimePeriod.value
            break
    }
    loadPosts()
}

function switchTimePeriod() {
    timePeriodCounter.value++
    if (timePeriodCounter.value === 5) timePeriodCounter.value = 0;
    currentTimePeriod.value = postLoadPeriodItems[timePeriodCounter.value].value
    onSortTypeSelect(PostSortType.POPULAR)
}

function switchEdition() {
    const edition = props.edition === GameEdition.BEDROCK ? GameEdition.JAVA : GameEdition.BEDROCK
    usePreferenceManager().setEdition(edition)
    router.push({name: `catalog.${edition.toLowerCase()}`})
}

function switchFresh() {
    onSortTypeSelect(PostSortType.LATEST)
    isFresh.value = true
}

function switchPopular() {
    onSortTypeSelect(PostSortType.POPULAR)
    isFresh.value = false
}

function getTotalRecordsCount() {
    return totalRecordsCount.value
}

switchFresh()
</script>

<template>
    <Banner class="md:h-[208px] h-[178px]" :images-src="bannerImagesSrc">
        <template v-slot:banner-content>
            <div class="title flex flex-col justify-center items-center w-full">
                <RouterLink
                    class="logo-wrap flex items-center full-locked relative orange"
                    :to="{ name: `catalog.${edition?.toLowerCase()}` }"
                >
                    <h1 class="flex justify-center w-full absolute invisible">Каталог Light Diamond</h1>
                    <img alt="Logo" class="materials-logo h-[48px] md:h-[100px]" src="/images/elements/light-diamond-materials-logo.png"/>
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
            <form class="catalog-panel ld-primary-background ld-primary-border flex flex-col w-full self-center" name="catalog">

                <nav class="flex flex-col">

                    <div class="line flex flex-wrap justify-between gap-3 p-3">
                        <div class="sub-line flex justify-center flex-wrap gap-3">
                            <ShineButton
                                class="flex items-center"
                                class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                                :class="{ 'active': isFresh }"
                                label="Свежие"
                                icon="icon-clock"
                                @click="switchFresh"
                            />
                            <ShineButton
                                class="flex items-center"
                                class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                                :class="{ 'active': !isFresh }"
                                label="Популярные"
                                icon="icon-crown"
                                @click="switchPopular"
                            />
                            <ShineButton
                                v-if="!isFresh"
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
                                :label="usePreferenceManager().getEdition() === GameEdition.BEDROCK ? 'Bedrock' : 'Java'"
                                :icon="usePreferenceManager().getEdition() === GameEdition.BEDROCK ? 'icon-bedrock-dev-small' : 'icon-minecraft-materials'"
                                @click="switchEdition"
                            />
                        </div>
                    </div>

                    <div class="menu-separator flex self-center"></div>

                    <div class="line flex flex-wrap sm:justify-start justify-center gap-3 p-3">
                        <template v-for="category in categoryStore.categories">
                            <ShineButton
                                v-if="category.edition === usePreferenceManager().getEdition() || category.edition === null"
                                as="RouterLink"
                                class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                                :label="category.name"
                                icon="icon-brilliant"
                                :to="{ path: `/${edition?.toLowerCase()}/${category.slug}` }"
                            />
                        </template>
                    </div>

                    <div class="menu-separator flex self-center"></div>

                    <button
                        class="filters-button line flex items-center gap-3 p-3"
                        @click="isFilters = !isFilters"
                        type="button"
                    >
                        <span :class="{ 'down-arrow-up': isFilters }" class="icon icon-down-arrow"/>
                        <span>Фильтры</span>
                    </button>

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

            <div v-if="isLoading" class="w-full">
                <div class="ld-primary-background ld-primary-border h-[48px] w-full mt-2"/>
                <div class="posts flex flex-wrap w-full gap-2">
                    <div
                        v-for="i in 6"
                        class="ld-primary-background ld-primary-border flex flex-col
                            xl:max-w-[421px] lg:max-w-[32.8%] sm:max-w-[49.3%] max-w-full"
                        style="flex: 1 0"
                    >
                        <div class="skeleton transfusion flex aspect-video"/>
                        <div class="flex flex-col flex-grow justify-between w-full">
                            <div class="flex flex-col">
                                <div class="ld-primary-border-top transfusion h-[40px]"/>
                                <div class="ld-tinted-background flex flex-col gap-2 p-2">
                                    <div class="skeleton transfusion h-2 w-full"/>
                                    <div class="skeleton transfusion h-2 w-[80%]"/>
                                    <div class="skeleton transfusion h-2 w-[60%]"/>
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <div class="flex flex-wrap justify-between items-center px-2">
                                    <div class="skeleton transfusion flex h-2 w-[30%]"/>
                                    <div class="skeleton transfusion flex h-[24px] w-[64px]"/>
                                </div>
                                <div class="flex justify-between px-2 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="skeleton transfusion h-8 min-w-8"/>
                                        <div class="skeleton transfusion flex h-2 w-[6rem]"/>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="skeleton transfusion flex h-2 w-[6rem]"/>
                                    </div>
                                </div>
                                <div class="menu-separator flex self-center w-[95%]"/>
                                <div
                                    class="actions ld-primary-background-container flex gap-2 p-2 overflow-x-auto overflow-y-hidden"
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
                </div>
                <div class="ld-primary-background ld-primary-border h-[48px] w-full mb-2"/>
            </div>
            <template v-else>
                <Paginator
                    class="ld-primary-background ld-primary-border h-[48px] w-full mt-2"
                    :class="{'hidden': posts.length === 0}"
                    :records-at-page="loadData.per_page"
                    :totalRecords="totalRecordsCount"
                    @page="onPageChange"
                />
                <div v-if="posts.length === 0" class="unavailable-post flex justify-center items-center w-full">
                    <div class="unavailable-post-container flex flex-col items-center p-8">
                        <h1 class="ld-title-font text-center sm:text-[2rem] text-[1.2rem]">Материалы не найдены</h1>
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
                <div v-else class="posts flex flex-wrap max-w-full w-full gap-2">
                    <PostCard
                        v-for="post in posts"
                        class="xl:max-w-[421px] lg:max-w-[32.8%] sm:max-w-[49.3%] max-w-full"
                        :post="post"
                    />
                </div>
                <Paginator
                    class="ld-primary-background ld-primary-border h-[48px] w-full mb-2"
                    :class="{'hidden': posts.length === 0}"
                    :records-at-page="loadData.per_page"
                    :totalRecords="totalRecordsCount"
                    @page="onPageChange"
                />
            </template>
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
    .logo-wrap .materials-logo {
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
    .logo-wrap .materials-logo {
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
