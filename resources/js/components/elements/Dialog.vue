<script setup lang="ts">

import {ref} from 'vue'

const container = ref<Element>()
const isMaskMouseDown = ref(false)
const isVisible = defineModel<Boolean>('visible', {required: true})

const props = defineProps({
    position: {
        type: String,
        validator: (val) => [
            'top-left', 'center-left', 'bottom-left',
            'top-center', 'center-center', 'bottom-center',
            'top-right', 'center-right', 'bottom-right'
        ].includes(val)
    }
});

function isContainerEventTarget(event: MouseEvent) {
    return container.value && container.value?.contains(event.target)
}

function onMaskMouseDown(event: MouseEvent) {
    if (!isContainerEventTarget(event)) {
        isMaskMouseDown.value = true
    }
}

function onMaskMouseUp(event: MouseEvent) {
    if (isMaskMouseDown.value && !isContainerEventTarget(event)) {
        isVisible.value = false
    }
    isMaskMouseDown.value = false
}

</script>

<template>

    <div
        v-if="isVisible"
        class="dialog-background flex justify-center items-center"
        @mousedown="onMaskMouseDown"
        @mouseup="onMaskMouseUp"
    >
        <div ref="container" class="container flex">
            <slot/>
        </div>
    </div>

</template>

<style>

.dialog-background {
    background-color: rgba(0, 0, 0, .5);
    pointer-events: none;
    position: fixed;
    transition: 1s;
    height: 100%;
    width: 100%;
    opacity: 0;
    z-index: 2;
}

.dialog-background.on {
    pointer-events: visible;
    opacity: 1;
}

</style>
