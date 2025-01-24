<script setup lang="ts">
import axios from 'axios'
import {RouterLink, useRoute, useRouter} from 'vue-router'
import {onMounted, ref} from 'vue'

import {useToastStore} from '@/stores/toast'

import Button from '@/components/elements/Button.vue'
import ProcessingMovingItems from '@/components/elements/ProcessingMovingItems.vue'

const toastStore = useToastStore()

const isProcessing = ref(true)
const isSuccess = ref(false)
const responseMessage = ref('')
const router = useRouter()
const route = useRoute()

onMounted(() => {
    tryVerifyEmail()
})

function tryVerifyEmail() {
    const fullUrl = route.fullPath

    axios.post(fullUrl).then((response) => {
        isSuccess.value = response.data.success
        responseMessage.value = response.data.message || ''
        if (isSuccess.value) {
            toastStore.success(`Ваша учётная запись успешно подтверждена!`)
        }
    }).finally(() => {
        isProcessing.value = false
    })
}
</script>

<template>
    <div class="global-error-window flex justify-center items-center">
        <div class="global-error-container ld-primary-background ld-primary-border flex flex-col items-center">

            <h1 v-if="isSuccess" class="text-4xl font-bold text-center mt-8">Поздравляем!</h1>
            <h1 v-else class="text-4xl font-bold text-center mt-8">Проверка...</h1>

            <p v-if="isSuccess" class="text-muted mt-4">
                {{ responseMessage || 'Адрес электронной почты успешно подтверждён! Наслаждайтесь классными эмоциями и вдохновением!' }}
            </p>
            <p v-else class="text-muted mt-4">{{
                    responseMessage || 'Произошла неизвестная ошибка!'
                }}
            </p>

            <div class="mob phantom flex justify-center items-center full-locked">
                <div class="animation-flying-phantom"></div>
            </div>

            <ProcessingMovingItems v-if="isProcessing" class="mb-9 mt-4" height="64px" width="64px"/>

            <Button
                as="RouterLink"
                class="max-w-[360px] w-full mb-4"
                press-classes="transfusion-light px-4"
                icon="icon-diamond"
                :to="{name: 'home'}"
                label="Отлично, вперёд!"
            />
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

.mob.phantom {
    overflow: hidden;
    max-width: 320px;
    height: 200px;
    width: 100%;
}

.mob.phantom div {
    background-size: 100% 100%;
    height: 160px;
    width: 320px;
}

.global-error-window h1,
.global-error-window p {
    color: var(--primary-text-color);
}

.global-error-container a {
    width: 95%;
}

.global-error-container p {
    max-width: 400px;
}

/* =============== [ Медиа-Запрос { ?px < 1024px + desktop-height } ] =============== */

@media screen and (max-width: 1023px) and (min-height: 654px) {
    .global-error-window {
        height: 680px;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1024px + mobile-height } ] =============== */

@media screen and (max-width: 1023px) and (max-height: 653px) {
    .global-error-window {
        height: 390px;
    }

    .mob.phantom {
        height: 100px;
    }

    .mob.phantom div {
        height: 80px;
        width: 160px;
    }
}
</style>
