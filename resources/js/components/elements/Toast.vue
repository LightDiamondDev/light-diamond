<script setup lang="ts">
import type {Toast} from '@/stores/toast'
import {ToastType, useToastStore} from '@/stores/toast'
import {computed, type PropType} from 'vue'

const toastStore = useToastStore()

const props = defineProps ({
    toast: {
        type: Object as PropType<Toast>,
        required: true
    }
})

const icon = computed(() => props.toast.icon || getTypeIcon())

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
</script>

<template>
    <div
        class="toast flex locked"
        :class="{
            'error': toast.type === ToastType.ERROR,
            'info': toast.type === ToastType.INFO,
            'success': toast.type === ToastType.SUCCESS,
            'warning': toast.type === ToastType.WARNING,
        }"
    >
        <div class="indicator">
            <div class="set icon icon-border flex justify-center items-center">
                <span class="icon" :class="icon"></span>
            </div>
        </div>
        <div class="content flex justify-between pl-2 pt-1">
            <div class="description flex flex-col">
                <h1>{{ toast.title }}</h1>
                <p>{{ toast.message }}</p>
            </div>
            <button
                class="close flex justify-center items-center"
                @click="toastStore.remove(toast.id)"
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
.toast .content {
    width: 100%;
}
.toast .indicator {
    background-image: url('/images/elements/base-background.png');
    width: 48px;
}
.toast.error .indicator {
    background-color: rgb(130, 20, 30);
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
    margin-right: .5rem;
    margin-left: .4rem;
    transition: .5s;
    height: 32px;
    width: 32px;
}
.toast .set span.icon {
    height: 24px;
    width: 24px;
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
.toast p {
    color: var(--primary-text-color);
    font-size: .8rem;
}

/* =============== [ Анимации Элементов ] =============== */
@keyframes icon-set-animation
{
    0% { margin-top: .5rem; }
    50% { margin-top: 1rem; }
    100% { margin-top: .5rem; }
}
</style>
