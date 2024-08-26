<script setup lang="ts">
const model = defineModel<string>({default: ''})

const props = defineProps({
    id: String,
    maxLength: Number,
    minLength: Number,
    placeholder: String
})
</script>

<template>
    <label class="label-input-text flex h-full max-w-full" :for="id">
        <textarea
            class="flex h-full w-full"
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
