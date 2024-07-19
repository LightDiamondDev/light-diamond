<script setup lang="ts">
import {useGlobalModalStore} from '@/stores/global-modal'
import {computed, ref} from 'vue'

enum AuthFormType {
    LOGIN,
    REGISTER,
    RESET,
    RECOVERY,
}

const globalModalStore = useGlobalModalStore()
const authFormType = ref(AuthFormType.LOGIN)
const container = ref<Element>()
const isMaskMouseDown = ref(false)

const authDialogTitle = computed(() => {
    switch (authFormType.value) {
        case AuthFormType.LOGIN:
            return 'Авторизация'
        case AuthFormType.REGISTER:
            return 'Регистрация'
        case AuthFormType.RESET:
            return 'Сброс пароля'
        case AuthFormType.RECOVERY:
            return 'Восстановление'
    }
})

let successNickname = false
let authCounter = ref(3)

function isContainerEventTarget(event: MouseEvent) {
    return container.value && container.value?.contains(event.target)
}

function onMaskMouseDown(event: MouseEvent) {
    if (!isContainerEventTarget(event)) {
        isMaskMouseDown.value = true
    }
}

function onMaskMouseUp(event: MouseEvent) {
    if (isMaskMouseDown.value && !isContainerEventTarget(event)) {
        globalModalStore.isAuth = false
    }
    isMaskMouseDown.value = false
}
</script>

<template>
    <div
        :class="{ 'on': globalModalStore.isAuth }"
        class="auth-modal-window flex justify-center items-center"
        @mousedown="onMaskMouseDown"
        @mouseup="onMaskMouseUp"
    >
        <div ref="container" class="interface flex">
            <div class="illustration">
                <div class="background-auth flex justify-center">
                    <a class="logo icon-logo" href="#">
                        <span class="splash">Я вернулся!</span>
                    </a>
                    <div class="back-background background-cherry-blossom-grove"></div>
                </div>
            </div>

            <div
                :class="{ 'id1': authCounter == 1, 'id2': authCounter == 2, 'id3': authCounter == 3, 'id4': authCounter == 4 }"
                class="forms"
            >
                <form action="" :class="{ 'on': authCounter == 1 }" class="recovery flex flex-col items-center">
                    <div class="header flex justify-between items-center">
                        <button class="flex justify-center items-center m-4" @click="authCounter = 2" type="button">
                            <span class="icon icon-long-left-arrow"></span>
                        </button>
                        <h1 class="text-[1.5rem]">Восстановление</h1>
                        <button class="flex justify-center items-center m-4" @click="globalModalStore.isAuth = false">
                            <span class="icon icon-cross"></span>
                        </button>
                    </div>
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

                    <button
                        class="auth-button-container button-container flex justify-center items-center"
                        type="submit"
                    >
                        <span class="action-button flex flex-col">
                            <span class="press flex justify-center items-center">
                                <span class="icon icon-bestiary mr-2"></span>
                                <span class="text duration-200">Подтвердить</span>
                            </span>
                            <span class="base"></span>
                        </span>
                    </button>
                    <button class="help-button mb-2 mt-2" type="button">
                        <span class="p-2 text-[0.9rem]" @click="authCounter = 3">Вспомнили пароль?</span>
                    </button>
                </form>

                <form action="" :class="{ 'on': authCounter == 2 }" class="reset flex flex-col items-center">
                    <div class="header flex justify-between items-center">
                        <button class="flex justify-center items-center m-4" @click="authCounter = 1" type="button">
                            <span class="icon icon-long-left-arrow"></span>
                        </button>
                        <h1 class="text-[1.8rem]">Сброс пароля</h1>
                        <button
                            class="flex justify-center items-center m-4"
                            @click="globalModalStore.isAuth = false" type="button"
                        >
                            <span class="icon icon-cross"></span>
                        </button>
                    </div>
                    <fieldset class="flex flex-col items-center">
                        <span class="task text-[0.8rem] mb-2 mt-2">
                            Укажите свой E-mail адрес, к которому привязана учётная запись,
                            и мы отправим на него письмо с подтверждением сброса пароля:
                        </span>
                        <span class="subtitle text-[1.1rem] m-2">E-mail</span>
                        <label for="reset-email">
                            <input class="text-[0.9rem]" id="reset-email" placeholder="steve@minecraft.net"
                                   type="email">
                        </label>
                        <span
                            :class="{ 'error': !successNickname, 'success': successNickname}"
                            class="status text-[0.8rem] mt-1"
                        >
                            Ошибка текст с ошибкой текст!
                        </span>
                    </fieldset>

                    <div class="mob iron-golem flex justify-center items-center mb-4">
                        <div class="animation-walking-iron-golem mb-8 ml-10"></div>
                    </div>

                    <button
                        class="auth-button-container button-container flex justify-center items-center"
                        type="submit"
                    >
                        <span class="action-button flex flex-col">
                            <span class="press flex justify-center items-center">
                                <span class="icon icon-bestiary mr-2"></span>
                                <span class="text duration-200">Получить Письмо</span>
                            </span>
                            <span class="base"></span>
                        </span>
                    </button>
                    <button class="help-button mb-2 mt-2" type="button">
                        <span class="p-2 text-[0.9rem]" @click="authCounter = 3">Вспомнили пароль?</span>
                    </button>
                </form>

                <form action="" :class="{ 'on': authCounter == 3 }" class="login flex flex-col items-center">
                    <div class="header flex justify-between items-center">
                        <button class="flex justify-center items-center m-4" @click="authCounter = 4" type="button">
                            <span class="icon icon-long-left-arrow"></span>
                        </button>
                        <h1 class="text-[1.8rem]">Авторизация</h1>
                        <button
                            class="flex justify-center items-center m-4"
                            @click="globalModalStore.isAuth = false" type="button"
                        >
                            <span class="icon icon-cross"></span>
                        </button>
                    </div>
                    <fieldset class="flex flex-col items-center">
                        <span class="subtitle text-[1.1rem] m-2">
                            Никнейм
                            <span class="ml-1 mr-1" style="font-size: .8rem; opacity: .8;">или</span>
                            E-mail
                        </span>
                        <label for="login-nickname">
                            <input class="text-[0.9rem]" id="login-nickname" placeholder="Steve или steve@minecraft.net"
                                   type="text">
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
                                <span class="text-[0.8rem]" @click="authCounter = 2">Забыли пароль?</span>
                            </button>
                        </div>
                    </fieldset>

                    <div class="mob parrot flex justify-center items-center mb-4">
                        <div class="animation-dancing-multicoloured-parrot"></div>
                    </div>

                    <button
                        class="auth-button-container button-container flex justify-center items-center"
                        type="submit"
                    >
                        <span class="action-button flex flex-col">
                            <span class="press flex justify-center items-center">
                                <span class="icon icon-bestiary mr-2"></span>
                                <span class="text duration-200">Авторизоваться</span>
                            </span>
                            <span class="base"></span>
                        </span>
                    </button>
                    <button class="help-button mb-2 mt-2" type="button">
                        <span class="p-2 text-[0.9rem]" @click="authCounter = 4">Ещё нет учётной записи?</span>
                    </button>
                </form>

                <form action="" :class="{ 'on': authCounter == 4 }" class="register flex flex-col items-center">
                    <div class="header flex justify-between items-center">
                        <button class="flex justify-center items-center m-4" @click="authCounter = 3" type="button">
                            <span class="icon icon-long-left-arrow"></span>
                        </button>
                        <h1 class="text-[1.8rem]">Регистрация</h1>
                        <button
                            class="flex justify-center items-center m-4"
                            @click="globalModalStore.isAuth = false" type="button"
                        >
                            <span class="icon icon-cross"></span>
                        </button>
                    </div>
                    <fieldset class="flex flex-col items-center">
                        <span class="subtitle text-[1.1rem] m-2">Никнейм</span>
                        <label for="register-nickname">
                            <input class="text-[0.9rem]" id="register-nickname" placeholder="Steve" type="text">
                        </label>
                        <span
                            :class="{ 'error': !successNickname, 'success': successNickname}"
                            class="status text-[0.8rem] m-2"
                        >
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
                        </span>
                        <span class="subtitle text-[1.1rem] m-2">Пароль</span>
                        <label for="register-password">
                            <input class="text-[0.9rem]" id="register-password" placeholder="Пароль" type="password">
                        </label>
                        <span
                            :class="{ 'error': !successNickname, 'success': successNickname}"
                            class="status text-[0.8rem] m-2"
                        >
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
                    </span>
                    </fieldset>
                    <button
                        class="auth-button-container button-container flex justify-center items-center"
                        type="submit"
                    >
                        <span class="action-button flex flex-col">
                            <span class="press flex justify-center items-center">
                                <span class="icon icon-bestiary mr-2"></span>
                                <span class="text duration-200">Зарегистрироваться</span>
                            </span>
                            <span class="base"/>
                        </span>
                    </button>
                    <button class="help-button mb-2 mt-2" type="button">
                        <span class="p-2 text-[0.9rem]" @click="authCounter = 3">Уже есть учётная запись?</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
<style scoped>
.auth-modal-window {
    background-color: rgba(0, 0, 0, .5);
    pointer-events: none;
    position: fixed;
    transition: 1s;
    height: 100%;
    width: 100%;
    opacity: 0;
    z-index: 2;
}

.auth-modal-window.on {
    pointer-events: visible;
    opacity: 1;
}

.auth-modal-window .interface {
    box-shadow: 0 0 10px 10px rgba(0, 0, 0, .5);
    pointer-events: none;
    margin-top: 200px;
    transition: .5s;
    height: 656px;
    opacity: 0;
}

.auth-modal-window.on .interface {
    pointer-events: visible;
    overflow: hidden;
    transition: .5s;
    margin-top: 0;
    opacity: 1;
}

.auth-modal-window form,
.auth-modal-window .forms {
    max-width: 380px;
    height: 656px;
    width: 100%;
}

.auth-modal-window .forms.id1 form {
    transform: translateY(0);
}

.auth-modal-window .forms.id2 form {
    transform: translateY(-100%);
}

.auth-modal-window .forms.id3 form {
    transform: translateY(-200%);
}

.auth-modal-window .forms.id4 form {
    transform: translateY(-300%);
}

.auth-modal-window form {
    transition: 1s;
    opacity: 0;
}

.auth-modal-window form.on {
    opacity: 1;
}

.auth-modal-window form fieldset,
.auth-modal-window form .auth-button-container {
    width: 85%;
}

.auth-modal-window form button.m-4 {
    height: 48px;
    width: 48px;
}

.auth-modal-window form button .icon {
    height: 32px;
    width: 32px;
}

.auth-modal-window form h1 {
    color: var(--primary-text-color);
    user-select: none;
}

.auth-modal-window form fieldset * {
    color: var(--primary-text-color);
}

.auth-modal-window form fieldset label {
    height: 40px;
    width: 100%
}

.auth-modal-window form fieldset label input {
    height: 100%;
    width: 100%;
}

.auth-modal-window form fieldset span {
    user-select: none;
    width: 100%;
}

.auth-modal-window form fieldset .status {
    line-height: 1.3;
    height: 12px;
}

.auth-modal-window form fieldset input::placeholder {
    font-size: .8rem;
}

.auth-modal-window form fieldset .status.error {
    color: #ff3050;
}

.auth-modal-window form fieldset .status.success {
    color: var(--hover-text-color);
}

.auth-modal-window form .auth-options {
    width: 100%;
}

.auth-modal-window form .auth-options .auth-remember-line {
    width: fit-content;
}

.auth-modal-window form .auth-options .auth-remember-line input {
    width: fit-content;
}

.auth-modal-window form .auth-options .auth-remember-line {
    cursor: pointer;
    border: none;
}

#auth-remember-checkbox {
    pointer-events: none;
    position: relative;
    opacity: 0;
}

.auth-modal-window form .ld-checkbox {
    position: relative;
    right: 14px;
}

.auth-modal-window form .ld-checkbox,
.auth-modal-window form .ld-checkbox .icon-tick {
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

.auth-modal-window form .mob {
    user-select: none;
    overflow: hidden;
}

.auth-modal-window form .mob.creeper {
    height: 176px;
}

.auth-modal-window form .mob.iron-golem {
    height: 216px;
}

.auth-modal-window form .mob.parrot {
    height: 157px;
    width: 156px;
}

.auth-modal-window form .mob div {
    background-size: 100% 100%;
    height: 100%;
    width: 100%;
}

.auth-modal-window form .mob .animation-agree-creeper {
    height: 240px;
    width: 240px;
}

.auth-modal-window form .mob .animation-walking-iron-golem {
    height: 320px;
    width: 460px;
}

.auth-modal-window form .mob .animation-dancing-multicoloured-parrot {
    animation: parrot-flip-animation 10s infinite;
}

.auth-button-container {
    height: 80px;
    width: 80%;
}

.auth-modal-window form .help-button span {
    color: var(--primary-text-color);
    transition: .2s;
}

.auth-modal-window form .help-button,
.auth-modal-window form .help-button:focus-visible,
.auth-modal-window form .help-button:hover {
    background: none;
    border: none;
}

.auth-modal-window form .help-button:focus-visible span, .auth-modal-window form .help-button:hover span {
    color: var(--hover-text-color);
    text-decoration: underline;
}

.auth-modal-window .illustration {
    overflow: hidden;
    max-width: 656px;
    height: 656px;
    width: 100%;
}

.auth-modal-window .illustration .background-auth {
    animation: background-auth-animation 20s infinite;
}

.auth-modal-window .illustration .background-auth,
.auth-modal-window .illustration .background-cherry-blossom-grove {
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
    .auth-modal-window .illustration {
        display: none;
    }
}

/* =============== [ Медиа-Запрос { ?px < 426px } ] =============== */

@media screen and (max-width: 425px) {
    .auth-modal-window form h1 {
        font-size: 1.2rem;
    }

    .auth-button-container .action-button .text {
        font-size: 0.8rem;
    }

    .auth-modal-window form fieldset .status {
        font-size: 0.7rem;
        line-height: 1.1;
    }
}
</style>
