<script setup lang="ts">
import {computed, ref} from 'vue'

const props = defineProps({
    allowFloat: {
        type: Boolean,
        default: false
    },
    autocomplete: {
        type: String
    },
    id: String,
    inputClasses: String,
    maxLength: Number,
    minLength: Number,
    numeric: {
        type: Boolean,
        default: false
    },
    type: {
        type: String,
        validator: (val) => ['email', 'file', 'password', 'text'].includes(val),
        default: 'text'
    },
    placeholder: String
})

const isPasswordHidden = ref(true)
const model = defineModel<number|string|undefined>({default: undefined})
const inputValue = ref<string>()
const prevInputValue = ref<string>()
const currentType = computed(
    () => props.type !== 'password' ? props.type : (isPasswordHidden.value ? 'password' : 'text')
)

function onInput() {
    if (props.numeric) {
        if (inputValue.value === '') {
            prevInputValue.value = ''
            model.value = undefined
            return
        }
        let dottedInputValue = inputValue.value!.replace(',', '.')
        if (/^[0-9]+\.?[0-9]*$/.test(dottedInputValue)) {
            prevInputValue.value = inputValue.value
            model.value = parseFloat(dottedInputValue)
            console.log(model.value)
        } else {
            inputValue.value = prevInputValue.value
        }
    }
    else {
        model.value = inputValue.value
    }
}
inputValue.value = String(model.value ?? '')
</script>

<template>
    <label class="flex items-center" :for="id">
        <input
            v-model="inputValue"
            :autocomplete="autocomplete"
            :class="inputClasses"
            :id="id"
            :maxlength="maxLength"
            :minlength="minLength"
            :placeholder="placeholder"
            :type="currentType"
            @input="onInput"
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
