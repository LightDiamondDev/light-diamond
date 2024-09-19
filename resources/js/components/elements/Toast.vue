<script setup lang="ts">
import type {Toast} from '@/stores/toast'
import {ToastType, useToastStore} from '@/stores/toast'
import {computed, onMounted, onUnmounted, type PropType} from 'vue'

const toastStore = useToastStore()

const props = defineProps ({
    toast: {
        type: Object as PropType<Toast>,
        required: true
    }
})

const icon = computed(() => props.toast.icon || getTypeIcon())

let closeTimeout: NodeJS.Timeout | undefined = undefined

onMounted(() => {
    startCloseTimeout()
})

onUnmounted(() => {
    clearCloseTimeout()
})

function getTypeIcon() {
    switch(props.toast!.type) {
        case ToastType.ERROR:
            return 'icon-small-cross'
        case ToastType.INFO:
            return 'icon-eye'
        case ToastType.SUCCESS:
            return 'icon-tick'
        case ToastType.WARNING:
            return 'icon-hand'
    }
}

function startCloseTimeout() {
    closeTimeout = setTimeout(close, props.toast!.duration)
}

function clearCloseTimeout() {
    clearTimeout(closeTimeout)
    closeTimeout = undefined
}

function close() {
    toastStore.remove(props.toast!.id)
}
</script>

<template>
    <div
        class="toast ld-primary-background flex locked"
        :class="{
            'error': toast.type === ToastType.ERROR,
            'info': toast.type === ToastType.INFO,
            'success': toast.type === ToastType.SUCCESS,
            'warning': toast.type === ToastType.WARNING
        }"
        @mouseenter="clearCloseTimeout"
        @mouseleave="startCloseTimeout"
    >
        <div class="indicator flex justify-center">
            <div class="set icon icon-border flex justify-center items-center">
                <span class="icon" :class="icon"></span>
            </div>
        </div>
        <div class="ld-primary-border-bottom
            ld-primary-border-right
            ld-primary-border-top
            flex justify-between
            w-full pl-2 pt-1"
        >
            <div class="description ld-shadow-text flex flex-col">
                <h1 class="ld-title-font">{{ toast.title }}</h1>
                <p>{{ toast.message }}</p>
            </div>
            <button
                class="close flex justify-center items-center"
                @click="close"
            >
                <span class="icon icon-cross"></span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.toast {
    margin: .5rem .5rem 0 .5rem;
    min-height: 72px;
}

.toast .indicator {
    background-image: url('/images/elements/base-background.png');
    width: 72px;
}

.toast.error .indicator {
    background-color: rgb(200, 30, 50);
}

.toast.info .indicator {
    background-color: rgb(0, 135, 150);
}

.toast.success .indicator {
    background-color: rgb(10, 120, 10);
}

.toast.warning .indicator {
    background-color: rgb(180, 150, 0);
}

.toast .indicator .set {
    animation: icon-set-animation 1s infinite;
    transition: .5s;
    height: 48px;
    width: 48px;
}

.toast .set span.icon {
    height: 32px;
    width: 32px;
}

.toast button.close {
    cursor: pointer;
    min-width: 32px;
    height: 32px;
}

.toast button.close .icon {
    height: 24px;
    width: 24px;
}

.toast h1 {
    color: var(--primary-text-color);
    font-size: 1.1rem;
}
.toast.error h1 {
    color: rgb(200, 30, 70);
}
.toast.info h1 {
    color: rgb(0, 200, 220);
}
.toast.success h1 {
    color: rgb(30, 180, 30);
}
.toast.warning h1 {
    color: rgb(200, 180, 0);
}

.toast p {
    color: var(--primary-text-color);
    font-size: .8rem;
}

/* =============== [ Анимации Элементов ] =============== */
@keyframes icon-set-animation {
    0% {
        margin-top: .5rem;
    }
    50% {
        margin-top: 1rem;
    }
    100% {
        margin-top: .5rem;
    }
}
</style>
