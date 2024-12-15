<script setup lang="ts">
import axios, {type AxiosError} from 'axios'

import {computed, nextTick, onMounted, onUnmounted, onUpdated, reactive, ref, watch} from 'vue'

import {useAuthStore} from '@/stores/auth'
import {useGlobalModalStore} from '@/stores/global-modal'
import {useToastStore} from '@/stores/toast'
import {useRoute, useRouter} from 'vue-router'

import {changeTitle, convertDateToString, countHTMLTag, getErrorMessageByCode} from '@/helpers'

import {type Post, type PostComment} from '@/types'

import Button from '@/components/elements/Button.vue'
import Dialog from '@/components/elements/Dialog.vue'

import ProcessingDiggingBlocks from '@/components/elements/ProcessingDiggingBlocks.vue'
import PostInfoBar from '@/components/post/PostInfoBar.vue'
import PostActionBar from '@/components/post/PostActionBar.vue'
import PostCommentComponent from '@/components/post/comment/PostComment.vue'
import PostCommentEditor from '@/components/post/comment/PostCommentEditor.vue'
import PostVersionFile from '@/components/post/editor/PostVersionFile.vue'

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

const post = ref<Post>()
const postContent = ref<Element>()
const comments = ref<PostComment[]>()
const commentsBlock = ref<Element>()
const newComment = reactive<EditedComment>({})
const commentsBlockObserver = new IntersectionObserver(observeCommentsBlock)

const isLoading = ref(true)
const isLoadingComments = ref(true)
const isSubmittingNewComment = ref(false)
const isDownloadWindow = ref(false)
const isWideSidebar = ref(true)

const rootComments = computed(() => comments.value!.filter((comment) => !comment.parent_comment_id))

onUpdated(() => {
    tryConnectCommentsBlockObserver()
})

onMounted(() => {
    loadPost()
})

onUnmounted(() => {
    commentsBlockObserver.disconnect()
})

watch(() => route.params.slug, () => {
    loadPost()
})

const destroyAfterEachCallback = useRouter().afterEach((to, from) => {
    if (to.name === from.name) {
        changeTitle(post.value?.version?.title!)
    } else {
        destroyAfterEachCallback()
    }
})

function updateTitle() {
    nextTick(() => {
        changeTitle(post.value!.version!.title!)
    })
}

function tryConnectCommentsBlockObserver() {
    if (commentsBlock.value && !comments.value) {
        commentsBlockObserver.observe(commentsBlock.value!)
    }
}

function observeCommentsBlock([entry]: IntersectionObserverEntry[]) {
    if (entry.isIntersecting) {
        loadComments()
    }
}

function loadPost() {
    isLoading.value = true
    isLoadingComments.value = true
    isSubmittingNewComment.value = false
    comments.value = undefined

    axios.get(`/api/posts/${props.slug}`).then((response) => {
        const postData: Post = response.data

        if (Object.keys(postData).length !== 0) {
            post.value = postData
            updateTitle()

            if (route.hash === '#comments' || route.hash.match(/^#comment-\d+$/)) {
                loadComments()
            } else {
                tryConnectCommentsBlockObserver()
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

function submitNewComment() {
    newComment.errors = {}
    isSubmittingNewComment.value = true

    if (countHTMLTag(newComment.content!, 'p') < 1) {
        toastStore.error(`В комментарии должно быть хотя бы одно слово`, 'Комментарии')
        return
    }

    axios.post(`/api/posts/${post.value!.id}/comments`, {content: newComment.content}).then((response) => {
        if (response.data.success) {
            toastStore.info('Вы прокомментировали «' + post!.value!.version!.title + '».', 'Комментарии')
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

function openDownloadWindow() {
    isDownloadWindow.value = true
}

</script>

<template>
    <div v-if="isLoading" class="flex justify-center items-center overflow-hidden h-[85vh] w-full">
        <ProcessingDiggingBlocks processing-classes="md:h-[192px] md:w-[192px] h-[128px] w-[128px]"/>
    </div>
    <div v-else-if="post" class="smooth-dark-background flex flex-col items-center w-full duration-500"
         :class="{'wide': isWideSidebar}"
    >
        <Dialog
            v-if="!post.version?.category?.is_article"
            class="post-version-history w-full top-0"
            form-container-classes="max-w-[800px] w-full"
            v-model:visible="isDownloadWindow"
            title="Скачать"
            style="top: 0"
            :header="true"
            :modal="true"
        >
            <div class="ld-tinted-background darker flex flex-col sm:gap-4 gap-2 sm:p-4 p-2">
                <h2 class="ld-title-font text-center">{{ post.version?.title }}</h2>
                <div class="flex flex-col w-full sm:gap-4 gap-2">
                    <PostVersionFile
                        v-for="file in post.version?.files ?? []"
                        :key="file.path || file.url"
                        :file="file"
                    />
                </div>
            </div>
        </Dialog>
        <div></div>
        <section class="section flex justify-between xl:flex-row flex-col-reverse xl:items-start items-center
            xl:max-w-[1280px] max-w-[832px] w-full gap-4 lg:mt-4">

            <aside class="left-post-interaction xl-left-post-interaction
                xl:flex hidden xl:flex-col sticky text-[12px] mb-12"
            >
                <PostActionBar class="ld-secondary-background-container flex-col gap-4" :post="post"/>
            </aside>

            <div
                class="post center-interaction bright-background ld-fixed-background flex flex-col items-center max-w-[832px] w-full"
                ref="postContent">

                <div class="post-info-dates xl:hidden flex lg:justify-between justify-center w-full xs:px-4 px-2">
                    <PostInfoBar class="xl:flex-col flex-wrap justify-center gap-4 lg:mt-0 mt-4 duration-500"
                                 :post="post"/>
                    <button class="lg:flex hidden items-start" @click="isWideSidebar = !isWideSidebar">
                        <span class="icon flex my-4"
                              :class="{'icon-right-arrow': isWideSidebar, 'icon-left-arrow': !isWideSidebar}"/>
                    </button>
                </div>

                <h1 class="post-name ld-secondary-text text-center
                    md:text-[3rem] sm:text-[2rem] text-[1.5rem] my-4 xs:px-4 px-2"
                >
                    {{ post!.version!.title }}
                </h1>

                <div class="preview-wrap flex w-full mt-0 xs:mx-4 xs:px-4 px-2">
                    <img alt="" class="preview w-full mt-0" :src="post!.version!.cover_url">
                </div>

                <div class="ld-secondary-text max-w-full xs:px-4 px-2 py-2" v-html="post!.version!.content"/>

                <div v-if="!post!.version?.category?.is_article"
                     class="page-container flex justify-center max-w-[800px] xl:my-8 my-4">
                    <Button
                        label-classes="text-base"
                        @click="openDownloadWindow"
                        class="max-w-[320px] w-[80%]"
                        icon="icon-download"
                        label="Скачать"
                    />
                </div>

                <div
                    class="ld-secondary-background ld-fixed-background ld-trinity-border-top xl:hidden
                        flex sm:justify-start justify-center sticky w-full bottom-0 mt-2 xs:px-4 px-2 py-2"
                >
                    <PostActionBar class="ld-secondary-background-container xs:gap-4 gap-2" :post="post"/>
                </div>

            </div>

            <aside class="right-post-interaction xl-right-post-interaction xl:flex hidden xl:flex-col xl:sticky
                text-[12px] xl:max-w-[336px] gap-4"
            >
                <div class="first-bright-block bright-background flex flex-col">
                    <button class="flex justify-end p-[4px]" @click="isWideSidebar = !isWideSidebar">
                        <span
                            class="icon flex"
                            :class="{'icon-right-direction-arrow': isWideSidebar, 'icon-left-direction-arrow': !isWideSidebar}"
                        />
                    </button>
                    <PostInfoBar class="right-post-info-bar xl:flex-col justify-center gap-4 duration-500"
                        :post="post"/>
                </div>
                <!-- xl:flex -->
                <div class="next-bright-block bright-background hidden flex-col overflow-hidden">
                    <div class="post-addition-content flex flex-col w-full p-4 duration-500" style="color: dimgray">
                        Дополнительный Контент
                    </div>
                </div>
            </aside>

        </section>
        <section class="ld-primary-background ld-fixed-background flex w-full relative">
            <div class="ld-tinted-reversed-background darker flex justify-center w-full">
                <div class="comments page-container max-w-[832px] mb-4" id="comments">
                    <div class="comments-title flex gap-3 xs:px-4 px-2 py-4">
                        <p class="md:text-[1.2rem] text-base">Комментарии</p>
                        <p class="text-[var(--primary-color)]">{{ post!.comment_count }}</p>
                    </div>

                    <div class="flex flex-col gap-2 xs:px-4 px-2">
                        <PostCommentEditor
                            v-model="newComment.content"
                            class="post-comment-editor"
                            @click="onNewCommentEditorClick"
                        />
                        <Button
                            :loading="isSubmittingNewComment"
                            :disabled="!authStore.isAuthenticated"
                            class="self-center md:self-start max-h-[72px] max-w-[240px] w-[80%] mt-2"
                            @click="submitNewComment"
                            icon="icon-comment"
                            label="Отправить"
                        />
                    </div>

                    <div ref="commentsBlock">
                        <div v-if="isLoadingComments" class="flex flex-col gap-6 mt-8 xs:px-4 px-2">
                            <div v-for="i in 3" class="flex gap-2">
                                <div class="skeleton transfusion bordered h-[2.5rem] min-w-[2.5rem]"/>
                                <div class="flex flex-col w-full gap-4">
                                    <div class="flex gap-2 items-center">
                                        <div class="skeleton transfusion bordered h-[1.2rem] w-[10rem]"/>
                                        <div class="skeleton transfusion bordered h-[1.2rem] w-[5rem]"/>
                                        <div class="skeleton transfusion bordered h-[1rem] w-[5rem]"/>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <div class="skeleton transfusion bordered h-[1rem] w-[85%]"/>
                                        <div class="skeleton transfusion bordered h-[1rem] w-[80%]"/>
                                        <div class="skeleton transfusion bordered h-[1rem] w-[75%]"/>
                                    </div>
                                    <div class="flex gap-2 items-center">
                                        <div class="flex gap-4 items-center">
                                            <div class="skeleton transfusion bordered h-[2rem] w-[2rem]"/>
                                            <div class="skeleton transfusion bordered h-[1.5rem] w-[6rem]"/>
                                        </div>
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
            </div>
        </section>
    </div>
</template>

<style>
.tooltip::before {
    margin-left: -100px;
}
</style>

<style scoped>
.preview-wrap,
.preview {
    object-position: center;
    aspect-ratio: 16/9;
    object-fit: cover;
}

.smooth-dark-background.wide {
    background-color: rgba(0, 0, 0, .2);
}

.section {
    transition: flex 0.5s ease;
}

.bright-background {
    border: 2px solid transparent;
    transition: .5s;
}

.wide .bright-background {
    background-image: url('/images/elements/base-background-code.png');
    background-color: var(--secondary-bg-color);
    border: var(--secondary-border);
}

.xl-left-post-interaction,
.xl-right-post-interaction {
    transition: .5s;
}

.post-addition-content {
    transform: translateX(100%);
    opacity: 0;
}

.wide .post-addition-content {
    transform: translateX(0);
    opacity: 1;
}

.wide .center-interaction {
    margin-bottom: 1rem;
}

/* =============== [ Медиа-Запрос { 1281px > ?px } ] =============== */

@media screen and (min-width: 1281px) {
    .xl-left-post-interaction,
    .xl-right-post-interaction {
        width: 208px;
        top: 96px;
    }

    .wide .xl-right-post-interaction {
        width: 336px;
        top: 80px;
    }

    .xl-left-post-interaction {
        margin-top: 0;
    }

    .wide .xl-left-post-interaction {
        width: 80px;
    }

    .right-post-info-bar {
        padding-left: 3rem;
    }

    .wide .right-post-info-bar {
        padding: 0 1rem 1rem 1rem;
    }

    #comments {
        transition: .5s;
    }

    .wide #comments {
        margin-right: 256px;
    }

    .first-bright-block {
        margin-bottom: 3rem;
    }

    .wide .first-bright-block {
        margin-bottom: 1rem;
    }

    .next-bright-block {
        margin-bottom: 1rem;
    }

    .wide .next-bright-block {
        margin-bottom: 3rem;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1281px } ] =============== */

@media screen and (max-width: 1280px) {
    .xl-right-post-interaction {
        width: 100%;
    }

    .right-date-info {
        width: 100%;
    }
}

/* =============== [ Медиа-Запрос { ?px < 767px } ] =============== */

@media screen and (max-width: 768px) {
    .smooth-dark-background.wide {
        background-color: transparent;
    }

    .wide .bright-background {
        background-color: transparent;
        background-image: none;
        border: none;
    }

    .wide .center-interaction {
        margin-bottom: 0;
    }
}
</style>
