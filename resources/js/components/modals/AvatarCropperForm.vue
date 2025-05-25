<script setup lang="ts">
import {ref} from 'vue'

import {Cropper} from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'

import Button from '@/components/elements/Button.vue'
import {getErrorMessageByCode, withCaptcha} from '@/helpers'
import axios, {type AxiosError} from 'axios'
import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'

defineProps({
    imageSrc: {
        type: String,
        required: true
    },
})

const emit = defineEmits<{
    (e: 'close'): void
}>()

const authStore = useAuthStore()
const toastStore = useToastStore()
const avatarCropper = ref<any>()
const isProcessingAvatar = ref(false)

function cancelChangeAvatar() {
    emit('close')
}

function submitChangeAvatar() {
    isProcessingAvatar.value = true

    avatarCropper.value?.getResult().canvas.toBlob((blob) => {
        withCaptcha(() => {
            const formData = new FormData()
            formData.append('avatar', blob)
            axios.post('/api/settings/profile/avatar', formData).then((response) => {
                if (response.data.success) {
                    authStore.user!.avatar_url = response.data.avatar_url
                } else {
                    if (response.data.errors) {
                        toastStore.error(response.data.errors['avatar'][0])
                    }
                }
            }).catch((error: AxiosError) => {
                toastStore.error(getErrorMessageByCode(error.response!.status))
            }).finally(() => {
                isProcessingAvatar.value = false
                emit('close')
            })
        })
    })
}
</script>

<template>
    <form action="" class="avatar-cropper-form flex flex-col items-center" name="avatar-cropper">
        <p class="ld-primary-border-bottom subtitle md:text-[14px] text-[12px] text-center py-2 px-4">
            Выбранная область изображения будет отображаться в качестве Вашего Аватара.
        </p>
        <Cropper
            :src="imageSrc"
            :stencil-props="{ aspectRatio: 1 }"
            ref="avatarCropper"
        />
        <div class="ld-primary-border-top flex justify-center items-end w-full gap-2 py-2">
            <Button
                button-type="button"
                label="Подтвердить"
                label-classes="px-6"
                class="success xs:w-[40%]"
                :loading="isProcessingAvatar"
                @click="submitChangeAvatar"
            />
            <Button
                button-type="button"
                label="Отмена"
                label-classes="px-6"
                class="secondary xs:w-[40%]"
                @click="cancelChangeAvatar"
            />
        </div>
    </form>
</template>
