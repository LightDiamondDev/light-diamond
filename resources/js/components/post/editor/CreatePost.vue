<script setup lang="ts">
import PostEditor from '@/components/post/editor/PostEditor.vue'
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, getFullPresentableDate, getRelativeDate} from '@/helpers'
import { useToastStore } from '@/stores/toast'
import OverlayPanel from '@/components/elements/OverlayPanel.vue'
import {RouterLink, useRouter} from 'vue-router'
import type {PostVersion} from '@/types'
import {useAuthStore} from '@/stores/auth'
import ShineButton from '@/components/elements/ShineButton.vue'
import Banner from '@/components/elements/Banner.vue'

const authStore = useAuthStore()
const toastStore = useToastStore()
const router = useRouter()
const isSubmitting = ref(false)
const isSavingAsDraft = ref(false)
const submitOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()
const postVersion = ref<PostVersion>({})

const errors = ref<{ [key: string]: string[] }>({})

function submit() {
    isSubmitting.value = true
    errors.value = {}

    const formData = new FormData()
    Object.keys(postVersion.value).forEach(key => formData.append(key, postVersion.value[key]))

    axios.post('/api/post-versions/submit', formData).then((response) => {
        if (response.data.success) {
            toastStore.success('Материал отправлен на модерацию.')
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

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))

    axios.post('/api/post-versions', formData).then((response) => {
        if (response.data.success) {
            toastStore.success('Материал сохранён как черновик.')
            const postVersionId = response.data.id
            router.push({name: 'post-version', params: {id: postVersionId}})
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
        isSavingAsDraft.value = false
    })
}
</script>

<template>
    <PostEditor
        v-model="postVersion"
        :author="authStore.user!"
        :errors="errors"
        :is-upper-sidebar="false"
        upper-sidebar-classes="hidden"
    >

        <template v-slot:banner>
            <Banner :is-title-display="false"/>
        </template>

        <template v-slot:header>
            <div class="banner-title page-container flex flex-col justify-center items-center xs:items-end max-w-[800px]">

                <RouterLink
                    class="logo-wrap flex justify-center items-center relative xs:w-full full-locked"
                    :to="{ name: 'home' }"
                >
                    <h1 class="title-font text-center">Создание Материала</h1>
                </RouterLink>

            </div>
        </template>

        <template v-slot:sidebar>
            <div class="create-post-upper-interaction flex flex-col gap-2 p-2">
                <ShineButton
                    class="ld-shine-button w-full confirm"
                    @click="submitOverlayPanel?.toggle"
                    label="На рассмотрение"
                    icon="icon-eye"
                />
                <ShineButton
                    class="ld-shine-button w-full"
                    @click="saveAsDraft"
                    label="Сохранить как черновик"
                    :loading="isSavingAsDraft"
                    icon="icon-script"
                />
            </div>
        </template>
    </PostEditor>

    <OverlayPanel ref="submitOverlayPanel" class="overlay-panel ld-primary-background max-w-[100vw] p-4 mt-24rem">
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
</style>

<style scoped>

</style>
