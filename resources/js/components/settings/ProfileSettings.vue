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
    avatar: authStore.user,
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
    <div class="section flex flex-col min-h-[100vh]">
        <div class="banner flex justify-center items-center max-h-[168px] w-full overflow-hidden">
            <img
                alt=""
                class="profile sm:pt-0 pt-2"
                src="/images/elements/stylization-banner.png"
                style="animation: banner-scale-animation 15s infinite;"
            >
        </div>
        <form action="" class="flex flex-col h-full w-full">

            <div class="section-title ld-title-font flex justify-center transfusion text-[20px] mt-2">Аватар</div>

            <fieldset class="flex flex-col m-2">
                <div class="flex flex-col xs:flex-row gap-2">
                    <div class="flex self-center md:self-start">
                        <ImageLoader
                            class="min-h-[72px] min-w-[72px]"
                            v-model="avatarData.avatar"
                            filler-icon="icon-white-pencil"
                            id="settings-profile-avatar"
                            image-path="/images/users/content/funny-girl.png"
                        />
                    </div>
                    <div class="description flex flex-col">
                        <p class="sm:text-[12px] text-[10px] xs:text-start text-center">
                            Аватар — прекрасное средство графического самовыражения, а также идентификации!
                            Загрузите своё собственное изображение, чтобы другие Пользователи могли проще
                            и быстрее Вас отличить!
                        </p>
                    </div>
                </div>
            </fieldset>

            <div class="section-title ld-title-font flex justify-center transfusion text-[20px]">Никнейм</div>

            <fieldset class="flex flex-col m-2">
                <div class="flex flex-col">
                    <div class="flex flex-col gap-3">
                        <div class="description">
                            <p class="sm:text-[12px] text-[10px] xs:text-start text-center">
                                Что может быть лучше оригинального и звучного слогана? — оригинальный и звучный
                                Никнейм! Главное — подбирайте с умом: обновлять Никнейм можно только 1 раз в месяц!
                            </p>
                        </div>
                        <div v-if="!isEditingUsername">
                            <span class="subtitle text-[14px]">Никнейм</span>
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
                            <span class="subtitle text-[14px]">Новый Никнейм</span>
                            <Input
                                v-model="usernameData.username"
                                class="text-[14px] h-[48px]"
                                id="settings-profile-nickname"
                                :placeholder="authStore.username"
                                autocomplete="username"
                            />
                        </div>
                        <span
                            :class="{ 'error': usernameErrors['username'], 'success': !usernameErrors['username']}"
                            class="status sm:text-[12px] text-[10px] my-0.5 locked"
                        >
                            {{ usernameErrors['username']?.[0] || '&nbsp;' }}
                        </span>
                    </div>
                    <div v-if="isEditingUsername" class="flex gap-2">
                        <Button
                            :disabled="usernameData.username === authStore.username || !usernameData.username"
                            class="confirm max-h-[64px] max-w-[200px] w-[80%]"
                            button-type="submit"
                            label="Подтвердить"
                            :loading="isProcessingUsername"
                            @click.prevent="submitChangeUsername()"
                        />
                        <Button
                            class="cancel max-h-[64px] max-w-[200px] w-[80%]"
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
