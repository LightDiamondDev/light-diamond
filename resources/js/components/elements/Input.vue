<script setup lang="ts">
import {computed, ref} from 'vue'

const isPasswordHidden = ref(true)
const model = defineModel<string>({default: ''})
const currentType = computed(
    () => props.type !== 'password' ? props.type : (isPasswordHidden.value ? 'password' : 'text')
)

const props = defineProps({
    autocomplete: {
        type: String,
        validator: (val) => ['off', 'on'].includes(val),
        default: 'on'
    },
    id: String,
    type: {
        type: String,
        validator: (val) => ['email', 'file', 'password', 'text'].includes(val),
        default: 'text'
    },
    placeholder: String
})
</script>

<template>
    <label class="flex items-center" :for="id">
        <input
            v-model="model"
            :autocomplete="autocomplete"
            class="text-[0.9rem]"
            :id="id"
            :placeholder="placeholder"
            :type="currentType"
        >
        <button
            v-if="type === 'password'"
            class="flex justify-center items-center"
            @click="isPasswordHidden = !isPasswordHidden"
            type="button"
        >
            <span
                :class="{ 'icon-eye-cross': isPasswordHidden, 'icon-eye': !isPasswordHidden }"
                class="flex icon"
            />
        </button>
    </label>
</template>

<style scoped>
label input {
    height: 100%;
    width: 100%;
}

label button {
    height: 36px;
    width: 40px;
}

label button .icon {
    height: 28px;
    width: 28px;
}
</style>
