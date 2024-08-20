<script setup lang="ts">
import PostEditor from '@/components/post/editor/PostEditor.vue'
import Button from '@/components/elements/Button.vue'
import {onUnmounted, ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode} from '@/helpers'
import { useToastStore } from '@/stores/toast'
import OverlayPanel from '@/components/elements/OverlayPanel.vue'
import {useRouter} from 'vue-router'
import type {PostVersion} from '@/types'
import {useAuthStore} from '@/stores/auth'
import ShineButton from '@/components/elements/ShineButton.vue'

const authStore = useAuthStore()
const toastStore = useToastStore()
const router = useRouter()
const isSubmitting = ref(false)
const isSavingAsDraft = ref(false)
const submitOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()
const postVersion = ref<PostVersion>({})

document.addEventListener('scroll', onScroll)

onUnmounted(() => {
    document.removeEventListener('scroll', onScroll)
})

function onScroll() {
    if (submitOverlayPanel.value?.['visible']) {
        // submitOverlayPanel.value?.alignOverlay()
    }
}

function submit() {
    isSubmitting.value = true

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
                // TODO отображать ошибки в редакторе
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

    const formData = new FormData()
    Object.keys(postVersion.value).forEach(key => formData.append(key, postVersion.value[key]))

    axios.post('/api/post-versions', formData).then((response) => {
        if (response.data.success) {
            toastStore.success('Материал сохранён как черновик.')
            const postVersionId = response.data.id
            router.push({name: 'post-version', params: {id: postVersionId}})
        } else {
            if (response.data.errors) {
                toastStore.error('Не все поля заполнены корректно.')
                // TODO отображать ошибки в редакторе
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
    <PostEditor v-model="postVersion" :author="authStore.user!">

        <template v-slot:title>
            <div class="title flex items-center h-[72px] p-12">
                <h1 class="text-[1.5rem] md:text-[3rem] flex justify-center w-full">Создание Материала</h1>
            </div>
        </template>

        <template v-slot:sidebar>
            <ShineButton
                class="shine-button w-full confirm"
                @click="submitOverlayPanel?.toggle"
                text="На рассмотрение"
                icon="icon-eye"
            />
            <ShineButton
                class="shine-button w-full"
                @click="saveAsDraft"
                text="Сохранить как черновик"
                :loading="isSavingAsDraft"
                icon="icon-script"
            />
        </template>
    </PostEditor>

    <OverlayPanel ref="submitOverlayPanel" class="overlay-panel max-w-[100vw] p-4 mt-20rem">
        <div class="flex flex-col gap-2">

            <p class="flex">Вы точно хотите отправить Материал на рассмотрение?</p>

            <div class="flex gap-2">
                <ShineButton
                    class="flex confirm"
                    :loading="isSubmitting" @click="submit"
                    icon="icon-tick"
                    text="Да, отправить"
                />
                <ShineButton
                    class="flex"
                    @click="submitOverlayPanel?.hide()"
                    icon="icon-small-cross"
                    text="Отмена"
                />
            </div>
        </div>
    </OverlayPanel>

</template>

<style>
.title {
    color: var(--secondary-text-color);
}
.shine-button .press,
.shine-button .preset {
    width: 100%;
}
.shine-button .text {
    height: 2rem;
}
.shine-button .preset {
    padding: .5rem;
}
</style>
