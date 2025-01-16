<script setup lang='ts'>
import axios, {type AxiosError} from 'axios'
import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'
import {getErrorMessageByCode} from '@/helpers'
import {type User, UserRole} from '@/types'
import {ref} from 'vue'

import Select from '@/components/elements/Select.vue'
import Input from '@/components/elements/Input.vue'
import Button from '@/components/elements/Button.vue'

interface ResponseData {
    success: boolean
    message?: string
    errors?: string[][]
}

const props = defineProps<{
    user?: User
}>()

const authStore = useAuthStore()
const toastStore = useToastStore()
const apiUrl = '/api/users'

const user = ref<User>(props.user || { role: UserRole.USER })
const errors = ref<string[][]>([])
const isProcessing = ref(false)

const userRoles = ref([
    { icon: 'icon-skin', label: 'Пользователь', value: UserRole.USER },
    { icon: 'icon-emerald-dagger', label: 'Модератор', value: UserRole.MODERATOR },
    { icon: 'icon-charoit-crown', label: 'Администратор', value: UserRole.ADMIN },
])

const emit = defineEmits(['cancel', 'processed'])

function update() {
    errors.value = []
    isProcessing.value = true

    axios.put(`${apiUrl}/${user.value.id}`, user.value!).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            toastStore.success(`Данные Пользователя ${user.value.username} изменены.`)
            emit('processed')
        } else {
            if (data.errors) {
                errors.value = data.errors
            }
            if (data.message) {
                toastStore.error(data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessing.value = false
    })
}

function add() {
    errors.value = []
    isProcessing.value = true

    axios.post(`${apiUrl}`, user.value).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            toastStore.success(`Добавлен новый Пользователь ${user.value.username}.`)
            emit('processed')
        } else {
            if (data.errors) {
                errors.value = data.errors
            }
            if (data.message) {
                toastStore.error(data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessing.value = false
    })
}

function save() {
    user.value.id ? update() : add()
}
</script>

<template>
    <form action="" class="user flex flex-col items-center w-full" name="user" @submit.prevent="save">
        <fieldset class="flex flex-col items-center w-full">
            <div class="group flex flex-col w-[85%]">
                <span class="subtitle md:text-[14px] text-[12px] my-2" :class="{ 'error': errors['username'] }">Никнейм</span>

                <Input
                    v-model="user.username"
                    class="h-[48px]"
                    id="user-nickname"
                />

                <span
                    class="status md:text-[12px] text-[10px] mt-2"
                    :class="{ 'error': errors['username'] }"
                >
                    {{ errors['username']?.[0] || '&nbsp;' }}
                </span>

                <span class="subtitle md:text-[14px] text-[12px] my-2" :class="{ 'error': errors['email'] }">E-mail</span>

                <Input
                    v-model="user.email"
                    class="h-[48px]"
                    id="user-email"
                    type="email"
                />

                <span
                    class="status md:text-[12px] text-[10px] mt-2"
                    :class="{ 'error': errors['email'] }"
                >
                    {{ errors['email']?.[0] || '&nbsp;' }}
                </span>

                <span class="subtitle md:text-[14px] text-[12px] my-2" :class="{ 'error': errors['role'] }">Роль</span>

                <Select
                    options-classes="ld-primary-background ld-primary-border mt-[-2px]"
                    class="flex self-center w-full"
                    v-model="user.role"
                    button-classes="ld-primary-border ld-title-font h-[64px]"
                    input-id="user-role"
                    :options="userRoles"
                    option-classes="h-[64px] gap-2 pl-4"
                    option-label-key="label"
                    option-icon-key="icon"
                    option-value-key="value"
                    placeholder="Роль"
                />

                <span
                    class="status md:text-[12px] text-[10px] mt-2"
                    :class="{ 'error': errors['role'] }"
                >
                    {{ errors['role']?.[0] || '&nbsp;' }}
                </span>
            </div>

            <div class="flex justify-center w-[85%] gap-2 mb-6 mt-2">
                <Button
                    :loading="isProcessing"
                    button-type="submit"
                    label="Сохранить"
                    class="success w-full"
                />

                <Button
                    label="Отмена"
                    class="secondary w-full"
                    @click="$emit('cancel')"
                />
            </div>
        </fieldset>
    </form>
</template>

<style scoped>

</style>
