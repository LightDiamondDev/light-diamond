<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {ref} from 'vue'

import {getErrorMessageByCode} from '@/helpers'
import {useToastStore} from '@/stores/toast'
import {useRouter, useRoute} from 'vue-router'

import Button from '@/components/elements/Button.vue'
import Input from '@/components/elements/Input.vue'

const toastStore = useToastStore()

const loginData = ref({
    username: '',
    password: '',
    remember: true
})

const isProcessing = ref(false)
const errors = ref([])
const router = useRouter()
const route = useRoute()

const emit = defineEmits([
    'switch-to-forgot-password-form',
    'switch-to-register-form'
])
// errors.value['password'][0] = 'Тест'
function submitLogin() {
    isProcessing.value = true
    errors.value = []

    axios.post('/api/auth/login', loginData.value).then((response) => {
        if (response.data.success) {
            router.replace({ path: route.path, query: { ...route.query, authorized: true } }).then(() => {
                router.go(0)
            })
        } else {
            if (response.data.errors) {
                errors.value = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessing.value = false
    })
}
</script>

<template>
    <form action="" class="login flex flex-col items-center">
        <fieldset class="flex flex-col items-center">
            <span class="subtitle text-[1.1rem] m-2">
                Никнейм
                <!--
                <span class="ml-1 mr-1" style="font-size: .8rem; opacity: .8;">или</span>
                E-mail
                -->
            </span>

            <!-- Steve или steve@minecraft.net -->
            <Input
                v-model="loginData.username"
                id="login-nickname"
                placeholder="Steve"
            />

            <span
                :class="{ 'error': errors['username'], 'success': !errors['username']}"
                class="status text-[0.8rem] m-2"
            >
                {{ errors['username']?.[0] || '&nbsp;' }}
            </span>
            <span class="subtitle text-[1.1rem] m-2">Пароль</span>

            <Input
                v-model="loginData.password"
                id="login-password"
                placeholder="Пароль"
                type="password"
            />

            <span
                :class="{ 'error': errors['password'], 'success': !errors['password']}"
                class="status text-[0.8rem] m-2"
            >
                {{ errors['password']?.[0] || '&nbsp;' }}
            </span>
            <div class="auth-options flex justify-between mt-2">
                <label class="auth-remember-line flex items-center" for="auth-remember-checkbox">
                    <input
                        v-model="loginData.remember"
                        class="text-[0.9rem]"
                        id="auth-remember-checkbox"
                        name="remember"
                        type="checkbox"
                        value="true"
                    >
                    <span class="ld-checkbox icon-border flex justify-center items-center">
                        <span class="icon-tick"></span>
                    </span>
                    <span class="auth-remember-me text-[0.8rem]">Запомнить меня</span>
                </label>
                <button class="help-button flex items-center" type="button">
                    <span class="text-[0.8rem]" @click="emit('switch-to-forgot-password-form')">Забыли пароль?</span>
                </button>
            </div>
        </fieldset>

        <div class="group flex flex-col items-center">
            <div class="mob parrot flex justify-center items-center mb-4">
                <div class="animation-dancing-multicoloured-parrot"></div>
            </div>

            <Button
                button-type="submit"
                icon="icon-bestiary"
                icon-size="32px"
                text="Войти"
                :loading="isProcessing"
                @click.prevent="submitLogin"
            />

            <button class="help-button mb-2 mt-2" type="button">
                <span class="p-2 text-[0.9rem]" @click="emit('switch-to-register-form')">Ещё нет учётной записи?</span>
            </button>
        </div>

    </form>

</template>

<style scoped>
.auth-remember-line { background: none; }
</style>
