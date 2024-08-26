<script setup lang="ts">
const model = defineModel<string>()
defineProps({
    maxLength: {
        type: Number,
        required: true
    },
    minLength: {
        type: Number,
        required: true
    },
    trueColor: String,
    falseColor: String
})
</script>

<template>
<p>Осталось символов: {{ maxLength - model!.length }}</p>
</template>

<style scoped>

</style>
