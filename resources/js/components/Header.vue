<script setup lang='ts'>
    import {useAuthStore} from '@/stores/auth'
    import useThemeManager from '@/theme-manager'
    import {useRouter} from 'vue-router'
    import {useModalStore} from '@/stores/modal'
    import {usePostCategoryStore} from '@/stores/postCategory'
    import {getCssVariableValue, lockGlobalScroll, remToPixels, unlockGlobalScroll} from '@/helpers'
    import {onMounted, onUnmounted, ref} from 'vue'

    const router = useRouter()
    const authStore = useAuthStore()
    const categoryStore = usePostCategoryStore()
    const modalStore = useModalStore()
    const themeManager = useThemeManager()

    const headerHeight = remToPixels(getCssVariableValue('--header-height'))

    let isHeaderFixed = ref(false)
    const isHeaderHidden = ref(false)
    let isHeaderSidebarDisplay = ref(false);
    let isDesktopSidebarDisplay = ref(false);
    let isLightTheme = ref(true);

    let previousScroll = 0;
    let currentScroll;
    function headerScrollEvent()
    {
        currentScroll = window.scrollY;
        isHeaderHidden.value = currentScroll > previousScroll && currentScroll > headerHeight

        previousScroll = currentScroll;
    }
    function closeHeaderSidebar()
    {
        isHeaderSidebarDisplay.value = false
        unlockGlobalScroll();
    }
    function openHeaderSidebar()
    {
        isHeaderSidebarDisplay.value = true
        lockGlobalScroll()
    }
    function switchHeaderDisplay()
    {
        isHeaderFixed.value ? isHeaderFixed.value = false : isHeaderFixed.value = true
    }
    function switchDesktopSidebarDisplay()
    {
        isDesktopSidebarDisplay.value ? isDesktopSidebarDisplay.value = false : isDesktopSidebarDisplay.value = true
    }
    function switchLightTheme()
    {
        isLightTheme.value ? isLightTheme.value = false : isLightTheme.value = true
    }

    onMounted(() => document.addEventListener('scroll', headerScrollEvent))

    onUnmounted(() => document.removeEventListener('scroll', headerScrollEvent))
</script>

<template>

    <div class="h-[var(--header-height)]" id="header" :class="{'header-hidden': isHeaderHidden}">></div>

    <header class="h-[var(--header-height)] flex justify-center" :class="{'header-hidden': !isHeaderFixed && isHeaderHidden}">

        <div
            v-if="isDesktopSidebarDisplay"
            class="header-blur flex"
            :class="{'header-blur-on': isHeaderSidebarDisplay, 'header-blur-off': !isHeaderSidebarDisplay}">
            <aside
                class="left-header-sidebar flex flex-col"
                :class="{'header-sidebar-on': isHeaderSidebarDisplay, 'header-sidebar-off': !isHeaderSidebarDisplay}">

                <div class="left-header-sidebar-interaction flex justify-between items-center">
                    <div></div>
                    <a class="logo-wrap flex items-center" href="#">
                        <span class="logo"></span>
                    </a>
                    <button
                        class="closing-header-sidebar-button flex justify-center items-center"
                        :class="{'closing-button': !isHeaderSidebarDisplay, 'opening-button': isHeaderSidebarDisplay}"
                        @click="closeHeaderSidebar">
                        <span class="icon icon-cross"></span>
                    </button>
                </div>

                <div class="unit-title flex justify-center text-[1.1rem]">Действия</div>

                <a v-if="authStore.isAuthenticated" class="profile-link laminated-link flex items-center" href="#">
                    <span class="icon-outline flex justify-center items-center icon icon-border">
                        <img alt="" src="/images/users/content/funny-girl.png" style="height: 26px;">
                    </span>
                    <div class="flex flex-col">
                        <span class="title list-label-text text-sm">S3V3N1ce</span>
                        <span class="subtitle list-label-text text-[0.75rem]">Профиль</span>
                    </div>
                </a>

                <a v-if="authStore.isAuthenticated" class="naked-link flex items-center" href="#">
                    <span class="icon icon-bottle"></span>
                    <span class="list-label-text text-base">Контент-Студия</span>
                </a>

                <a v-if="authStore.isAuthenticated" class="naked-link flex items-center" href="#">
                    <span class="icon icon-download"></span>
                    <span class="list-label-text text-base">Создать Материал</span>
                </a>

                <a v-if="authStore.isAuthenticated" class="naked-link flex items-center" href="#">
                    <span class="icon icon-gear"></span>
                    <span class="list-label-text text-base">Настройки</span>
                </a>

                <button class="quick-settings-button button-link naked-link flex justify-between items-center" @click="switchHeaderDisplay">
                    <span v-if="isHeaderFixed" class="quick-settings-icon icon icon-fixed-header"></span>
                    <span v-if="!isHeaderFixed" class="quick-settings-icon icon icon-free-header"></span>
                    <span v-if="isHeaderFixed" class="list-label-text text-[0.7rem]">Фиксированная Шапка</span>
                    <span v-if="!isHeaderFixed" class="list-label-text text-sm">Свободная Шапка</span>
                    <span class="quick-settings-switcher icon icon-switcher-way flex justify-center items-center">
                        <span :class="{'off': !isHeaderFixed, 'on': isHeaderFixed}" class="handle icon icon-switcher-handle"></span>
                    </span>
                </button>

                <button class="quick-settings-button button-link naked-link flex justify-between items-center" @click="switchLightTheme">
                    <span v-if="isLightTheme" class="quick-settings-icon icon icon-sun"></span>
                    <span v-if="!isLightTheme" class="quick-settings-icon icon icon-moon"></span>
                    <span v-if="isLightTheme" class="list-label-text text-[1.1rem]">Светлая Тема</span>
                    <span v-if="!isLightTheme" class="list-label-text text-[1.1rem]">Тёмная Тема</span>
                    <span class="quick-settings-switcher icon icon-switcher-way flex justify-center items-center">
                        <span :class="{'off': isLightTheme, 'on': !isLightTheme}" class="handle icon icon-switcher-handle"></span>
                    </span>
                </button>

                <button v-if="!authStore.isAuthenticated" class="sign-in-button flex items-center" href="#">
                    <span class="icon icon-border-profile"></span>
                    <span class="list-label-text text-[1.1rem]">Войти</span>
                </button>

                <div class="unit-title flex justify-center text-[1.1rem]">Контент</div>

                <a class="naked-link flex items-center" href="#">
                    <span class="icon icon-book"></span>
                    <span class="list-label-text text-base">Каталог</span>
                </a>
                <a class="naked-link flex items-center" href="#">
                    <span class="icon icon-apple flex"></span>
                    <span class="list-label-text text-base">Аддон LD</span>
                </a>
                <a class="naked-link flex items-center" href="#">
                    <span class="icon icon-axolotl-bucket"></span>
                    <span class="list-label-text text-base">Ресурс-Паки</span>
                </a>
                <a class="naked-link flex items-center" href="#">
                    <span class="icon icon-spawn-egg"></span>
                    <span class="list-label-text text-base">Аддоны</span>
                </a>
                <a class="naked-link flex items-center" href="#">
                    <span class="icon icon-skin"></span>
                    <span class="list-label-text text-base">Скины</span>
                </a>
                <a class="naked-link flex items-center" href="#">
                    <span class="icon icon-map"></span>
                    <span class="list-label-text text-base">Карты</span>
                </a>
                <a class="naked-link flex items-center" href="#">
                    <span class="icon icon-script"></span>
                    <span class="list-label-text text-base">Статьи</span>
                </a>

                <div class="unit-title flex justify-center text-[1.1rem]">Материалы</div>

                <a class="naked-link flex items-center" href="#">
                    <span class="icon icon-bestiary"></span>
                    <span class="list-label-text text-sm">Бестиарий Light Diamond</span>
                </a>
                <a class="naked-link flex items-center" href="https://github.com/Mojang/bedrock-samples/releases">
                    <span class="icon icon-documentary"></span>
                    <span class="list-label-text text-sm">Документация Light Diamond</span>
                </a>
                <a class="naked-link flex items-center" href="https://learn.microsoft.com/en-us/minecraft/creator/reference/content/entityreference/examples/componentlist">
                    <span class="icon icon-microsoft-small"></span>
                    <span class="list-label-text text-sm">Документация Microsoft</span>
                </a>
                <a class="naked-link flex items-center" href="https://bedrock.dev/">
                    <span class="icon icon-bedrock-dev-small"></span>
                    <span class="list-label-text text-sm">Документация Bedrock.Dev</span>
                </a>
                <a class="naked-link flex items-center" href="https://wiki.bedrock.dev/">
                    <span class="icon icon-bedrock-wiki"></span>
                    <span class="list-label-text text-sm">Документация Bedrock Wiki</span>
                </a>
                <a class="naked-link flex items-center" href="https://github.com/Mojang/bedrock-samples/releases">
                    <span class="icon icon-minecraft-materials"></span>
                    <span class="list-label-text text-sm">Материалы Minecraft</span>
                </a>

                <div class="unit-title flex justify-center text-[1.1rem]">Ссылки</div>

                <a class="naked-link flex items-center" href="https://vk.com/light.diamond">
                    <span class="icon icon-diamond"></span>
                    <span class="list-label-text text-base">Группа VK</span>
                </a>
                <a class="naked-link flex items-center" href="https://www.youtube.com/@grostlight3303">
                    <span class="icon icon-eye"></span>
                    <span class="list-label-text text-base">YouTube</span>
                </a>
                <a class="naked-link flex items-center" href="#">
                    <span class="icon icon-hand"></span>
                    <span class="list-label-text text-base">Правила Пользования</span>
                </a>
                <a class="naked-link flex items-center" href="#">
                    <span class="icon icon-script"></span>
                    <span class="list-label-text text-sm">Политика Конфиденциальности</span>
                </a>

                <div class="unit-title flex justify-center text-[1.1rem]">Поддержка</div>

                <a class="naked-link flex items-center" href="#">
                    <span class="icon icon-diamond-sword"></span>
                    <span class="list-label-text text-[1.1rem]">Помощь</span>
                </a>
                <a class="naked-link flex items-center" href="#">
                    <span class="icon icon-diamond-pickaxe"></span>
                    <span class="list-label-text text-[1.1rem]">Гайды</span>
                </a>
                <a class="naked-link flex items-center" href="#">
                    <span class="icon icon-faq"></span>
                    <span class="list-label-text text-[1.1rem]">О Проекте</span>
                </a>

            </aside>
            <div class="close-header-sidebar-background" @click="closeHeaderSidebar"></div>
        </div>

        <nav class="header-wrap flex justify-between">
            <div class="flex items-center">

                <button
                    :class="{'on': isDesktopSidebarDisplay}"
                    class="opening-header-sidebar-button flex justify-center items-center"
                    @click="openHeaderSidebar"
                    type="button">
                    <span class="icon icon-units"></span>
                </button>

                <a class="logo-wrap flex items-center" href="#">
                    <span class="logo"></span>
                </a>

                <div class="header-dropdown inline-flex flex-col">
                    <button class="flex list-label items-center" type="button">
                        <span class="icon icon-down-arrow"></span>
                        <span class="list-label-text text-[1.1rem]">Контент</span>
                    </button>
                    <div class="header-dropdown-content">
                        <a class="flex items-center" href="#">
                            <span class="icon icon-book"></span>
                            <span class="list-label-text text-[1.1rem]">Каталог</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-apple flex"></span>
                            <span class="list-label-text text-[1.1rem]">Аддон LD</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-axolotl-bucket"></span>
                            <span class="list-label-text text-[1.1rem]">Ресурс-Паки</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-spawn-egg"></span>
                            <span class="list-label-text text-[1.1rem]">Аддоны</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-skin"></span>
                            <span class="list-label-text text-[1.1rem]">Скины</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-map"></span>
                            <span class="list-label-text text-[1.1rem]">Карты</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-script"></span>
                            <span class="list-label-text text-[1.1rem]">Статьи</span>
                        </a>
                    </div>
                </div>

                <div class="header-dropdown inline-flex flex-col">
                    <button class="flex list-label items-center" type="button">
                        <span class="icon icon-down-arrow"></span>
                        <span class="list-label-text text-[1.1rem]">Материалы</span>
                    </button>
                    <div class="header-dropdown-content">
                        <a class="flex items-center" href="#">
                            <span class="icon icon-bestiary"></span>
                            <span class="list-label-text text-[1.1rem]">Бестиарий Light Diamond</span>
                        </a>
                        <a class="flex items-center" href="https://github.com/Mojang/bedrock-samples/releases">
                            <span class="icon icon-documentary"></span>
                            <span class="list-label-text text-[1.1rem]">Документация Light Diamond</span>
                        </a>
                        <a class="flex items-center" href="https://learn.microsoft.com/en-us/minecraft/creator/reference/content/entityreference/examples/componentlist">
                            <span class="icon icon-microsoft-small"></span>
                            <span class="list-label-text text-[1.1rem]">Документация Microsoft</span>
                        </a>
                        <a class="flex items-center" href="https://bedrock.dev/">
                            <span class="icon icon-bedrock-dev-small"></span>
                            <span class="list-label-text text-[1.1rem]">Документация Bedrock.Dev</span>
                        </a>
                        <a class="flex items-center" href="https://wiki.bedrock.dev/">
                            <span class="icon icon-bedrock-wiki"></span>
                            <span class="list-label-text text-[1.1rem]">Документация Bedrock Wiki</span>
                        </a>
                        <a class="flex items-center" href="https://github.com/Mojang/bedrock-samples/releases">
                            <span class="icon icon-minecraft-materials"></span>
                            <span class="list-label-text text-[1.1rem]">Материалы Minecraft</span>
                        </a>
                    </div>
                </div>

                <div class="header-dropdown inline-flex flex-col">
                    <button class="flex list-label items-center" type="button">
                        <span class="icon icon-down-arrow"></span>
                        <span class="list-label-text text-[1.1rem]">Ссылки</span>
                    </button>
                    <div class="header-dropdown-content">
                        <a class="flex items-center" href="https://vk.com/light.diamond">
                            <span class="icon icon-diamond"></span>
                            <span class="list-label-text text-[1.1rem]">Группа VK</span>
                        </a>
                        <a class="flex items-center" href="https://www.youtube.com/@grostlight3303">
                            <span class="icon icon-eye"></span>
                            <span class="list-label-text text-[1.1rem]">YouTube</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-hand"></span>
                            <span class="list-label-text text-[1.1rem]">Правила Пользования</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-script"></span>
                            <span class="list-label-text text-base">Политика Конфиденциальности</span>
                        </a>
                    </div>
                </div>

                <div class="header-dropdown inline-flex flex-col">
                    <button class="flex list-label items-center" type="button">
                        <span class="icon icon-down-arrow"></span>
                        <span class="list-label-text text-[1.1rem]">Поддержка</span>
                    </button>
                    <div class="header-dropdown-content">
                        <a class="flex items-center" href="#">
                            <span class="icon icon-diamond-sword"></span>
                            <span class="list-label-text text-[1.1rem]">Помощь</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-diamond-pickaxe"></span>
                            <span class="list-label-text text-[1.1rem]">Гайды</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-faq"></span>
                            <span class="list-label-text text-[1.1rem]">О Проекте</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="flex">

                <form class="desktop-search search-content-form flex items-center">
                    <label class="flex items-center" for="search-content-input">
                        <input class="text-sm" id="search-content-input" placeholder="Поиск" type="text">
                    </label>
                    <button class="flex items-center" type="button">
                        <span class="icon icon-magnifier"></span>
                    </button>
                </form>

                <div class="mobile-search mobile-search-dropdown inline-flex flex-col">

                    <div class="mobile-search-button flex items-center" style="height: 100%;">
                        <button class="flex items-center" type="button">
                            <span class="icon icon-magnifier"></span>
                        </button>
                    </div>

                    <div class="mobile-search-dropdown-content flex justify-center">

                        <form class="search-content-form flex items-center">
                            <label class="flex items-center" for="search-content-input">
                                <input class="text-sm" id="search-content-input" placeholder="Поиск" type="text">
                            </label>
                            <button class="flex items-center" type="button">
                                <span class="icon icon-magnifier"></span>
                            </button>
                        </form>

                    </div>
                </div>

                <div class="profile-dropdown desktop-profile-dropdown inline-flex flex-col">

                    <div v-if="!authStore.isAuthenticated" class="flex items-center" style="height: 100%;">
                        <button class="sign-in flex list-label items-center" type="button">
                            <span class="icon icon-border-profile"></span>
                        </button>
                    </div>

                    <div v-if="authStore.isAuthenticated" class="flex items-center" style="height: 100%;">
                        <button class="flex list-label items-center" type="button">
                            <span class="profile-border flex justify-center items-center icon icon-border">
                                <img alt="" src="/images/users/content/funny-girl.png" style="height: 26px;">
                            </span>
                        </button>
                    </div>

                    <div class="profile-dropdown-content">

                        <a v-if="authStore.isAuthenticated" class="profile-link laminated-link flex items-center" href="#">
                            <span class="icon-outline flex justify-center items-center icon icon-border">
                                <img alt="" src="/images/users/content/funny-girl.png" style="height: 26px;">
                            </span>
                            <div class="flex flex-col">
                                <span class="title list-label-text text-sm">S3V3N1ce</span>
                                <span class="subtitle list-label-text text-[0.75rem]">Профиль</span>
                            </div>
                        </a>

                        <a v-if="authStore.isAuthenticated" class="naked-link flex items-center" href="#">
                            <span class="icon icon-bottle"></span>
                            <span class="list-label-text text-[1.1rem]">Контент-Студия</span>
                        </a>

                        <a v-if="authStore.isAuthenticated" class="naked-link flex items-center" href="#">
                            <span class="icon icon-download"></span>
                            <span class="list-label-text text-[1.1rem]">Создать Материал</span>
                        </a>

                        <a v-if="authStore.isAuthenticated" class="naked-link flex items-center" href="#">
                            <span class="icon icon-gear"></span>
                            <span class="list-label-text text-[1.1rem]">Настройки</span>
                        </a>

                        <button class="quick-settings-button button-link naked-link flex justify-between items-center" @click="switchHeaderDisplay">
                            <span v-if="isHeaderFixed" class="quick-settings-icon icon icon-fixed-header"></span>
                            <span v-if="!isHeaderFixed" class="quick-settings-icon icon icon-free-header"></span>
                            <span v-if="isHeaderFixed" class="list-label-text text-sm">Фиксированная Шапка</span>
                            <span v-if="!isHeaderFixed" class="list-label-text text-sm">Свободная Шапка</span>
                            <span class="quick-settings-switcher icon icon-switcher-way flex justify-center items-center">
                                <span :class="{'off': !isHeaderFixed, 'on': isHeaderFixed}" class="handle icon icon-switcher-handle"></span>
                            </span>
                        </button>

                        <div class="line flex self-center"></div>

                        <button class="quick-settings-button button-link naked-link flex justify-between items-center" @click="switchDesktopSidebarDisplay">
                            <span v-if="isDesktopSidebarDisplay" class="quick-settings-icon icon icon-units"></span>
                            <span v-if="!isDesktopSidebarDisplay" class="quick-settings-icon icon icon-cross"></span>
                            <span v-if="isDesktopSidebarDisplay" class="list-label-text text-[1.1rem]">Боковое Меню</span>
                            <span v-if="!isDesktopSidebarDisplay" class="list-label-text text-sm">Без Бокового Меню</span>
                            <span class="quick-settings-switcher icon icon-switcher-way flex justify-center items-center">
                                <span :class="{'off': !isDesktopSidebarDisplay, 'on': isDesktopSidebarDisplay}" class="handle icon icon-switcher-handle"></span>
                            </span>
                        </button>

                        <div class="line flex self-center"></div>

                        <button class="quick-settings-button button-link naked-link flex justify-between items-center" @click="switchLightTheme">
                            <span v-if="isLightTheme" class="quick-settings-icon icon icon-sun"></span>
                            <span v-if="!isLightTheme" class="quick-settings-icon icon icon-moon"></span>
                            <span v-if="isLightTheme" class="list-label-text text-[1.1rem]">Светлая Тема</span>
                            <span v-if="!isLightTheme" class="list-label-text text-[1.1rem]">Тёмная Тема</span>
                            <span class="quick-settings-switcher icon icon-switcher-way flex justify-center items-center">
                                <span :class="{'off': isLightTheme, 'on': !isLightTheme}" class="handle icon icon-switcher-handle"></span>
                            </span>
                        </button>

                        <div class="line flex self-center"></div>

                        <button v-if="!authStore.isAuthenticated" class="button-link naked-link flex items-center">
                            <span class="icon icon-diamond"></span>
                            <span class="list-label-text text-[1.1rem]">Войти</span>
                        </button>

                        <a v-if="authStore.isAuthenticated" class="button-link naked-link flex items-center" href="#">
                            <span class="icon icon-left-arrow"></span>
                            <span class="list-label-text text-[1.1rem]">Выйти</span>
                        </a>
                    </div>
                </div>

            </div>
        </nav>

    </header>
</template>

<style scoped>

/* =============== [ Структура ] =============== */

header {
    background-color: var(--primary-bg-color);
    user-select: none;
    position: fixed;
    transition: .3s;
    width: 100%;
    top: 0;
}
.header-hidden { transform: translateY(-100%); }
.header-wrap {
    height: var(--header-height);
    max-width: 1200px;
    width: 100%;
}
.header-blur {
    position: absolute;
    transition: 1s;
    height: 500vw;
    width: 100%;
    z-index: 1;
}
header .header-blur aside {
    background-color: var(--primary-bg-color);
    position: relative;
    overflow-y: scroll;
    max-width: 320px;
    transition: .5s;
    height: 100vh;
}
header .close-header-sidebar-background {
    width: calc(100% - 320px);
    height: 100%;
}
.left-header-sidebar-interaction,
.right-header-sidebar-interaction {
    height: var(--header-height);
    width: 100%;
}
.closing-header-sidebar-button,
.opening-header-sidebar-button {
    height: 48px;
    width: 48px;
}
.left-header-sidebar .left-header-sidebar-interaction,
.left-header-sidebar .profile-link,
.left-header-sidebar .sign-in-button,
.left-header-sidebar .naked-link,
.right-header-sidebar-interaction,
.right-header-sidebar .profile-link,
.right-header-sidebar .sign-in-button,
.right-header-sidebar .naked-link {
    min-height: 72px;
}
.left-header-sidebar .profile-link,
.right-header-sidebar .profile-link {
    min-height: 56px;
}
.left-header-sidebar .sign-in-button .icon,
.right-header-sidebar .sign-in-button .icon {
    margin: 0 6px 0 14px;
    height: 42px;
    width: 42px;
}
.closing-header-sidebar-button { margin-right: 16px; }
.logo {
    background-image: url('/images/logo.png');
    background-size: 100% 100%;
    height: 48px;
    width: 162px;
}
.header-wrap .logo-wrap {
    margin: 0 12px 0 8px;
    height: 100%;
}
header .icon,
.header-dropdown .icon {
    height: 32px;
    width: 32px;
}
.header-dropdown .icon { margin-left: 4px; }
header .list-label {
    cursor: pointer;
    height: 100%;
}
header .list-label-text,
.header-dropdown-content .list-label-text {
    color: var(--primary-text-color);
    transition: .2s;
    padding: 4px;
}
header .list-label:focus-visible .list-label-text, header .list-label:hover .list-label-text,
.header-dropdown-content a:focus-visible .list-label-text, .header-dropdown-content a:hover .list-label-text,

.left-header-sidebar .naked-link:focus-visible .list-label-text, .left-header-sidebar .naked-link:hover .list-label-text,
.left-header-sidebar .profile-link:focus-visible .list-label-text, .left-header-sidebar .profile-link:hover .list-label-text,
.left-header-sidebar .sign-in-button:focus-visible .list-label-text, .left-header-sidebar .sign-in-button:hover .list-label-text,

.right-header-sidebar .naked-link:focus-visible .list-label-text, .right-header-sidebar .naked-link:hover .list-label-text,
.right-header-sidebar .profile-link:focus-visible .list-label-text, .right-header-sidebar .profile-link:hover .list-label-text,
.right-header-sidebar .sign-in-button:focus-visible .list-label-text, .right-header-sidebar .sign-in-button:hover .list-label-text,

.profile-dropdown-content a:focus-visible .list-label-text, .profile-dropdown-content a:hover .list-label-text,
.profile-dropdown-content .button-link:focus-visible .list-label-text, .profile-dropdown-content .button-link:hover .list-label-text {
    color: var(--hover-text-color);
}
button.list-label .icon { transition: .2s; }
button.list-label:focus-visible .icon, button.list-label:hover .icon,
.header-dropdown-content a:focus-visible .icon, .header-dropdown-content a:hover .icon,
.left-header-sidebar .naked-link:focus-visible .icon, .left-header-sidebar .naked-link:hover .icon,
.right-header-sidebar .naked-link:focus-visible .icon, .right-header-sidebar .naked-link:hover .icon,
.profile-dropdown-content .naked-link:focus-visible .icon, .profile-dropdown-content .naked-link:hover .icon,
.search-content-form button:focus-visible, .search-content-form button:hover {
    animation: icon-trigger-up-animation .3s ease;
}
.header-dropdown,
.mobile-search-dropdown,
.profile-dropdown {
    position: relative;
    height: 100%;
}
.mobile-search-button button {
    height: 48px;
    width: 48px;
}
.profile-dropdown button {
    margin-right: 8px;
    height: 48px;
}
.profile-dropdown button .profile-border {
    height: 42px;
    width: 42px;
    margin: 0;
}
.profile-dropdown .sign-in { margin-left: -4px; }
.profile-dropdown .sign-in .icon {
    height: 42px;
    width: 42px;
}
.header-dropdown-content,
.mobile-search-dropdown-content,
.profile-dropdown-content {
    display: none;
    box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
    background-color: var(--primary-bg-color);
    position: absolute;
    transition: 1s;
    opacity: 1;
    top: 72px;
}
.header-dropdown-content { width: 332px; }
.mobile-search-dropdown-content {
    height: 56px;
    padding: 8px;
}
.profile-dropdown-content { width: 320px; }
.header-dropdown-content { left: 0; }
.mobile-search-dropdown-content { right: 0; }
.profile-dropdown-content { right: 0; }
.header-dropdown-content a,
.profile-dropdown-content a,
.profile-dropdown-content .button-link,
.left-header-sidebar .naked-link,
.right-header-sidebar .naked-link {
    height: 72px;
}
.profile-dropdown-content .button-link { margin-right: 0; }
.header-dropdown-content .icon,
.profile-dropdown-content .icon {
    margin: 0 4px 0 12px;
    height: 32px;
    width: 32px;
}
.profile-dropdown-content .quick-settings-button .icon {
    margin: 0;
}
.header-dropdown:active .header-dropdown-content,
.header-dropdown:hover .header-dropdown-content,
.header-dropdown:focus .header-dropdown-content,

.mobile-search-dropdown:active .mobile-search-dropdown-content,
.mobile-search-dropdown:hover .mobile-search-dropdown-content,
.mobile-search-dropdown:focus .mobile-search-dropdown-content,

.profile-dropdown:active .profile-dropdown-content,
.profile-dropdown:hover .profile-dropdown-content,
.profile-dropdown:focus .profile-dropdown-content {
    display: flex;
    animation: smooth-appear-animation .5s ease;
    flex-direction: column;
    opacity: 1;
}
.mobile-search-dropdown:active .mobile-search-button,
.mobile-search-dropdown:hover .mobile-search-button,
.mobile-search-dropdown:focus .mobile-search-button {
    opacity: 0;
}
.left-header-sidebar .profile-link,
.right-header-sidebar .profile-link,
.profile-dropdown-content .profile-link,
.profile-dropdown-content .ld-pay-link {
    background-size: 400% 100%;
    height: 56px;
    margin: 8px;
}
.left-header-sidebar .profile-link .icon-outline,
.profile-dropdown-content .profile-link .icon-outline {
    height: 42px;
    width: 42px;
}
.left-header-sidebar .profile-link .icon-outline { margin: 0 6px 0 6px; }
.left-header-sidebar .profile-link .title,
.left-header-sidebar .profile-link .subtitle,
.right-header-sidebar .profile-link .title,
.right-header-sidebar .profile-link .subtitle,
.profile-dropdown-content .profile-link .title,
.profile-dropdown-content .profile-link .subtitle,
.profile-dropdown-content .ld-pay-link .title,
.profile-dropdown-content .ld-pay-link .subtitle {
    margin-left: 4px;
    padding: 0;
}
.left-header-sidebar .profile-link .subtitle,
.right-header-sidebar .profile-link .subtitle,
.profile-dropdown-content .profile-link .subtitle,
.profile-dropdown-content .ld-pay-link .subtitle { opacity: .7; }
.profile-dropdown-content .ld-pay-link .icon-diamond {
    height: 42px;
    width: 42px;
}
.left-header-sidebar .unit-title,
.right-header-sidebar .unit-title {
    color: var(--primary-text-color);
    background-size: 400% 100%;
    padding: 4px 0;
}
.left-header-sidebar .naked-link .icon,
.right-header-sidebar .naked-link .icon {
    margin: 0 8px 0 14px;
}
.left-header-sidebar .naked-link .list-label-text,
.right-header-sidebar .naked-link .list-label-text {
    line-height: 1.2;
}
.search-content-form label { height: 48px; }
.search-content-form label input {
    color: var(--primary-text-color);
    height: 40px;
    width: 200px;
}
.search-content-form button .icon { margin: 6px; }
.profile-dropdown-content .naked-link .icon { margin-left: 20px; }
.profile-dropdown-content .line {
    opacity: .2;
    height: 1px;
    width: 90%;
}
.profile-dropdown-content .quick-settings-button .quick-settings-switcher {
    margin-right: 20px;
    margin-left: 0;
}
.quick-settings-button .quick-settings-switcher .handle {
    position: relative;
    transition: .5s;
    margin: 0;
}
.quick-settings-button .quick-settings-switcher .handle.on { transform: translateX(40%); }
.quick-settings-button .quick-settings-switcher .handle.off { transform: translateX(-40%); }
.quick-settings-button:focus-visible .quick-settings-switcher, .quick-settings-button:hover .quick-settings-switcher,
.quick-settings-button:focus-visible .quick-settings-switcher .handle, .quick-settings-button:hover .quick-settings-switcher .handle {
    animation: none;
}

/* =============== [ Медиа-Запрос { 1280px > ?px } ] =============== */

@media screen and (min-width: 1280px)
{
    .opening-header-sidebar-button { display: none; }
    .opening-header-sidebar-button.on { display: flex; }
}

/* =============== [ Медиа-Запрос { ?px < 1280px } ] =============== */

@media screen and (max-width: 1279px)
{
    .header-dropdown { display: none; }
    .opening-header-sidebar-button { display: flex; }
    .desktop-profile-dropdown { display: none; }
}

/* =============== [ Медиа-Запрос { 500px > ?px } ] =============== */

@media screen and (min-width: 500px)
{
    .mobile-search { display: none; }
}

/* =============== [ Медиа-Запрос { ?px < 500px } ] =============== */

@media screen and (max-width: 499px)
{
    .desktop-search { display: none; }
}



.green { background-color: green; }
.orange { background-color: orange; }
.yellowgreen { background-color: yellowgreen; }






/*


backdrop-filter: blur(2px);
.blurred-background-appear { background-color: rgba(0, 0, 0, .5); }



.header-profile-dropdown {
    display: inline-flex;
    position: relative;
}
.header-profile-dropdown-button {
    display: flex;
    align-items: center;
    padding: 5px 10px;
    color: #ffffff;
}
.header-profile-dropdown-button:hover span { color: #00ffff; }
.header-profile-dropdown-content a:hover .header-profile-span { color: #00ffff; }
.header-profile-dropdown-button .border, .header-profile-link .border { margin-right: 8px; }

.header-profile-dropdown-content {
    display: none;
    background-image: url('/images/elements/base-background.png');
    box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
    background-color: #005a6a;
    position: absolute;
    transition: 1s;
    width: 300px;
    opacity: 0;
    top: 72px;
    right: 0;
}
.header-dropdown-content a, .header-profile-dropdown-content a { height: 72px; }
.header-logout-form button {
    display: flex;
    align-items: center;
    transition: .2s;
    height: 72px;
    width: 100%;
}
.header-dropdown-content a .icon,
.header-profile-dropdown-content a .icon,
.header-logout-form button .icon {
    margin-left: 20px;
    margin-right: 8px;
    height: 40px;
}
.header-logout-form button:hover .icon, .header-logout-form button:focus-visible .icon {
    animation: icon-trigger-up-animation .3s ease;
}
.header-profile-dropdown-content .header-logout-form button:hover span { color: #00ffff; }























.header-profile-link {
    background: linear-gradient(90deg, rgba(0, 255, 255, .1), rgba(0, 255, 255, .3), rgba(0, 255, 255, .1), rgba(0, 255, 255, .3), rgba(0, 255, 255, .1));
    animation: transfusion-animation 10s linear infinite;
    border: 2px solid rgba(0, 255, 255, .3);
    background-size: 400% 100%;
    padding: 4px;
    margin: 12px;
}

.header-profile-link:hover { background-color: #00ffff; }
.header-profile-link .border {
    background-image: url('/images/items/diamond-border.png');
    background-size: 100% 100%;
    min-width: 48px;
    height: 48px;
}
.header-ld-pay-link {
    background: linear-gradient(90deg, rgba(0, 200, 0, .1), rgba(0, 200, 0, .3), rgba(0, 200, 0, .1), rgba(0, 200, 0, .3), rgba(0, 200, 0, .1));
    animation: transfusion-animation 10s linear infinite;
    border: 2px solid rgba(0, 200, 0, .3);
    background-size: 400% 100%;
    padding: 4px;
    margin: 12px;
}
.header-ld-pay-link:hover { background-color: #00ca00; }










.header-profile-link .border .avatar { height: 26px; }
.header-profile-link .meta, .header-ld-pay-link .meta { display: flex; flex-direction: column; }
.header-profile-dropdown-content .header-profile-link .meta span, .header-profile-dropdown-content .header-ld-pay-link .meta span { line-height: 1.5; }
.header-profile-link .meta .name, .header-ld-pay-link .meta .name { font-size: 16px; color: #00ffff; }
.header-profile-link .meta .id, .header-ld-pay-link .meta .ld-pay  {
    font-size: 12px;
    color: #dadada;
}
.header-profile-dropdown-content a:hover, .header-logout-form button:hover { background-color: rgba(0, 255, 255, .1) }
.header-profile-dropdown:active .header-profile-dropdown-content,
.header-profile-dropdown:hover .header-profile-dropdown-content,
.header-profile-dropdown:focus .header-profile-dropdown-content,
.header-profile-dropdown:focus-visible .header-profile-dropdown-content,
.header-profile-dropdown-button:active .header-profile-dropdown-content,
.header-profile-dropdown-button:hover .header-profile-dropdown-content,
.header-profile-dropdown-button:focus .header-profile-dropdown-content,
.header-profile-dropdown-button:focus-visible .header-profile-dropdown-content {
    display: block;
    animation: smooth-appear-animation .5s ease;
    opacity: 1;
}
.header-profile-dropdown:active .header-profile-dropdown-button,
.header-profile-dropdown:hover .header-profile-dropdown-button,
.header-profile-dropdown:focus .header-profile-dropdown-button,
.header-profile-dropdown:focus-visible .header-profile-dropdown-button {
    color: #00ffff;
}
.header-profile-dropdown:hover .header-profile-dropdown-button .border,
.header-profile-dropdown:focus-visible .header-profile-dropdown-button .border,
.header-profile-dropdown-content a:hover .icon,
#header-sliding-menu .units a:hover .icon {
    animation: icon-trigger-up-animation .3s ease;
}
.header-dropdown {
    display: inline-flex;
    position: relative;
}
.header-dropdown-button {
    display: flex;
    align-items: center;
    padding: 5px 10px;
    color: #ffffff;
}
.header-dropdown-button span {
    font-family: "Minecraft Ten", sans-serif;
    font-size: 20px;
}
.header-dropdown-button .icon { height: 40px; margin-right: 8px; }
.header-dropdown-button:hover span { color: #00ffff; }
.header-dropdown-button:hover .icon, .header-dropdown-button:focus-visible .icon {
    animation: icon-trigger-up-animation .3s ease;
}
.header-dropdown-button .border { margin-right: 8px; }
.header-dropdown-content {
    display: none;
    background-image: url('/images/elements/base-background.png');
    box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
    background-color: #005a6a;
    position: absolute;
    transition: 1s;
    width: 300px;
    opacity: 1;
    top: 72px;
    left: 0;
}
.header-dropdown-content a span { line-height: 1.5; }
.header-dropdown-content a:hover { background-color: rgba(0, 255, 255, .1) }
.header-dropdown:active .header-dropdown-content,
.header-dropdown:hover .header-dropdown-content,
.header-dropdown:focus .header-dropdown-content,
.header-dropdown:focus-visible .header-dropdown-content,
.header-dropdown-button:active .header-dropdown-content,
.header-dropdown-button:hover .header-dropdown-content,
.header-dropdown-button:focus .header-dropdown-content,
.header-dropdown-button:focus-visible .header-dropdown-content {
    display: block;
    animation: smooth-appear-animation .5s ease;
    opacity: 1;
}
.header-dropdown:active .header-dropdown-button,
.header-dropdown:hover .header-dropdown-button,
.header-dropdown:focus .header-dropdown-button,
.header-dropdown:focus-visible .header-dropdown-button {
    color: #00ffff;
}
.header-dropdown:hover .header-dropdown-button .border,
.header-dropdown:focus-visible .header-dropdown-button .border,
.header-profile-dropdown-button:hover .border {
    animation: icon-trigger-up-animation .3s ease;
}
.smooth-disappear-animation { animation: smooth-disappear-animation .3s ease }
.smooth-appear-animation { animation: smooth-appear-animation .3s ease; }
.rotate-right-360-animation { animation: rotate-right-360-animation .5s ease; }
.rotate-left-360-animation { animation: rotate-left-360-animation .5s ease; }
.rotate-left-360-animation:focus-visible, .rotate-right-360-animation:focus-visible { border-radius: 50%; }
#header-sliding-menu .units {
    display: flex;
    flex-direction: column;
    width: 100%;
}
#header-sliding-menu .units a:hover, #header-sliding-menu .units a:focus-visible {
    background-color: rgba(255, 255, 255, .15);
    color: #00ffff;
}













footer {
    display: flex;
    background-image: url('/images/elements/base-background.png');
    flex-direction: column;
    align-items: center;
    user-select: none;
    width: 100%;
}
.footer-general-menu {
    display: flex;
    justify-content: space-between;
    max-width: 1200px;
    width: 100%;
}
.footer-general-menu a { padding: 5px 10px; }
.footer-general-menu a .logo {
    height: 50px;
    margin: 4px;
}





 =============== [ Анимации Элементов ] ===============
@keyframes footer-anchor-icon-animation
{
    0% { margin-top: -10px; }
    50% { margin-top: 10px; }
    100% { margin-top: -10px; }
}
@keyframes footer-link-icon-animation
{
    0% { margin-top: 0; }
    50% { margin-top: -10px; }
    100% { margin-top: 0; }
}





*/
</style>
