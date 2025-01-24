<script setup lang='ts'>
import axios, {type AxiosError} from 'axios'
import {computed, ref} from 'vue'

import {getErrorMessageByCode} from '@/helpers'
import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'

import MaterialCommentComponent from '@/components/material/comment/MaterialComment.vue'
import {type MaterialComment, type User} from '@/types'

import Paginator from '@/components/elements/Paginator.vue'

const authStore = useAuthStore()
const toastStore = useToastStore()

const comments = ref<MaterialComment[]>([])

const records = ref<User[]>([])
const totalRecords = ref(0)

const isLoading = ref(false)

const page = ref(1)
const perPage = ref(8)

const loadRequestData = computed(() => ({
    page: page.value,
    per_page: perPage.value
}))

function loadComments() {
    isLoading.value = true
    comments.value = []

    axios.get(`/api/material-comments`, {params: loadRequestData}).then((response) => {
        const responseData: CommentLoadResponseData = response.data
        if (responseData.success) {
            comments.value = responseData.records!
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

loadComments()

</script>

<template>
    <div class="section ld-shadow-text flex flex-col h-full">
        <div v-if="isLoading" class="flex flex-col gap-6 px-2">
            <div class="skeleton transfusion flex max-w-[192px] h-4 w-full mt-4 mx-2"/>
            <div v-for="i in 5" :key="i" class="flex w-full gap-2 p-2">
                <div class="flex flex-col w-full gap-3">
                    <p class="skeleton transfusion flex max-w-[512px] h-4 mb-2"/>
                    <div class="flex flex-wrap items-center md:text-[12px] text-[10px] gap-2 mt-0.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="skeleton transfusion flex h-10 w-10 mr-2"/>
                            <p class="skeleton transfusion flex h-4 w-[112px]"/>
                            <p class="skeleton transfusion flex h-4 w-[96px]"/>
                        </div>
                    </div>
                    <div class="flex flex-col gap-6 ml-[3.5rem]">
                        <div class="flex flex-col gap-3">
                            <div class="skeleton transfusion flex h-3 max-w-[85%] w-full"/>
                            <div class="skeleton transfusion flex h-3 max-w-[70%] w-full"/>
                            <div class="skeleton transfusion flex h-3 max-w-[55%] w-full"/>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="skeleton transfusion flex h-8 w-8"/>
                            <p class="skeleton transfusion flex h-4 w-[32px] mr-4"/>
                            <p class="skeleton transfusion flex h-4 max-w-[192px] w-full"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <template v-else>
            <div
                v-if="comments.length === 0"
                class="flex flex-col justify-center items-center min-h-[480px] gap-6"
            >
                <p class="text-muted text-center text-[14px] max-w-[480px]">
                    Хм... Комментарии не найдены.
                </p>
                <div class="mob wandering-trader flex justify-center items-center mb-4">
                    <div class="animation-wandering-trader h-[244px] w-[130px]"></div>
                </div>
            </div>
            <div v-else class="flex flex-col">
                <p class="p-4">Комментариев: {{ comments.length }}</p>
                <div class="data-table flex flex-col min-h-[100vh] overflow-x-auto">
                    <MaterialCommentComponent
                        v-for="comment in comments"
                        :comment="comment"
                        :id="`comment-${comment.id}`"
                        is-profile-comment
                        :key="comment.id"
                    />
                </div>
            </div>
        </template>
        <div class="flex sticky bottom-[0]" style="z-index: 1">
            <Paginator
                v-model="loadRequestData.page"
                class="ld-primary-background ld-fixed-background ld-primary-border-top h-[48px] w-full"
                :records-at-page="loadRequestData.per_page"
                :total-records="totalRecords"
                @update:model-value="loadComments"
            />
        </div>
    </div>
</template>
