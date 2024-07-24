<script setup lang="ts">

import {ref} from 'vue'
import Button from '@/components/elements/Button.vue'

const container = ref<Element>()
const isMaskMouseDown = ref(false)
const isVisible = defineModel<Boolean>('visible', {required: true})

const props = defineProps({
    position: {
        type: String,
        validator: (val) => [
            'top-left', 'center-left', 'bottom-left',
            'top-center', 'center', 'bottom-center',
            'top-right', 'center-right', 'bottom-right'
        ].includes(val),
        default: 'center'
    },
    title: {
        type: String,
        default: ''
    },
    backButton: {
        type: Boolean,
        default: false
    },
    closeButton: {
        type: Boolean,
        default: true
    },
})

const emit = defineEmits(['back'])

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
        <div ref="container" class="flex">
            <slot name="left-content"/>
            <div>
                <div class="flex justify-between items-center">
                    <button
                        v-if="backButton"
                        class="flex justify-center items-center m-4"
                        type="button"
                        @click="emit('back')"
                    >
                        <span class="icon icon-long-left-arrow"></span>
                    </button>

                    <h1 class="text-[1.8rem]">{{title}}</h1>

                    <button
                        v-if="closeButton"
                        class="flex justify-center items-center m-4"
                        type="button"
                        @click="isVisible = false"
                    >
                        <span class="icon icon-cross"></span>
                    </button>
                </div>
                <slot/>
            </div>
        </div>
    </div>

</template>

<style>

.dialog-background {
    background-color: rgba(0, 0, 0, .5);
    position: fixed;
    transition: 1s;
    height: 100%;
    width: 100%;
    z-index: 2;
}

</style>
