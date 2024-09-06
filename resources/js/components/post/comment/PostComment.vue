<script setup lang="ts">
import axios, {type AxiosError, type AxiosResponse} from 'axios'
import {computed, onMounted, onUnmounted, type PropType, reactive, ref} from 'vue'

import {getAppUrl, getErrorMessageByCode, getFullDate, getFullPresentableDate, getRelativeDate} from '@/helpers'
import {useAuthStore} from '@/stores/auth'

import PostCommentEditor from '@/components/post/comment/PostCommentEditor.vue'
import {type PostComment} from '@/types'

import Button from '@/components/elements/Button.vue'
import EffectIcon from '@/components/elements/EffectIcon.vue'
import Menu, {type MenuItem} from '@/components/elements/Menu.vue'
import UserAvatar from '@/components/user/UserAvatar.vue'

import {useGlobalModalStore} from '@/stores/global-modal'
import {useRoute, useRouter} from 'vue-router'
import {useToastStore} from '@/stores/toast'

interface EditedComment {
    errors?: { [key: string]: string[] }
    content?: string
}

const emit = defineEmits<{
    (e: 'submitReply', id: bigint, content: string): void,
    (e: 'remove'): void,
}>()

const props = defineProps({
    comment: {
        type: Object as PropType<PostComment>,
        required: true
    }
})

const authStore = useAuthStore()
const globalModalStore = useGlobalModalStore()
const toastStore = useToastStore()
const route = useRoute()

const actionsMenu = ref()
const comment = reactive(props.comment!)
const editData = ref<EditedComment | null>(null)
const replyData = ref<EditedComment | null>(null)
const router = useRouter()

const isSubmittingEdit = ref(false)
const isSubmittingReply = ref(false)
const isHighlighted = ref(false)

const isEditable = computed(() => authStore.id === comment.user_id && !isEditTimeExpired.value)

const isEditTimeExpired = computed(() => {
    const dateDiffInMinutes = (new Date().getTime() - new Date(comment.created_at).getTime()) / (1000 * 60)
    return dateDiffInMinutes >= 30
})

const isRemovable = computed(() => authStore.isModerator || (authStore.id === comment.user_id && !isEditTimeExpired.value))

const actionsMenuItems = computed<MenuItem[]>(() => [
    {
        label: 'Отредактировать',
        icon: 'icon-small-pencil',
        visible: isEditable.value,
        command: () => editData.value = { content: comment.content },
    },
    {
        label: 'Удалить',
        icon: 'icon-small-cross',
        visible: isRemovable.value,
        command: remove,
    },
    {
        label: 'Копировать ссылку',
        icon: 'icon-share',
        command: copyUrl,
    }
])

const removeAfterEachRouteHook = router.afterEach((to, from) => {
    if (to.path === from.path) {
        updateHighlighting()
    }
})

onMounted(() => {
    updateHighlighting()
})

onUnmounted(() => {
    removeAfterEachRouteHook()
    document.removeEventListener('mousedown', removeHighlighting)
})

function updateHighlighting() {
    isHighlighted.value = route.hash === getCommentUrlHash(comment.id)
    if (isHighlighted.value) {
        document.addEventListener('mousedown', removeHighlighting, {once: true})
    }
}

function removeHighlighting() {
    isHighlighted.value = false
}

function getCommentUrlHash(commentId: bigint): string {
    return `#comment-${commentId}`
}

function submitEdit() {
    editData.value!.errors = {}
    isSubmittingEdit.value = true

    axios.patch(
        `/api/post-comments/${comment.id}`, {content: editData.value!.content}
    ).then((response) => {
        if (response.data.success) {
            comment.updated_at = new Date().toISOString()
            comment.content = editData.value!.content!
            editData.value = null
            toastStore.success('Комментарий успешно изменён!')
        } else {
            if (response.data.errors) {
                editData.value!.errors = {} = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isSubmittingEdit.value = false
    })
}

function remove() {
    axios.delete(`/api/post-comments/${comment.id}`,).then((response) => {
        if (response.data.success) {
            toastStore.success('Комментарий успешно удалён!')
            emit('remove')
            actionsMenu.value.hide()
        } else {
            if (response.data.errors) {
                replyData.value!.errors = {} = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    })
}

function submitReply() {
    replyData.value!.errors = {}
    isSubmittingReply.value = true

    axios.post(
        `/api/posts/${comment.post_id}/comments`,
        {parent_comment_id: comment.id, content: replyData.value!.content}
    ).then((response) => {
        if (response.data.success) {
            toastStore.success('Ответ успешно отправлен!')
            emit('submitReply', response.data.id, replyData.value!.content!)
            replyData.value = null
        } else {
            if (response.data.errors) {
                replyData.value!.errors = {} = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isSubmittingReply.value = false
    })
}

function copyUrl() {
    const url = new URL(
        router.resolve(
            {name: 'post', params: {slug: comment.post!.slug}, hash: getCommentUrlHash(comment.id)}
        ).fullPath,
        getAppUrl()
    ).href

    navigator.clipboard.writeText(url)
    toastStore.success('Ссылка на комментарий скопирована!')
}

function toggleLike() {
    comment.is_liked = !comment.is_liked
    comment.like_count += comment.is_liked ? 1 : -1
}

function onLikeClick() {
    if (!authStore.isAuthenticated) {
        globalModalStore.isAuth = true
        return
    }

    toggleLike()

    const apiUrl = `/api/post-comments/${comment.id}/likes`
    const responseCallback = function (response: AxiosResponse) {
        if (!response.data.success) {
            toggleLike()
            toastStore.error(response.data.message)
        }
    }
    const errorCallback = function (error: AxiosError) {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }

    if (comment.is_liked) {
        axios.post(apiUrl).then(responseCallback).catch(errorCallback)
    } else {
        axios.delete(apiUrl).then(responseCallback).catch(errorCallback)
    }
}

function onReplyClick() {
    if (!authStore.isAuthenticated) {
        globalModalStore.isAuth = true
        return
    }
    replyData.value = {}
}
</script>

<template>
    <div class="flex gap-2 flex-1">
        <div v-if="editData" class="flex flex-col gap-2 flex-1 min-w-0">
            <p class="text-sm">Редактировать комментарий</p>
            <PostCommentEditor v-model="editData.content" class="post-comment-editor"/>
            <div class="flex flex-col xs:flex-row w-full xs:w-auto self-start gap-1">
                <Button label="Отмена" outlined severity="secondary" size="small" @click="editData = null"/>
                <Button label="Отправить" outlined size="small" :loading="isSubmittingEdit" @click="submitEdit"/>
            </div>
        </div>
        <template v-else>
            <UserAvatar :user="comment.user" class="min-w-[2rem]"/>
            <div class="flex flex-col gap-2 flex-1 min-w-0 md:mr-8">
                <div
                    class="comment-header flex justify-between items-center transition-all duration-300 gap-2"
                    :class="{'border-transparent': !isHighlighted}"
                >
                    <p class="comment-username flex">
                        <span class="nickname text-[var(--primary-color)]">{{ comment.user!.username }}</span>
                        <span
                            v-tooltip.left="`${getRelativeDate(comment.created_at)} (${getFullPresentableDate(comment.created_at)})`"
                            class="flex items-center text-xs"
                        >
                            {{ getRelativeDate(comment.created_at) }}
                        </span>
                    </p>
                    <p
                        v-if="comment.created_at !== comment.updated_at"
                        v-tooltip.left="`Изменено ${getRelativeDate(comment.updated_at)} (${getFullPresentableDate(comment.updated_at)})`"
                        class="text-xs"
                    >
                        (ред.)
                    </p>
                    <span class="icon-ellipsis icon cursor-pointer" @click="actionsMenu!.toggle"/>
                </div>
                <div class="flex flex-col gap-1">
                    <span v-if="comment.parent_comment?.parent_comment_id">
                        <RouterLink
                            :to="{name: 'post', params: {slug: comment.post!.slug}, hash: getCommentUrlHash(comment.parent_comment_id!), replace: true}"
                            class="text-[var(--primary-color)]"
                        >
                            @{{ comment.parent_comment!.user!.username }}
                        </RouterLink>
                        <span>,</span>
                    </span>

                    <span v-html="comment.content" class="post-comment-html post-comment-content"></span>
                </div>
                <div class="flex gap-3 items-center -ml-2">
                    <button
                        :class="{ 'active': comment.is_liked }"
                        class="set-mark flex items-center"
                        @click="onLikeClick"
                    >
                        <EffectIcon icon="icon-heart"/>
                        <span class="counter flex p-1">{{ comment.like_count }}</span>
                    </button>
                    <button class="post-comment-reply-button" @click="onReplyClick">
                        <span class="text text-sm p-2">Ответить</span>
                    </button>
                </div>
                <div v-if="replyData" class="flex flex-col gap-2" @keydown.esc="() => replyData = null">
                    <div class="text-sm flex gap-1">
                        <p>Ответить</p>
                        <p class="font-semibold text-[var(--primary-color)]">{{ comment.user!.username }}</p>
                    </div>
                    <PostCommentEditor v-model="replyData.content" class="post-comment-editor"/>
                    <div class="flex flex-col xs:flex-row w-full self-start gap-4">
                        <Button
                            class="cancel max-h-[72px] max-w-[200px]"
                            @click="replyData = null"
                            icon="icon-small-cross"
                            text="Отмена"
                        />
                        <Button
                            class="max-h-[72px] max-w-[200px]"
                            :loading="isSubmittingReply"
                            @click="submitReply"
                            icon="icon-comment"
                            text="Отправить"
                        />
                    </div>
                </div>
            </div>
        </template>

        <Menu
            class="post-comment-actions"
            :model="actionsMenuItems"
            :items="actionsMenuItems"
            style="z-index: 1"
            ref="actionsMenu"
        />
    </div>
</template>

<style>
.post-comment-actions .item-button {
    min-height: 64px;
}
.comments .icon-border {
    height: 42px;
    width: 42px;
}
.comments .icon-border img {
    height: 28px;
}
.comment-username {
    gap: 1rem;
}
.comment-username .nickname {
    font-size: 16px;
}
</style>

<style scoped>
.post-comment-editor {
    padding: 1rem 3rem;
}
/* =============== [ Медиа-Запрос { ?px < 769px } ] =============== */

@media screen and (max-width: 768px) {
    .post-comment-editor {
        padding: .5rem;
    }
}
/* =============== [ Медиа-Запрос { ?px < 451px } ] =============== */

@media screen and (max-width: 450px) {
    .comment-username {
        flex-direction: column;
        gap: 2px;
    }
    .comment-username .nickname {
        font-size: 14px;
    }
    .post-comment-html {
        margin-top: .5rem;
    }
}
</style>
