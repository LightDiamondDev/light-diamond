<script setup lang="ts">

import {onMounted, onUnmounted, ref, watch} from 'vue'
import Button from '@/components/elements/Button.vue'
import {lockGlobalScroll, unlockGlobalScroll} from '@/helpers'

const props = defineProps({
    animation: {
        type: String,
        validator: (val) => [ 'smooth-opacity', 'top-translate' ].includes(val),
        default: 'smooth-opacity'
    },
    header: {
        type: Boolean,
        default: true
    },
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
    formContainerClasses: String,
    class: {
        type: String,
        default: ''
    },
})

const emit = defineEmits(['back'])

const classes = ref()

const container = ref<Element>()
const isMaskMouseDown = ref(false)
const isVisible = defineModel<Boolean>('visible', {required: true})

watch(isVisible, (value: boolean) => {
    if (value) {
        lockGlobalScroll()
    } else {
        unlockGlobalScroll()
    }
})

onMounted(() => {
    document.addEventListener('keydown', onKeyDown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', onKeyDown)
    if (isVisible.value) {
        unlockGlobalScroll()
    }
})

function onKeyDown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        isVisible.value = false
    }
}

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

classes.value = props.position === 'center' ? 'items-center ' + props.class : 'items-start ' + props.class
</script>

<template>
    <Teleport to="body">
        <Transition :name="animation">
            <div
                v-if="isVisible"
                class="dialog-background outer flex justify-center fixed w-full h-full top-0 left-0 z-[3]"
                :class="classes"
                @mousedown="onMaskMouseDown"
                @mouseup="onMaskMouseUp"
            >
                <div
                    v-if="isVisible"
                    class="dialog-form-container inner flex"
                    :class="formContainerClasses"
                    ref="container"
                >
                    <slot name="left-content"/>

                    <div class="interface ld-primary-background w-full">

                        <div v-if="header" class="dialog-header flex justify-between items-center">
                            <button
                                v-if="backButton"
                                class="flex justify-center items-center m-2"
                                type="button"
                                @click="emit('back')"
                            >
                                <span class="icon icon-long-left-arrow"></span>
                            </button>

                            <div v-else class="back-button-replacement m-2"/>

                            <h1 class="text-[1.2rem] md:text-[1.8rem] text-center">{{ title }}</h1>

                            <button
                                v-if="closeButton"
                                class="flex justify-center items-center m-2"
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
        </Transition>
    </Teleport>
</template>

<style scoped>

.dialog-background {
    background-color: rgba(0, 0, 0, .5);
}

.interface .dialog-header .back-button-replacement,
.interface .dialog-header button {
    user-select: none;
    height: 48px;
    width: 48px;
}

.interface .dialog-header button .icon {
    height: 32px;
    width: 32px;
}

.interface .dialog-header h1 {
    color: var(--primary-text-color);
    user-select: none;
}

/* =============== [ Анимации ] =============== */

.smooth-opacity-enter-active, .smooth-opacity-leave-active {
    transition: all 0.4s ease-in-out;
}
.smooth-opacity-enter-from,
.smooth-opacity-leave-to {
    opacity: 0;
}
.smooth-opacity-enter-active .inner,
.smooth-opacity-leave-active .inner {
    transition: all 0.4s ease-in-out;
}
.smooth-opacity-enter-from .inner,
.smooth-opacity-leave-to .inner {
    transform: translateY(200px);
    opacity: 0.001;
}

.top-translate-enter-active, .top-translate-leave-active {
    transition: all 0.4s ease-in-out;
}
.top-translate-enter-from,
.top-translate-leave-to {
    opacity: 0;
}
.top-translate-enter-active .inner,
.top-translate-leave-active .inner {
    transition: all 0.4s ease-in-out;
}
.top-translate-enter-from .inner,
.top-translate-leave-to .inner {
    transform: translateY(-100%);
    opacity: 0.001;
}

</style>
