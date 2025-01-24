<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {computed, type PropType, ref} from 'vue'

import {getErrorMessageByCode} from '@/helpers'
import {useToastStore} from '@/stores/toast'

import {type Material, type MaterialSubmission, MaterialSubmissionStatus} from '@/types'
import MaterialSubmissionCard from '@/components/material/MaterialSubmissionCard.vue'

import Paginator from '@/components/elements/Paginator.vue'
import TabMenu, {type TabMenuChangeEvent} from '@/components/elements/TabMenu.vue'

interface MaterialSubmissionLoadResponseData {
    success: boolean
    message?: string
    status?: string
    errors?: object
    records?: Material[]
    pagination?: {
        total_records: number
        current_page: number
        total_pages: number
    }
}

const props = defineProps({
    status: {
        type: String as PropType<MaterialSubmissionStatus>,
        default: MaterialSubmissionStatus.PENDING
    }
})

const toastStore = useToastStore()

const materialSubmissions = ref<MaterialSubmission[]>([])
const totalRecords = ref(0)

const page = ref(1)
const perPage = ref(8)

const loadRequestData = computed(() => ({
    status: props.status,
    page: page.value,
    per_page: perPage.value
}))

const isLoading = ref(false)

const tabMenuItems = computed(() => [
    {
        label: 'Ожидающие',
        icon: 'icon-clock',
        route: {name: 'dashboard.material-submissions'},
        routes: ['dashboard.material-submissions'],
        status: MaterialSubmissionStatus.PENDING
    },
    {
        label: 'Принятые',
        icon: 'icon-tick',
        route: {name: 'dashboard.material-submissions.accepted'},
        routes: ['dashboard.material-submissions.accepted'],
        status: MaterialSubmissionStatus.ACCEPTED
    },
    {
        label: 'Отклонённые',
        icon: 'icon-small-cross',
        route: {name: 'dashboard.material-submissions.rejected'},
        routes: ['dashboard.material-submissions.rejected'],
        status: MaterialSubmissionStatus.REJECTED
    }
])

function loadMaterialSubmissions() {
    isLoading.value = true
    materialSubmissions.value = []

    axios.get('/api/material-submissions', {params: loadRequestData.value}).then((response) => {
        const responseData: MaterialSubmissionLoadResponseData = response.data
        if (responseData.success) {
            materialSubmissions.value = responseData.records!
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

function onTabChange(event: TabMenuChangeEvent) {
    const selectedStatus = tabMenuItems.value[event.tabIndex].status
    if (loadRequestData.value.status !== selectedStatus) {
        loadRequestData.value.status = selectedStatus
        loadRequestData.value.page = 1
        totalRecords.value = 0
        loadMaterialSubmissions()
    }
}

loadMaterialSubmissions()
</script>

<template>
    <section class="section">
        <div class="ld-shadow-text flex flex-col min-h-[100vh]">
            <div>
                <div class="flex md:justify-start justify-center">
                    <TabMenu
                        item-classes="ld-title-font justify-center h-[48px] min-w-[64px] md:text-[1rem] text-[14px] gap-1 lg:px-4 px-1"
                        item-label-classes="xs:flex hidden"
                        :items="tabMenuItems"
                        @tab-change="onTabChange"
                    />
                </div>

                <div v-if="isLoading" class="flex flex-col">
                    <div v-for="i in 5" :key="i" class="flex w-full gap-2 p-2">

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
                        v-if="materialSubmissions.length === 0"
                        class="flex flex-col justify-center items-center min-h-[480px] gap-6"
                    >
                        <p class="text-muted text-center text-[14px] max-w-[480px]">
                            Хм... Заявки не найдены.
                        </p>
                        <div class="mob wandering-trader flex justify-center items-center mb-4">
                            <div class="animation-wandering-trader h-[244px] w-[130px]"></div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col">
                        <MaterialSubmissionCard
                            v-for="materialSubmission in materialSubmissions"
                            :material-submission="materialSubmission"
                        />
                    </div>
                </div>
            </div>

        </div>
        <div class="flex sticky bottom-[0]" style="z-index: 1">
            <Paginator
                v-model="loadRequestData.page"
                class="ld-primary-background ld-fixed-background ld-primary-border-top h-[48px] w-full"
                :records-at-page="loadRequestData.per_page"
                :total-records="totalRecords"
                @update:model-value="loadMaterialSubmissions"
            />
        </div>
    </section>
</template>

<style scoped>

</style>
