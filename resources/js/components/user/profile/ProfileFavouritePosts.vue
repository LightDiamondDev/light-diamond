<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {reactive, ref} from 'vue'

import {getErrorMessageByCode} from '@/helpers'
import {useAuthStore} from '@/stores/auth'
import {type Post} from '@/types'

import Paginator from '@/components/elements/Paginator.vue'
import ProfilePostCard from '@/components/user/profile/ProfilePostCard.vue'

const props = defineProps({
    userId: {
        type: Number,
        required: true
    },
    username: {
        type: String,
        default: 'Steve'
    }
})

const loadRequestData = reactive({
    page: 1,
    per_page: 8
})

const authStore = useAuthStore()
const posts = ref<Post[]>([])
const totalRecords = ref(0)

const isLoading = ref(false)

function loadPosts() {
    isLoading.value = true
    posts.value = []

    axios.get(`/api/users/${props.userId}/favourite-posts`, {params: loadRequestData}).then((response) => {
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

loadPosts()
</script>

<template>
    <div>
        <div v-if="isLoading" class="flex flex-col">
            <div v-for="i in 5" class="flex w-full gap-2 p-2">
                <div class="skeleton transfusion flex
                            sm:h-[112px] sm:max-w-[196px] sm:min-w-[196px]
                            xs:h-[76px] xs:max-w-[132px] xs:min-w-[132px]
                            h-[58px] max-w-[100px] min-w-[100px]
                            "
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
                v-if="posts.length < 1"
                class="flex flex-col justify-center items-center min-h-[480px] gap-6"
            >
                <p v-if="authStore.id === userId" class="text-muted text-center text-[14px] max-w-[480px]">
                    <span>У Вас пока ещё нет любимых Постов, ещё найдёте! ;D</span>
                </p>
                <p v-else class="text-muted text-center text-[14px] max-w-[480px]">
                    <span>У </span>
                    <span>{{ username }}</span>
                    <span> пока ещё нет любимых Постов.</span>
                </p>
                <div class="mob wandering-trader flex justify-center items-center mb-4">
                    <div class="animation-wandering-trader h-[244px] w-[130px]"/>
                </div>
            </div>
            <div v-else class="flex flex-col" :class="{'paginator-refresh': isLoading}">
                <ProfilePostCard v-for="post in posts" :post="post"/>
            </div>
        </div>
        <Paginator
            v-if="posts.length > 0"
            v-model="loadRequestData.page"
            class="ld-primary-background ld-fixed-background ld-primary-border-right ld-primary-border-left
            ld-primary-border-top ld-primary-text h-[48px] w-full flex sticky bottom-[0]"
            :records-at-page="loadRequestData.per_page"
            :total-records="totalRecords"
            @update:model-value="loadPosts"
        />
    </div>
</template>

<style>
.tooltip::before {
    top: -2rem;
}
.moder.tooltip::before {
    height: 2.5rem;
}
</style>

<style scoped>
.preview-wrap,
.preview {
    object-position: center;
    aspect-ratio: 16 / 9;
    object-fit: cover;
}
.post-version-card:hover .preview {
    transform: scale(1.2);
}
</style>
