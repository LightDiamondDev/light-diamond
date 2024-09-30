<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'
import {getErrorMessageByCode, getFullPresentableDate, getRelativeDate} from '@/helpers'
import {computed, reactive, ref} from 'vue'
import {type RouteLocationRaw, RouterLink} from 'vue-router'

import {type Post, type PostVersion, PostVersionActionType as ActionType, PostVersionStatus} from '@/types'
import PostVersionCard from '@/components/post/PostVersionCard.vue'

import Paginator from '@/components/elements/Paginator.vue'
import TabMenu, {type TabMenuChangeEvent} from '@/components/elements/TabMenu.vue'
import ProcessingDiggingBlocks from '@/components/elements/ProcessingDiggingBlocks.vue'
import UserAvatar from '@/components/user/UserAvatar.vue'
import PostVersionAction from '@/components/post/PostVersionAction.vue'

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
    <div class="section ld-shadow-text flex flex-col h-full">
        <div class="ld-primary-border-bottom flex md:justify-start justify-center">
            <TabMenu
                item-classes="justify-center h-[48px] min-w-[64px] md:text-[1rem] text-[14px] gap-1 lg:px-4 px-1"
                item-label-classes="xs:flex hidden"
                :items="tabMenuItems"
                @tab-change="onTabChange"
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
