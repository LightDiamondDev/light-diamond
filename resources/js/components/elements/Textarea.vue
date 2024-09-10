<script setup lang="ts">
const model = defineModel<string>({default: ''})

const props = defineProps({
    editable: {
        type: Boolean,
        default: true
    },
    id: String,
    maxLength: Number,
    minLength: Number,
    placeholder: String
})
</script>

<template>
    <label class="label-input-text flex h-full max-w-full" :for="id">
        <textarea
            class="ld-tinted-background flex h-full w-full"
            :disabled="!editable"
            v-model="model"
            :maxlength="maxLength"
            :minlength="minLength"
            :id="id"
            :placeholder="placeholder"
            type="text"
        >
        </textarea>
    </label>
</template>

<style scoped>
textarea {
    resize: none;
}
</style>
