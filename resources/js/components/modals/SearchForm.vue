<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {computed, ref, watch} from 'vue'

import {changeTitle, getErrorMessageByCode} from '@/helpers'
import {useToastStore} from '@/stores/toast'
import {useRoute} from 'vue-router'

import Button from '@/components/elements/Button.vue'
import type {Post} from '@/types'
import SearchingPostCard from '@/components/modals/SearchingPostCard.vue'

const posts = ref<Post[]>([])

const toastStore = useToastStore()
const route = useRoute()

const searchingTerm = computed(() => route.query.term)
const searchingModel = defineModel<string>({default: ''})

const isTryingSearchTimeout = ref()
const isLoading = ref(false)

function updateTitle() {
    changeTitle(`Поиск «${searchingTerm.value as string | undefined ?? ''}»`)
}

watch(route, () => {
    updateTitle()
})

updateTitle()

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

const loadData = computed(() => ({
    page: 1,
    term: searchingModel.value,
    per_page: 10,
    sort_type: PostSortType.POPULAR,
    period: PostLoadPeriod.ALL_TIME
}))

function searchPosts() {
    isLoading.value = true
    axios.get('/api/posts', {params: {...loadData.value}}).then((response) => {
        const responseData: PostLoadResponseData = response.data
        if (responseData.success) {
            posts.value = responseData.records
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
       isLoading.value = false
    })
}

function trySearchPosts() {
    clearTimeout(isTryingSearchTimeout.value)
    isTryingSearchTimeout.value = setTimeout(() => {
        searchPosts()
    }, 1500)
}
</script>

<template>
    <div class="search-form ld-primary-border-bottom ld-primary-border-right ld-primary-border-left
        inner flex justify-center w-full"
    >
        <form action="" class="flex flex-col w-full">
            <fieldset class="flex justify-center px-2">
                <label class="flex items-center max-w-[768px] w-full my-[9px]" for="search-input">
                    <button class="flex justify-center items-center min-w-[42px] h-[42px]" id="search-button" type="button">
                        <span class="icon icon-magnifier"/>
                    </button>
                    <input
                        v-model="searchingModel"
                        class="flex text-[14px] w-full p-0"
                        id="search-input"
                        placeholder="Поиск..."
                        type="text"
                        @input="trySearchPosts"
                    >
                    <button class="flex justify-center items-center min-w-[42px] h-[42px]" type="reset">
                        <span class="icon icon-cross"/>
                    </button>
                </label>
            </fieldset>
            <fieldset
                class="search-results ld-tinted-background darker flex
                    justify-center md:max-h-[548px] min-h-[64px] w-full overflow-hidden"
            >
                <div v-if="isLoading" class="flex flex-col items-center w-full gap-3 px-2.5 py-3">
                    <div
                        v-for="i in 3"
                        class="ld-primary-background
                            flex items-center min-h-[64px] max-w-[768px] w-full cursor-pointer"
                    >
                        <div
                            class="skeleton transfusion ld-primary-border-bottom ld-primary-border-left
                                ld-primary-border-top flex h-[64px] min-w-[112px]"
                            style="aspect-ratio: 16/9; object-fit: cover"
                        />
                        <div class="description ld-primary-border flex justify-center items-center h-full min-w-[48px] w-full">
                            <div
                                class="flex flex-col justify-center max-h-[72px]
                                    h-full w-full pl-2 overflow-hidden relative"
                            >
                                <h3 class="skeleton transfusion absolute h-2 w-[40%] top-[8px]"/>
                                <p class="skeleton transfusion absolute h-2 w-[90%] bottom-[26px]"/>
                                <p class="skeleton transfusion absolute h-2 w-[20%] bottom-[10px]"/>
                            </div>
                            <div class="skeleton transfusion min-w-[2rem] h-[2rem] mr-2"/>
                        </div>
                    </div>
                </div>
                <div
                    v-else-if="!isLoading && (searchingModel.length > 0 && posts.length === 0)"
                    class="unavailable-post-container flex flex-col items-center p-8"
                >
                    <p class="text-center md:text-[14px] text-[12px]">
                        <span class="opacity-80">Не удалось найти результатов по запросу «</span>
                        <i class="ld-term-text">{{ searchingModel }}</i>
                        <span class="opacity-80">».</span>
                    </p>
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
                <div
                    v-else-if="searchingModel.length > 0 && posts.length > 0"
                    class="flex flex-col items-center w-full gap-3 px-2.5 py-3"
                    :class="{ 'md:pl-[1.5rem]': searchingModel.length > 0 && posts.length > 7 }"
                >
                    <SearchingPostCard
                        v-for="(post) in posts"
                        class="max-w-[768px]"
                        :key='post.id'
                        :post="post"
                        :term="searchingModel"
                    />
                </div>
            </fieldset>
        </form>
    </div>
</template>

<style scoped>
.search-dialog .dialog-form-container {
    max-width: 1232px;
    transition: .5s;
}
.search-dialog .dialog-form-container .interface {
    width: 100%;
}
.search-dialog {
    padding-right: 0;
}
.search-results {
    overflow-y: auto;
}

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px) {
    .search-results {
        scrollbar-width: thin
    }
}
</style>
