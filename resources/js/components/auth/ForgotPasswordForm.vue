<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {reactive} from 'vue'
import {ref} from 'vue'

import {getErrorMessageByCode} from '@/helpers'
import {useToastStore} from '@/stores/toast'

import Button from '@/components/elements/Button.vue'
import Input from '@/components/elements/Input.vue'

const toastStore = useToastStore()

const forgotPasswordData = reactive({ email: '' })
const isProcessing = ref(false)
const errors = ref([])

function submitForgotPassword() {
    isProcessing.value = true
    errors.value = []

    axios.post('/api/auth/forgot-password', forgotPasswordData).then((response) => {
        if (response.data.success) {
            toastStore.success(`Ссылка для сброса пароля отправлена на ${forgotPasswordData.email}.`)
        } else {
            if (response.data.errors) {
                errors.value = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(`${response.data.message}`)
            }
            return
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessing.value = false
    })
}

</script>

<template>

    <form action="" class="forgot-password flex flex-col items-center">

        <fieldset class="flex flex-col items-center">
            <span class="task text-[0.8rem] mb-2 mt-2">
                Укажите свой E-mail адрес, к которому привязана учётная запись,
                и мы отправим на него письмо с подтверждением сброса пароля:
            </span>
            <span
                :class="{ 'error': errors['email'], 'success': !errors['email']}"
                class="subtitle text-[1.1rem] m-2"
            >
                E-mail
            </span>

            <Input
                v-model="forgotPasswordData.email"
                id="reset-email"
                placeholder="steve@minecraft.net"
                type="email"
            />

            <span
                :class="{ 'error': errors['email'], 'success': !errors['email']}"
                class="status text-[0.8rem] mt-1"
            >
                {{ errors['email']?.[0] || '&nbsp;' }}
            </span>
        </fieldset>

        <div class="group flex flex-col items-center">
            <div class="mob iron-golem flex justify-center items-center mb-4">
                <div class="animation-walking-iron-golem mb-8 ml-10"></div>
            </div>

            <Button
                button-type="submit"
                icon="icon-bestiary"
                icon-size="32px"
                text="Получить Письмо"
                :loading="isProcessing"
                @click.prevent="submitForgotPassword"
            />

        </div>

    </form>

</template>

<style scoped>

</style>
