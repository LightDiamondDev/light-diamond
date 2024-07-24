<script setup lang="ts">
import Button from '@/components/elements/Button.vue'

let successNickname = false
</script>

<template>
    <form action="" class="reset-form flex flex-col items-center">
        <fieldset class="flex flex-col items-center">
            <span class="task text-[0.8rem] mb-2 mt-2">
                Придумайте новый пароль. Он должен быть надёжным!
            </span>
            <span class="subtitle text-[1.1rem] m-2">Новый пароль</span>
            <label for="recovery-password">
                <input class="text-[0.9rem]" id="recovery-password" placeholder="Пароль" type="password">
            </label>
            <span
                :class="{ 'error': !successNickname, 'success': successNickname}"
                class="status text-[0.8rem] mt-1"
            >
                Ошибка текст с ошибкой текст!
            </span>
            <span class="subtitle text-[1.1rem] m-2">Повторный пароль</span>
            <label for="recovery-repeat-password">
                <input
                    class="text-[0.9rem]"
                    id="recovery-repeat-password"
                    placeholder="Повторный пароль"
                    type="password"
                >
            </label>
            <span
                :class="{ 'error': !successNickname, 'success': successNickname}"
                class="status text-[0.8rem] mt-1"
            >
                Ошибка текст с ошибкой текст!
            </span>
        </fieldset>

        <div class="mob creeper flex justify-center items-center mb-4">
            <div class="animation-agree-creeper"></div>
        </div>

        <Button button-type="submit" icon="icon-bestiary" size="32px" text="Подтвердить"/>

    </form>

</template>

<style scoped>

</style>
