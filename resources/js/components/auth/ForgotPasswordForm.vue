<script setup lang="ts">

import Button from '@/components/elements/Button.vue'

import {reactive} from 'vue'
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'

import {getErrorMessageByCode} from '@/helpers'
import {useToastStore} from '@/stores/toast'

const toastStore = useToastStore()

const isProcessing = ref(false)
const errors = ref([])
const forgotPasswordData = reactive({
    email: '',
})

function submitForgotPassword() {
    isProcessing.value = true
    errors.value = []

    toastStore.error('Тестовая ошибка!')

    axios.post('/api/auth/forgot-password', forgotPasswordData).then((response) => {
        if (response.data.success) {

            toastStore.success(`Ссылка для сброса пароля отправлена на ${forgotPasswordData.email}.`)

        } else {
            if (response.data.errors) {
                errors.value = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(`${response.data.message}`)
                console.log(response.data.message)
            }
            return
        }
    }).catch((error: AxiosError) => {
        toastStore.error(`${getErrorMessageByCode(error.response!.status)}`)
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

            <label for="reset-email">
                <input
                    v-model="forgotPasswordData.email"
                    aria-describedby="email-error"
                    autocomplete="email"
                    class="text-[0.9rem]"
                    id="reset-email"
                    placeholder="steve@minecraft.net"
                    type="email"
                >
            </label>

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
