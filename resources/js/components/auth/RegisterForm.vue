<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {reactive} from 'vue'
import {ref} from 'vue'

import {getErrorMessageByCode} from '@/helpers'
import {useToastStore} from '@/stores/toast'
import {useRouter, useRoute} from 'vue-router'

import Button from '@/components/elements/Button.vue'
import Input from '@/components/elements/Input.vue'

const toastStore = useToastStore()

const registerData = reactive({
    username: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const isProcessing = ref(false)
const errors = ref([])
const router = useRouter()
const route = useRoute()

const emit = defineEmits([ 'switch-to-login-form' ])

function submitRegister() {
    isProcessing.value = true
    errors.value = []

    axios.post('/api/auth/register', registerData).then((response) => {
        if (response.data.success) {
            router.replace({ path: route.path, query: { ...route.query, registered: true } }).then(() => {
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
    <form action="" class="register flex flex-col items-center" name="register">
        <fieldset class="flex flex-col items-center">
            <div class="group flex flex-col items-center">
                <span class="subtitle text-[1.1rem] m-2">Никнейм</span>

                <Input
                    v-model="registerData.username"
                    id="register-nickname"
                    placeholder="Steve"
                    autocomplete="off"
                />

                <span
                    :class="{ 'error': errors['username'], 'success': !errors['username']}"
                    class="status text-[0.8rem] m-2"
                >
                    {{ errors['username']?.[0] || '&nbsp;' }}
                </span>
                <span class="subtitle text-[1.1rem] m-2">E-mail</span>

                <Input
                    v-model="registerData.email"
                    id="register-email"
                    placeholder="steve@minecraft.net"
                    autocomplete="off"
                    type="email"
                />

                <span
                    :class="{ 'error': errors['email'], 'success': !errors['email']}"
                    class="status text-[0.8rem] m-2"
                >
                    {{ errors['email']?.[0] || '&nbsp;' }}
                </span>
            </div>
            <div class="group set flex flex-col items-center">
                <span class="subtitle text-[1.1rem] m-2">Пароль</span>

                <Input
                    v-model="registerData.password"
                    id="register-password"
                    placeholder="Пароль"
                    autocomplete="off"
                    type="password"
                />

                <span
                    :class="{ 'error': errors['password'], 'success': !errors['password']}"
                    class="status text-[0.8rem] m-2"
                >
                    {{ errors['password']?.[0] || '&nbsp;' }}
                </span>

                <span class="subtitle text-[1.1rem] m-2">Повторный пароль</span>

                <Input
                    v-model="registerData.password_confirmation"
                    id="register-repeat-password"
                    placeholder="Повторный пароль"
                    autocomplete="off"
                    type="password"
                />

                <span
                    :class="{ 'error': errors['password_confirmation'], 'success': !errors['password_confirmation']}"
                    class="status text-[0.8rem] m-2"
                >
                    {{ errors['password_confirmation']?.[0] || '&nbsp;' }}
                </span>
            </div>
        </fieldset>

        <Button
            button-type="submit"
            icon="icon-bestiary"
            label="Зарегистрироваться"
            :loading="isProcessing"
            @click.prevent="submitRegister"
        />

        <button class="help-button mb-2 mt-2" type="button">
            <span class="p-2 text-[0.9rem]" @click="emit('switch-to-login-form')">Уже есть учётная запись?</span>
        </button>

    </form>
</template>

<style scoped>

</style>
