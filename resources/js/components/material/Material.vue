<script setup lang="ts">
import axios, {type AxiosError} from 'axios'

import {computed, nextTick, onMounted, onUnmounted, onUpdated, type PropType, reactive, ref, watch} from 'vue'

import {useAuthStore} from '@/stores/auth'
import useCategoryRegistry, {type Category} from '@/categoryRegistry'
import {useGlobalModalStore} from '@/stores/global-modal'
import usePreferenceManager from '@/preference-manager'
import {useToastStore} from '@/stores/toast'
import {RouterLink, useRoute, useRouter} from 'vue-router'

import {changeTitle, convertDateToString, countHTMLTag, getErrorMessageByCode, withCaptcha} from '@/helpers'

import {GameEdition, type Material, type MaterialComment, type MaterialVersion} from '@/types'

import Button from '@/components/elements/Button.vue'

import ProcessingDiggingBlocks from '@/components/elements/ProcessingDiggingBlocks.vue'
import MaterialInfoBar from '@/components/material/MaterialInfoBar.vue'
import MaterialActionBar from '@/components/material/MaterialActionBar.vue'
import MaterialCommentComponent from '@/components/material/comment/MaterialComment.vue'
import MaterialCommentEditor from '@/components/material/comment/MaterialCommentEditor.vue'
import MaterialFile from '@/components/material/MaterialFile.vue'
import MaterialVersionSelect from '@/components/material/MaterialVersionSelect.vue'

const props = defineProps({
    edition: String as PropType<GameEdition>,
    category: {
        type: Object as PropType<Category>,
        required: true
    },
    slug: {
        type: String,
        required: true
    },
})

interface EditedComment {
    errors?: { [key: string]: string[] }
    content?: string
}

const authStore = useAuthStore()
const globalModalStore = useGlobalModalStore()
const preferenceManager = usePreferenceManager()
const toastStore = useToastStore()

const route = useRoute()
const router = useRouter()

const material = ref<Material>()
const materialContent = ref<Element>()
const comments = ref<MaterialComment[]>()
const commentsBlock = ref<Element>()
const newComment = reactive<EditedComment>({})
const commentsBlockObserver = new IntersectionObserver(observeCommentsBlock)
const currentVersion = ref<MaterialVersion>()

const isLoading = ref(true)
const isLoadingComments = ref(true)
const isSubmittingNewComment = ref(false)
const isDownloadWindow = ref(false)

const rootComments = computed(() => comments.value!.filter((comment) => !comment.parent_comment_id))
const isDownloadable = computed(() => useCategoryRegistry().get(material.value!.category).isDownloadable)

onUpdated(() => {
    tryConnectCommentsBlockObserver()
})

onMounted(() => {
    loadMaterial()
})

onUnmounted(() => {
    commentsBlockObserver.disconnect()
})

watch(() => [props.edition, props.category, props.slug], () => {
    if (!history.state?.sameMaterial) {
        loadMaterial()
    }
})

const destroyAfterEachCallback = router.afterEach((to, from) => {
    if (to.name === from.name) {
        changeTitle(material.value!.state!.localization!.title!)
    } else {
        destroyAfterEachCallback()
    }
})

function updateTitle() {
    nextTick(() => {
        changeTitle(material.value!.state!.localization!.title!)
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

function loadMaterial() {
    isLoading.value = true
    isLoadingComments.value = true
    isSubmittingNewComment.value = false
    comments.value = undefined

    axios.get(`/api/materials/${props.slug}`, {
        params: {
            edition: props.edition,
            category: props.category!.type
        }
    }).then((response) => {
        const materialData: Material = response.data

        if (Object.keys(materialData).length !== 0) {
            material.value = materialData
            if (material.value.versions) {
                currentVersion.value = material.value.versions[material.value.versions.length - 1]
            }
            if (material.value.edition !== props.edition) {
                router.replace({
                    name: 'material',
                    params: {edition: material.value.edition, category: props.category!.slug, slug: props.slug},
                    state: {sameMaterial: true},
                })
            }
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

    axios.get(`/api/materials/${material.value!.id}/comments`).then((response) => {
        comments.value = response.data.records
        updateMaterialCommentCount()
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoadingComments.value = false
    })
}

function submitNewComment() {
    withCaptcha(() => {
        newComment.errors = {}
        isSubmittingNewComment.value = true

        if (countHTMLTag(newComment.content!, 'p') < 1) {
            toastStore.error(`В комментарии должно быть хотя бы одно слово`, 'Комментарии')
            return
        }

        axios.post(`/api/materials/${material.value!.id}/comments`, {content: newComment.content}).then((response) => {
            if (response.data.success) {
                toastStore.info('Вы прокомментировали «' + material!.value!.state!.localization!.title + '».', 'Комментарии')
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
    })
}

function addComment(id: bigint, content: string, parentComment: MaterialComment | null = null): void {
    const nowDate = convertDateToString(new Date())
    const comment: MaterialComment = {
        id: id,
        parent_comment_id: parentComment?.id,
        parent_comment: parentComment,
        user_id: authStore.id!,
        user: authStore.user,
        content: content,
        likes_count: 0,
        is_liked: false,
        created_at: nowDate,
        updated_at: nowDate
    }

    comments.value!.unshift(comment)
    updateMaterialCommentCount()
}

function removeComment(comment: MaterialComment) {
    comments.value = comments.value!.filter(v => v !== comment && !isDescendantComment(v, comment.id))
    updateMaterialCommentCount()
}

function updateMaterialCommentCount() {
    material.value!.comments_count = comments.value!.length
}

function isDescendantComment(comment: MaterialComment, ancestorId: bigint): boolean {
    while (comment?.parent_comment_id !== undefined) {
        if (comment.parent_comment_id === ancestorId) return true
        comment = comments.value!.find(c => c.id === comment.parent_comment_id)!
    }
    return false
}

function getDescendantComments(ancestorId: bigint): MaterialComment[] {
    return comments.value!
        .filter(comment => isDescendantComment(comment, ancestorId))
        .sort((a, b) => a.created_at.localeCompare(b.created_at))
}

function onNewCommentEditorClick() {
    if (!authStore.isAuthenticated) {
        globalModalStore.authModal = true
    } else if (!authStore.hasVerifiedEmail) {
        globalModalStore.notVerifiedEmailModal = true
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
    <div v-else-if="material" class="smooth-dark-background flex flex-col items-center w-full duration-500"
         :class="{'wide': preferenceManager.isMaterialFullViewVisible()}"
    >
        <section class="section flex justify-between xl:flex-row flex-col-reverse xl:items-start items-center
            xl:max-w-[1280px] max-w-[832px] w-full gap-4 lg:mt-4">

            <aside class="left-material-interaction xl-left-material-interaction
                xl:flex hidden xl:flex-col sticky text-[12px] mb-12"
            >
                <MaterialActionBar class="ld-secondary-background-container flex-col gap-4" :material="material"/>
            </aside>

            <div
                class="material center-interaction bright-background ld-fixed-background flex flex-col items-center max-w-[832px] w-full xs:px-4 px-2"
                ref="materialContent">

                <div class="material-info-dates xl:hidden flex lg:justify-between justify-center w-full">
                    <MaterialInfoBar class="xl:flex-col flex-wrap justify-center gap-4 lg:mt-0 mt-4 duration-500"
                        :material="material"/>
                    <button class="lg:flex hidden items-start" @click="preferenceManager.switchMaterialFullView()">
                        <span
                            class="icon flex my-4"
                            :class="{
                                'icon-right-arrow': preferenceManager.isMaterialFullViewVisible(),
                                'icon-left-arrow': !preferenceManager.isMaterialFullViewVisible()
                            }"
                        />
                    </button>
                </div>

                <h1 class="material-name ld-secondary-text text-center
                    md:text-[3rem] sm:text-[2rem] text-[1.5rem] my-4"
                >
                    {{ material!.state!.localization!.title }}
                </h1>

                <div class="preview-wrap flex w-full mt-0 xs:mx-4">
                    <img alt="" class="preview w-full mt-0" :src="material!.state!.localization!.cover_url">
                </div>

                <div class="ld-secondary-text max-w-full pb-8 py-2"
                     v-html="material!.state!.localization!.content"/>

                <div v-if="isDownloadable" class="flex flex-col gap-2 w-full" id="download">
                    <MaterialVersionSelect
                        v-model="currentVersion"
                        :versions="material!.versions!.sort((a, b) => b.published_at!.localeCompare(a.published_at!))"
                    />

                    <div
                        v-if="currentVersion"
                        class="w-full outline-offset-[5px]"
                    >
                        <div class="flex flex-col w-full gap-3 mb-4">
                            <h4 class="ld-secondary-text flex xs:flex-row flex-col justify-center text-center xs:gap-2 mt-0">
                                Скачать Материал
                            </h4>
                            <template v-for="(file, index) in currentVersion.files" :key="index">
                                <MaterialFile :file="file"/>
                            </template>
                        </div>
                    </div>
                </div>

                <div
                    class="ld-secondary-background ld-fixed-background ld-trinity-border-top xl:hidden
                        flex sm:justify-start justify-center sticky w-full bottom-0 mt-2 xs:px-4 px-2 py-2"
                >
                    <MaterialActionBar class="ld-secondary-background-container xs:gap-4 gap-2" :material="material"/>
                </div>
            </div>

            <aside class="right-material-interaction xl-right-material-interaction xl:flex hidden xl:flex-col xl:sticky
                text-[12px] xl:max-w-[336px] gap-4"
            >
                <div class="first-bright-block bright-background flex flex-col">
                    <button class="flex justify-end p-[4px]" @click="preferenceManager.switchMaterialFullView()">
                        <span
                            class="icon flex"
                            :class="{
                                'icon-right-direction-arrow': preferenceManager.isMaterialFullViewVisible(),
                                'icon-left-direction-arrow': !preferenceManager.isMaterialFullViewVisible()
                            }"
                        />
                    </button>
                    <MaterialInfoBar
                        class="right-material-info-bar xl:flex-col justify-center gap-4 duration-500"
                        :material="material"
                    />
                </div>
                <!-- xl:flex -->
                <div class="next-bright-block bright-background hidden flex-col overflow-hidden">
                    <div class="material-addition-content flex flex-col w-full p-4 duration-500" style="color: dimgray">
                        Дополнительный Контент
                    </div>
                </div>
            </aside>

        </section>
        <section class="ld-primary-background ld-fixed-background flex w-full relative">
            <div class="ld-tinted-reversed-background darker flex justify-center w-full xs:px-4 px-2">
                <div class="comments page-container max-w-[832px] mb-4" id="comments">
                    <div class="comments-title flex gap-3 p-4">
                        <p class="md:text-[1.2rem] text-base">Комментарии</p>
                        <p class="text-[var(--primary-color)]">{{ material!.comments_count }}</p>
                    </div>

                    <div class="flex flex-col gap-2 px-4">
                        <MaterialCommentEditor
                            v-model="newComment.content"
                            class="material-comment-editor"
                            :class="{'red-border': newComment.errors?.['content']?.[0]}"
                            :style="{
                                'border: 2px solid var(--primary-text-color);': newComment.errors?.['content']?.[0]
                            }"
                            @click="onNewCommentEditorClick"
                        />
                        <p class="error text-[14px]">{{ newComment.errors?.['content']?.[0] || ' ' }}</p>
                        <Button
                            :loading="isSubmittingNewComment"
                            :disabled="!authStore.isAuthenticated || !authStore.hasVerifiedEmail"
                            class="self-start w-full max-h-[72px] sm:max-w-[240px] mt-2"
                            @click="submitNewComment"
                            icon="icon-comment"
                            label="Отправить"
                        />
                    </div>

                    <div ref="commentsBlock">
                        <div v-if="isLoadingComments" class="flex flex-col gap-6 mt-8">
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
                                <MaterialCommentComponent
                                    :comment="{...comment, ...{material: material}}"
                                    :material="material"
                                    :id="`comment-${comment.id}`"
                                    :key="comment.id"
                                    @submit-reply="(id, content) => addComment(id, content, comment)"
                                    @remove="removeComment(comment)"
                                />
                                <MaterialCommentComponent
                                    v-for="descendantComment in getDescendantComments(comment.id)"
                                    :comment="{...descendantComment, ...{material: material}}"
                                    :material="material"
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

    <div v-else class="unavailable-material flex justify-center items-center">
        <div class="unavailable-material-container flex flex-col items-center">
            <h1 class="text-4xl font-bold text-center mt-8 mb-4">Материал не найден</h1>
            <div class="mob phantom flex justify-center items-center full-locked">
                <div class="animation-flying-phantom"/>
            </div>
            <RouterLink class="flex justify-center max-w-[480px] w-full mb-8" :to="{ name: 'home' }">
                <Button
                    press-classes="px-4"
                    button-type="submit"
                    icon="item-ender-pearl"
                    icon-size="32px"
                    label="Телепортироваться Домой"
                />
            </RouterLink>
        </div>
    </div>
</template>

<style>
.tooltip::before {
    margin-left: -100px;
}

.material-info-dates .date-publication,
.material-info-dates .date-update {
    max-width: 90px;
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

.xl-left-material-interaction,
.xl-right-material-interaction {
    transition: .5s;
}

.material-addition-content {
    transform: translateX(100%);
    opacity: 0;
}

.wide .material-addition-content {
    transform: translateX(0);
    opacity: 1;
}

.wide .center-interaction {
    margin-bottom: 1rem;
}

.unavailable-material {
    height: 720px;
    width: 100%;
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

/* =============== [ Медиа-Запрос { ?px < 1024px + desktop-height } ] =============== */

@media screen and (max-width: 1023px) and (min-height: 654px) {
    .unavailable-material {
        height: 540px;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1024px + mobile-height } ] =============== */

@media screen and (max-width: 1023px) and (max-height: 653px) {
    .unavailable-material {
        height: 380px;
    }

    .mob.phantom {
        height: 100px;
    }

    .mob.phantom div {
        height: 80px;
        width: 160px;
    }
}

/* =============== [ Медиа-Запрос { 1281px > ?px } ] =============== */

@media screen and (min-width: 1281px) {
    .xl-left-material-interaction,
    .xl-right-material-interaction {
        width: 208px;
        top: 96px;
    }

    .wide .xl-right-material-interaction {
        width: 336px;
        top: 80px;
    }

    .xl-left-material-interaction {
        margin-top: 0;
    }

    .wide .xl-left-material-interaction {
        width: 80px;
    }

    .right-material-info-bar {
        padding-left: 3rem;
    }

    .wide .right-material-info-bar {
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
    .xl-right-material-interaction {
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
