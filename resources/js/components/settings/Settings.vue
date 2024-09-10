<script setup lang="ts">
import {useRoute} from 'vue-router'
import {ref, watch} from 'vue'
import ProfileSettings from '@/components/settings/ProfileSettings.vue'
import SecuritySettings from '@/components/settings/SecuritySettings.vue'
import ItemButton from '@/components/elements/ItemButton.vue'

enum Section {
    PROFILE,
    SECURITY
}
const route = useRoute()

let currentSection = ref(Section.PROFILE)
let isMobileMenu = ref(true)
let settingsTitle;

watch(route, () => {
    updateCurrentSectionByRoute()
})

function updateCurrentSectionByRoute() {
    switch (route.name) {
        case 'settings.profile':
            currentSection.value = Section.PROFILE
            break
        case 'settings.security':
            currentSection.value = Section.SECURITY
            break
    }
}

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

updateCurrentSectionByRoute()
</script>

<template>
    <div class="settings flex flex-col w-full p-2">
        <div class="title ld-primary-background ld-primary-border flex items-center mb-2 pl-2 pr-2">
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
            <aside class="ld-primary-background ld-primary-border" :class="{ 'on': isMobileMenu }">
                <div class="units">

                    <RouterLink
                        :class="{ 'transfusion': currentSection === Section.PROFILE }"
                        class="h-fit flex"
                        :to="{ name: 'settings.profile' }"
                    >
                        <ItemButton
                            class="pl-8 pr-4 w-full text-[1.1rem]"
                            @click="setProfileSection()"
                            icon="icon-diamond"
                            label="Профиль"
                            tabindex="-1"
                        />
                    </RouterLink>

                    <RouterLink
                        :class="{ 'transfusion': currentSection === Section.SECURITY }"
                        class="h-fit flex"
                        :to="{name: 'settings.security'}"
                    >
                        <ItemButton
                            class="pl-8 pr-4 w-full text-[1.1rem]"
                            @click="setSecuritySection()"
                            label="Безопасность"
                            icon="icon-apple"
                            tabindex="-1"
                        />
                    </RouterLink>

                </div>
            </aside>

            <div
                :class="{ 'on': !isMobileMenu }"
                class="settings-container ld-primary-background flex flex-col"
            >

                <RouterView v-slot="{ Component }">
                    <Transition name="smooth-settings-switch">
                        <Component :is="Component"/>
                    </Transition>
                </RouterView>

            </div>
        </div>
    </div>
</template>

<style>
.settings {
    max-width: 1280px;
}
.settings aside {
    margin-right: .5rem;
    min-width: 280px;
}
.settings aside .units a {
    background-size: 400% 100%;
}
.settings .title {
    justify-content: center;
    height: 72px;
}
.settings .title .arrow {
    display: none;
}
.settings .interface {
    overflow: hidden;
}
.settings .settings-container {
    position: relative;
    min-height: 800px;
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

.settings form fieldset .status.error {
    color: #ff3050;
}

.settings form fieldset .status.success {
    color: var(--hover-text-color);
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
        width: 0;
    }
    .settings .interface aside.on {
        transform: translateX(0);
        max-width: 100%;
        width: 100%;
        opacity: 1;
    }
    .settings .interface .settings-container {
        transform: translateX(100%);
        min-height: 1024px;
        transition: .5s;
        opacity: 0;
        border: 0;
        width: 0;
    }
    .settings .interface .settings-container.on {
        transform: translateX(0);
        width: 100%;
        opacity: 1;
        right: 2px;
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

/* =============== [ Медиа-Запрос { ?px < 350px } ] =============== */

@media screen and (max-width: 349px) {
    .settings {
        margin: 0 .5rem;
    }
}
</style>
