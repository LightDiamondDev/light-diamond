<script setup lang="ts">
import {computed, ref} from 'vue'
import Dialog from '@/components/elements/Dialog.vue'
import ForgotPasswordForm from '@/components/auth/ForgotPasswordForm.vue'
import LoginForm from '@/components/auth/LoginForm.vue'
import RegisterForm from '@/components/auth/RegisterForm.vue'
import {getRandomSplash} from '@/stores/splashes'

enum AuthFormType {
    LOGIN,
    FORGOT_PASSWORD,
    REGISTER
}

const isVisible = defineModel<Boolean>('visible', {required: true})
const formType = ref(AuthFormType.LOGIN)

const authDialogTitle = computed(() => {
    switch (formType.value) {
        case AuthFormType.LOGIN:
            return 'Вход'
        case AuthFormType.FORGOT_PASSWORD:
            return 'Сброс пароля'
        case AuthFormType.REGISTER:
            return 'Регистрация'
    }
})

const activeSplash = getRandomSplash()

</script>

<template>
    <Dialog
        v-model:visible="isVisible"
        class="auth-dialog"
        :title="authDialogTitle"
        :back-button="formType !== AuthFormType.LOGIN"
        @back="formType = AuthFormType.LOGIN"
    >
        <template v-slot:left-content>
            <div class="illustration flex justify-center items-center">
                <div class="background-auth flex justify-center items-center">
                    <a class="logo icon-logo" href="#">
                        <span class="base">
                            <span
                                class="splash"
                                :class="{
                                    'large-splash': activeSplash.length > 30,
                                    'small-splash': activeSplash.length < 31
                                }"
                            >
                                {{ activeSplash }}
                            </span>
                        </span>
                    </a>
                    <div class="back-background background-cherry-blossom-grove"></div>
                </div>
            </div>
        </template>

        <Transition name="smooth-auth-switch">

            <LoginForm
                v-if="formType === AuthFormType.LOGIN"
                @switch-to-forgot-password-form="formType = AuthFormType.FORGOT_PASSWORD"
                @switch-to-register-form="formType = AuthFormType.REGISTER">
            </LoginForm>

            <ForgotPasswordForm v-else-if="formType === AuthFormType.FORGOT_PASSWORD"/>

            <RegisterForm v-else @switch-to-login-form="formType = AuthFormType.LOGIN"/>

        </Transition>

    </Dialog>
</template>

<style>

.auth-dialog {
    box-shadow: 0 0 10px 10px rgba(0, 0, 0, .5);
    overflow: hidden;
}

.auth-dialog .interface {
    position: relative;
    width: 380px;
    transition: .5s;
}

.auth-dialog form fieldset,
.auth-dialog form .button-container {
    width: 85%;
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

.auth-dialog .interface form {
    width: 100%;
}

.auth-dialog form .mob {
    pointer-events: none;
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

.button-container {
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
    min-height: 658px;
    overflow: hidden;
    width: 658px;
}

.illustration .background-auth {
    animation: background-auth-animation 20s infinite;
}

.illustration .background-auth,
.illustration .background-cherry-blossom-grove {
    background-size: 100% 100%;
    min-height: 654px;
    height: 100%;
    width: 654px;
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

.background-auth .logo .base {
    transform-origin: center center;
    transform: rotate(-15deg);
    position: absolute;
    height: 10px;
    bottom: -15%;
    width: 10px;
    right: 55%;
}

.background-auth .logo .splash {
    animation: splash-animation 1s infinite;
    text-shadow: 2px 2px black;
    position: absolute;
    text-align: center;
    color: #fff500;
    width: 250px;
}

.large-splash {
    font-size: .8rem;
}

.small-splash {
    font-size: .9rem;
}

.auth-dialog .group {
    width: 100%;
}

/* =============== [ Анимации ] =============== */

.smooth-auth-switch-enter-active,
.smooth-auth-switch-leave-active {
    transition: .8s ease;
    position: absolute;
}

.smooth-auth-switch-enter-from {
    transform: translateY(100%);
    transition: .8s;
    opacity: 0;
}

.smooth-auth-switch-leave-to {
    transform: translateY(-100%);
    transition: .8s;
    opacity: 0;
}

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

@keyframes splash-animation {
    0% {
        transform: scale(1.1);
    }
    50% {
        transform: scale(1);
    }
    100% {
        transform: scale(1.1);
    }
}

/* =============== [ Медиа-Запрос { ?px < 1024px + desktop-height } ] =============== */

@media screen and (max-width: 1023px) and (min-height: 654px) {
    .auth-dialog .illustration {
        display: none;
    }
    .auth-dialog .interface {
        min-height: 658px;
        max-width: 380px;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1024px + mobile-height } ] =============== */

@media screen and (max-width: 1023px) and (max-height: 653px) {
    .auth-dialog .illustration {
        display: none;
    }
    .auth-dialog {
        justify-content: center;
        align-items: center;
        max-width: 658px;
        width: 90%;
    }
    .auth-dialog .interface {
        height: 380px;
        width: 100%;
    }
    .auth-dialog .forgot-password,
    .auth-dialog .login {
        flex-direction: row;
        padding: 16px;
    }
    .auth-dialog .forgot-password .mob.iron-golem {
        height: 210px;
        width: 380px;
    }
    .auth-dialog .register fieldset {
        flex-direction: row;
        margin-top: -3%;
    }
    .auth-dialog .register .group {
        margin-right: .5rem;
    }
    .auth-dialog .register .group.set {
        margin: 0 0 0 .5rem;
    }
    .auth-dialog .register fieldset label {
        height: 30px;
    }
    .auth-dialog .register fieldset span {
        margin: 0.25rem;
    }
}

/* =============== [ Медиа-Запрос { ?px < 426px } ] =============== */

@media screen and (max-width: 425px) {
    .auth-dialog form h1 {
        font-size: 1.2rem;
    }

    .button-container .action-button .text {
        font-size: 0.8rem;
    }

    .auth-dialog form fieldset .status {
        font-size: 0.7rem;
        line-height: 1.1;
    }
}

</style>
