<script setup lang='ts'>
import {useGlobalModalStore} from '@/stores/global-modal'

useGlobalModalStore().auth = true
</script>

<template>
    <div class="flex justify-center">
        Для просмотра страницы необходимо войти в аккаунт.
    </div>
</template>

<style scoped>

</style>
