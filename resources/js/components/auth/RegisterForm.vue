<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {reactive, ref} from 'vue'

import {withCaptcha, getErrorMessageByCode} from '@/helpers'
import {useToastStore} from '@/stores/toast'
import {useRoute, useRouter} from 'vue-router'

import Button from '@/components/elements/Button.vue'
import Input from '@/components/elements/Input.vue'

const toastStore = useToastStore()

const registerData = reactive({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    agree: true
})

const isProcessing = ref(false)
const errors = ref([])
const router = useRouter()
const route = useRoute()

const emit = defineEmits(['switch-to-login-form', 'close'])

function submitRegister() {
    withCaptcha(() => {
        errors.value = []
        isProcessing.value = true

        axios.post('/api/auth/register', registerData).then((response) => {
            if (response.data.success) {
                router.replace({path: route.path, query: {...route.query, registered: true}}).then(() => {
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
    })
}
</script>

<template>
    <form action="" class="register flex flex-col items-center" name="register">
        <fieldset class="flex flex-col items-center">
            <div class="group flex flex-col items-center">
                <span class="subtitle text-[1.1rem] m-1">Никнейм</span>

                <Input
                    v-model="registerData.username"
                    id="register-nickname"
                    placeholder="Steve"
                    autocomplete="off"
                />

                <span
                    :class="{ 'error': errors['username'], 'success': !errors['username']}"
                    class="status text-[0.8rem] m-1"
                >
                    {{ errors['username']?.[0] || '&nbsp;' }}
                </span>
                <span class="subtitle text-[1.1rem] m-1">E-mail</span>

                <Input
                    v-model="registerData.email"
                    id="register-email"
                    placeholder="steve@minecraft.net"
                    autocomplete="off"
                    type="email"
                />

                <span
                    :class="{ 'error': errors['email'], 'success': !errors['email']}"
                    class="status text-[0.8rem] m-1"
                >
                    {{ errors['email']?.[0] || '&nbsp;' }}
                </span>
            </div>
            <div class="group set flex flex-col items-center">
                <span class="subtitle text-[1.1rem] m-1">Пароль</span>

                <Input
                    v-model="registerData.password"
                    id="register-password"
                    placeholder="Пароль"
                    autocomplete="off"
                    type="password"
                />

                <span
                    :class="{ 'error': errors['password'], 'success': !errors['password']}"
                    class="status text-[0.8rem] m-1"
                >
                    {{ errors['password']?.[0] || '&nbsp;' }}
                </span>

                <span class="subtitle text-[1.1rem] m-1">Повторный пароль</span>

                <Input
                    v-model="registerData.password_confirmation"
                    id="register-repeat-password"
                    placeholder="Повторный пароль"
                    autocomplete="off"
                    type="password"
                />

                <span
                    :class="{ 'error': errors['password_confirmation'], 'success': !errors['password_confirmation']}"
                    class="status text-[0.8rem] m-1"
                >
                    {{ errors['password_confirmation']?.[0] || '&nbsp;' }}
                </span>
            </div>
        </fieldset>

        <fieldset class="flex flex-col items-center">
            <div class="auth-options flex justify-between items-center mb-2 ">
                <label class="policy-agree-line flex items-center" for="policy-agree-checkbox">
                    <input
                        v-model="registerData.agree"
                        class="text-[0.9rem]"
                        id="policy-agree-checkbox"
                        name="agree"
                        type="checkbox"
                        value="true"
                    >
                    <span class="ld-checkbox icon-border flex justify-center items-center">
                        <span class="icon-tick"/>
                    </span>
                    <span class="policy-agree-me text-[0.8rem] whitespace-nowrap">Я соглашаюсь</span>
                </label>
                <div class="terms ld-title-font flex flex-col text-center text-[0.7rem]">
                    <RouterLink
                        class="ld-brilliant-link border-0"
                        :to="{ name: 'terms-of-use' }"
                        @click="emit('close')"
                    >
                        с Условиями Использования
                    </RouterLink>
                    <RouterLink
                        class="ld-brilliant-link border-0"
                        :to="{ name: 'privacy-policy' }"
                        @click="emit('close')"
                    >
                        и Политикой Конфиденциальности
                    </RouterLink>
                </div>
            </div>

            <Button
                :loading="isProcessing"
                button-type="submit"
                class="w-[85%]"
                icon="icon-bestiary max-w-[2rem]"
                label="Зарегистрироваться"
                @click.prevent="submitRegister"
                :disabled="!registerData.agree"
            />

            <button class="help-button mb-2 mt-2" type="button">
                <span class="p-2 text-[0.9rem]" @click="emit('switch-to-login-form')">Уже есть учётная запись?</span>
            </button>
        </fieldset>

    </form>
</template>

<style>
.auth-dialog form fieldset .action-button .label {
    width: fit-content;
}
</style>

<style scoped>
.ld-brilliant-link {
    color: var(--brilliant-color);
}

.ld-brilliant-link:hover {
    color: var(--hover-text-color);
    text-decoration: underline;
}

.policy-agree-line {
    background: none;
}
</style>
