<script setup lang="ts">

import Button from '@/components/elements/Button.vue'

</script>

<template>

    <form action="" class="forgot-password flex flex-col items-center">

        <fieldset class="flex flex-col items-center">
            <span class="task text-[0.8rem] mb-2 mt-2">
                Укажите свой E-mail адрес, к которому привязана учётная запись,
                и мы отправим на него письмо с подтверждением сброса пароля:
            </span>
            <span class="subtitle text-[1.1rem] m-2">E-mail</span>
            <label for="reset-email">
                <input class="text-[0.9rem]" id="reset-email" placeholder="steve@minecraft.net" type="email">
            </label>
            <span
                :class="{ 'error': !successNickname, 'success': successNickname}"
                class="status text-[0.8rem] mt-1"
            >
                Ошибка текст с ошибкой текст!
                Ошибка текст с ошибкой текст!
            </span>
        </fieldset>

        <div class="group flex flex-col items-center">
            <div class="mob iron-golem flex justify-center items-center mb-4">
                <div class="animation-walking-iron-golem mb-8 ml-10"></div>
            </div>

            <Button button-type="submit" icon="icon-bestiary" icon-size="32px" text="Получить Письмо"/>
        </div>

    </form>

</template>

<style scoped>

</style>
