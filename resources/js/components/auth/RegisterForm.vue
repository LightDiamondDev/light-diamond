<script setup lang="ts">
import Button from '@/components/elements/Button.vue'

const emit = defineEmits([ 'switch-to-login-form' ])

let successNickname = false
</script>

<template>
    <form action="" class="register flex flex-col items-center">
        <fieldset class="flex flex-col items-center">
            <div class="group flex flex-col items-center">
                <span class="subtitle text-[1.1rem] m-2">Никнейм</span>
                <label for="register-nickname">
                    <input class="text-[0.9rem]" id="register-nickname" placeholder="Steve" type="text">
                </label>
                <span
                    :class="{ 'error': !successNickname, 'success': successNickname}"
                    class="status text-[0.8rem] m-2"
                >
                    Ошибка текст с ошибкой текст!
                    Ошибка текст с ошибкой текст!
                </span>
                <span class="subtitle text-[1.1rem] m-2">E-mail</span>
                <label for="register-email">
                    <input class="text-[0.9rem]" id="register-email" placeholder="steve@minecraft.net"
                           type="email">
                </label>
                <span
                    :class="{ 'error': !successNickname, 'success': successNickname}"
                    class="status text-[0.8rem] m-2"
                >
                    Ошибка текст с ошибкой текст!
                    Ошибка текст с ошибкой текст!
                </span>
            </div>
            <div class="group set flex flex-col items-center">
                <span class="subtitle text-[1.1rem] m-2">Пароль</span>
                <label for="register-password">
                    <input class="text-[0.9rem]" id="register-password" placeholder="Пароль" type="password">
                </label>
                <span
                    :class="{ 'error': !successNickname, 'success': successNickname}"
                    class="status text-[0.8rem] m-2"
                >
                    Ошибка текст с ошибкой текст!
                    Ошибка текст с ошибкой текст!
                </span>

                <span class="subtitle text-[1.1rem] m-2">Повторный пароль</span>
                <label for="register-repeat-password">
                    <input
                        class="text-[0.9rem]"
                        id="register-repeat-password"
                        placeholder="Повторный пароль"
                        type="password"
                    >
                </label>
                <span
                    :class="{ 'error': !successNickname, 'success': successNickname}"
                    class="status text-[0.8rem] m-2"
                >
                        Ошибка текст с ошибкой текст!
                        Ошибка текст с ошибкой текст!
                </span>
            </div>
        </fieldset>

        <Button button-type="submit" icon="icon-bestiary" icon-size="32px" text="Зарегистрироваться"/>

        <button class="help-button mb-2 mt-2" type="button">
            <span class="p-2 text-[0.9rem]" @click="emit('switch-to-login-form')">Уже есть учётная запись?</span>
        </button>

    </form>

</template>

<style scoped>

</style>
