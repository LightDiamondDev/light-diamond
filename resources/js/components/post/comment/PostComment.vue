<script setup lang="ts">
import axios, {type AxiosError, type AxiosResponse} from 'axios'
import {computed, nextTick, onMounted, onUnmounted, type PropType, reactive, ref} from 'vue'

import {
    getAppUrl,
    getErrorMessageByCode,
    getFullPresentableDate,
    getRelativeDate
} from '@/helpers'
import {useAuthStore} from '@/stores/auth'

import PostCommentEditor from '@/components/post/comment/PostCommentEditor.vue'
import {type PostComment, UserRole} from '@/types'

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

const props = defineProps({
    comment: {
        type: Object as PropType<PostComment>,
        required: true
    }
})

const emit = defineEmits<{
    (e: 'submitReply', id: bigint, content: string): void,
    (e: 'remove'): void,
}>()


const globalModalStore = useGlobalModalStore()
const toastStore = useToastStore()
const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const editData = ref<EditedComment | null>(null)
const replyData = ref<EditedComment | null>(null)
const comment = reactive(props.comment!)
const commentHTMLRef = ref<HTMLHtmlElement>()
const actionsMenu = ref()

const isEditable = computed(() => authStore.id === comment.user_id && !isEditTimeExpired.value)
const isRemovable = computed(() => authStore.isModerator || (authStore.id === comment.user_id && !isEditTimeExpired.value))
const isEditTimeExpired = computed(() => {
    const dateDiffInMinutes = (new Date().getTime() - new Date(comment.created_at).getTime()) / (1000 * 60)
    return dateDiffInMinutes >= 30
})
const isSubmittingEdit = ref(false)
const isSubmittingReply = ref(false)
const isHighlighted = ref(false)
const isExpanded = ref(false)

const actionsMenuItems = computed<MenuItem[]>(() => [
    {
        action: () => editData.value = { content: comment.content },
        label: 'Отредактировать',
        icon: 'icon-small-pencil',
        visible: isEditable.value,
    },
    {
        label: 'Удалить',
        icon: 'icon-small-cross',
        visible: isRemovable.value,
        action: remove,
    },
    {
        label: 'Копировать ссылку',
        icon: 'icon-share',
        action: copyUrl,
    }
])

const removeAfterEachRouteHook = router.afterEach((to, from) => {
    if (to.path === from.path) {
        updateHighlighting()
    }
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
            toastStore.info('Комментарий был изменён.', 'Комментарии')
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

function submitReply() {
    replyData.value!.errors = {}
    isSubmittingReply.value = true

    axios.post(
        `/api/posts/${comment.post_id}/comments`,
        {parent_comment_id: comment.id, content: replyData.value!.content}
    ).then((response) => {
        if (response.data.success) {
            if (comment.user?.username === authStore.username) {
                toastStore.info('Вы ответили на свой комментарий.', 'Комментарии')
            } else {
                toastStore.info('Вы ответили на комментарий Пользователя ' + comment.user?.username + '.', 'Комментарии')
            }
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

function remove() {
    axios.delete(`/api/post-comments/${comment.id}`,).then((response) => {
        if (response.data.success) {
            toastStore.warning('Комментарий был удалён!', 'Комментарии')
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

onMounted(() => {
    updateHighlighting()
})

onUnmounted(() => {
    removeAfterEachRouteHook()
    document.removeEventListener('mousedown', removeHighlighting)
})

function copyUrl() {
    const url = new URL(
        router.resolve(
            {name: 'post', params: {slug: comment.post!.slug}, hash: getCommentUrlHash(comment.id)}
        ).fullPath,
        getAppUrl()
    ).href

    navigator.clipboard.writeText(url)
    toastStore.info('Ссылка на комментарий скопирована.', 'Комментарии')
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
function verifyCommentAnswerOld(content: string) {
    let parser = new DOMParser();
    let result = content.replace(/<img[^>]+(>|$)/g, '<span class="icon-image icon"></span>')
    result = result.replace(/<blockquote[^>]+(>|$)/g, '<span>[Цитата]</span>')
    result = result.replace(/<code[^>]+(>|$)/g, '<span>[Код]</span>')
    return parser.parseFromString(result, 'text/html')
}

function verifyCommentAnswer(content: string) {
    return content.replace(/<blockquote>(.+)<\/blockquote>/g, '<span class="icon-quote icon flex"></span>')
    .replace(/(<pre>)([\S\s]*?)(<\/pre>)/g, '<span class="icon-code icon flex"></span>')
    .replace(/<img[^>]+(>|$)/g, '<span class="icon-image icon flex"></span>')
    .replace(/<ul>(.+)<\/ul>/g, '<span class="icon-unordered-list icon flex"></span>')
    .replace(/<ol>(.+)<\/ol>/g, '<span class="icon-ordered-list icon flex"></span>')
    .replace(/<br>/g, ' ')
}
</script>

<template>
    <div class="flex gap-2 flex-1">
        <div v-if="editData" class="flex flex-col gap-2 flex-1 min-w-0">
            <p class="text-sm">Редактировать комментарий</p>
            <PostCommentEditor v-model="editData.content" class="post-comment-editor"/>
            <div class="flex flex-col xs:flex-row w-full self-start gap-4">
                <Button
                    class="confirm max-h-[72px] max-w-[200px] w-[80%]"
                    :loading="isSubmittingReply"
                    @click="submitEdit"
                    icon="icon-comment"
                    label="Отправить"
                />
                <Button
                    class="cancel max-h-[72px] max-w-[200px] w-[80%]"
                    @click="editData = null"
                    icon="icon-small-cross"
                    label="Отмена"
                />
            </div>
        </div>
        <template v-else>
            <div :class="{'post-comment-highlighted': isHighlighted}" class="post-comment-body post-comment flex w-full">
                <div class="post-comment-inner flex w-full gap-2 xs:p-4 p-2">
                    <div class="flex relative">
                        <UserAvatar
                            v-if="authStore.isAuthenticated"
                            border-class-list="md:h-10 md:min-w-10 h-8 min-w-8"
                            icon-class-list="md:h-7 md:min-w-7 h-6 min-w-6"
                            :user="comment.user!"
                        />
                        <span
                            :class="{
                                'icon-charoit-crown': comment.user?.role === UserRole.ADMIN,
                                'icon-emerald-dagger': comment.user?.role === UserRole.MODERATOR
                            }"
                            class="icon flex absolute
                                xs:h-8 xs:w-8 h-6 w-6
                                xs:right-[-12px] xs:top-[-12px]
                                right-[-8px] top-[-8px]"
                        />
                    </div>
                    <div class="flex flex-col gap-1 flex-1 min-w-0">
                        <div
                            class="comment-header flex justify-between items-center transition-all duration-300 gap-2"
                        >
                            <div class="flex md:items-center items-end gap-2">
                                <p class="comment-username flex flex-wrap flex-row gap-3">
                                    <span class="flex gap-2">
                                        <span class="nickname text-[var(--primary-color)]">{{ comment.user!.username }}</span>
                                        <span
                                            v-if="comment.user?.username === comment.post?.version?.author?.username"
                                            class="ld-lightgray-text flex items-center text-[12px] pl-1"
                                        >
                                            Автор
                                        </span>
                                    </span>
                                    <span
                                        v-tooltip.left="`${getRelativeDate(comment.created_at)} (${getFullPresentableDate(comment.created_at)})`"
                                        class="flex items-center ld-lightgray-text text-[12px]"
                                    >
                                        {{ getRelativeDate(comment.created_at) }}
                                    </span>
                                </p>
                                <p
                                    v-if="comment.created_at !== comment.updated_at"
                                    v-tooltip.top="`Изменено ${getRelativeDate(comment.updated_at)} (${getFullPresentableDate(comment.updated_at)})`"
                                    class="text-xs ld-lightgray-text cursor-pointer mt-0"
                                >
                                    [ред.]
                                </p>
                            </div>
                            <span class="icon-ellipsis icon cursor-pointer" @click="actionsMenu!.toggle"/>
                        </div>
                        <div class="flex flex-col">
                            <span v-if="comment.parent_comment?.parent_comment_id">
                                <RouterLink
                                    :to="{name: 'post', params: {slug: comment.post!.slug},
                                        hash: getCommentUrlHash(comment.parent_comment_id!),
                                        replace: true}"
                                    class="mention ld-tinted-background darker left ld-secondary-text
                                        flex flex-col sm:text-[14px] text-[12px] mb-2 pl-3 py-2"
                                >
                                    <span class="text-[var(--primary-color)]">{{ comment.parent_comment!.user!.username }}</span>
                                    <span class="ld-primary-text comment-answer truncate whitespace-nowrap inline-block"
                                          v-html="verifyCommentAnswer(comment.parent_comment!.content)"
                                    />
                                </RouterLink>
                            </span>
                            <span
                                v-html="comment.content"
                                class="post-comment-html post-comment"
                                :class="{'full': isExpanded}"
                                ref="commentHTMLRef"
                            />
                            <button
                                v-if="commentHTMLRef && commentHTMLRef.offsetHeight > 280"
                                class="ld-tinted-background darker left flex items-center min-h-[48px] px-4"
                                @click="isExpanded = !isExpanded"
                            >
                                Читать далее
                            </button>
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
                            <div class="text-sm flex gap-1 mt-2">
                                <p>Ответить</p>
                                <p class="text-[var(--primary-color)] mt-0">{{ comment.user!.username }}</p>
                            </div>
                            <PostCommentEditor v-model="replyData.content" class="post-comment-editor"/>
                            <div class="flex flex-col xs:flex-row w-full self-start gap-4">
                                <Button
                                    class="confirm max-h-[72px] xl:max-w-[200px] w-full"
                                    action-button-classes="w-full"
                                    :loading="isSubmittingReply"
                                    @click="submitReply"
                                    icon="icon-comment"
                                    label="Отправить"
                                />
                                <Button
                                    class="cancel max-h-[72px] xl:max-w-[200px] w-full"
                                    @click="replyData = null"
                                    icon="icon-small-cross"
                                    label="Отмена"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <Menu
            class="post-comment-actions ld-primary-background ld-primary-border"
            item-classes="case-font text-[0.8rem] min-h-[32px] p-1"
            :items="actionsMenuItems"
            :align-right="true"
            style="z-index: 1"
            ref="actionsMenu"
        />
    </div>
</template>

<style>
.comments .icon-border {
    height: 42px;
    width: 42px;
}
.comments .icon-border img {
    height: 28px;
}
.comment-username .nickname {
    font-size: 16px;
}
.comment-answer {
    height: 32px;
}
.comment-answer * {
    display: inline-block;
    align-content: center;
    height: 32px;
}
.comment-answer .icon {
    min-height: 32px;
    min-width: 32px;
}
.comment-answer p {
    margin-top: 0;
}
</style>

<style scoped>
.tooltip::before {
    min-width: 200px;
    left: -4rem;
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
