<script setup lang="ts">
import Toast from '@/components/elements/Toast.vue'
import {useToastStore} from '@/stores/toast'

const toastStore = useToastStore()

</script>

<template>
    <div class="toaster flex flex-col max-w-[360px] gap-2 mt-2 fixed">

        <TransitionGroup name="bounce">
            <Toast
                v-for="toast of toastStore.toasts"
                class="overflow-hidden"
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
    z-index: 3;
    right: 0;
}

.bounce-enter-from {
    transition: all 1s ease-in-out;
    transform: translateX(110%);
}

.bounce-enter-active {
    transform: translateX(110%);
    animation: bounce-in 0.5s;
}

.bounce-leave-from {
    transform: translateX(0);
}

.bounce-leave-active {
    animation: bounce-out 3s;
}

@keyframes bounce-in {
    0% {
        transform: translateX(110%);
    }
    50% {
        transform: translateX(110%);
    }
    100% {
        transform: translateX(0);
    }
}

@keyframes bounce-out {
    0% {
        transform: translateX(0);
    }
    50% {
        transform: translateX(200%);
    }
    55% {
        transform: translateX(200%);
        min-height: 0;
        max-height: 0;
    }
    100% {
        transform: translateX(200%);
        min-height: 0;
        max-height: 0;
    }
}

/* =============== [ Анимации ] =============== */

.toast-sliding-enter-active {
    transition: all 1s ease-in-out;
}

.toast-sliding-enter-from {
    transform: translateX(110%);
}

.toast-sliding-leave-active {
    transition: all 1s ease-in-out;
    animation: goo 2s;
}

@keyframes goo {
    0% {
        max-height: fit-content;
        min-height: fit-content;
        transform: translateX(0);
    }
    50% {
        max-height: fit-content;
        min-height: fit-content;
        transform: translateX(110%);
    }
    100% {
        transform: translateX(110%);
        max-height: 0;
        min-height: 0;
    }
}

</style>
