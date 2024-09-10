<script setup lang="ts">
import axios, {type AxiosError, type AxiosResponse} from 'axios'

import {computed, nextTick, onUnmounted, onUpdated, reactive, ref} from 'vue'

import {useAuthStore} from '@/stores/auth'
import {useGlobalModalStore} from '@/stores/global-modal'
import {useToastStore} from '@/stores/toast'
import {useRoute, RouterLink} from 'vue-router'

import {
    changeTitle,
    convertDateToString,
    getErrorMessageByCode, getFullPresentableDate,
    getRelativeDate
} from '@/helpers'

import {type Post, type PostComment} from '@/types'

import PostCommentComponent from '@/components/post/comment/PostComment.vue'
import PostCommentEditor from '@/components/post/comment/PostCommentEditor.vue'

import Button from '@/components/elements/Button.vue'

import UserAvatar from '@/components/user/UserAvatar.vue'

import ProcessingMovingItems from '@/components/elements/ProcessingMovingItems.vue'
import EffectIcon from '@/components/elements/EffectIcon.vue'

const props = defineProps({
    slug: {
        type: String,
        required: true
    }
})

interface EditedComment {
    errors?: { [key: string]: string[] }
    content?: string
}

const authStore = useAuthStore()
const globalModalStore = useGlobalModalStore()
const toastStore = useToastStore()
const route = useRoute()

const isLoading = ref(true)
const post = ref<Post>()
const comments = ref<PostComment[]>()
const isLoadingComments = ref(true)
const isSubmittingNewComment = ref(false)
const commentsBlock = ref<Element>()
const newComment = reactive<EditedComment>({})
const commentsBlockObserver = new IntersectionObserver(observeCommentsBlock)

const rootComments = computed(() => comments.value?.filter((comment) => !comment.parent_comment_id))

onUpdated(() => {
    if (commentsBlock.value && !comments.value) {
        commentsBlockObserver.observe(commentsBlock.value!)
    }
})

onUnmounted(() => {
    commentsBlockObserver.disconnect()
})

loadPost()

function updateTitle() {
    nextTick(() => {
        changeTitle(post.value!.version!.title!)
    })
}

function observeCommentsBlock([entry]: IntersectionObserverEntry[]) {
    if (entry.isIntersecting) {
        loadComments()
    }
}

function loadPost() {
    axios.get(`/api/posts/${props.slug}`).then((response) => {
        const postData: Post = response.data

        if (Object.keys(postData).length !== 0) {
            post.value = postData
            updateTitle()

            if (route.hash === '#comments' || route.hash.match(/^#comment-\d+$/)) {
                loadComments()
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

function loadComments() {
    commentsBlockObserver.disconnect()

    axios.get(`/api/posts/${post.value!.id}/comments`).then((response) => {
        comments.value = response.data.records
        updatePostCommentCount()
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoadingComments.value = false
    })
}

function toggleLike() {
    post.value!.is_liked = !post.value!.is_liked
    post.value!.like_count += post.value!.is_liked ? 1 : -1
}

function onLikeClick() {
    if (!authStore.isAuthenticated) {
        globalModalStore.isAuth = true
        return
    }

    toggleLike()

    const apiUrl = `/api/posts/${post.id}/likes`
    const responseCallback = function (response: AxiosResponse) {
        if (!response.data.success) {
            toggleLike()
            toastStore.error(response.data.message)
        }
    }
    const errorCallback = function (error: AxiosError) {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }

    if (post.value!.is_liked) {
        axios.post(apiUrl).then(responseCallback).catch(errorCallback)
    } else {
        axios.delete(apiUrl).then(responseCallback).catch(errorCallback)
    }
}

function submitNewComment() {
    newComment.errors = {}
    isSubmittingNewComment.value = true

    axios.post(`/api/posts/${post.value!.id}/comments`, {content: newComment.content}).then((response) => {
        if (response.data.success) {
            toastStore.success('Комментарий успешно отправлен.')
            addComment(response.data.id, newComment.content!)
            newComment.content = ''
        } else {
            if (response.data.errors) {
                newComment.errors = {} = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isSubmittingNewComment.value = false
    })
}

function addComment(id: bigint, content: string, parentComment: PostComment | null = null): void {
    const nowDate = convertDateToString(new Date())
    const comment: PostComment = {
        id: id,
        post_id: post.value!.id,
        parent_comment_id: parentComment?.id,
        parent_comment: parentComment,
        user_id: authStore.id!,
        user: authStore.user,
        content: content,
        like_count: 0,
        is_liked: false,
        created_at: nowDate,
        updated_at: nowDate
    }

    comments.value!.unshift(comment)
    updatePostCommentCount()
}

function removeComment(comment: PostComment) {
    comments.value = comments.value!.filter(v => v !== comment && !isDescendantComment(v, comment.id))
    updatePostCommentCount()
}

function updatePostCommentCount() {
    post.value!.comment_count = comments.value!.length
}

function isDescendantComment(comment: PostComment, ancestorId: bigint): boolean {
    while (comment?.parent_comment_id !== undefined) {
        if (comment.parent_comment_id === ancestorId) return true
        comment = comments.value!.find(c => c.id === comment.parent_comment_id)!
    }
    return false
}

function getDescendantComments(ancestorId: bigint): PostComment[] {
    return comments.value!
        .filter(comment => isDescendantComment(comment, ancestorId))
        .sort((a, b) => a.created_at.localeCompare(b.created_at))
}

function onNewCommentEditorClick() {
    if (!authStore.isAuthenticated) {
        globalModalStore.isAuth = true
    }
}
</script>

<template>
    <div v-if="isLoading" class="flex justify-center items-center">
        <ProcessingMovingItems/>
    </div>
    <template v-else>
        <section v-if="post" class="page-container flex flex-col justify-between items-center relative">

            <div class="content w-full">

                <div class="interface flex flex-col">

                    <h1 class="material-name text-center mt-4">{{ post!.version!.title }}</h1>

                    <img alt="" class="post-cover w-full" :src="post.version!.cover_url">

                    <div v-html="post.version!.content" class="post-content"/>

                </div>
            </div>

            <div class="base-post-interaction page-container flex flex-col-reverse xl:flex-row xl:justify-between items-center static xl:fixed">

                <aside class="left-post-interaction flex flex-col items-start">
                    <div class="flex flex-row xl:flex-col justify-center flex-wrap gap-4">
                        <button :class="{ 'active': post.is_liked }" class="set-mark flex items-center" @click="onLikeClick">
                            <EffectIcon icon="icon-heart"/>
                            <span class="counter flex p-1">{{ post.like_count }}</span>
                        </button>
                        <button :class="{ 'active': post.is_liked }" class="set-mark flex items-center" @click="onLikeClick">
                            <EffectIcon icon="icon-diamond"/>
                            <span class="counter flex p-1">{{ post.like_count }}</span>
                        </button>
                        <RouterLink
                            :to="{name: 'post', params: {slug: post.slug}, hash: '#comments'}"
                            :class="{ 'active': post.is_liked }"
                            class="set-mark flex items-center"
                        >
                            <span class="icon icon-comment flex"/>
                            <span class="counter flex p-1">{{ post.comment_count }}</span>
                        </RouterLink>
                        <RouterLink class="active set-mark flex items-center mini" :to="{ name: 'home' }">
                            <span class="icon icon-eye flex"/>
                            <span class="counter flex p-1">{{ post.view_count }}</span>
                        </RouterLink>
                        <RouterLink class="set-mark flex items-center mini" :to="{ name: 'home' }">
                            <span class="icon icon-download flex"/>
                            <span class="counter flex p-1">1,1K</span>
                        </RouterLink>
                    </div>
                </aside>

                <aside class="right-post-interaction flex xl:flex-col items-end">
                    <div class="flex flex-col md:flex-row xl:flex-col gap-2 xs:gap-8 xl:gap-2">

                        <RouterLink class="author-wrap flex items-center order-2 xl:order-1 w-fit gap-2" :to="{name: 'home'}">
                            <span class="icon-border flex justify-center items-center h-[48px] w-[48px]">
                                <UserAvatar
                                    v-if="authStore.isAuthenticated"
                                    border-class-list="h-12 w-12"
                                    icon-class-list="h-8 w-8"
                                    :user="post.version!.author"
                                />
                            </span>
                            <span
                                :class="{ 'short-username': post.version!.author!.username!.length < 16 }"
                                class="username duration-200"
                            >
                                {{ post.version!.author!.username }}
                            </span>
                        </RouterLink>

                        <div class="date-publication flex order-1 xl:order-2">
                            <span class="icon-apple icon flex mr-1"/>
                            <p class="date-action flex flex-col">
                                <span class="date-action-subtitle">Опубликовано</span>
                                <span v-tooltip.top="getFullPresentableDate(post.updated_at)">
                                    {{ getRelativeDate(post.created_at) }}
                                </span>
                            </p>
                        </div>

                        <div v-if="post.updated_at !== post.created_at" class="date-update flex order-3">
                            <span class="icon-refresh icon flex mr-1"/>
                            <p class="date-action flex flex-col">
                                <span class="date-action-subtitle">Обновлено</span>
                                <span v-tooltip.top="getFullPresentableDate(post.created_at)">
                                    {{ getRelativeDate(post.updated_at) }}
                                </span>
                            </p>
                        </div>

                    </div>
                </aside>
            </div>

            <div class="page-container flex justify-center max-w-[800px] mb-8 mt-6">
                <Button
                    :loading="isSubmittingNewComment"
                    :disabled="!authStore.isAuthenticated"
                    @click="submitNewComment"
                    class="max-w-[320px]"
                    icon="icon-download"
                    label="Скачать"
                />
            </div>

            <div class="comments
                    ld-primary-background
                    ld-fixed-background
                    page-container
                    max-w-[800px] mb-4 p-4"
                id="comments"
            >
                <div class="comments-title flex gap-3">
                    <p>Комментарии</p>
                    <p class="text-[var(--primary-color)]">{{ post!.comment_count }}</p>
                </div>

                <div class="flex flex-col gap-2 mt-4">
                    <PostCommentEditor
                        v-model="newComment.content"
                        class="post-comment-editor"
                        @click="onNewCommentEditorClick"
                    />
                    <Button
                        :loading="isSubmittingNewComment"
                        :disabled="!authStore.isAuthenticated"
                        class="self-center md:self-start max-h-[72px] max-w-[240px] mt-2"
                        @click="submitNewComment"
                        icon="icon-comment"
                        label="Отправить"
                    />
                </div>

                <div ref="commentsBlock">
                    <div v-if="isLoadingComments" class="flex flex-col gap-6 mt-8">
                        <div v-for="i in 3" class="flex gap-2">
                            <div class="skeleton" shape="circle" size="2rem"/>
                            <div class="flex flex-col flex-1 gap-4">
                                <div class="flex gap-2 items-center">
                                    <div class="skeleton" height="0.6rem" width="5rem"/>
                                    <div class="skeleton" height="0.6rem" width="5rem"/>
                                    <div class="skeleton" shape="circle" size="1rem"/>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <div class="skeleton" height="0.7rem" width="70%"/>
                                    <div class="skeleton" height="0.7rem"/>
                                    <div class="skeleton" height="0.7rem" width="50%"/>
                                </div>
                                <div class="flex gap-2 items-center">
                                    <div class="flex gap-1 items-center">
                                        <div class="skeleton" shape="circle" size="1.25rem"/>
                                        <div class="skeleton" height="0.6rem" width="1.5rem"/>
                                    </div>
                                    <div class="skeleton" height="0.6rem" width="5rem"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="comments!.length !== 0" class="flex flex-col gap-6 mt-8">
                        <div v-for="comment in rootComments" class="flex flex-col gap-6">
                            <PostCommentComponent
                                :comment="{...comment, ...{post: post}}"
                                :id="`comment-${comment.id}`"
                                :key="comment.id"
                                @submit-reply="(id, content) => addComment(id, content, comment)"
                                @remove="removeComment(comment)"
                            />
                            <PostCommentComponent
                                v-for="descendantComment in getDescendantComments(comment.id)"
                                :comment="{...descendantComment, ...{post: post}}"
                                :id="`comment-${descendantComment.id}`"
                                :key="descendantComment.id"
                                class="ml-10"
                                @submit-reply="(id, content) => addComment(id, content, descendantComment)"
                                @remove="removeComment(descendantComment)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div v-else class="unavailable-post flex justify-center items-center">
            <div class="unavailable-post-container flex flex-col items-center">
                <h1 class="text-4xl font-bold text-center mt-8">Материал не найден</h1>
                <p class="text-muted text-center mt-4">Материал не существует, либо у Вас нет к нему доступа.</p>
                <div class="mob phantom flex justify-center items-center full-locked">
                    <div class="animation-flying-phantom"></div>
                </div>
                <RouterLink class="flex justify-center max-w-[480px] w-full mb-8" :to="{ name: 'home' }">
                    <Button button-type="submit" icon="item-ender-pearl" icon-size="32px" label="Телепортироваться Домой"/>
                </RouterLink>
            </div>
        </div>
    </template>
</template>

<style scoped>
.content {
    max-width: 800px;
}
.content h1,
.content h2,
.content h3,
.content img {
    line-height: 1.1;
    margin: 1rem 0;
}
.content h4,
.content h5,
.content h6 {
    text-shadow: none;
}
.content .material-name {
    margin: 3rem 0 2rem 0;
    font-size: 2rem;
}
a, b, blockquote, code, li, ol, p, s, strong, ul {
    font-size: 14px;
}
.base-post-interaction {
    pointer-events: none;
    height: 100vh;
    top: 0;
}
.base-post-interaction span,
.base-post-interaction p {
    text-shadow: none;
    font-size: 12px;
}
.base-post-interaction .date-action-subtitle,
.base-post-interaction .short-username {
    font-size: 14px;
}
.left-post-interaction,
.right-post-interaction {
    color: var(--secondary-text-color);
    pointer-events: initial;
    height: fit-content;
    min-width: 220px;
    font-size: 12px;
    width: 220px;
}
.left-post-interaction {
    left: 0;
}
.right-post-interaction {
    right: 0;
}
.date-action span {
    align-items: center;
    height: 1.8rem;
    display: flex;
}
.date-action-subtitle{
    color: var(--trinity-text-color);
}
.tooltip::before {
    min-width: 200px;
}
.comments-title p {
    font-size: 1.2rem;
}
.unavailable-post {
    height: 720px;
    width: 100%;
}
.unavailable-post-container {
    max-width: 520px;
    width: 95%;
}
.mob.phantom {
    overflow: hidden;
    max-width: 320px;
    height: 200px;
    width: 100%;
}
.mob.phantom div {
    background-size: 100% 100%;
    height: 160px;
    width: 320px;
}

/* =============== [ Медиа-Запрос { ?px < 1281px } ] =============== */

@media screen and (max-width: 1280px) {
    .base-post-interaction {
        height: fit-content;
        margin-bottom: 1rem;
        gap: 1rem;
    }
    .left-post-interaction,
    .right-post-interaction {
        min-width: fit-content;
        width: fit-content;
    }
}

/* =============== [ Медиа-Запрос { ?px < 451px } ] =============== */

@media screen and (max-width: 450px) {
    .content .material-name {
        margin: 1rem 0 0 0;
        font-size: 1.5rem;
    }
}
</style>
