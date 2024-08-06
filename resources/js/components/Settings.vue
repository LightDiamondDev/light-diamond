<script setup lang="ts">
import ImageLoader from '@/components/elements/ImageLoader.vue'
import ItemButton from '@/components/elements/ItemButton.vue'
import {reactive, ref} from 'vue'
import Input from '@/components/elements/Input.vue'
import Button from '@/components/elements/Button.vue'

enum Section {
    PROFILE,
    SECURITY
}

const profileData = reactive({
    avatar: '',
    username: ''
})

const securityData = reactive({
    email: '',
    password: '',
    password_confirmation: ''
})

let currentSection = ref(Section.PROFILE)
const isProcessing = ref(false)

let isMobileMenu = ref(true)
let isSettingsUsernameEdit = ref(false)
let isSettingsEmailEdit = ref(false)
let isSettingsPasswordEdit = ref(false)

let settingsTitle;

let unitId = ref(0)
const errors = ref([])

function setMobileMenu() {
    isMobileMenu.value = true
}

function setProfileSection() {
    currentSection.value = Section.PROFILE
    settingsTitle = 'Профиль'
    isMobileMenu.value = false
}

function setSecuritySection() {
    currentSection.value = Section.SECURITY
    settingsTitle = 'Безопасность'
    isMobileMenu.value = false
}

setProfileSection()

function submitProfile() {
    console.log('submitProfile()')
}

function submitSecurity() {
    console.log('submitSecurity()')
}
</script>

<template>
    <div class="settings flex flex-col pb-2 pt-2">
        <div class="title flex items-center mb-2 pl-2 pr-2">
            <button
                class="h-[48px] w-[48px] justify-center items-center arrow locked"
                :class="{ 'invisible': isMobileMenu }"
                @click="setMobileMenu()"
            >
                <span class="flex icon icon-left-arrow"></span>
            </button>
            <div class="flex items-center">
                <span class="text-[1.2rem] md:text-[2rem]">Настройки</span>
                <span v-if="!isMobileMenu" class="text-[1.1rem] md:text-[2rem] opacity-80 locked">ﾠ>ﾠ</span>
                <span v-if="!isMobileMenu" class="text-[0.9rem] md:text-[1.8rem] opacity-80">{{ settingsTitle }}</span>
            </div>
            <div class="h-[48px] w-[48px] arrow locked"></div>
        </div>
        <div class="interface flex">
            <aside :class="{ 'on': isMobileMenu }">
                <div class="units">
                    <ItemButton
                        :class="{ 'active': currentSection === Section.PROFILE }"
                        @click="setProfileSection()"
                        class="pl-8 pr-4 w-full text-[1.1rem]"
                        icon="icon-diamond"
                        text="Профиль"
                    />
                    <ItemButton
                        :class="{ 'active': currentSection === Section.SECURITY }"
                        @click="setSecuritySection()"
                        class="pl-8 pr-4 w-full text-[1.1rem]"
                        icon="icon-apple"
                        text="Безопасность"
                    />
                </div>
            </aside>

            <div
                :class="{ 'on': !isMobileMenu }"
                class="container flex flex-col"
            >

                <Transition name="smooth-settings-switch">

                    <div v-if="currentSection === Section.PROFILE" class="section flex flex-col h-full">
                        <div class="banner flex justify-center items-center w-full">
                            <img alt="" class="profile" src="/images/elements/stylization-banner.png">
                        </div>
                        <form action="" class="flex flex-col h-full w-full">

                            <div class="section-title flex justify-center text-[1.5rem] mt-4">Аватар</div>

                            <fieldset class="flex flex-col m-4">
                                <div class="flex flex-col-reverse md:flex-row">
                                    <div class="flex self-center mt-2 md:self-start md:mt-0">
                                        <ImageLoader
                                            v-model="profileData.avatar"
                                            filler-icon="icon-white-pencil"
                                            id="settings-profile-avatar"
                                            image-path="/images/users/content/funny-girl.png"
                                        />
                                    </div>
                                    <div class="description flex flex-col md:ml-2">
                                        <p class="text-[0.9rem] min-h-[96px] p-3">
                                            Аватар — прекрасное средство графического самовыражения, а также идентификации!
                                            Загрузите своё собственное изображение, чтобы другие Пользователи могли проще
                                            и быстрее Вас отличить!
                                        </p>
                                    </div>
                                </div>
                            </fieldset>

                            <div class="section-title flex justify-center text-[1.5rem]">Никнейм</div>

                            <fieldset class="flex flex-col m-4">
                                <div class="flex flex-col">
                                    <div class="flex flex-col">
                                        <div class="description">
                                            <p class="text-[0.9rem] p-2">
                                                Что может быть лучше оригинального и звучного слогана? — оригинальный и звучный
                                                Никнейм! Главное подбирайте с умом: обновлять Никнейм можно только 1 раз в месяц!
                                            </p>
                                        </div>
                                        <div v-if="!isSettingsUsernameEdit" class="mt-4">
                                            <span class="subtitle text-[1.1rem]">Никнейм</span>
                                            <div
                                                class="
                                                    current-data-field
                                                    flex
                                                    justify-between
                                                    items-center
                                                    h-[48px]"
                                            >
                                                <div class="flex pl-3 text-[14px]">S3V3N1ce</div>
                                                <button
                                                    @click="isSettingsUsernameEdit = !isSettingsUsernameEdit"
                                                    class="edit-button p-[6px] mr-[-2px]"
                                                    type="button"
                                                >
                                                    <span class="flex icon icon icon-white-pencil"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <div v-else class="flex flex-col mt-4">
                                            <span class="subtitle text-[1.1rem]">Новый Никнейм</span>
                                            <Input
                                                v-model="profileData.username"
                                                id="settings-profile-nickname"
                                                placeholder="S3V3N1ce"
                                            />
                                        </div>
                                        <span
                                            :class="{ 'error': errors['username'], 'success': !errors['username']}"
                                            class="status text-[0.8rem] locked"
                                        >
                                            {{ errors['username']?.[0] || '&nbsp;' }}
                                        </span>
                                    </div>
                                    <div v-if="isSettingsUsernameEdit" class="flex gap-2">
                                        <Button
                                            class="confirm max-h-[64px] max-w-[200px]"
                                            button-type="submit"
                                            text="Подтвердить"
                                            :loading="isProcessing"
                                            @click.prevent="submitProfile"
                                        />
                                        <Button
                                            class="cancel max-h-[64px] max-w-[200px]"
                                            button-type="button"
                                            text="Отменить"
                                            :loading="isProcessing"
                                            @click.prevent="isSettingsUsernameEdit = false"
                                        />
                                    </div>
                                </div>
                            </fieldset>

                        </form>
                    </div>

                    <div v-else-if="currentSection === Section.SECURITY" class="section flex flex-col h-full">
                        <div class="banner flex justify-center items-center w-full">
                            <img alt="" class="profile" src="/images/elements/security-banner.png">
                        </div>
                        <form action="" class="flex flex-col h-full w-full">

                            <div class="section-title flex justify-center text-[1.5rem] mt-4">Email</div>

                            <fieldset class="flex flex-col m-4">
                                <div class="flex flex-col">
                                    <div class="flex flex-col">
                                        <div class="description">
                                            <p class="text-[0.9rem] p-2">
                                                Адрес электронной почты, к которому привязана Ваша учётная запись.
                                            </p>
                                        </div>
                                        <div v-if="!isSettingsEmailEdit" class="mt-4">
                                            <span class="subtitle text-[1.1rem]">Email</span>
                                            <div
                                                class="
                                                current-data-field
                                                flex
                                                justify-between
                                                items-center
                                                h-[48px]"
                                            >
                                                <div class="flex pl-3 text-[14px]">s3v3n1ce@minecraft.play</div>
                                                <button
                                                    @click="isSettingsEmailEdit = !isSettingsEmailEdit"
                                                    class="edit-button p-[6px] mr-[-2px]"
                                                    type="button"
                                                >
                                                    <span class="flex icon icon icon-white-pencil"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <div v-else class="flex flex-col mt-4">
                                            <span class="subtitle text-[1.1rem]">Новый Email</span>
                                            <Input
                                                v-model="securityData.email"
                                                id="settings-profile-nickname"
                                                placeholder="s3v3n1ce@minecraft.play"
                                            />
                                        </div>
                                        <span
                                            :class="{ 'error': errors['email'], 'success': !errors['email']}"
                                            class="status text-[0.8rem] locked"
                                        >
                                        {{ errors['email']?.[0] || '&nbsp;' }}
                                    </span>
                                    </div>
                                    <div v-if="isSettingsEmailEdit" class="flex gap-2">
                                        <Button
                                            class="confirm max-h-[64px] max-w-[200px]"
                                            button-type="submit"
                                            text="Подтвердить"
                                            :loading="isProcessing"
                                            @click.prevent="submitSecurity"
                                        />
                                        <Button
                                            class="cancel max-h-[64px] max-w-[200px]"
                                            button-type="button"
                                            text="Отменить"
                                            :loading="isProcessing"
                                            @click.prevent="isSettingsEmailEdit = false"
                                        />
                                    </div>
                                </div>
                            </fieldset>

                            <div class="section-title flex justify-center text-[1.5rem]">Пароль</div>

                            <fieldset class="flex flex-col m-4">
                                <div class="flex flex-col">
                                    <div class="flex flex-col">
                                        <div class="description">
                                            <p class="text-[0.9rem] p-2">
                                                Пароль должен быть надёжным, содержать заглавные и строчные буквы,
                                                а также цифры и спец. символы! Хороший пароль — ключ к неуязвимости!
                                            </p>
                                        </div>
                                        <div v-if="!isSettingsPasswordEdit" class="mt-4">
                                            <span class="subtitle text-[1.1rem]">Пароль</span>
                                            <div
                                                class="
                                                current-data-field
                                                flex
                                                justify-between
                                                items-center
                                                h-[48px]"
                                            >
                                                <div class="flex pl-3 text-[14px]">••••••••••</div>
                                                <button
                                                    @click="isSettingsPasswordEdit = !isSettingsPasswordEdit"
                                                    class="edit-button p-[6px] mr-[-2px]"
                                                    type="button"
                                                >
                                                    <span class="flex icon icon icon-white-pencil"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <div v-else class="flex flex-col mt-4">
                                            <span class="subtitle text-[1.1rem]">Новый пароль</span>
                                            <Input
                                                v-model="securityData.password"
                                                id="settings-profile-nickname"
                                                placeholder="Пароль"
                                            />
                                            <span
                                                :class="{ 'error': errors['password'], 'success': !errors['password']}"
                                                class="status text-[0.8rem] locked"
                                            >
                                                {{ errors['password']?.[0] || '&nbsp;' }}
                                            </span>
                                            <span class="subtitle text-[1.1rem]">Новый повторный пароль</span>
                                            <Input
                                                v-model="securityData.password_confirmation"
                                                id="settings-profile-nickname"
                                                placeholder="Повторный пароль"
                                            />
                                            <span
                                                :class="{ 'error': errors['password_confirmation'], 'success': !errors['password_confirmation']}"
                                                class="status text-[0.8rem] locked"
                                            >
                                                {{ errors['password_confirmation']?.[0] || '&nbsp;' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div v-if="isSettingsPasswordEdit" class="flex gap-2">
                                        <Button
                                            class="confirm max-h-[64px] max-w-[200px]"
                                            button-type="submit"
                                            text="Подтвердить"
                                            :loading="isProcessing"
                                            @click.prevent="submitSecurity"
                                        />
                                        <Button
                                            class="cancel max-h-[64px] max-w-[200px]"
                                            button-type="button"
                                            text="Отменить"
                                            :loading="isProcessing"
                                            @click.prevent="isSettingsPasswordEdit = false"
                                        />
                                    </div>
                                </div>
                            </fieldset>

                        </form>
                    </div>

                </Transition>

            </div>
        </div>
    </div>
</template>

<style scoped>
.settings aside {
    margin-right: .5rem;
    min-width: 280px;
}
.settings aside .units .item-button {
    background-size: 400% 100%;
}
.settings .title {
    justify-content: center;
    height: 72px;
}
.settings .title .arrow {
    display: none;
}
.settings .container {
    position: relative;
    min-height: 720px;
    overflow: hidden;
}
.settings .banner {
    overflow: hidden;
    height: 200px;
    width: 100%;
}
.settings .banner .profile {
    animation: banner-scale-animation 15s infinite;
    width: 100%;
}
.settings form .section-title {
    background-size: 400% 100%;
}
.settings form fieldset label {
    height: 48px;
    width: 100%;
}
.settings form fieldset .image-loader {
    height: 96px;
    width: 96px
}
.settings form fieldset label input {
    height: 100%;
    width: 100%;
}

/* =============== [ Анимации ] =============== */

.smooth-settings-switch-enter-active,
.smooth-auth-switch-leave-active {
    transition: .8s ease;
    position: absolute;
}

.smooth-settings-switch-enter-from {
    transform: translateY(100%);
    transition: .8s;
    opacity: 0;
}

.smooth-settings-switch-leave-to {
    transform: translateY(-100%);
    transition: .8s;
    opacity: 0;
}

@keyframes banner-scale-animation {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

/* =============== [ Медиа-Запрос { ?px < 769px } ] =============== */

@media screen and (max-width: 768px) {
    .settings {
        margin: 0 .5rem;
    }
    .settings .title {
        justify-content: space-between;
        height: 64px;
    }
    .settings .title .arrow {
        display: flex;
    }
    .settings .interface aside {
        transform: translateX(-100%);
        transition: .5s;
        margin-right: 0;
        min-width: 0;
        opacity: 0;
        border: 0;
        width: 0;
    }
    .settings .interface aside.on {
        transform: translateX(0);
        max-width: 100%;
        width: 100%;
        opacity: 1;
    }
    .settings .interface .container {
        transform: translateX(100%);
        min-height: 1024px;
        transition: .5s;
        opacity: 0;
        border: 0;
        width: 0;
    }
    .settings .interface .container.on {
        transform: translateX(0);
        max-width: 100%;
        width: 100%;
        opacity: 1;
    }
    .settings .banner {
        height: fit-content;
    }
    .smooth-settings-switch-enter-active,
    .smooth-auth-switch-leave-active {
        transition: none;
    }

    .smooth-settings-switch-enter-from {
        transform: translateY(0);
        transition: none;
    }

    .smooth-settings-switch-leave-to {
        transform: translateY(0);
        transition: none;
    }
}
</style>
