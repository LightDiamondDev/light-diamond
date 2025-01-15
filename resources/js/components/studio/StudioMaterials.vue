<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {reactive, ref} from 'vue'

import {getErrorMessageByCode} from '@/helpers'
import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'

import {type Material} from '@/types'
import Paginator from '@/components/elements/Paginator.vue'
import MaterialStudioCard from '@/components/material/MaterialStudioCard.vue'

import ShineButton from '@/components/elements/ShineButton.vue'

interface MaterialLoadResponseData {
    success: boolean
    message?: string
    errors?: object
    records?: Material[]
    pagination?: {
        total_records: number
        current_page: number
        total_pages: number
    }
}

const authStore = useAuthStore()
const toastStore = useToastStore()

const materials = ref<Material[]>([])
const totalRecords = ref(0)

const loadRequestData = reactive({
    page: 1,
    per_page: 8
})

const isLoading = ref(false)

function loadMaterials() {
    isLoading.value = true
    materials.value = []

    axios.get(`/api/users/${authStore.id}/materials`, {params: loadRequestData}).then((response) => {
        const responseData: MaterialLoadResponseData = response.data
        if (responseData.success) {
            materials.value = responseData.records!
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

loadMaterials()
</script>

<template>
    <section class="section">
        <div class="ld-shadow-text flex flex-col min-h-[100vh]">
            <div class="flex flex-col">
                <div class="flex md:justify-start justify-end gap-2 p-2">

                    <ShineButton
                        as="RouterLink"
                        class-preset="ld-title-font gap-1 px-6 py-0.5"
                        label="Создать"
                        icon="icon-brilliant"
                        :to="{ name: 'create-material' }"
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
                        v-if="materials.length === 0"
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
                        <MaterialStudioCard v-for="material in materials" :material="material"/>
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
                @update:model-value="loadMaterials"
            />
        </div>
    </section>
</template>

<style scoped>

</style>
