<script setup lang="ts">
import {RouterLink} from 'vue-router'
import Button from '@/components/elements/Button.vue'
</script>

<template>
    <div class="global-error-window flex justify-center items-center">
        <div class="global-error-container flex flex-col items-center">
            <h1 class="text-4xl font-bold text-center mt-8">404: Не найдено</h1>
            <p class="text-muted text-center mt-4">Похоже, что запрашиваемая страница не была найдена!</p>
            <div class="mob phantom flex justify-center items-center full-locked">
                <div class="animation-flying-phantom"></div>
            </div>
            <RouterLink class="flex justify-center mb-8" :to="{ name: 'home' }">
                <Button button-type="submit" icon="item-ender-pearl" icon-size="32px" text="Телепортироваться домой"/>
            </RouterLink>
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
    max-width: 300px;
}

/* =============== [ Медиа-Запрос { ?px < 1024px + desktop-height } ] =============== */

@media screen and (max-width: 1023px) and (min-height: 654px) {
    .global-error-window {
        height: 540px;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1024px + mobile-height } ] =============== */

@media screen and (max-width: 1023px) and (max-height: 653px) {
    .global-error-window {
        height: 380px;
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
