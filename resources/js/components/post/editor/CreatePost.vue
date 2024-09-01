<script setup lang="ts">
import PostEditor from '@/components/post/editor/PostEditor.vue'
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode} from '@/helpers'
import { useToastStore } from '@/stores/toast'
import OverlayPanel from '@/components/elements/OverlayPanel.vue'
import {useRouter} from 'vue-router'
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
    <PostEditor v-model="postVersion" :author="authStore.user!" :errors="errors">

        <template v-slot:banner>
            <Banner title="Создание Материала"/>
        </template>

        <template v-slot:sidebar>
            <div class="flex flex-col gap-2 p-2">
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
                <p class="error">{{ errors['category_id']?.[0] || ' ' }}</p>
            </div>
        </template>
    </PostEditor>

    <OverlayPanel ref="submitOverlayPanel" class="overlay-panel max-w-[100vw] p-4 mt-24rem">
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

<style scoped>

</style>
