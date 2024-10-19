<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {useToastStore} from '@/stores/toast'
import {getErrorMessageByCode, getFullDate, getRelativeDate} from '@/helpers'
import {reactive, ref} from 'vue'

import {type Post} from '@/types'
import Paginator, {type PageChangeEvent} from '@/components/elements/Paginator.vue'
import PostStudioCard from '@/components/post/PostStudioCard.vue'

import {RouterLink} from 'vue-router'
import {useAuthStore} from '@/stores/auth'
import ItemButton from '@/components/elements/ItemButton.vue'
import ShineButton from '@/components/elements/ShineButton.vue'

interface PostLoadResponseData {
    success: boolean
    message?: string
    errors?: object
    records?: Post[]
    pagination?: {
        total_records: number
        current_page: number
        total_pages: number
    }
}

const authStore = useAuthStore()
const toastStore = useToastStore()

const posts = ref<Post[]>([])
const totalRecords = ref(0)

const loadRequestData = reactive({
    page: 1,
    per_page: 8
})

const isLoading = ref(false)

function loadPosts() {
    isLoading.value = true
    posts.value = []

    axios.get(`/api/users/${authStore.id}/posts`, {params: loadRequestData}).then((response) => {
        const responseData: PostLoadResponseData = response.data
        if (responseData.success) {
            posts.value = responseData.records!
            totalRecords.value = responseData.pagination!.total_records
        } else {
            toastStore.error('Произошла ошибка!')
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

function onPageChange(event: PageChangeEvent) {
    const selectedPage = event.pageNumber
    if (selectedPage !== loadRequestData.page) {
        loadRequestData.page = selectedPage
        loadPosts()
    }
}

loadPosts()
</script>

<template>
    <section class="section">
        <div class="ld-shadow-text flex flex-col min-h-[100vh]">
            <div class="flex flex-col">
                <div class="flex md:justify-start justify-center gap-2 p-2">

                    <ShineButton
                        as="RouterLink"
                        class-preset="ld-title-font gap-1 px-6 py-0.5"
                        label="Создать"
                        icon="icon-brilliant"
                        :to="{ name: 'create-post' }"
                    />

                </div>

                <div v-if="isLoading" class="flex flex-col">
                    <div v-for="i in 5" class="flex w-full gap-2 p-2">
                        <div class="skeleton transfusion flex
                            sm:h-[112px] sm:max-w-[196px] sm:min-w-[196px]
                            xs:h-[76px] xs:max-w-[132px] xs:min-w-[132px]
                            h-[58px] max-w-[100px] min-w-[100px]
                            overflow-hidden"
                        />
                        <div class="flex flex-col w-full gap-2">
                            <div class="skeleton transfusion flex h-4 max-w-[360px] w-full"/>
                            <div class="skeleton transfusion flex h-3 max-w-[80%] w-full"/>
                            <div class="skeleton transfusion flex h-3 max-w-[55%] w-full"/>
                            <div class="flex flex-wrap items-center md:text-[12px] text-[10px] gap-2 mt-0.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="skeleton transfusion flex h-7 w-7"/>
                                    <p class="skeleton transfusion flex h-4 w-[96px]"/>
                                    <p class="skeleton transfusion flex h-4 w-[72px]"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <div
                        v-if="posts.length === 0"
                        class="flex flex-col justify-center items-center min-h-[480px] gap-6"
                    >
                        <p class="text-muted text-center text-[14px] max-w-[480px]">
                            У Вас пока ещё нет собственных Материалов, начните творить! ;D
                        </p>
                        <div class="mob wandering-trader flex justify-center items-center mb-4">
                            <div class="animation-wandering-trader h-[244px] w-[130px]"></div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col">
                        <PostStudioCard v-for="post in posts" :post="post"/>
                    </div>
                </div>
            </div>

        </div>
        <div class="flex sticky bottom-[0]" style="z-index: 1">
            <Paginator
                class="ld-primary-background ld-fixed-background ld-primary-border-top h-[48px] w-full"
                :records-at-page="loadRequestData.per_page"
                :totalRecords="totalRecords"
                v-model="loadRequestData.page"
                @page-change="onPageChange"
            />
        </div>
    </section>
</template>

<style scoped>

</style>
