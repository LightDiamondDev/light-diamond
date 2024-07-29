<script setup lang="ts">
import {RouterLink} from 'vue-router'
</script>

<template>
    <div class="flex h-[75vh] justify-center items-center">
        <div class="global-error-container flex flex-col ml-2 mr-2">
            <h1 class="text-4xl font-bold text-center mb-4">404: Не найдено</h1>
            <p class="text-muted text-center mb-8">Похоже, что запрашиваемая страница не была найдена!</p>
            <RouterLink :to="{ name: 'home' }">
                Телепортироваться домой
            </RouterLink>
        </div>
    </div>
</template>

<style scoped>
.container {

}
</style>
