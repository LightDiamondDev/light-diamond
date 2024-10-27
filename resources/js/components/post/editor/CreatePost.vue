<script setup lang="ts">
import PostEditor from '@/components/post/editor/PostEditor.vue'
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {convertObjectToFormData, getErrorMessageByCode} from '@/helpers'
import { useToastStore } from '@/stores/toast'
import OverlayPanel from '@/components/elements/OverlayPanel.vue'
import {RouterLink, useRouter} from 'vue-router'
import type {PostVersion} from '@/types'
import {useAuthStore} from '@/stores/auth'
import ShineButton from '@/components/elements/ShineButton.vue'
import Banner from '@/components/elements/Banner.vue'
import Button from '@/components/elements/Button.vue'
import ProcessingMovingItems from '@/components/elements/ProcessingMovingItems.vue'
import Textarea from '@/components/elements/Textarea.vue'

const authStore = useAuthStore()
const toastStore = useToastStore()
const router = useRouter()

const bannerImagesSrc = [ '/images/elements/general-banner-ancient-city.png' ]

const isLoading = ref(false)
const isSubmitting = ref(false)
const isSavingAsDraft = ref(false)
const submitOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()
const postVersion = ref<PostVersion>({})

const errors = ref<{ [key: string]: string[] }>({})

function submit() {
    isSubmitting.value = true
    errors.value = {}

    const formData = convertObjectToFormData(postVersion.value)

    axios.post('/api/post-versions/submit', formData).then((response) => {
        if (response.data.success) {
            toastStore.success('Материал отправлен на рассмотрение.')
            submitOverlayPanel.value?.hide()
            router.push({name: 'post-version', params: {id: response.data.id}})
        } else {
            if (response.data.errors) {
                toastStore.error('Не все поля заполнены корректно.')
                errors.value = response.data.errors

                console.log(errors)
                console.log(errors.value)

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

function saveAsDraft() {
    isSavingAsDraft.value = true
    errors.value = {}

    const formData = convertObjectToFormData(postVersion.value)

    axios.post('/api/post-versions', formData).then((response) => {
        if (response.data.success) {
            toastStore.success('Материал сохранён как черновик.')
            const postVersionId = response.data.id
            router.push({name: 'post-version', params: {id: postVersionId}})
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
}
</script>

<template>
    <div v-if="isLoading" class="flex justify-center items-center">
        <ProcessingMovingItems/>
    </div>
    <template v-else>
        <PostEditor
            v-if="postVersion"
            v-model="postVersion"
            :author="authStore.user"
            upper-sidebar-classes="hidden"
            :is-upper-sidebar="false"
            :errors="errors"
        >
            <template v-slot:banner>
                <Banner class="md:h-[208px] h-[178px]" :images-src="bannerImagesSrc">
                    <template v-slot:banner-content>
                        <div class="banner-title page-container flex flex-col justify-center items-center md:items-end max-w-[800px]">

                            <h1 class="ld-title-font flex self-center text-center md:text-[3rem] text-[1.5rem] locked">Создание Материала</h1>

                        </div>
                    </template>
                </Banner>
            </template>

            <template v-slot:sidebar>
                <div class="create-post-upper-interaction flex flex-col w-full">

                    <div class="create-post-upper-interaction flex flex-col gap-2 p-2">
                        <ShineButton
                            class-wrap="ld-primary-background"
                            class-preset="text-[11px] gap-2 px-2 py-0.5"
                            class="confirm whitespace-nowrap"
                            @click="submitOverlayPanel?.toggle"
                            label="На рассмотрение"
                            icon="icon-eye"
                        />
                        <ShineButton
                            class-wrap="ld-primary-background"
                            class-preset="text-[11px] gap-2 px-2 py-0.5"
                            @click="saveAsDraft"
                            label="Сохранить как черновик"
                            icon="icon-script"
                        />
                    </div>
                </div>
            </template>
        </PostEditor>

        <div v-else class="unavailable-post flex justify-center items-center">
            <div class="unavailable-post-container flex flex-col items-center">
                <h1 class="text-4xl font-bold text-center mt-8">Создание публикации недоступно</h1>
                <p class="text-muted text-center mt-4">Вам было запрещено создавать публикации.</p>
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
        ref="submitOverlayPanel"
    >
        <div class="flex flex-col gap-2">

            <p class="flex">Вы точно хотите отправить Материал на рассмотрение?</p>

            <div class="flex gap-2">
                <ShineButton
                    class="confirm flex whitespace-nowrap"
                    :loading="isSubmitting" @click="submit"
                    class-preset="gap-2 px-2 py-0.5"
                    icon="icon-tick"
                    label="Да, отправить"
                />
                <ShineButton
                    class-preset="gap-2 px-2 py-0.5"
                    class="flex"
                    :loading="isSubmitting"
                    @click="submitOverlayPanel?.hide()"
                    label="Отмена"
                    icon="icon-small-cross"
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
        left: -.5rem;
    }
}
</style>
