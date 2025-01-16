<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {computed, ref} from 'vue'
import {getErrorMessageByCode} from '@/helpers'
import {useToastStore} from '@/stores/toast'
import {type MaterialComment} from '@/types'
import Paginator from '@/components/elements/Paginator.vue'
import MaterialCommentComponent from '@/components/material/comment/MaterialComment.vue'

const toastStore = useToastStore()

const comments = ref<MaterialComment[]>([])
const totalRecords = ref(0)

const page = ref(1)
const perPage = ref(8)

const loadRequestData = computed(() => ({
    page: page.value,
    per_page: perPage.value
}))

const isLoading = ref(false)

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
    <section class="dash-com-section section flex flex-col">
        <div class="ld-shadow-text flex flex-col min-h-[100vh] w-full">
            <div class="flex w-full">
                <div class="flex md:justify-start justify-center"/>

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
                    <div v-else class="prima flex flex-col max-w-[100%]" :class="{'paginator-refresh': isLoading}">
                        <div>
                            <p class="p-4">Комментариев: {{ comments.length }}</p>
                        </div>
                        <MaterialCommentComponent
                            v-for="comment in comments"
                            :comment="comment"
                            :id="`comment-${comment.id}`"
                            is-profile-comment
                            :key="comment.id"
                        />
                    </div>
                </template>
            </div>

        </div>
        <div class="flex sticky bottom-[0]" style="z-index: 1">
            <Paginator
                v-model="loadRequestData.page"
                class="ld-primary-background ld-fixed-background ld-primary-border-top h-[48px] w-full"
                :records-at-page="loadRequestData.per_page"
                :total-records="totalRecords"
                @update:model-value="loadComments"
            />
        </div>
    </section>
</template>

<style scoped>
/* =============== [ Медиа-Запрос { 1081px > ?px } ] =============== */

@media screen and (min-width: 1081px) {
    .dash-com-section {
        max-width: calc(100% - 318px);
    }
}

/* =============== [ Медиа-Запрос { 1181px > ?px } ] =============== */

@media screen and (min-width: 1181px) {
    .dash-com-section {
        max-width: calc(100% - 210px);
    }
}
</style>
