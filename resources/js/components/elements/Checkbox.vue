<script setup lang="ts">
import {computed} from 'vue'

const props = defineProps({
    checked: Boolean | undefined,
    id: String
})

const model = defineModel<boolean>({default: false})
const isChecked = computed(() => props.checked ?? model.value)

const emit = defineEmits(['check', 'uncheck'])

function check() {
    emit('check')
    model.value = true
}

function uncheck() {
    emit('uncheck')
    model.value = false
}
</script>

<template>
    <input
        @click="isChecked ? uncheck() : check()"
        class="ld-checkbox"
        :checked="isChecked"
        type="checkbox"
        :id="id"
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
