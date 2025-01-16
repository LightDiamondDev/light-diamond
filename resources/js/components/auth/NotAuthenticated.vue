<script setup lang='ts'>
import {useGlobalModalStore} from '@/stores/global-modal'
import Button from '@/components/elements/Button.vue'
import {onMounted} from 'vue'

const globalModalStore = useGlobalModalStore()
useGlobalModalStore().auth = true

onMounted(() => {
    globalModalStore.authModal = true
})
</script>

<template>
    <div class="global-error-window flex justify-center items-center">
        <div class="global-error-container ld-primary-background ld-primary-border flex flex-col items-center">
            <h1 class="text-xl font-bold text-center mt-8 md:test">У вас нет доступа!</h1>
            <p class="text-muted text-center mt-4">Для просмотра страницы необходимо войти в аккаунт!</p>
            <div class="mob parrot flex justify-center items-center full-locked">
                <div class="animation-dancing-red-parrot"></div>
            </div>
            <Button
                @click="globalModalStore.authModal = !globalModalStore.authModal"
                class="w-[80%] my-8"
                icon="item-diamond"
                label="Войти"
                type="button"
            />
        </div>
    </div>
</template>

<style scoped>
.test {
    background: #5aedda;
}
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
    max-width: 280px;
    height: 280px;
    width: 100%;
}
.mob.parrot div {
    background-size: 100% 100%;
    height: 280px;
    width: 280px;
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

/* =============== [ Медиа-Запрос { ?px < 1024px + desktop-height } ] =============== */

@media screen and (max-width: 1023px) and (min-height: 654px) {
    .global-error-window {
        height: 620px;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1024px + mobile-height } ] =============== */

@media screen and (max-width: 1023px) and (max-height: 653px) {
    .global-error-window {
        height: 420px;
    }
    .mob.parrot {
        height: 120px;
    }
    .mob.parrot div {
        height: 120px;
        width: 120px;
    }
}
</style>
