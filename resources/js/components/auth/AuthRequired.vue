<script setup lang='ts'>
import {useModalStore} from '@/stores/modal'

useModalStore().auth = true
</script>

<template>
    <div class="flex justify-center">
        Для просмотра страницы необходимо войти в аккаунт.
    </div>
</template>

<style scoped>

</style>
