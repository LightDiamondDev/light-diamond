<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {computed, ref} from 'vue'
import {useAuthStore} from '@/stores/auth'
import {getErrorMessageByCode} from '@/helpers'

import Input from '@/components/elements/Input.vue'
import Button from '@/components/elements/Button.vue'
import ShineButton from '@/components/elements/ShineButton.vue'
import {useToastStore} from '@/stores/toast'

const authStore = useAuthStore()
const toastStore = useToastStore()

const emailData = ref({
    email: authStore.email,
    email_confirmation: authStore.email,
})
const emailErrors = ref([])
const isEmailHidden = ref(true)
const isEditingEmail = ref(false)
const isProcessingEmail= ref(false)
const isProcessingSendEmailVerificationLink = ref(false)

const passwordData = ref({
    password: '',
    password_confirmation: '',
})
const passwordErrors = ref([])
const isEditingPassword = ref(false)
const isProcessingPassword = ref(false)

const hiddenEmail = computed(() => {
    const email = authStore.email!
    const atIndex = email.indexOf('@')
    const usernamePart = email.substring(0, Math.min(2, atIndex))
    const domainPart = email.substring(atIndex)

    return usernamePart + '***' + domainPart
})

function toggleHideEmail() {
    isEmailHidden.value = !isEmailHidden.value
}

function toggleEditingEmail() {
    isEditingEmail.value = !isEditingEmail.value
    if (isEditingEmail.value) {
        emailData.value.email = authStore.email
        emailData.value.email_confirmation = authStore.email
        emailErrors.value = []
    }
}

function toggleEditingPassword() {
    isEditingPassword.value = !isEditingPassword.value
    if (!isEditingPassword.value) {
        passwordData.value.password = ''
        passwordData.value.password_confirmation = ''
        passwordErrors.value = []
    }
}

function submitChangeEmail() {
    isProcessingEmail.value = true
    emailErrors.value = []

    axios.put('/api/settings/security/email', emailData.value).then((response) => {
        if (response.data.success) {
            toggleEditingEmail()
            toastStore.success('Email успешно изменён!')
            toastStore.info(
                `Мы отправили Вам письмо со ссылкой для подтверждения Вашей НОВОЙ электронной почты!`,
                'Подтверждение E-Mail',
                15000,
                'icon-letter'
            )
            authStore.fetchUser()
        } else {
            if (response.data.errors) {
                emailErrors.value = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessingEmail.value = false
    })
}

function sendEmailVerificationLink() {
    isProcessingSendEmailVerificationLink.value = true

    axios.post('/api/settings/security/email-verification').then((response) => {
        if (response.data.success) {
            toastStore.success('Ссылка для подтверждения Email отправлена!')
            authStore.fetchUser()
        } else {
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessingSendEmailVerificationLink.value = false
    })
}

function submitChangePassword() {
    isProcessingPassword.value = true
    passwordErrors.value = []

    axios.put('/api/settings/security/password', passwordData.value).then((response) => {
        if (response.data.success) {
            toggleEditingPassword()
            toastStore.success('Пароль успешно изменён!')
        } else {
            if (response.data.errors) {
                passwordErrors.value = response.data.errors
            }
            if (response.data.message) {
                toastStore.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessingPassword.value = false
    })
}
</script>

<template>
    <div class="section flex flex-col min-h-[100vh]">
        <div class="banner flex justify-center items-center max-h-[168px] w-full overflow-hidden">
            <img
                alt=""
                class="profile sm:pt-0 pt-2"
                src="/images/elements/security-banner.png"
                style="animation: banner-scale-animation 15s infinite;"
            >
        </div>
        <form action="" class="flex flex-col h-full w-full">

            <div class="section-title ld-title-font flex justify-center transfusion text-[20px] mt-2">Email</div>

            <fieldset class="flex flex-col m-2">
                <div class="flex flex-col">
                    <div class="flex flex-col gap-3">
                        <div class="description">
                            <p class="sm:text-[12px] text-[10px] xs:text-start text-center">
                                Адрес электронной почты, к которому привязана Ваша учётная запись.
                            </p>
                        </div>
                        <div v-if="!isEditingEmail">
                            <span class="subtitle text-[14px]">Email</span>
                            <div
                                class="
                                    current-data-field
                                    transfusion bordered
                                    flex
                                    items-center
                                    h-[48px]"
                            >
                                <div class="flex text-[14px] w-full pl-3">
                                    {{ isEmailHidden ? hiddenEmail : authStore.email }}
                                </div>
                                <button
                                    @click="toggleHideEmail()"
                                    class="edit-button justify-self-end p-[6px] mr-[-2px]"
                                    type="button"
                                >
                                    <span
                                        :class="{ 'icon-eye': !isEmailHidden, 'icon-eye-cross': isEmailHidden }"
                                        class="flex icon icon icon-eye"
                                    />
                                </button>
                                <button
                                    @click="toggleEditingEmail()"
                                    class="edit-button p-[6px] mr-[-2px]"
                                    type="button"
                                >
                                    <span class="flex icon icon icon-white-pencil"></span>
                                </button>
                            </div>
                        </div>
                        <div v-else class="flex flex-col mt-4">
                            <span class="subtitle text-[14px]">Новый Email</span>
                            <Input
                                v-model="emailData.email"
                                class="text-[14px] h-[48px]"
                                id="settings-security-email"
                                :placeholder="hiddenEmail"
                                autocomplete="email"
                            />
                            <span
                                :class="{ 'error': emailErrors['email'], 'success': !emailErrors['email']}"
                                class="status sm:text-[12px] text-[10px] my-0.5 locked"
                            >
                                {{ emailErrors['email']?.[0] || '&nbsp;' }}
                            </span>
                            <span class="subtitle text-[14px]">Новый повторный Email</span>
                            <Input
                                v-model="emailData.email_confirmation"
                                class="text-[14px] h-[48px]"
                                id="settings-security-email-confirmation"
                                :placeholder="hiddenEmail"
                                autocomplete="email"
                            />
                            <span
                                :class="{ 'error': emailErrors['email_confirmation'], 'success': !emailErrors['email_confirmation']}"
                                class="status sm:text-[12px] text-[10px] my-0.5 locked"
                            >
                                {{ emailErrors['email_confirmation']?.[0] || '&nbsp;' }}
                            </span>
                        </div>
                    </div>
                    <div v-if="isEditingEmail" class="flex gap-2">
                        <Button
                            :disabled="emailData.email === authStore.email || emailData.email !== emailData.email_confirmation || !emailData.email"
                            class="success max-h-[64px] max-w-[200px] w-[80%]"
                            button-type="submit"
                            :loading="isProcessingEmail"
                            label="Подтвердить"
                            @click.prevent="submitChangeEmail()"
                        />
                        <Button
                            class="secondary max-h-[64px] max-w-[200px] w-[80%]"
                            button-type="button"
                            label="Отменить"
                            :disabled="isProcessingEmail"
                            @click.prevent="isEditingEmail = false"
                        />
                    </div>
                    <!-- Message | type="warning" | :closable="false" -->
                    <div v-if="!authStore.hasVerifiedEmail" class="message warning flex mt-4">
                        <div class="flex items-center p-2">
                            <span class="icon icon-share flex sm:h-[48px] h-[32px] sm:w-[48px] w-[32px]"/>
                        </div>
                        <div class="flex flex-col">
                            <p class="sm:text-[12px] text-[10px] pr-3 pt-3">
                                Подтвердите свой адрес электронной почты, перейдя по ссылке из письма.
                            </p>
                            <ShineButton
                                :loading="isProcessingSendEmailVerificationLink"
                                class="warning-lite max-w-[240px] mr-2 my-2"
                                class-preset="text-[12px] gap-2 px-2"
                                @click="sendEmailVerificationLink"
                                label="Отправить повторно"
                                button-type="button"
                                icon="icon-letter"
                            />
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="section-title ld-title-font flex justify-center transfusion text-[20px]">Пароль</div>

            <fieldset class="flex flex-col m-2">
                <div class="flex flex-col">
                    <div class="flex flex-col gap-3">
                        <div class="description">
                            <p class="sm:text-[12px] text-[10px] xs:text-start text-center">
                                Пароль должен быть надёжным, содержать заглавные и строчные буквы, а также цифры и
                                спец. символы, ведь хороший пароль — это ключ к эффекту стойкости, а значит и победе!
                            </p>
                        </div>
                        <div v-if="!isEditingPassword">
                            <span class="subtitle text-[14px]">Пароль</span>
                            <div
                                class="
                                    current-data-field
                                    transfusion bordered
                                    flex
                                    justify-between
                                    items-center
                                    h-[48px]"
                            >
                                <div class="flex pl-3 text-[14px]">••••••••••</div>
                                <button
                                    @click="toggleEditingPassword()"
                                    class="edit-button p-[6px] mr-[-2px]"
                                    type="button"
                                >
                                    <span class="flex icon icon icon-white-pencil"></span>
                                </button>
                            </div>
                        </div>
                        <div v-else class="flex flex-col mt-4">
                            <span class="subtitle text-[14px]">Новый пароль</span>
                            <Input
                                v-model="passwordData.password"
                                class="text-[14px] h-[48px]"
                                id="settings-security-password"
                                autocomplete="password"
                                placeholder="Пароль"
                            />
                            <span
                                :class="{ 'error': passwordErrors['password'], 'success': !passwordErrors['password']}"
                                class="status text-[0.8rem] locked"
                            >
                                {{ passwordErrors['password']?.[0] || '&nbsp;' }}
                            </span>
                            <span class="subtitle text-[14px]">Новый повторный пароль</span>
                            <Input
                                v-model="passwordData.password_confirmation"
                                class="text-[14px] h-[48px]"
                                id="settings-security-password-confirmation"
                                placeholder="Повторный пароль"
                                autocomplete="password"
                            />
                            <span
                                :class="{ 'error': passwordErrors['password_confirmation'], 'success': !passwordErrors['password_confirmation']}"
                                class="status sm:text-[12px] text-[10px] my-0.5 locked"
                            >
                                {{ passwordErrors['password_confirmation']?.[0] || '&nbsp;' }}
                            </span>
                        </div>
                    </div>
                    <div v-if="isEditingPassword" class="flex gap-2">
                        <Button
                            :disabled="passwordData.password !== passwordData.password_confirmation || !passwordData.password"
                            class="success max-h-[64px] max-w-[200px] w-[80%]"
                            button-type="submit"
                            label="Подтвердить"
                            :loading="isProcessingPassword"
                            @click.prevent="submitChangePassword()"
                        />
                        <Button
                            class="secondary max-h-[64px] max-w-[200px] w-[80%]"
                            button-type="button"
                            label="Отменить"
                            :disabled="isProcessingPassword"
                            @click.prevent="isEditingPassword = false"
                        />
                    </div>
                </div>
            </fieldset>

        </form>
    </div>
</template>

<style scoped>

</style>
