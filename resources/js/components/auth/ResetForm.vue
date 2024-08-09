<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {RouterLink, useRoute} from 'vue-router'
import Button from '@/components/elements/Button.vue'
import {getErrorMessageByCode} from '@/helpers'
import {useToastStore} from '@/stores/toast'
import {ref} from 'vue'
import Input from '@/components/elements/Input.vue'

const toastStore = useToastStore()
const route = useRoute()

const resetData = ref({
    password: '',
    password_confirmation: '',
    email: route.query.email,
    token: route.query.token,
})

const resetPasswordApiUrl = route.path
const isProcessing = ref(false)
const errors = ref([])

function submitResetPassword() {
    isProcessing.value = true
    errors.value = []

    axios.post(resetPasswordApiUrl, resetData.value).then((response) => {
        if (response.data.success) {
            toastStore.success('Пароль успешно сброшен!')
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
    <div class="global-error-window flex justify-center items-center">
        <div class="global-error-container flex flex-col items-center">
            <h1 class="text-4xl text-center mt-8">Сброс пароля</h1>
            <p class="text-muted text-center text-[0.9rem] mb-2 mt-4">Придумайте новый пароль. Он должен быть надёжным!</p>

            <form action="" class="reset-form flex flex-col items-center" name="reset">
                <fieldset class="flex flex-col items-center">
                    <span class="subtitle text-[0.9rem] m-2">Новый пароль</span>

                    <Input
                        v-model="resetData.password"
                        id="reset-password"
                        placeholder="Пароль"
                        type="password"
                    />

                    <span
                        :class="{ 'error': errors['password'], 'success': !errors['password']}"
                        class="status text-[0.8rem] mt-1"
                    >
                        {{ errors['password']?.[0] || '&nbsp;' }}
                    </span>
                    <span class="subtitle text-[0.9rem] m-2">Повторный пароль</span>

                    <Input
                        v-model="resetData.password_confirmation"
                        id="reset-repeat-password"
                        placeholder="Повторный пароль"
                        type="password"
                    />

                    <span
                        :class="{ 'error': errors['password_confirmation'], 'success': !errors['password_confirmation']}"
                        class="status text-[0.8rem] mt-1"
                    >
                        {{ errors['password_confirmation']?.[0] || '&nbsp;' }}
                    </span>
                </fieldset>

                <div class="mob parrot flex justify-center items-center mb-4">
                    <div class="animation-dancing-lime-parrot"></div>
                </div>

                <Button
                    button-type="submit"
                    class="mb-8"
                    icon="icon-bestiary"
                    text="Подтвердить"
                    :loading="isProcessing"
                    @click.prevent="submitResetPassword"
                />

            </form>
        </div>
    </div>
</template>

<style scoped>
.global-error-window {
    height: 720px;
    width: 100%;
}
.global-error-container {
    max-width: 480px;
    width: 95%;
}
.mob.parrot {
    overflow: hidden;
    max-width: 140px;
    height: 170px;
    width: 100%;
}
.mob.parrot div {
    background-size: 100% 100%;
    height: 170px;
    width: 140px;
}
.global-error-window h1,
.global-error-window p {
    color: var(--primary-text-color);
}
.global-error-container a {
    width: 95%;
}
.global-error-container p {
    max-width: 300px;
}
.global-error-window form {
    width: 75%;
}
.global-error-window form fieldset {
    width: 100%;
}
.global-error-window form fieldset * {
    color: var(--primary-text-color);
}

.global-error-window form fieldset label {
    height: 40px;
    width: 100%
}

.global-error-window form fieldset label input {
    height: 100%;
    width: 100%;
}

.global-error-window form fieldset span {
    user-select: none;
    width: 100%;
}

.global-error-window form fieldset .status {
    line-height: 1.3;
}

.global-error-window form fieldset input::placeholder {
    font-size: .8rem;
}

.global-error-window form fieldset .status.error {
    color: #ff3050;
}

.global-error-window form fieldset .status.success {
    color: var(--hover-text-color);
}

/* =============== [ Медиа-Запрос { ?px < 1024px + desktop-height } ] =============== */

@media screen and (max-width: 1023px) and (min-height: 654px) {
    .global-error-window {
        height: 540px;
    }
    .global-error-window form {
        width: 90%;
    }
    .mob.parrot {
        display: none;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1024px + mobile-height } ] =============== */

@media screen and (max-width: 1023px) and (max-height: 653px) {
    .global-error-window {
        height: 460px;
    }
    .global-error-window form {
        width: 90%;
    }
    .mob.parrot {
        display: none;
    }
}
</style>
