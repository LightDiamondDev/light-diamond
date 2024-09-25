<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'
import {getErrorMessageByCode} from '@/helpers'
import {computed, reactive, ref} from 'vue'

import {type Post, type PostVersion, PostVersionStatus} from '@/types'
import PostVersionCard from '@/components/post/PostVersionCard.vue'

import Paginator from '@/components/elements/Paginator.vue'
import TabMenu, {type TabMenuChangeEvent} from '@/components/elements/TabMenu.vue'
import type {RouteLocationRaw} from 'vue-router'

export interface MenuItem {
    label?: string
    icon?: string
    visible?: boolean
    separator?: boolean
    route?: RouteLocationRaw
    action?: () => void
    closesMenu?: boolean
}

interface PostVersionLoadResponseData {
    success: boolean
    message?: string
    status?: string
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

const isLoading = ref(false)

const postVersions = ref<PostVersion[]>([])
const totalRecords = ref(0)
const loadRequestData = reactive({
    status: PostVersionStatus.PENDING,
    page: 1,
    per_page: 10,
})

const tabMenuItems = computed<MenuItem[]>(() => [
    {label: 'Ожидающие', icon: 'icon-clock', status: PostVersionStatus.PENDING},
    {label: 'Принятые', icon: 'icon-tick', status: PostVersionStatus.ACCEPTED},
    {label: 'Отклонённые', icon: 'icon-small-cross', status: PostVersionStatus.REJECTED},
])

function loadPostVersions() {
    isLoading.value = true
    postVersions.value = []

    axios.get('/api/post-versions', {params: loadRequestData}).then((response) => {
        const responseData: PostVersionLoadResponseData = response.data
        if (responseData.success) {
            postVersions.value = responseData.records!
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

function onPageChange(event: PageState) {
    const selectedPage = event.page + 1
    if (selectedPage !== loadRequestData.page) {
        loadRequestData.page = selectedPage
        loadPostVersions()
    }
}

function onTabChange(event: TabMenuChangeEvent) {
    const selectedStatus = tabMenuItems.value[event.tabIndex].status
    if (loadRequestData.status !== selectedStatus) {
        loadRequestData.status = selectedStatus
        loadRequestData.page = 1
        totalRecords.value = 0
        loadPostVersions()
    }
}

loadPostVersions()
</script>

<template>
    <div class="section flex flex-col h-full">
        <div class="flex ld-primary-border-bottom">
            <TabMenu
                item-classes="h-[48px] md:text-[1rem] text-[14px] px-4"
                :items="tabMenuItems"
                @tab-change="onTabChange"
            />
        </div>

        <div v-if="isLoading" class="flex flex-col rounded-md border">
            <div v-for="i in 3" class="flex xs:grid grid-cols-[6rem,1fr] gap-2 p-3 [&:not(:first-child)]:border-t">
                <div class="skeleton h-[4.5rem] hidden xs:block"/>
                <div class="flex flex-col w-full">
                    <div class="flex mt-1 gap-3 items-center">
                        <div class="skeleton h-[0.6rem] w-[4rem]"/>
                        <div class="skeleton h-[0.6rem] w-[6rem]"/>
                    </div>
                    <div class="flex flex-col mt-4 gap-2">
                        <div class="skeleton h-[0.9rem]"/>
                        <div class="skeleton h-[0.9rem] w-[70%]"/>
                    </div>
                </div>
            </div>
        </div>
        <template v-else>
            <div v-if="postVersions.length === 0">
                <p class="text-muted">Заявки не найдены.</p>
            </div>
            <div v-else class="flex flex-col">
                <PostVersionCard
                    v-for="postVersion in postVersions"
                    :post-version="postVersion"
                />
            </div>
        </template>
        <Paginator
            class="h-[48px] gap-6"
            :rows="loadRequestData.per_page"
            :totalRecords="totalRecords"
            @page="onPageChange"
            :class="{'hidden': !isLoading && postVersions.length === 0}"
        />
    </div>
</template>

<style scoped>

</style>
