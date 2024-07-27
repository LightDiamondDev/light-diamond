<script setup lang="ts">
import Toast from '@/components/elements/Toast.vue'
import Achievement from '@/components/elements/Achievement.vue'
</script>

<template>
    <div class="toaster flex flex-col">

        <Transition name="toast-sliding">
            <Toast icon="icon-download" title="Скачивание началось..." text="Благодарим за скачивание!" type="info"/>
        </Transition>

        <Transition name="toast-sliding">
            <Toast icon="icon-small-cross" title="Ошибка!" text="Что-то пошло не так... Запись не опубликована!" type="error"/>
        </Transition>

        <Transition name="toast-sliding">
            <Toast icon="icon-tick" title="Успех!" text="Ваша запись успешно опубликована!" type="success"/>
        </Transition>

        <Transition name="toast-sliding">
            <Toast icon="icon-hand" title="Предупреждение!" text="Вам отправили предупреждение в связи с нарушением Авторских Прав!" type="warning"/>
        </Transition>

        <Transition name="toast-sliding">
            <Achievement
                achievement-title="Признание I"
                achievement-description="заработать 50 лайков за публикации"
                icon="icon-achievement"
            />
        </Transition>

    </div>
</template>

<style scoped>
.toaster {
    top: var(--header-height);
    max-width: 360px;
    position: fixed;
    height: 90vh;
    width: 100%;
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
