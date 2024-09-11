<script setup lang="ts">
import axios, {type AxiosError} from 'axios'

import {computed, reactive, ref} from 'vue'

import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'
import {RouterLink, useRouter} from 'vue-router'

import {
    getAppUrl,
    getErrorMessageByCode,
    getFullPresentableDate,
    getPostVersionStatusInfo,
    getRelativeDate
} from '@/helpers'

import {
    type PostVersion,
    type PostVersionActionReject,
    type PostVersionActionRequestChanges,
    PostVersionActionType,
    PostVersionStatus,
    type User,
    UserRole
} from '@/types'

import slugify from '@sindresorhus/slugify'

import PostEditor from '@/components/post/editor/PostEditor.vue'
import PostVersionAction from '@/components/post/PostVersionAction.vue'

import Banner from '@/components/elements/Banner.vue'
import Button from '@/components/elements/Button.vue'
import Dialog from '@/components/elements/Dialog.vue'
import Input from '@/components/elements/Input.vue'
import OverlayPanel from '@/components/elements/OverlayPanel.vue'
import ShineButton from '@/components/elements/ShineButton.vue'
import Select from '@/components/elements/Select.vue'
import Textarea from '@/components/elements/Textarea.vue'

import UserAvatar from '@/components/user/UserAvatar.vue'

import ProcessingMovingItems from '@/components/elements/ProcessingMovingItems.vue'

const props = defineProps({
    id: {
        type: Number,
        required: true
    }
})

const authStore = useAuthStore()
const toastStore = useToastStore()
const router = useRouter()

const acceptOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()
const reconsiderOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()
const revisionOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()
const rejectOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()
const submitOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()

const postVersion = ref<PostVersion>()
const moderators = ref<User[]>()
const customSlug = ref<string>()

const isFirstVersion = computed(() => !postVersion.value!.post || postVersion.value!.updated_at === postVersion.value!.post.created_at)
const wasUpdated = computed(() => postVersion.value!.updated_at !== postVersion.value!.created_at)
const isReviewing = computed(() => authStore.isModerator && postVersion.value!.status === PostVersionStatus.PENDING)
const isOwnDraft = computed(() => postVersion.value!.author_id === authStore.id && postVersion.value!.status === PostVersionStatus.DRAFT)
const postVersionStatusInfo = computed(() => getPostVersionStatusInfo(postVersion.value?.status!))
const slug = computed({
    get() {
        return customSlug.value ?? slugify(postVersion.value?.title ?? '')
    },
    set(newValue) {
        customSlug.value = newValue
    }
})
const postUrl = computed(() =>
    new URL(router.resolve({name: 'post', params: {slug: slug.value}}).fullPath, getAppUrl()).href
)
const lastAction = computed(() => postVersion.value!.actions!.at(postVersion.value!.actions!.length - 1))
const rejectDetails = reactive<PostVersionActionReject>({reason: ''})
const requestChangesDetails = reactive<PostVersionActionRequestChanges>({message: ''})

const isAccepting = ref(false)
const isHistoryDialog = ref(false)
const isLoading = ref(false)
const isReconsidering = ref(false)
const isRejecting = ref(false)
const isRequestingChanges = ref(false)
const isSubmitting = ref(false)
const isUpdatingDraft = ref(false)

const errors = ref<{ [key: string]: string[] }>({})

const isModer = ref('true')

const moderatorOptions = computed(() => {
    if (!moderators.value) {
        const options: User[] = []
        if (postVersion.value!.assigned_moderator) {
            options.push(postVersion.value!.assigned_moderator)
        }
        if (postVersion.value!.assigned_moderator_id !== authStore.id) {
            options.push(authStore.user!)
        }
        return options
    }

    const firstModeratorIds = [postVersion.value!.assigned_moderator_id, authStore.id]

    return moderators.value.sort((a: User, b: User) => {
        if (firstModeratorIds.includes(a.id) && firstModeratorIds.includes(b.id)) {
            return firstModeratorIds.indexOf(a.id) - firstModeratorIds.indexOf(b.id)
        } else if (firstModeratorIds.includes(a.id)) {
            return -1
        } else if (firstModeratorIds.includes(b.id)) {
            return 1
        } else {
            return a.username!.localeCompare(b.username!)
        }
    })
})

function loadPostVersion() {
    isLoading.value = true
    axios.get(`/api/post-versions/${props.id}`).then((response) => {
        const version: PostVersion = response.data
        if (Object.keys(version).length !== 0) {
            postVersion.value = version
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

function loadModerators() {
    if (moderators.value) return
    axios.get(`/api/users`, {params: {roles: [UserRole.MODERATOR, UserRole.ADMIN]}}).then((response) => {
        moderators.value = response.data.records
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    })
}

function assignModerator(moderator: User) {
    postVersion.value!.assigned_moderator = moderator
    axios.put(
        `/api/post-versions/${props.id}/assigned-moderator`,
        {moderator_id: moderator.id}
    ).then((response) => {
        if (response.data.success) {
            toastStore.success(`Назначен Модератор ${moderator.username}.`)
        } else {
            if (response.data.message) {
                toastStore.error(response.data.message)
                errors.value = response.data.errors
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    })
}

function accept() {
    isAccepting.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))
    if (!postVersion.value?.post_id) {
        formData.append('slug', slug.value)
    }

    axios.patch(`/api/post-versions/${props.id}/accept`, formData).then((response) => {
        if (response.data.success) {
            toastStore.success('Материал успешно опубликован!')
            acceptOverlayPanel.value?.hide()
            loadPostVersion()
        } else {
            if (response.data.errors) {
                toastStore.error('Не все поля заполнены корректно.')
                errors.value = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isAccepting.value = false
    })
}

function submit() {
    isSubmitting.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))

    axios.patch(`/api/post-versions/${props.id}/submit`, formData).then((response) => {
        if (response.data.success) {
            toastStore.success('Материал отправлен на модерацию.')
            submitOverlayPanel.value?.hide()
            loadPostVersion()
        } else {
            if (response.data.errors) {
                toastStore.error('Не все поля заполнены корректно.')
                errors.value = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isSubmitting.value = false
    })
}

function reconsider() {}

function reject() {
    isRejecting.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))
    Object.keys(rejectDetails).forEach(key => formData.append(`details[${key}]`, rejectDetails[key]))

    axios.patch(`/api/post-versions/${props.id}/reject`, formData).then((response) => {
        if (response.data.success) {
            toastStore.warning('Материал был отклонён.', 'Отклонено')
            rejectOverlayPanel.value?.hide()
            loadPostVersion()
        } else {
            if (response.data.errors) {
                toastStore.error('Не все поля заполнены корректно.')
                errors.value = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isRejecting.value = false
    })
}

function revision() {
    isRequestingChanges.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))
    Object.keys(requestChangesDetails).forEach(key => formData.append(`details[${key}]`, requestChangesDetails[key]))

    axios.patch(`/api/post-versions/${props.id}/request-changes`, formData).then((response) => {
        if (response.data.success) {
            toastStore.success('Материал возвращён на доработку.')
            revisionOverlayPanel.value?.hide()
            loadPostVersion()
        } else {
            if (response.data.errors) {
                toastStore.error('Не все поля заполнены корректно.')
                errors.value = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isRequestingChanges.value = false
    })
}

function updateDraft() {
    isUpdatingDraft.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))

    axios.patch(`/api/post-versions/${props.id}`, formData).then((response) => {
        if (response.data.success) {
            toastStore.success('Изменения успешно сохранены.')
        } else {
            if (response.data.errors) {
                toastStore.error('Не все поля заполнены корректно.')
                errors.value = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isUpdatingDraft.value = false
    })
}
loadPostVersion()
</script>

<template>
    <div v-if="isLoading" class="flex justify-center items-center">
        <ProcessingMovingItems/>
    </div>
    <template v-else>
        <Dialog
            v-if="postVersion"
            class="post-version-history page-container max-w-[800px] top-0"
            v-model:visible="isHistoryDialog"
            title="История Действий"
            :dismissable-mask="true"
            :draggable="false"
            style="top: 0"
            :header="true"
            :modal="true"
        >
            <div class="post-version-actions flex flex-col gap-2 md:p-6 xs:p-4 p-2">
                <PostVersionAction v-for="action in postVersion.actions" :action="action"/>
            </div>
        </Dialog>

        <PostEditor
            v-if="postVersion"
            v-model="postVersion"
            :author="postVersion.author!"
            :editable="isOwnDraft || isReviewing"
            :errors="errors"
        >
            <template v-slot:banner>
                <Banner :is-title-display="false"/>
            </template>

            <template v-slot:header>
                <div class="banner-title page-container flex flex-col justify-center items-center md:items-end max-w-[800px]">

                    <RouterLink
                        class="logo-wrap flex justify-center items-center relative xs:w-full full-locked"
                        :to="{ name: 'home' }"
                    >
                        <h1 class="title-font text-center">Заявка на публикацию</h1>
                    </RouterLink>

                    <div class="mark-block flex absolute bottom-2 gap-2">
                        <div :class="{ 'new': isFirstVersion, 'update': !isFirstVersion }" class="material-type new flex items-center h-fit gap-2 locked">
                            <span class="icon-apple icon flex"/>
                            <p v-if="isFirstVersion" class="text-[9px] xs:text-[.7rem]">Новый Материал</p>
                            <p v-else class="text-[9px] xs:text-[.7rem]">Обновление</p>
                        </div>
                        <div
                            class="time-ago time flex items-center h-fit gap-2 locked tooltip whitespace-nowrap"
                            v-tooltip.top="getFullPresentableDate(postVersion!.updated_at!)"
                        >
                            <span class="icon-clock icon flex"/>
                            <p class="text-[9px] xs:text-[.7rem]">{{ getRelativeDate(postVersion!.updated_at!) }}</p>
                        </div>
                    </div>

                </div>
            </template>

            <template v-slot:sidebar>
                <div class="flex flex-col w-full">

                    <div v-if="authStore.isModerator &&
                            (postVersion.status !== PostVersionStatus.DRAFT || postVersion.assigned_moderator)"
                         class="flex xl:flex-col flex-row"
                    >
                        <div class="flex items-center max-w-[100px] xl:px-2.5 px-5 py-1">
                            <p class="xl:mt-1">Модератор</p>
                        </div>
                        <div class="flex xl:max-w-none max-w-[200px] w-full">
                            <Select
                                options-classes="ld-primary-background ld-primary-border mt-[-10px]"
                                :disabled="postVersion!.status !== PostVersionStatus.PENDING"
                                class="post-moderator flex self-center w-full"
                                v-model="postVersion!.assigned_moderator_id"
                                button-label-classes="hidden xs:flex"
                                button-classes="max-h-[64px]"
                                input-id="moderator"
                                :options="moderatorOptions"
                                option-classes="xl:pl-2 pl-4"
                                option-label-key="username"
                                option-value-key="id"
                                placeholder="Не назначен"
                                @show="loadModerators"
                                @change="assignModerator"
                            >

                                <template #option-icon="{option}: {option: User}">
                                    <UserAvatar
                                        border-class-list="h-12 w-12"
                                        icon-class-list="h-8 w-8"
                                        :user="option"
                                    />
                                </template>

                                <template #option="{option}: { option: User}">
                                    <div class="flex gap-2 items-center py-2 px-3 w-full"
                                         @click="assignModerator(option)">
                                        <UserAvatar
                                            border-class-list="h-12 w-12"
                                            icon-class-list="h-8 w-8"
                                            :user="option"
                                        />
                                        <p class="text-sm line-clamp-1">{{ option.username }}</p>
                                    </div>
                                </template>

                            </Select>
                        </div>
                    </div>

                    <div v-if="postVersion!.post" class="header-details-item flex xl:flex-col flex-row">
                        <div class="flex items-center max-w-[100px] xl:px-2.5 px-5 py-1">
                            <p>Материал</p>
                        </div>
                        <div class="flex items-center xl:px-2.5 px-4 py-1">
                            <RouterLink
                                :to="{name: 'post', params: {slug: postVersion!.post.slug}}"
                                class="ld-default-link line-clamp-2 text-[12px] border-0 hover:underline duration-200"
                            >
                                {{ postVersion!.post.version!.title }}
                            </RouterLink>
                        </div>
                    </div>

                    <div class="flex xl:flex-col flex-row">
                        <div class="flex items-center min-w-[100px] xl:px-2.5 px-5 py-1">
                            <p>Статус</p>
                        </div>
                        <div class="flex items-center xl:px-2.5 px-4 py-1">
                            <p
                                :class="postVersionStatusInfo.colorClass"
                                class="transfusion bordered px-1 py-0.5"
                            >
                                {{ postVersionStatusInfo.name }}
                            </p>
                        </div>
                    </div>

                    <div v-if="postVersion.actions!.length !== 0" class="flex flex-col xl:gap-2 gap-4 mt-1 xl:pb-1 xl:px-2 px-4">
                        <div class="flex items-center">
                            <PostVersionAction
                                v-if="[PostVersionActionType.REJECT, PostVersionActionType.REQUEST_CHANGES].includes(lastAction?.type!)"
                                class="sidebar-last-action px-2 md:px-1"
                                :action="lastAction!"
                                :minimized="true"
                            />
                        </div>
                        <ShineButton
                            class="ld-shine-button self-center text-[0.7rem] xl:mb-1 mb-4"
                            :class="{ 'non-mb-0': postVersion.status === PostVersionStatus.ACCEPTED
                            || postVersion.status === PostVersionStatus.REJECTED }"
                            @click="isHistoryDialog = true"
                            label="История действий"
                            icon="icon-script"
                        />
                    </div>

                    <div
                        v-if="postVersion.status === PostVersionStatus.ACCEPTED || postVersion.status === PostVersionStatus.REJECTED"
                        class="flex flex-col xl:gap-2 gap-4 xl:px-2 px-4 xl:pb-2 pb-4"
                    >
                        <ShineButton
                            class="ld-shine-button upper-unavailable w-full reconsider"
                            @click="reconsiderOverlayPanel?.toggle"
                            label="Вернуть на рассмотрение"
                            icon="icon-eye"
                        />
                    </div>

                    <div v-if="isReviewing" class="upper-unavailable flex flex-col xl:gap-2 gap-4 xl:px-2 px-4 xl:pb-2 pb-4">
                        <div class="separator"></div>
                        <ShineButton
                            class="ld-shine-button confirm"
                            @click="acceptOverlayPanel?.toggle"
                            label="Принять"
                            icon="icon-tick"
                        />
                        <ShineButton
                            class="ld-shine-button cancel"
                            @click="rejectOverlayPanel?.toggle"
                            label="Отклонить"
                            icon="icon-small-cross"
                        />
                        <ShineButton
                            class="ld-shine-button"
                            @click="revisionOverlayPanel?.toggle"
                            label="На доработку"
                            icon="icon-refresh"
                        />
                    </div>
                </div>
                <div v-if="isOwnDraft" class="flex flex-col w-full xl:gap-2 gap-4 xl:px-2 px-4 xl:pb-2 pb-4">
                    <div class="separator"></div>
                    <ShineButton
                        class="ld-shine-button upper-unavailable w-full confirm"
                        @click="submitOverlayPanel?.toggle"
                        label="На рассмотрение"
                        icon="icon-eye"
                    />
                    <ShineButton
                        class="ld-shine-button w-full"
                        @click="updateDraft"
                        label="Сохранить изменения"
                        :loading="isUpdatingDraft"
                        icon="icon-script"
                    />
                </div>
            </template>
        </PostEditor>

        <div v-else class="unavailable-post flex justify-center items-center">
            <div class="unavailable-post-container flex flex-col items-center">
                <h1 class="text-4xl font-bold text-center mt-8">Заявка на публикацию недоступна</h1>
                <p class="text-muted text-center mt-4">Текущая заявка на публикацию не существует, либо у Вас нет к ней доступа.</p>
                <div class="mob phantom flex justify-center items-center full-locked">
                    <div class="animation-flying-phantom"></div>
                </div>
                <RouterLink class="flex justify-center max-w-[480px] w-full mb-8" :to="{ name: 'home' }">
                    <Button button-type="submit" icon="item-ender-pearl" icon-size="32px" label="Телепортироваться Домой"/>
                </RouterLink>
            </div>
        </div>
    </template>

    <OverlayPanel class="overlay-panel ld-primary-background max-w-[100vw] p-4 mt-24rem" ref="acceptOverlayPanel">
        <div class="flex flex-col gap-2">

            <p class="flex">URL-идентификатор</p>

            <Input
                v-model="slug"
                class="material-url-id py-2"
                id="material-url-id"
            />

            <p class="error">{{ errors['slug']?.[0] || ' ' }}</p>

            <p class="flex text-[10px]">{{ postUrl }}</p>

            <div class="flex gap-2">
                <ShineButton
                    class="flex"
                    @click="acceptOverlayPanel?.hide()"
                    icon="icon-small-cross"
                    label="Отмена"
                />
                <ShineButton
                    class="flex confirm"
                    :loading="isAccepting"
                    @click="accept"
                    label="Опубликовать"
                    icon="icon-tick"
                />
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel class="overlay-panel ld-primary-background max-w-[100vw] p-4 mt-24rem" ref="reconsiderOverlayPanel">
        <div class="flex flex-col gap-2">

            <p class="flex">Вернуть на рассмотрение?</p>

            <p v-if="postVersion!.status === PostVersionStatus.ACCEPTED" class="flex">
                Материал будет убран с Каталога и пользователи потеряют к нему доступ.
            </p>

            <p v-else-if="postVersion!.status === PostVersionStatus.REJECTED" class="flex">
                Материал будет восстановлен и перемещён на рассмотрение, пока не будет принят или снова отклонён.
            </p>

            <div class="flex gap-2">
                <ShineButton
                    class="flex"
                    @click="reconsiderOverlayPanel?.hide()"
                    icon="icon-small-cross"
                    label="Отмена"
                />
                <ShineButton
                    class="flex reconsider"
                    :loading="isReconsidering"
                    @click="reconsider"
                    icon="icon-tick"
                    label="Вернуть"
                />
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel class="overlay-panel ld-primary-background max-w-[100vw] p-4 mt-24rem" ref="rejectOverlayPanel">
        <div class="flex flex-col gap-2">

            <p class="flex">Причина отклонения</p>

            <Textarea
                v-model="rejectDetails.reason"
                class="material-reject-reason"
                id="material-reject-reason"
                :max-length="185"
                :min-length="15"
                placeholder="Неприемлимый Контент"
                rows="3"
            />

            <p class="error">{{ errors['details.reason']?.[0] || ' ' }}</p>

            <div class="flex gap-2">
                <ShineButton
                    class="flex"
                    @click="rejectOverlayPanel?.hide()"
                    icon="icon-small-cross"
                    label="Отмена"
                />
                <ShineButton
                    class="flex cancel"
                    :loading="isRejecting" @click="reject"
                    icon="icon-tick"
                    label="Отклонить"
                />
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel class="overlay-panel ld-primary-background max-w-[100vw] p-4 mt-24rem" ref="revisionOverlayPanel">
        <div class="flex flex-col gap-2">

            <p class="flex">Что не так?</p>

            <Textarea
                v-model="requestChangesDetails.message"
                class="material-revision-reason"
                id="material-revision-reason"
                :max-length="185"
                :min-length="15"
                placeholder="Необходимо доработать Материал..."
                rows="3"
            />

            <p class="error">{{ errors['details.message']?.[0] || ' ' }}</p>

            <div class="flex gap-2">
                <ShineButton
                    class="flex"
                    @click="revisionOverlayPanel?.hide()"
                    icon="icon-small-cross"
                    label="Отмена"
                />
                <ShineButton
                    class="flex confirm"
                    :loading="isRequestingChanges" @click="revision"
                    icon="icon-tick"
                    label="Отправить"
                />
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel class="overlay-panel ld-primary-background max-w-[100vw] p-4 mt-24rem" ref="submitOverlayPanel">
        <div class="flex flex-col gap-2">

            <p class="flex">Вы точно хотите отправить Материал на рассмотрение?</p>

            <div class="flex gap-2">
                <ShineButton
                    class="flex confirm"
                    :loading="isSubmitting" @click="submit"
                    label="Да, отправить"
                    icon="icon-tick"
                />
                <ShineButton
                    class="flex"
                    @click="submitOverlayPanel?.hide()"
                    icon="icon-small-cross"
                    label="Отмена"
                />
            </div>
        </div>
    </OverlayPanel>

</template>

<style>
.post-version-history .dialog-header h1 {
    text-align: center;
    line-height: 1.2;
}
.post-moderator.select .select-button,
.post-moderator.select .options {
    border-right: 0;
    border-left: 0;
}
.post-moderator.select .options {
    max-height: 216px;
}
.post-moderator.select .options .icon-border,
.post-moderator.select .select-button .icon-border {
    height: 42px;
    width: 42px;
}
.post-moderator.select .options .icon-border img,
.post-moderator.select .select-button .icon-border img {
    height: 28px;
    width: 28px;
}
.material-reject-reason textarea,
.material-revision-reason textarea {
    min-height: 136px;
    padding: 4px 8px;
}
.upper-interaction .upper-unavailable {
    display: none;
}
.ld-shine-button .press,
.ld-shine-button .preset {
    width: 100%;
}
.ld-shine-button .text {
    height: 2rem;
}
.ld-shine-button .preset {
    padding: .5rem;
}
.loading-icon {
    min-width: 28px;
}
.time-ago.tooltip::before {
    align-items: center;
    bottom: 2.6rem;
    display: flex;
    height: 2rem;
    left: 2.3rem;
}
.upper-interaction  .non-mb-0 {
    margin-bottom: 0;
}
</style>

<style scoped>
.content h1 {
    font-size: 3rem;
}
.material-type, .time-ago {
    padding: .125rem .25rem;
    cursor: pointer;
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
.global-error-window h1,
.global-error-window p {
    color: var(--primary-text-color);
}
.global-error-container a {
    width: 95%;
}
.global-error-container p {
    max-width: 300px;
}
.post-version-actions {
    max-height: 500px;
    overflow-y: auto;
}
.sidebar-last-action {
    font-size: 12px;
}

/* =============== [ Медиа-Запрос { ?px < 1024px + desktop-height } ] =============== */

@media screen and (max-width: 1023px) and (min-height: 654px) {
    .unavailable-post {
        height: 540px;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1024px + mobile-height } ] =============== */

@media screen and (max-width: 1023px) and (max-height: 653px) {
    .unavailable-post {
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

/* =============== [ Медиа-Запрос { ?px < 451px } ] =============== */

@media screen and (max-width: 450px) {
    .material-type, .time-ago {
        padding: 0 .25rem;
        cursor: pointer;
    }
    .time-ago.tooltip::before {
        bottom: 2.4rem;
        left: 1.2rem;
    }
}
</style>
