<script setup lang="ts">

import {computed, ref} from 'vue'
import Dialog from '@/components/elements/Dialog.vue'
import ForgotPasswordForm from '@/components/auth/ForgotPasswordForm.vue'
import LoginForm from '@/components/auth/LoginForm.vue'
import RegisterForm from '@/components/auth/RegisterForm.vue'
import Button from '@/components/elements/Button.vue'

enum AuthFormType {
    FORGOT_PASSWORD,
    LOGIN,
    REGISTER,
    RESET
}

const authDialogTitle = computed(() => {
    switch (authFormType.value) {
        case AuthFormType.FORGOT_PASSWORD:
            return 'Сброс пароля'
        case AuthFormType.LOGIN:
            return 'Авторизация'
        case AuthFormType.REGISTER:
            return 'Регистрация'
        case AuthFormType.RESET:
            return 'Восстановление'
    }
})

const authFormType = ref(AuthFormType.LOGIN)

const isVisible = defineModel<Boolean>('visible', {required: true})

</script>

<template>

    <Dialog v-model:visible="isVisible">

        <div class="auth-dialog">
            <div class="illustration">
                <div class="background-auth flex justify-center">
                    <a class="logo icon-logo" href="#">
                        <span class="splash">Я вернулся!</span>
                    </a>
                    <div class="back-background background-cherry-blossom-grove"></div>
                </div>
            </div>

            <div class="forms">

                <ForgotPasswordForm v-if="AuthFormType.FORGOT_PASSWORD"/>

                <LoginForm v-else-if="AuthFormType.LOGIN"/>

                <RegisterForm v-else-if="AuthFormType.REGISTER"/>

            </div>
        </div>

    </Dialog>

</template>

<style>

.auth-dialog {
    box-shadow: 0 0 10px 10px rgba(0, 0, 0, .5);
    overflow: hidden;
    transition: .5s;
    height: 656px;
    /*
    margin-top: 200px;
    opacity: 0;
    */
}

.auth-dialog form,
.auth-dialog .forms {
    max-width: 380px;
    height: 656px;
    width: 100%;
}

.auth-dialog .forms.id1 form {
    transform: translateY(0);
}

.auth-dialog .forms.id2 form {
    transform: translateY(-100%);
}

.auth-dialog .forms.id3 form {
    transform: translateY(-200%);
}

.auth-dialog .forms.id4 form {
    transform: translateY(-300%);
}

.auth-dialog form {
    transition: .8s;
    opacity: 0;
}

.auth-dialog form.on {
    opacity: 1;
}

.auth-dialog form fieldset,
.auth-dialog form .auth-button-container {
    width: 85%;
}

.auth-dialog form button.m-4 {
    height: 48px;
    width: 48px;
}

.auth-dialog form button .icon {
    height: 32px;
    width: 32px;
}

.auth-dialog-window form h1 {
    color: var(--primary-text-color);
    user-select: none;
}

.auth-dialog form fieldset * {
    color: var(--primary-text-color);
}

.auth-dialog form fieldset label {
    height: 40px;
    width: 100%
}

.auth-dialog form fieldset label input {
    height: 100%;
    width: 100%;
}

.auth-dialog form fieldset span {
    user-select: none;
    width: 100%;
}

.auth-dialog-window form fieldset .status {
    line-height: 1.3;
}

.auth-dialog form fieldset input::placeholder {
    font-size: .8rem;
}

.auth-dialog form fieldset .status.error {
    color: #ff3050;
}

.auth-dialog form fieldset .status.success {
    color: var(--hover-text-color);
}

.auth-dialog form .auth-options {
    width: 100%;
}

.auth-dialog form .auth-options .auth-remember-line {
    width: fit-content;
}

.auth-dialog form .auth-options .auth-remember-line input {
    width: fit-content;
}

.auth-dialog form .auth-options .auth-remember-line {
    cursor: pointer;
    border: none;
}

#auth-remember-checkbox {
    pointer-events: none;
    position: relative;
    opacity: 0;
}

.auth-dialog form .ld-checkbox {
    position: relative;
    right: 14px;
}

.auth-dialog form .ld-checkbox,
.auth-dialog form .ld-checkbox .icon-tick {
    background-size: 100% 100%;
    height: 32px;
    width: 32px;
}

#auth-remember-checkbox + .ld-checkbox .icon-tick {
    position: relative;
    transition: .1s;
    bottom: 1px;
    opacity: 0;
}

.auth-remember-line .auth-remember-me {
    position: relative;
    right: 8px;
}

.auth-remember-line:hover .auth-remember-me,
#auth-remember-checkbox:focus-visible + .ld-checkbox + .auth-remember-me {
    color: var(--hover-text-color);
}

#auth-remember-checkbox:checked + .ld-checkbox .icon-tick {
    opacity: 1;
}

.auth-dialog form .mob {
    user-select: none;
    overflow: hidden;
}

.auth-dialog form .mob.creeper {
    height: 176px;
}

.auth-dialog form .mob.iron-golem {
    height: 216px;
}

.auth-dialog form .mob.parrot {
    height: 157px;
    width: 156px;
}

.auth-dialog form .mob div {
    background-size: 100% 100%;
    height: 100%;
    width: 100%;
}

.auth-dialog form .mob .animation-agree-creeper {
    height: 240px;
    width: 240px;
}

.auth-dialog form .mob .animation-walking-iron-golem {
    height: 320px;
    width: 460px;
}

.auth-dialog form .mob .animation-dancing-multicoloured-parrot {
    animation: parrot-flip-animation 10s infinite;
}

.auth-button-container {
    height: 80px;
    width: 80%;
}

.auth-dialog form .help-button span {
    color: var(--primary-text-color);
    transition: .2s;
}

.auth-dialog form .help-button,
.auth-dialog form .help-button:focus-visible,
.auth-dialog form .help-button:hover {
    background: none;
    border: none;
}

.auth-dialog form .help-button:focus-visible span, .auth-dialog form .help-button:hover span {
    color: var(--hover-text-color);
    text-decoration: underline;
}

.auth-dialog .illustration {
    overflow: hidden;
    max-width: 656px;
    height: 656px;
    width: 100%;
}

.auth-dialog .illustration .background-auth {
    animation: background-auth-animation 20s infinite;
}

.auth-dialog .illustration .background-auth,
.auth-dialog .illustration .background-cherry-blossom-grove {
    background-size: 100% 100%;
    height: 652px;
    width: 652px;
}

.back-background {
    animation: background-back-animation 60s infinite;
}

.background-auth .logo {
    background-repeat: no-repeat;
    background-size: 100% 100%;
    user-select: none;
    position: fixed;
    height: 83px;
    width: 280px;
    z-index: 1;
    top: 12%;
}

.background-auth .logo .splash {
    animation: small-splash-animation 1s infinite;
    transform-origin: center center;
    text-shadow: 2px 2px black;
    transform: rotate(-15deg);
    position: absolute;
    text-align: center;
    color: #fff500;
    right: -30px;
    bottom: 5px;
}

/* =============== [ Анимации ] =============== */

@keyframes background-auth-animation {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}

@keyframes background-back-animation {
    0% {
        opacity: 0;
    }
    45% {
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
    95% {
        opacity: 1;
    }
    100% {
        opacity: 0;
    }
}

@keyframes parrot-flip-animation {
    0% {
        transform: rotate(0deg);
    }
    85% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

@keyframes big-splash-animation {
    0% {
        font-size: 0.9rem;
    }
    50% {
        font-size: 0.8rem;
    }
    100% {
        font-size: 0.9rem;
    }
}

@keyframes small-splash-animation {
    0% {
        font-size: 1rem;
    }
    50% {
        font-size: 0.9rem;
    }
    100% {
        font-size: 1rem;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1024px } ] =============== */

@media screen and (max-width: 1023px) {
    .auth-dialog .illustration {
        display: none;
    }
}

/* =============== [ Медиа-Запрос { ?px < 426px } ] =============== */

@media screen and (max-width: 425px) {
    .auth-dialog form h1 {
        font-size: 1.2rem;
    }

    .auth-button-container .action-button .text {
        font-size: 0.8rem;
    }

    .auth-dialog form fieldset .status {
        font-size: 0.7rem;
        line-height: 1.1;
    }
}

</style>
