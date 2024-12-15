<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {reactive, ref} from 'vue'

import {getErrorMessageByCode} from '@/helpers'
import {useAuthStore} from '@/stores/auth'
import {type PostComment} from '@/types'

import Paginator from '@/components/elements/Paginator.vue'
import PostCommentComponent from '@/components/post/comment/PostComment.vue'

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
const comments = ref<PostComment[]>([])
const totalRecords = ref(0)

const isLoading = ref(false)

function loadComments() {
    isLoading.value = true
    comments.value = []

    axios.get(`/api/users/${props.userId}/comments`, {params: loadRequestData}).then((response) => {
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
    <div>
        <div v-if="isLoading" class="flex flex-col">
            <div v-for="i in 5" class="flex gap-2">
                <div class="skeleton transfusion h-[2.5rem] min-w-[2.5rem]"/>
                <div class="flex flex-col w-full gap-4">
                    <div class="flex gap-2 items-center">
                        <div class="skeleton transfusion h-[1.2rem] w-[10rem]"/>
                        <div class="skeleton transfusion h-[1.2rem] w-[5rem]"/>
                        <div class="skeleton transfusion h-[1rem] w-[5rem]"/>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="skeleton transfusion h-[1rem] w-[85%]"/>
                        <div class="skeleton transfusion h-[1rem] w-[80%]"/>
                        <div class="skeleton transfusion h-[1rem] w-[75%]"/>
                    </div>
                    <div class="flex gap-2 items-center">
                        <div class="flex gap-4 items-center">
                            <div class="skeleton transfusion h-[2rem] w-[2rem]"/>
                            <div class="skeleton transfusion h-[1.5rem] w-[6rem]"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <div
                v-if="comments.length < 1"
                class="flex flex-col justify-center items-center min-h-[480px] gap-6"
            >
                <p v-if="authStore.id === userId" class="text-muted text-center text-[14px] max-w-[480px]">
                    <span>Пользователи пока не комментировали Ваше творчество — всё впереди! ;D</span>
                </p>
                <p v-else class="text-muted text-center text-[14px] max-w-[480px]">
                    <span>Пользователи пока не комментировали Материалы </span>
                    <span>{{ username }}</span>
                    <span>.</span>
                </p>
                <div class="mob wandering-trader flex justify-center items-center mb-4">
                    <div class="animation-wandering-trader h-[244px] w-[130px]"/>
                </div>
            </div>
            <div v-else class="flex flex-col" :class="{'paginator-refresh': isLoading}">
                <PostCommentComponent
                    v-for="comment in comments"
                    :comment="comment"
                    :id="`comment-${comment.id}`"
                    is-profile-comment
                    :key="comment.id"
                />
            </div>
        </div>
        <Paginator
            v-if="comments.length > 0"
            v-model="loadRequestData.page"
            class="ld-primary-background ld-fixed-background ld-primary-border-right ld-primary-border-left
            ld-primary-border-top ld-primary-text h-[48px] w-full flex sticky bottom-[0]"
            :records-at-page="loadRequestData.per_page"
            :total-records="totalRecords"
            @update:model-value="loadComments"
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
