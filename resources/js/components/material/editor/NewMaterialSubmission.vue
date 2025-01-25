<script setup lang="ts">
import MaterialEditor from '@/components/material/editor/MaterialEditor.vue'
import {computed, onMounted, onUnmounted, type PropType, ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, withCaptcha} from '@/helpers'
import {useToastStore} from '@/stores/toast'
import OverlayPanel from '@/components/elements/OverlayPanel.vue'
import {onBeforeRouteLeave, RouterLink, useRouter} from 'vue-router'
import {GameEdition, type Material, type MaterialSubmission, SubmissionType} from '@/types'
import {useAuthStore} from '@/stores/auth'
import ShineButton from '@/components/elements/ShineButton.vue'
import Banner from '@/components/elements/Banner.vue'
import Textarea from '@/components/elements/Textarea.vue'
import Button from '@/components/elements/Button.vue'
import usePreferenceManager from '@/preference-manager'
import useCategoryRegistry, {type Category} from '@/categoryRegistry'
import ProcessingDiggingBlocks from '@/components/elements/ProcessingDiggingBlocks.vue'

const props = defineProps({
    edition: String as PropType<GameEdition>,
    category: Object as PropType<Category>,
    slug: String,
})

const authStore = useAuthStore()
const toastStore = useToastStore()
const router = useRouter()
const preferenceManager = usePreferenceManager()

const bannerImagesSrc = ['/images/elements/general-banner-ancient-city.png']

const isLoading = ref(true)
const isSubmitting = ref(false)
const isSavingAsDraft = ref(false)
const materialEditor = ref<InstanceType<typeof MaterialEditor>>()
const submitOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()
const materialSubmission = ref<MaterialSubmission>()
const errors = ref<{ [key: string]: string[] }>({})

const canCreateSubmission = ref(true)
const activeSubmissionCount = ref(0)
const maxActiveSubmissionCount = ref(0)

const materialRoute = computed(() => ({
    name: 'material',
    params: {
        edition: materialSubmission.value?.material?.edition?.toLowerCase(),
        category: useCategoryRegistry().get(materialSubmission.value?.material?.category)!.slug,
        slug: materialSubmission.value?.material?.slug
    }
}))

if (!authStore.isModerator) {
    axios.get(`/api/auth/user/can-create-submission`).then((response) => {
        canCreateSubmission.value = response.data.can_create_submission
        activeSubmissionCount.value = response.data.active_submission_count
        maxActiveSubmissionCount.value = response.data.max_active_submission_count
        isLoading.value = false

        if (canCreateSubmission.value) {
            load()
        }
    })
} else {
    isLoading.value = false
    load()
}

function load() {
    if (props.slug && props.category) {
        loadMaterial()
    } else {
        materialSubmission.value = {
            material: {
                versions: [],
                edition: preferenceManager.getEdition()
            },
            material_state: {
                author_id: authStore.id,
                author: authStore.user,
                localizations: [{language: 'ru'}]
            },
            submitter: authStore.user,
            version_submissions: [],
            type: SubmissionType.CREATE
        }
    }
}

function loadMaterial() {
    isLoading.value = true
    axios.get(`/api/materials/${props.slug}`, {
        params: {
            edition: props.edition,
            category: props.category.type
        }
    }).then((response) => {
        const material: Material = response.data
        if (Object.keys(material).length !== 0 && material.state!.author_id === authStore.id) {
            if (material.active_submission_id) {
                router.replace({name: 'material-submission', params: {id: material.active_submission_id}})
                return
            }

            materialSubmission.value = {
                material: material,
                material_state: structuredClone(material.state),
                submitter: authStore.user,
                version_submissions: [],
                type: SubmissionType.UPDATE
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

function submit() {
    withCaptcha(() => {
        isSubmitting.value = true
        errors.value = {}

        axios.post('/api/material-submissions/submit', materialSubmission.value).then((response) => {
            if (response.data.success) {
                toastStore.success('Заявка отправлена на рассмотрение.')
                submitOverlayPanel.value?.hide()
                router.push({name: 'material-submission', params: {id: response.data.id}})
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

function saveAsDraft() {
    withCaptcha(() => {
        isSavingAsDraft.value = true
        errors.value = {}

        axios.post('/api/material-submissions', materialSubmission.value).then((response) => {
            if (response.data.success) {
                toastStore.success('Заявка сохранена как черновик.')
                const materialSubmissionId = response.data.id
                router.push({name: 'material-submission', params: {id: materialSubmissionId}})
            } else {
                if (response.data.errors) {
                    toastStore.error('Не все поля заполнены верно.')
                    errors.value = response.data.errors
                }
                if (response.data.message) {
                    toastStore.error(response.data.message)
                }
            }
        }).catch((error: AxiosError) => {
            toastStore.error(getErrorMessageByCode(error.response!.status))
        }).finally(() => {
            isSavingAsDraft.value = false
        })
    })
}

function confirmExit() {
    return window.confirm('Вы не сохранили заявку! Вы точно хотите выйти?')
}

function onBeforePageUnload(e: BeforeUnloadEvent|CloseEvent) {
    if (materialEditor.value?.hasAnyOfFieldsFilled() && !confirmExit()) {
        e.preventDefault()
        e.returnValue = ''
    }
}

onBeforeRouteLeave((to, from , next) => {
    if (isSavingAsDraft.value || isSubmitting.value || !materialEditor.value?.hasAnyOfFieldsFilled() || confirmExit()) {
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
</script>

<template>
    <div v-if="isLoading" class="flex justify-center items-center overflow-hidden h-[85vh] w-full">
        <ProcessingDiggingBlocks processing-classes="md:h-[192px] md:w-[192px] h-[128px] w-[128px]"/>
    </div>
    <div v-else-if="!canCreateSubmission" class="unavailable-material flex justify-center items-center">
        <div class="unavailable-material-container flex flex-col items-center">
            <h1 class="text-4xl font-bold text-center mt-8">Слишком много заявок</h1>
            <p class="text-muted text-center mt-4">
                Вы достигли лимита по количеству активных заявок на публикацию. <br>
                Максимальное количество - {{ maxActiveSubmissionCount }}.
            </p>
            <div class="mob guardian flex justify-center items-center full-locked">
                <div class="animation-elder-guardian"></div>
            </div>
            <div class="flex justify-center max-w-[480px] w-full mb-8">
                <Button
                    press-classes="px-4"
                    button-type="submit"
                    icon="icon icon-long-left-arrow"
                    icon-size="32px"
                    label="Вернуться назад"
                    @click="router.go(-1)"
                />
            </div>
        </div>
    </div>
    <div v-else-if="!materialSubmission" class="unavailable-material flex justify-center items-center">
        <div class="unavailable-material-container flex flex-col items-center">
            <h1 class="text-4xl font-bold text-center mt-8">Материал недоступен для обновления</h1>
            <p class="text-muted text-center mt-4">Материал не существует, либо вы не можете его обновлять.</p>
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
    <template v-else>
        <MaterialEditor
            ref="materialEditor"
            v-model="materialSubmission"
            upper-sidebar-classes="hidden"
            :is-upper-sidebar="false"
            :errors="errors"
        >
            <template v-slot:banner>
                <Banner class="md:h-[208px] h-[178px]" :images-src="bannerImagesSrc">
                    <template v-slot:banner-content>
                        <div
                            class="banner-title page-container flex flex-col justify-center items-center md:items-end max-w-[800px]"
                        >
                            <h1 class="ld-title-font flex self-center text-center md:text-[3rem] text-[1.5rem] locked">
                                {{
                                    materialSubmission.type === SubmissionType.CREATE ? 'Создание Материала' : 'Обновление Материала'
                                }}
                            </h1>
                        </div>
                    </template>
                </Banner>
            </template>

            <template v-slot:sidebar>
                <div class="create-material-upper-interaction flex flex-col w-full gap-2">
                    <div
                        v-if="materialSubmission.material?.published_at"
                        class="header-details-item flex xl:flex-col flex-row mt-1"
                    >
                        <div class="ld-secondary-text flex items-center min-w-[100px] xl:px-2.5 px-5 py-1">
                            <p class="text-[12px]">Материал</p>
                        </div>
                        <div class="flex items-center xl:px-2.5 px-4 py-1">
                            <RouterLink
                                :to="materialRoute"
                                class="ld-special-text line-clamp-2 text-[12px]
                                    border-0 hover:underline duration-200"
                            >
                                {{ materialSubmission.material.state.localization.title }}
                            </RouterLink>
                        </div>
                        <div class="separator flex opacity-40 xl:mb-1 bg-[var(--secondary-text-color)]"/>
                    </div>

                    <div class="upper-unavailable flex flex-col gap-2">
                        <ShineButton
                            class-wrap="ld-primary-background"
                            class-preset="text-[11px] gap-2 px-2 py-0.5"
                            class="success whitespace-nowrap"
                            label="На рассмотрение"
                            icon="icon-eye"
                            :disabled="!materialEditor?.hasAllFieldsFilled()"
                            @click="submitOverlayPanel?.toggle"
                        />
                        <ShineButton
                            class-wrap="ld-primary-background"
                            class-preset="text-[11px] gap-2 px-2 py-0.5"
                            label="Сохранить как черновик"
                            icon="icon-book min-w-[2rem]"
                            :disabled="!materialEditor?.hasAllFieldsFilled()"
                            @click="saveAsDraft"
                        />
                    </div>
                </div>
            </template>
        </MaterialEditor>

        <OverlayPanel
            class="overlay-panel ld-primary-background max-w-[100vw] p-4 mt-24rem"
            style="z-index: 1;"
            ref="submitOverlayPanel"
        >
            <div class="flex flex-col gap-2">
                <p class="flex">Вы точно хотите отправить заявку на рассмотрение?</p>

                <div class="flex gap-2">
                    <ShineButton
                        class="success flex whitespace-nowrap"
                        class-preset="gap-2 px-2 py-0.5"
                        icon="icon-tick"
                        label="Да, отправить"
                        :loading="isSubmitting"
                        @click="submit"
                    />
                    <ShineButton
                        class-preset="gap-2 px-2 py-0.5"
                        class="flex"
                        label="Отмена"
                        icon="icon-small-cross"
                        :disabled="isSubmitting"
                        @click="submitOverlayPanel?.hide()"
                    />
                </div>
            </div>
        </OverlayPanel>
    </template>
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

.mob.guardian {
    overflow: hidden;
    max-width: 260px;
    height: 200px;
    width: 100%;
}

.mob.guardian div {
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
