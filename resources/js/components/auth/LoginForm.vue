<script setup lang="ts">
import Button from '@/components/elements/Button.vue'

let successNickname = false

const emit = defineEmits([
    'switch-to-forgot-password-form',
    'switch-to-register-form'
])

</script>

<template>
    <form action="" class="login flex flex-col items-center">
        <fieldset class="flex flex-col items-center">
            <span class="subtitle text-[1.1rem] m-2">
                Никнейм
                <!--
                <span class="ml-1 mr-1" style="font-size: .8rem; opacity: .8;">или</span>
                E-mail
                -->
            </span>
            <label for="login-nickname">
                <input class="text-[0.9rem]" id="login-nickname" placeholder="Steve или steve@minecraft.net" type="text">
            </label>
            <span
                :class="{ 'error': !successNickname, 'success': successNickname}"
                class="status text-[0.8rem] m-2"
            >
                Ошибка текст с ошибкой текст!
            </span>
            <span class="subtitle text-[1.1rem] m-2">Пароль</span>
            <label for="login-password">
                <input class="text-[0.9rem]" id="login-password" placeholder="Пароль" type="password">
            </label>
            <span
                :class="{ 'error': !successNickname, 'success': successNickname}"
                class="status text-[0.8rem] m-2"
            >
                Ошибка текст с ошибкой текст!
            </span>
            <div class="auth-options flex justify-between mt-2">
                <label class="auth-remember-line flex items-center" for="auth-remember-checkbox">
                    <input id="auth-remember-checkbox" name="remember" type="checkbox" value="false">
                    <span class="ld-checkbox icon-border flex justify-center items-center">
                        <span class="icon-tick"></span>
                    </span>
                    <span class="auth-remember-me text-[0.8rem]">Запомнить меня</span>
                </label>
                <button class="help-button flex items-center" type="button">
                    <span class="text-[0.8rem]" @click="emit('switch-to-forgot-password-form')">Забыли пароль?</span>
                </button>
            </div>
        </fieldset>

        <div class="group flex flex-col items-center">
            <div class="mob parrot flex justify-center items-center mb-4">
                <div class="animation-dancing-multicoloured-parrot"></div>
            </div>

            <Button button-type="submit" icon="icon-bestiary" icon-size="32px" text="Войти"/>

            <button class="help-button mb-2 mt-2" type="button">
                <span class="p-2 text-[0.9rem]" @click="emit('switch-to-register-form')">Ещё нет учётной записи?</span>
            </button>
        </div>

    </form>

</template>

<style scoped>

</style>
