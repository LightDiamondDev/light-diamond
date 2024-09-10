<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'
import {getErrorMessageByCode} from '@/helpers'
import ImageLoader from '@/components/elements/ImageLoader.vue'
import Input from '@/components/elements/Input.vue'
import Button from '@/components/elements/Button.vue'
import {ref} from 'vue'

const authStore = useAuthStore()
const toastStore = useToastStore()

const avatarData = ref({
    avatar: authStore.avatar,
})
const avatarErrors = ref([])
const isEditingAvatar = ref(false)
const isProcessingAvatar = ref(false)

const usernameData = ref({
    username: authStore.username,
})
const usernameErrors = ref([])
const isEditingUsername = ref(false)
const isProcessingUsername = ref(false)

function toggleEditingUsername() {
    isEditingUsername.value = !isEditingUsername.value
    if (isEditingUsername.value) {
        usernameData.value.username = authStore.username
        usernameErrors.value = []
    }
}

function submitChangeUsername() {
    isProcessingUsername.value = true
    usernameErrors.value = []

    axios.put('/api/settings/security/username', usernameData.value).then((response) => {
        if (response.data.success) {
            toggleEditingUsername()
            toastStore.success('Никнейм успешно изменён!')
            authStore.fetchUser()
        } else {
            if (response.data.errors) {
                usernameErrors.value = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessingUsername.value = false
    })
}

function submitChangeAvatar() {
    isProcessingUsername.value = true
    avatarErrors.value = []

    axios.put('/api/settings/security/username', avatarData.value).then((response) => {
        if (response.data.success) {
            // toggleEditingAvatar()
            toastStore.success('Аватар успешно изменён!')
            authStore.fetchUser()
        } else {
            if (response.data.errors) {
                avatarErrors.value = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessingAvatar.value = false
    })
}
</script>

<template>
    <div class="section flex flex-col h-full">
        <div class="banner flex justify-center items-center w-full">
            <img alt="" class="profile" src="/images/elements/stylization-banner.png">
        </div>
        <form action="" class="flex flex-col h-full w-full">

            <div class="section-title flex justify-center transfusion text-[1.5rem] mt-4">Аватар</div>

            <fieldset class="flex flex-col m-4">
                <div class="flex flex-col-reverse md:flex-row">
                    <div class="flex self-center mt-2 md:self-start md:mt-0">
                        <ImageLoader
                            v-model="avatarData.avatar"
                            filler-icon="icon-white-pencil"
                            id="settings-profile-avatar"
                            image-path="/images/users/content/funny-girl.png"
                        />
                    </div>
                    <div class="description flex flex-col md:ml-2">
                        <p class="text-[0.9rem] min-h-[96px] p-3">
                            Аватар — прекрасное средство графического самовыражения, а также идентификации!
                            Загрузите своё собственное изображение, чтобы другие Пользователи могли проще
                            и быстрее Вас отличить!
                        </p>
                    </div>
                </div>
            </fieldset>

            <div class="section-title flex justify-center transfusion text-[1.5rem]">Никнейм</div>

            <fieldset class="flex flex-col m-4">
                <div class="flex flex-col">
                    <div class="flex flex-col">
                        <div class="description">
                            <p class="text-[0.9rem] p-2">
                                Что может быть лучше оригинального и звучного слогана? — оригинальный и звучный
                                Никнейм! Главное подбирайте с умом: обновлять Никнейм можно только 1 раз в месяц!
                            </p>
                        </div>
                        <div v-if="!isEditingUsername" class="mt-4">
                            <span class="subtitle text-[1.1rem]">Никнейм</span>
                            <div
                                class="
                                    current-data-field
                                    transfusion bordered
                                    flex
                                    justify-between
                                    items-center
                                    h-[48px]"
                                >
                                <div class="flex pl-3 text-[14px]">{{ authStore.username }}</div>
                                <button
                                    class="edit-button p-[6px] mr-[-2px]"
                                    @click="toggleEditingUsername()"
                                    type="button"
                                >
                                    <span class="flex icon icon icon-white-pencil"></span>
                                </button>
                            </div>
                        </div>
                        <div v-else class="flex flex-col mt-4">
                            <span class="subtitle text-[1.1rem]">Новый Никнейм</span>
                            <Input
                                v-model="usernameData.username"
                                id="settings-profile-nickname"
                                :placeholder="authStore.username"
                                autocomplete="username"
                            />
                        </div>
                        <span
                            :class="{ 'error': usernameErrors['username'], 'success': !usernameErrors['username']}"
                            class="status text-[0.8rem] locked"
                        >
                            {{ usernameErrors['username']?.[0] || '&nbsp;' }}
                        </span>
                    </div>
                    <div v-if="isEditingUsername" class="flex gap-2">
                        <Button
                            :disabled="usernameData.username === authStore.username || !usernameData.username"
                            class="confirm max-h-[64px] max-w-[200px]"
                            button-type="submit"
                            label="Подтвердить"
                            :loading="isProcessingUsername"
                            @click.prevent="submitChangeUsername()"
                        />
                        <Button
                            class="cancel max-h-[64px] max-w-[200px]"
                            button-type="button"
                            label="Отменить"
                            :loading="isProcessingUsername"
                            @click.prevent="isEditingUsername = false"
                        />
                    </div>
                </div>
            </fieldset>

        </form>
    </div>
</template>

<style scoped>

</style>
