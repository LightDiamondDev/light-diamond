<script setup lang="ts">
import Toast from '@/components/elements/Toast.vue'
import {useToastStore} from '@/stores/toast'

const toastStore = useToastStore()

</script>

<template>
    <div class="toaster flex flex-col max-w-[360px] fixed">

        <TransitionGroup name="toast-sliding">
            <Toast
                v-for="toast of toastStore.toasts"
                class="toast overflow-hidden"
                :key="toast.id"
                :toast="toast"
            />
        </TransitionGroup>

    </div>
</template>

<style scoped>
.toaster {
    top: var(--header-height);
    width: 100%;
    z-index: 4;
    right: 0;
}

.toast { margin-top: 8px; }

/* =============== [ Анимации ] =============== */

.toast-sliding-enter-active {
    transition: all 0.8s ease-out;
}
.toast-sliding-leave-active {
    transition: all 1s ease-out;
    animation: toast-sliding-leave-animation 1.2s linear;
}

.toast-sliding-enter-from,
.toast-sliding-leave-to {
    transform: translateX(110%);
}

@keyframes toast-sliding-leave-animation {
    0% {
        transform: scaleY(1) translateX(0);
        transform-origin: top;
        margin-top: 8px;
    }
    50% {
        transform: scaleY(1) translateX(110%);
        transform-origin: top;
        margin-top: 8px;
    }
    100% {
        transform: scaleY(0) translateX(110%);
        transform-origin: top;
        min-height: 0;
        max-height: 0;
        margin-top: 0;
    }
}

</style>
