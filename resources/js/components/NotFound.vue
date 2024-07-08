<script setup lang="ts">
import {RouterLink} from 'vue-router'
</script>

<template>
    <div class="flex flex-col h-[75vh] justify-center items-center ml-2 mr-2">
        <h1 class="text-4xl font-bold text-center mb-4">404: Не найдено</h1>
        <p class="text-muted text-center mb-8">Извините, запрашиваемая страница не была найдена.</p>
        <RouterLink :to="{ name: 'home' }">
            Вернуться на главную
        </RouterLink>
    </div>
</template>

<style scoped>

</style>
