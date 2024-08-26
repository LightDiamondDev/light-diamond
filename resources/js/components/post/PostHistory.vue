<script setup lang="ts">
import Dialog from '@/components/elements/Dialog.vue'

const isVisible = defineModel<Boolean>('visible', { required: true })
</script>

<template>
    <Dialog
        v-model:visible="isVisible"
        class="auth-dialog"
        title="История действий"
    >
        <p>
            sadsdsadasd
            asdasdasdsad
            asdasdasdasd
            gfdfgfdg
            dfgfdgfdg
            fdgg
        </p>
    </Dialog>
</template>

<style scoped>

</style>
