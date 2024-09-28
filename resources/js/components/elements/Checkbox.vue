<script setup lang="ts">
import {computed, ref} from 'vue'

const props = defineProps({
    checked: Boolean | undefined
})

const model = defineModel<boolean>({default: false})
const isChecked = computed(() => props.checked ?? model.value)

const emit = defineEmits(['check', 'uncheck'])

function check() {
    emit('check')
    model.value = true
    console.log(model.value)
}

function uncheck() {
    emit('uncheck')
    model.value = false
    console.log(model.value)
}
</script>

<template>
    <input
        @click="isChecked ? uncheck() : check()"
        class="ld-checkbox"
        :value="isChecked"
        type="checkbox"
    >
</template>

<style scoped>
input[type="checkbox"] {
    -webkit-tap-highlight-color: transparent;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    cursor: pointer;
}
input[type="checkbox"]:focus {
    outline: none;
}
.ld-checkbox {
    justify-content: center;
    background-size: cover;
    display: inline-flex;
    position: relative;
    height: 32px;
    width: 32px;
}
.ld-checkbox:after {
    content: "";
    position: absolute;
    height: 32px;
    width: 32px;
}
</style>
