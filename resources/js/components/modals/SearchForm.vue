<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {computed, ref, watch} from 'vue'

import {changeTitle, getErrorMessageByCode} from '@/helpers'
import {useToastStore} from '@/stores/toast'
import {useRoute} from 'vue-router'

import Button from '@/components/elements/Button.vue'
import type {Post} from '@/types'
import SearchingPostCard from '@/components/modals/SearchingPostCard.vue'

const emit = defineEmits(['close'])

const posts = ref<Post[]>([])

const toastStore = useToastStore()
const route = useRoute()

const searchTerm = ref('')

const isTryingSearchTimeout = ref()
const isLoaded = ref(false)

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
    term: searchTerm.value,
    per_page: 10,
    sort_type: PostSortType.POPULAR,
    period: PostLoadPeriod.ALL_TIME
}))

function searchPosts() {
    axios.get('/api/posts', {params: {...loadData.value}}).then((response) => {
        const responseData: PostLoadResponseData = response.data
        if (responseData.success) {
            posts.value = responseData.records
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoaded.value = true
    })
}

function onInput() {
    isLoaded.value = false
    clearTimeout(isTryingSearchTimeout.value)
    isTryingSearchTimeout.value = setTimeout(() => {
        searchPosts()
    }, 1000)
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
                        v-model="searchTerm"
                        class="flex text-[14px] w-full p-0"
                        id="search-input"
                        placeholder="Поиск..."
                        type="text"
                        @input="onInput"
                    >
                    <button class="flex justify-center items-center min-w-[42px] h-[42px]" type="reset">
                        <span class="icon icon-cross"/>
                    </button>
                </label>
            </fieldset>
            <fieldset
                class="search-results ld-tinted-background darker flex
                    justify-center min-h-[64px] w-full"
            >
                <template v-if="isLoaded">
                    <div
                        v-if="posts.length === 0"
                        class="unavailable-post-container flex flex-col items-center p-8"
                    >
                        <p class="text-center md:text-[14px] text-[12px]">
                            <span class="opacity-80">Не удалось найти результатов по запросу «</span>
                            <i class="ld-term-text">{{ searchTerm }}</i>
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
                        v-else
                        class="final-results flex flex-col items-center w-full gap-3
                            px-2.5 py-3 md:max-h-[548px] max-h-[60vh] overflow-auto"
                        :class="{ 'md:pl-[1.5rem]': searchTerm.length > 0 && posts.length > 7 }"
                    >
                        <SearchingPostCard
                            v-for="(post) in posts"
                            class="max-w-[768px]"
                            :key='post.id'
                            :post="post"
                            :term="searchTerm"
                            @click="emit('close')"
                        />
                    </div>
                </template>
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

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px) {
    .final-results {
        scrollbar-width: thin
    }
}
</style>
