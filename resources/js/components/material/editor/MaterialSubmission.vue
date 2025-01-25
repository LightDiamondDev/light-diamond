<script setup lang="ts">
import axios, {type AxiosError} from 'axios'

import {computed, nextTick, onMounted, onUnmounted, reactive, ref} from 'vue'

import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'
import {onBeforeRouteLeave, RouterLink, useRouter} from 'vue-router'

import {
    getAppUrl,
    getErrorMessageByCode,
    getFullPresentableDate,
    getMaterialSubmissionStatusInfo,
    getRelativeDate,
    withCaptcha
} from '@/helpers'

import {
    type MaterialSubmission,
    type MaterialSubmissionActionReject,
    type MaterialSubmissionActionRequestChanges,
    MaterialSubmissionActionType,
    MaterialSubmissionStatus,
    SubmissionType,
    type User,
    UserRole
} from '@/types'

import slugify from '@sindresorhus/slugify'

import MaterialEditor from '@/components/material/editor/MaterialEditor.vue'
import MaterialSubmissionAction from '@/components/material/MaterialSubmissionAction.vue'

import Banner from '@/components/elements/Banner.vue'
import Button from '@/components/elements/Button.vue'
import Dialog from '@/components/elements/Dialog.vue'
import Input from '@/components/elements/Input.vue'
import OverlayPanel from '@/components/elements/OverlayPanel.vue'
import ShineButton from '@/components/elements/ShineButton.vue'
import Select, {type SelectOptionChangeEvent} from '@/components/elements/Select.vue'
import Textarea from '@/components/elements/Textarea.vue'

import UserAvatar from '@/components/user/UserAvatar.vue'
import useCategoryRegistry from '@/categoryRegistry'
import ProcessingDiggingBlocks from '@/components/elements/ProcessingDiggingBlocks.vue'

const props = defineProps({
    id: {
        type: Number,
        required: true
    }
})

const authStore = useAuthStore()
const toastStore = useToastStore()
const router = useRouter()

const bannerImagesSrc = ['/images/elements/general-banner-ancient-city.png']

const materialEditor = ref<InstanceType<typeof MaterialEditor>>()
const acceptOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()
const reconsiderOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()
const requestChangesOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()
const rejectOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()
const submitOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()

const materialSubmission = ref<MaterialSubmission>()
const moderators = ref<User[]>()

const isReviewing = computed(() => authStore.isModerator && materialSubmission.value!.status === MaterialSubmissionStatus.PENDING)
const isOwnDraft = computed(() => materialSubmission.value!.submitter_id === authStore.id && materialSubmission.value!.status === MaterialSubmissionStatus.DRAFT)
const materialSubmissionStatusInfo = computed(() => getMaterialSubmissionStatusInfo(materialSubmission.value?.status!))
const slug = computed<string>({
    get() {
        return materialSubmission.value!.material!.slug || slugify(materialSubmission.value!.material_state!.localizations![0].title ?? '')
    },
    set(newValue) {
        materialSubmission.value!.material!.slug = newValue
    }
})

const materialRoute = computed(() => ({
    name: 'material',
    params: {
        edition: materialSubmission.value!.material!.edition?.toLowerCase(),
        category: useCategoryRegistry().get(materialSubmission.value!.material!.category!)!.slug,
        slug: slug.value
    }
}))

const materialUrl = computed(() => new URL(router.resolve(materialRoute.value).fullPath, getAppUrl()).href)
const actions = computed(() => materialSubmission.value!.actions!.sort((a, b) => a.created_at!.localeCompare(b.created_at!)))
const lastAction = computed(() => actions.value!.at(actions.value!.length - 1))
const rejectDetails = reactive<MaterialSubmissionActionReject>({reason: ''})
const requestChangesDetails = reactive<MaterialSubmissionActionRequestChanges>({message: ''})

const actionHistoryContainer = ref<HTMLElement>()

const isChanged = ref(false)
const isAccepting = ref(false)
const isHistoryDialog = ref(false)
const isLoading = ref(false)
const isReconsidering = ref(false)
const isRejecting = ref(false)
const isRequestingChanges = ref(false)
const isSubmitting = ref(false)
const isUpdating = ref(false)

const errors = ref<{ [key: string]: string[] }>({})

const moderatorOptions = computed(() => {
    if (!moderators.value) {
        const options: User[] = []
        if (materialSubmission.value!.assigned_moderator) {
            options.push(materialSubmission.value!.assigned_moderator)
        }
        if (materialSubmission.value!.assigned_moderator_id !== authStore.id) {
            options.push(authStore.user!)
        }
        return options
    }

    const firstModeratorIds = [materialSubmission.value!.assigned_moderator_id, authStore.id]

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

function loadMaterialSubmission() {
    isLoading.value = true
    axios.get(`/api/material-submissions/${props.id}`).then((response) => {
        const submission: MaterialSubmission = response.data
        if (Object.keys(submission).length !== 0) {
            materialSubmission.value = submission
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

function submit() {
    withCaptcha(() => {
        isSubmitting.value = true
        errors.value = {}

        axios.patch(`/api/material-submissions/${props.id}/submit`, materialSubmission.value).then((response) => {
            if (response.data.success) {
                materialSubmission.value = response.data.submission
                isChanged.value = false
                toastStore.success('Заявка отправлена на рассмотрение.')
                submitOverlayPanel.value?.hide()
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
    })
}

function saveChanges() {
    withCaptcha(() => {
        isUpdating.value = true
        errors.value = {}

        axios.patch(`/api/material-submissions/${props.id}`, materialSubmission.value).then((response) => {
            if (response.data.success) {
                materialSubmission.value = response.data.submission
                isChanged.value = false
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
            isUpdating.value = false
        })
    })
}

function assignModerator(event: SelectOptionChangeEvent) {
    const moderator: User = event.option
    materialSubmission.value!.assigned_moderator = moderator

    axios.put(
        `/api/material-submissions/${props.id}/assigned-moderator`,
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
    errors.value = {}
    if (!materialSubmission.value!.material!.slug) {
        materialSubmission.value!.material!.slug = slug.value
    }

    const data = {...materialSubmission.value, slug: slug.value}

    axios.patch(`/api/material-submissions/${props.id}/accept`, data).then((response) => {
        if (response.data.success) {
            materialSubmission.value = response.data.submission
            isChanged.value = false
            switch (materialSubmission.value!.type) {
                case SubmissionType.CREATE:
                    toastStore.success(`Материал успешно опубликован!`)
                    break
                case SubmissionType.UPDATE:
                    toastStore.success(`Материал успешно обновлён!`)
                    break
                case SubmissionType.DELETE:
                    toastStore.success(`Материал успешно удалён!`)
                    break
            }
            acceptOverlayPanel.value?.hide()
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

function reject() {
    isRejecting.value = true
    errors.value = {}

    const data = {...materialSubmission.value, action_details: rejectDetails}

    axios.patch(`/api/material-submissions/${props.id}/reject`, data).then((response) => {
        if (response.data.success) {
            materialSubmission.value = response.data.submission
            isChanged.value = false
            toastStore.success('Заявка была отклонена.', 'Отклонено')
            rejectOverlayPanel.value?.hide()
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

function requestChanges() {
    isRequestingChanges.value = true
    errors.value = {}

    const data = {...materialSubmission.value, action_details: requestChangesDetails}

    axios.patch(`/api/material-submissions/${props.id}/request-changes`, data).then((response) => {
        if (response.data.success) {
            materialSubmission.value = response.data.submission
            isChanged.value = false
            toastStore.success('Заявка возвращена на доработку.')
            requestChangesOverlayPanel.value?.hide()
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

function reconsider() {
}

function openHistoryDialog() {
    isHistoryDialog.value = true
    nextTick(() => {
        scrollActionHistoryContainerToBottom()
    })
}

function scrollActionHistoryContainerToBottom() {
    actionHistoryContainer.value!.scrollTo({top: actionHistoryContainer.value!.scrollHeight, behavior: 'smooth'})
}

function confirmExit() {
    return window.confirm('Вы не сохранили изменения! Вы точно хотите выйти?')
}

function onBeforePageUnload(e: BeforeUnloadEvent|CloseEvent) {
    if (isChanged.value && !confirmExit()) {
        e.preventDefault()
        e.returnValue = ''
    }
}

onBeforeRouteLeave((to, from , next) => {
    if (!isChanged.value || confirmExit()) {
        next()
    } else {
        next(false)
    }
})

onMounted(() => {
    window.addEventListener('beforeunload', onBeforePageUnload)
    window.addEventListener('close', onBeforePageUnload)
})

onUnmounted(() => {
    window.removeEventListener('beforeunload', onBeforePageUnload)
    window.removeEventListener('close', onBeforePageUnload)
})

loadMaterialSubmission()
</script>

<template>
    <div v-if="isLoading" class="flex justify-center items-center overflow-hidden h-[85vh] w-full">
        <ProcessingDiggingBlocks processing-classes="md:h-[192px] md:w-[192px] h-[128px] w-[128px]"/>
    </div>
    <template v-else>
        <Dialog
            v-if="materialSubmission"
            class="material-submission-history w-full"
            v-model:visible="isHistoryDialog"
            title="История Действий"
            :dismissable-mask="true"
            :draggable="false"
            style="top: 0"
            :header="true"
            :modal="true"
        >
            <div class="material-submission-actions flex flex-col gap-4 md:p-6 xs:p-4 p-2" ref="actionHistoryContainer">
                <MaterialSubmissionAction v-for="action in actions" :action="action"/>
            </div>
        </Dialog>

        <MaterialEditor
            ref="materialEditor"
            v-if="materialSubmission"
            v-model="materialSubmission"
            :editable="isOwnDraft || isReviewing"
            :errors="errors"
            @edit="isChanged = true"
        >
            <template v-slot:banner>
                <Banner class="md:h-[208px] h-[178px] w-full" :images-src="bannerImagesSrc">
                    <template v-slot:banner-content>
                        <div
                            class="banner-title page-container flex flex-col justify-center items-center md:items-end max-w-[800px]">

                            <h1 class="ld-title-font flex self-center text-center md:text-[3rem] text-[1.5rem] locked">
                                Заявка на публикацию</h1>

                            <div class="mark-block flex absolute bottom-2 gap-2">
                                <div
                                    :class="{
                                        'new': materialSubmission.type === SubmissionType.CREATE,
                                        'update': materialSubmission.type === SubmissionType.UPDATE
                                    }"
                                    class="material-type new flex items-center h-fit gap-2 locked"
                                >
                                    <span
                                        class="icon flex"
                                        :class="{
                                            'icon-apple': materialSubmission.type === SubmissionType.CREATE,
                                            'icon-medium-top-arrow': materialSubmission.type === SubmissionType.UPDATE
                                        }"
                                    />
                                    <p class="text-[9px] xs:text-[.7rem]">
                                        {{
                                            materialSubmission.type === SubmissionType.CREATE ? 'Новый Материал' : 'Обновление'
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="time-ago time flex items-center h-fit gap-2 locked tooltip whitespace-nowrap"
                                    v-tooltip.top="'Обновлено: ' + getFullPresentableDate(materialSubmission!.updated_at!)"
                                >
                                    <span class="icon-clock icon flex"/>
                                    <p class="text-[9px] xs:text-[.7rem]">
                                        {{ getRelativeDate(materialSubmission!.updated_at!) }}
                                    </p>
                                </div>
                            </div>

                        </div>
                    </template>
                </Banner>
            </template>

            <template v-slot:sidebar>
                <div class="flex flex-col w-full">
                    <div
                        v-if="authStore.isModerator &&
                            (materialSubmission.status !== MaterialSubmissionStatus.DRAFT || materialSubmission.assigned_moderator)"
                        class="flex xl:flex-col flex-row"
                    >
                        <div class="ld-secondary-text flex items-center min-w-[100px] xl:px-2.5 px-5 py-1">
                            <p class="text-[12px] xl:mt-1">Модератор</p>
                        </div>
                        <div class="flex xl:max-w-none max-w-[200px] w-full">
                            <Select
                                v-model="materialSubmission.assigned_moderator_id"
                                button-classes="ld-primary-background ld-primary-border max-h-[64px]"
                                button-label-classes="hidden xs:flex"
                                options-classes="ld-primary-background ld-primary-border top-[56px]"
                                class="material-moderator flex items-center w-full mx-2"
                                :editable="materialSubmission.status === MaterialSubmissionStatus.PENDING"
                                :is-custom-option-item="true"
                                input-id="moderator"
                                :options="moderatorOptions"
                                option-classes="min-h-[54px] gap-1 px-2"
                                option-label-key="username"
                                option-value-key="id"
                                placeholder="Не назначен"
                                option-icon-key="icon"
                                @show="loadModerators"
                                @change="assignModerator"
                            >
                                <template #option-icon="{option}: {option: User}">
                                    <UserAvatar
                                        border-class-list="h-10 min-w-10"
                                        icon-class-list="h-7 min-w-7"
                                        :user="option"
                                    />
                                </template>

                                <template #option-item="{option}: { option: User}">
                                    <button class="flex gap-1 items-center cursor-pointer py-1 px-2 w-full"
                                            type="button">
                                        <UserAvatar
                                            border-class-list="h-10 min-w-10"
                                            icon-class-list="h-7 min-w-7"
                                            :user="option"
                                        />
                                        <p
                                            class="text-[12px] line-clamp-1"
                                            style="margin-top: 0;"
                                        >
                                            {{ option.username }}
                                        </p>
                                    </button>
                                </template>
                            </Select>
                        </div>
                    </div>

                    <div v-if="materialSubmission!.material!.published_at"
                         class="header-details-item flex xl:flex-col flex-row mt-1">
                        <div class="ld-secondary-text flex items-center min-w-[100px] xl:px-2.5 px-5 py-1">
                            <p class="text-[12px]">Материал</p>
                        </div>
                        <div class="flex items-center xl:px-2.5 px-4 py-1">
                            <RouterLink
                                :to="materialRoute"
                                class="ld-special-text line-clamp-2 text-[12px]
                                    border-0 hover:underline duration-200"
                            >
                                {{ materialSubmission!.material_state.localization.title }}
                            </RouterLink>
                        </div>
                    </div>

                    <div class="flex xl:flex-col flex-row">
                        <div
                            class="ld-secondary-text flex xl:min-w-[100px] min-w-[114px] xl:px-2.5 px-5 py-1"
                            :class="{'xl:mt-0 mt-3': !authStore.isModerator}"
                        >
                            <p class="flex items-center text-[12px]">Статус</p>
                        </div>
                        <div
                            class="flex items-center xl:px-2.5 px-4 py-1"
                            :class="{'xl:mt-0 mt-3': !authStore.isModerator}"
                        >
                            <div class="ld-primary-background">
                                <p
                                    :class="materialSubmissionStatusInfo.colorClass"
                                    class="transfusion bordered text-[12px] px-1 py-0.5"
                                >
                                    {{ materialSubmissionStatusInfo.name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="actions!.length !== 0"
                         class="flex flex-col xl:gap-1 gap-2 mt-1 xl:pb-1 xl:px-2 px-4">
                        <div class="separator flex opacity-40 xl:mb-1 bg-[var(--secondary-text-color)]"/>
                        <div
                            v-if="[MaterialSubmissionActionType.REJECT, MaterialSubmissionActionType.REQUEST_CHANGES].includes(lastAction?.type!)"
                            class="flex items-center"
                        >
                            <MaterialSubmissionAction
                                class="sidebar-last-action ld-secondary-background-action px-2 md:px-1"
                                :action="lastAction!"
                                :minimized="true"
                            />
                        </div>
                        <ShineButton
                            class="self-center text-[0.7rem] w-full xl:mb-1 mb-2"
                            :class="{
                                'non-mb-0': materialSubmission.status === MaterialSubmissionStatus.ACCEPTED ||
                                materialSubmission.status === MaterialSubmissionStatus.REJECTED
                            }"
                            class-wrap="ld-primary-background"
                            class-preset="gap-1 px-2 py-0.5 whitespace-nowrap"
                            class-label="block truncate"
                            label="История действий"
                            icon="icon-script min-w-[2rem]"
                            @click="openHistoryDialog"
                        />
                    </div>

                    <!--                    <div-->
                    <!--                        v-if="materialSubmission.status === MaterialSubmissionStatus.ACCEPTED || materialSubmission.status === MaterialSubmissionStatus.REJECTED"-->
                    <!--                        class="flex flex-col xl:gap-2 gap-4 xl:px-2 px-4 xl:pb-2 pb-4"-->
                    <!--                    >-->
                    <!--                        <ShineButton-->
                    <!--                            class="upper-unavailable w-full warning"-->
                    <!--                            class-wrap="ld-primary-background"-->
                    <!--                            class-preset="gap-2 px-2 py-0.5"-->
                    <!--                            @click="reconsiderOverlayPanel?.toggle"-->
                    <!--                            label="Вернуть на рассмотрение"-->
                    <!--                            icon="icon-eye"-->
                    <!--                        />-->
                    <!--                    </div>-->

                    <div v-if="isReviewing" class="upper-unavailable flex flex-col gap-2 xl:px-2 px-4 xl:pb-2 pb-4">
                        <div class="separator flex opacity-40" style="background-color: var(--secondary-text-color);"/>
                        <ShineButton
                            class-wrap="ld-primary-background"
                            class-preset="gap-2 px-2 py-0.5"
                            class="success"
                            label="Принять"
                            icon="icon-tick"
                            :disabled="!materialEditor?.hasAllFieldsFilled()"
                            @click="acceptOverlayPanel?.toggle"
                        />
                        <ShineButton
                            class-wrap="ld-primary-background"
                            class-preset="gap-2 px-2 py-0.5"
                            class="warning"
                            label="На доработку"
                            icon="icon-refresh"
                            :disabled="!materialEditor?.hasAllFieldsFilled()"
                            @click="requestChangesOverlayPanel?.toggle"
                        />
                        <ShineButton
                            class-wrap="ld-primary-background"
                            class-preset="gap-2 px-2 py-0.5"
                            class="danger"
                            label="Отклонить"
                            icon="icon-small-cross"
                            :disabled="!materialEditor?.hasAllFieldsFilled()"
                            @click="rejectOverlayPanel?.toggle"
                        />
                        <div class="separator flex opacity-40 xl:mb-1 bg-[var(--secondary-text-color)]"/>
                        <ShineButton
                            class-wrap="ld-primary-background"
                            class-preset="gap-2 px-2 py-0.5"
                            class="w-full"
                            label="Сохранить изменения"
                            :loading="isUpdating"
                            :disabled="!isChanged || !materialEditor?.hasAllFieldsFilled()"
                            icon="icon-book"
                            @click="saveChanges"
                        />
                    </div>
                </div>
                <div v-if="isOwnDraft"
                     class="upper-unavailable flex flex-col w-full xl:gap-2 gap-4 xl:px-2 px-4 xl:pb-2 pb-4">
                    <div class="separator flex opacity-40" style="background-color: var(--secondary-text-color);"/>
                    <ShineButton
                        class="w-full success"
                        class-wrap="ld-primary-background"
                        class-preset="gap-2 px-2 py-0.5"
                        label="На рассмотрение"
                        icon="icon-eye"
                        :disabled="!materialEditor?.hasAllFieldsFilled()"
                        @click="submitOverlayPanel?.toggle"
                    />
                    <ShineButton
                        class-wrap="ld-primary-background"
                        class-preset="gap-2 px-2 py-0.5"
                        class="w-full"
                        label="Сохранить изменения"
                        icon="icon-book"
                        :loading="isUpdating"
                        :disabled="!isChanged || !materialEditor?.hasAllFieldsFilled()"
                        @click="saveChanges"
                    />
                </div>
            </template>
        </MaterialEditor>

        <div v-else class="unavailable-material flex justify-center items-center">
            <div class="unavailable-material-container flex flex-col items-center">
                <h1 class="text-4xl font-bold text-center mt-8">Заявка на публикацию недоступна</h1>
                <p class="text-muted text-center mt-4">
                    Запрошенная заявка не существует, либо у Вас нет к ней доступа.
                </p>
                <div class="mob phantom flex justify-center items-center full-locked">
                    <div class="animation-flying-phantom"></div>
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

    <OverlayPanel
        class="overlay-panel ld-primary-background max-w-[100vw] p-4 mt-24rem"
        style="z-index: 1;"
        ref="acceptOverlayPanel"
    >
        <div class="flex flex-col gap-2">
            <template v-if="materialSubmission!.type === SubmissionType.CREATE">
                <p class="flex">URL-идентификатор</p>

                <Input
                    v-model="slug"
                    class="material-url-id ld-tinted-background ld-secondary-border h-[40px]"
                    placeholder="URL-идентификатор Материала"
                    id="material-url-id"
                />

                <p class="error">{{ errors['slug']?.[0] || ' ' }}</p>

                <p class="flex text-[10px] opacity-80">{{ materialUrl }}</p>
            </template>
            <template v-else>
                Вы точно хотите опубликовать изменения Материала?
            </template>

            <div class="flex gap-2">
                <ShineButton
                    class="flex"
                    @click="acceptOverlayPanel?.hide()"
                    class-preset="gap-2 px-2 py-0.5"
                    icon="icon-small-cross"
                    label="Отмена"
                />
                <ShineButton
                    class-preset="gap-2 px-2 py-0.5"
                    class="flex success"
                    :loading="isAccepting"
                    @click="accept"
                    label="Опубликовать"
                    icon="icon-tick"
                />
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel
        class="overlay-panel ld-primary-background max-w-[100vw] p-4 mt-24rem"
        ref="reconsiderOverlayPanel"
        style="z-index: 1;"
    >
        <div class="flex flex-col gap-2">

            <p class="flex">Вернуть на рассмотрение?</p>

            <p v-if="materialSubmission!.status === MaterialSubmissionStatus.ACCEPTED" class="flex">
                Материал будет убран с Каталога и Пользователи потеряют к нему доступ.
            </p>

            <p v-else-if="materialSubmission!.status === MaterialSubmissionStatus.REJECTED" class="flex">
                Материал будет восстановлен и перемещён на рассмотрение, пока не будет принят или снова отклонён.
            </p>

            <div class="flex gap-2">
                <ShineButton
                    class="flex"
                    @click="reconsiderOverlayPanel?.hide()"
                    class-preset="gap-2 px-2 py-0.5"
                    icon="icon-small-cross"
                    label="Отмена"
                />
                <ShineButton
                    class-preset="gap-2 px-2 py-0.5"
                    class="flex warning"
                    :loading="isReconsidering"
                    @click="reconsider"
                    icon="icon-tick"
                    label="Вернуть"
                />
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel
        class="overlay-panel ld-primary-background max-w-[100vw] p-4 mt-24rem"
        style="z-index: 1;"
        ref="rejectOverlayPanel"
    >
        <div class="flex flex-col gap-2">

            <p class="flex">Причина отклонения</p>

            <Textarea
                v-model="rejectDetails.reason"
                class="material-reject-reason ld-tinted-background ld-secondary-border"
                id="material-reject-reason"
                :max-length="185"
                :min-length="15"
                placeholder="Например, «Неприемлемый контент»"
                rows="3"
            />

            <p class="error">{{ errors['action_details.reason']?.[0] || ' ' }}</p>

            <div class="flex gap-2">
                <ShineButton
                    class="flex"
                    @click="rejectOverlayPanel?.hide()"
                    class-preset="gap-2 px-2 py-0.5"
                    icon="icon-small-cross"
                    label="Отмена"
                />
                <ShineButton
                    class="flex danger-lite"
                    :loading="isRejecting" @click="reject"
                    class-preset="gap-2 px-2 py-0.5"
                    icon="icon-tick"
                    label="Отклонить"
                />
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel
        class="overlay-panel ld-primary-background max-w-[100vw] p-4 mt-24rem z-[1]"
        ref="requestChangesOverlayPanel"
    >
        <div class="flex flex-col gap-2">

            <p class="flex">Что не так?</p>

            <Textarea
                v-model="requestChangesDetails.message"
                class="material-revision-reason ld-tinted-background ld-secondary-border"
                id="material-revision-reason"
                :max-length="185"
                :min-length="15"
                placeholder="Например, «Слишком короткое содержимое»"
                rows="3"
            />

            <p class="error">{{ errors['action_details.message']?.[0] || ' ' }}</p>

            <div class="flex gap-2">
                <ShineButton
                    class="flex"
                    @click="requestChangesOverlayPanel?.hide()"
                    class-preset="gap-2 px-2 py-0.5"
                    icon="icon-small-cross"
                    label="Отмена"
                />
                <ShineButton
                    class="flex warning-lite"
                    :loading="isRequestingChanges" @click="requestChanges"
                    class-preset="gap-2 px-2 py-0.5"
                    icon="icon-tick"
                    label="Отправить"
                />
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel class="overlay-panel ld-primary-background max-w-[100vw] p-4 mt-24rem" ref="submitOverlayPanel">
        <div class="flex flex-col gap-2">

            <p class="flex">Вы точно хотите отправить заявку на рассмотрение?</p>

            <div class="flex gap-2">
                <ShineButton
                    class="flex success"
                    :loading="isSubmitting" @click="submit"
                    class-preset="gap-2 px-2 py-0.5"
                    label="Да, отправить"
                    icon="icon-tick"
                />
                <ShineButton
                    class="flex"
                    @click="submitOverlayPanel?.hide()"
                    class-preset="gap-2 px-2 py-0.5"
                    icon="icon-small-cross"
                    label="Отмена"
                />
            </div>
        </div>
    </OverlayPanel>

</template>

<style>
.material-submission-history .dialog-header h1 {
    text-align: center;
    line-height: 1.2;
}

.material-reject-reason textarea,
.material-revision-reason textarea {
    min-height: 136px;
    padding: 4px 8px;
}

.upper-interaction .upper-unavailable {
    display: none;
}

.ld-shine-button .text {
    height: 2rem;
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

.upper-interaction .non-mb-0 {
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

.unavailable-material {
    height: 720px;
    width: 100%;
}

.unavailable-material-container {
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

.material-submission-actions {
    max-height: 500px;
    overflow-y: auto;
}

.sidebar-last-action {
    font-size: 12px;
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

/* =============== [ Медиа-Запрос { ?px < 451px } ] =============== */

@media screen and (max-width: 450px) {
    .material-type, .time-ago {
        padding: 0 .25rem;
        cursor: pointer;
    }

    .time-ago.tooltip::before {
        bottom: 2.4rem;
        left: -.5rem;
    }
}
</style>
