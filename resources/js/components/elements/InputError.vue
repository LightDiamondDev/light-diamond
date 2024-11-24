<script setup lang="ts">
const props = defineProps({
    error: String,
    fieldName: String
})
</script>

<template>
    <span
        v-if="error"
        :class="{ 'error': error, 'success': !error}"
        class="status"
    >
        {{ (fieldName ? fieldName + ': ' : '') + error }}
    </span>
</template>

<style scoped>

</style>
