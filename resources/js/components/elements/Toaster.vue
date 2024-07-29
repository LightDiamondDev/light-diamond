<script setup lang="ts">
import Toast from '@/components/elements/Toast.vue'
import Achievement from '@/components/elements/Achievement.vue'
import {useToastStore} from '@/stores/toast'

const toastStore = useToastStore()

</script>

<template>
    <div class="toaster flex flex-col">

        <TransitionGroup name="toast-sliding">

            <Toast
                v-for="toast of toastStore.toasts"
                :key="toast.id"
                :toast="toast"
            />

                <!--
                icon="icon-download"
                title="Скачивание началось..."
                text="Благодарим за скачивание!"
                type="info"
                -->




            <!--

            <Toast
                @close="remove($event)"
                icon="icon-small-cross"
                title="Ошибка!"
                text="Что-то пошло не так... Запись не опубликована!"
                type="error"
            />

            <Toast
                @close="remove($event)"
                icon="icon-tick"
                title="Успех!"
                text="Ваша запись успешно опубликована!"
                type="success"
            />

            <Toast
                @close="remove($event)"
                icon="icon-hand"
                title="Предупреждение!"
                text="Вам отправили предупреждение в связи с нарушением Авторских Прав!"
                type="warning"
            />


            -->



            <!--
            <Achievement
                @close="remove($event)"
                achievement-title="Признание I"
                achievement-description="заработать 50 лайков за публикации"
                icon="icon-achievement"
                :key="achievement.id"
            />
            -->

        </TransitionGroup>

    </div>
</template>

<style scoped>
.toaster {
    top: var(--header-height);
    max-width: 360px;
    position: fixed;
    width: 100%;
    z-index: 3;
    right: 0;
}

/* =============== [ Анимации ] =============== */

.toast-sliding-enter-active, .top-translate-leave-active {
    transition: all .8s ease-in-out;
}
.toast-sliding-enter-from,
.toast-sliding-leave-to {
    transform: translateX(110%);
}
</style>
