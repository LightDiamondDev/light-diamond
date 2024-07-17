<script setup lang='ts'>
    import {useAuthStore} from '@/stores/auth'
    import useDesignManager from '@/design-manager'
    import {useRouter} from 'vue-router'
    import {useGlobalModalStore} from '@/stores/global-modal'
    import {usePostCategoryStore} from '@/stores/postCategory'
    import {getCssVariableValue, lockGlobalScroll, remToPixels, unlockGlobalScroll} from '@/helpers'
    import {onMounted, onUnmounted, ref} from 'vue'

    const router = useRouter()
    const authStore = useAuthStore()
    const categoryStore = usePostCategoryStore()
    const globalModalStore = useGlobalModalStore()
    const designManager = useDesignManager()

    const headerHeight = remToPixels(getCssVariableValue('--header-height'))

    const isHeaderSidebar = ref(false)
    const isHeaderHidden = ref(false)
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
        isHeaderSidebar.value = false
        unlockGlobalScroll();
    }
    function openHeaderSidebar()
    {
        isHeaderSidebar.value = true
        lockGlobalScroll()
    }
    function switchLightTheme()
    {
        isLightTheme.value = !isLightTheme.value
    }

    onMounted(() => document.addEventListener('scroll', headerScrollEvent))

    onUnmounted(() => document.removeEventListener('scroll', headerScrollEvent))
</script>

<template>

    <div
        class="header-blur flex visible"
        :class="{'header-blur-on': isHeaderSidebar, 'header-blur-off': !isHeaderSidebar}">
        <aside
            class="left-header-sidebar flex flex-col"
            :class="{'xl:hidden': !designManager.isDesktopSidebarVisible(),
            'translate-x-0': isHeaderSidebar,
            '-translate-x-full': !isHeaderSidebar,
            'fixed': designManager.isHeaderFixedVisible()}">
            <div class="left-header-sidebar-interaction flex justify-between items-center">
                <div></div>
                <a class="logo-wrap flex items-center" href="#">
                    <span class="logo"></span>
                </a>
                <button
                    class="closing-header-sidebar-button flex justify-center items-center"
                    :class="{'closing-button': !isHeaderSidebar, 'opening-button': isHeaderSidebar}"
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
                <span class="list-label-text">Контент-Студия</span>
            </a>

            <a v-if="authStore.isAuthenticated" class="naked-link flex items-center" href="#">
                <span class="icon icon-download"></span>
                <span class="list-label-text">Создать Материал</span>
            </a>

            <a v-if="authStore.isAuthenticated" class="naked-link flex items-center" href="#">
                <span class="icon icon-gear"></span>
                <span class="list-label-text">Настройки</span>
            </a>

            <button class="quick-settings-button button-link naked-link flex justify-between items-center" @click="designManager.switchHeaderFixed()">
                <span v-if="designManager.isHeaderFixedVisible()" class="quick-settings-icon icon icon-fixed-header"></span>
                <span v-if="!designManager.isHeaderFixedVisible()" class="quick-settings-icon icon icon-free-header"></span>
                <span class="list-label-text text-sm">Свободная Шапка</span>
                <span class="quick-settings-switcher icon icon-switcher-way flex justify-center items-center">
                    <span :class="{'off': designManager.isHeaderFixedVisible(), 'on': !designManager.isHeaderFixedVisible()}" class="handle icon icon-switcher-handle"></span>
                </span>
            </button>

            <button class="quick-settings-button button-link naked-link flex justify-between items-center" @click="switchLightTheme">
                <span v-if="isLightTheme" class="quick-settings-icon icon icon-sun"></span>
                <span v-if="!isLightTheme" class="quick-settings-icon icon icon-moon"></span>
                <span class="list-label-text text-[1.1rem]">Тёмная Тема</span>
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
                <span class="list-label-text">Каталог</span>
            </a>
            <a class="naked-link flex items-center" href="#">
                <span class="icon icon-apple flex"></span>
                <span class="list-label-text">Аддон LD</span>
            </a>
            <a class="naked-link flex items-center" href="#">
                <span class="icon icon-axolotl-bucket"></span>
                <span class="list-label-text">Ресурс-Паки</span>
            </a>
            <a class="naked-link flex items-center" href="#">
                <span class="icon icon-spawn-egg"></span>
                <span class="list-label-text">Аддоны</span>
            </a>
            <a class="naked-link flex items-center" href="#">
                <span class="icon icon-skin"></span>
                <span class="list-label-text">Скины</span>
            </a>
            <a class="naked-link flex items-center" href="#">
                <span class="icon icon-map"></span>
                <span class="list-label-text">Карты</span>
            </a>
            <a class="naked-link flex items-center" href="#">
                <span class="icon icon-script"></span>
                <span class="list-label-text">Статьи</span>
            </a>

            <div class="unit-title flex justify-center text-[1.1rem]">Материалы</div>

            <a class="naked-link flex items-center" href="#">
                <span class="icon icon-bestiary"></span>
                <span class="list-label-text small">Бестиарий Light Diamond</span>
            </a>
            <a class="naked-link flex items-center" href="https://github.com/Mojang/bedrock-samples/releases">
                <span class="icon icon-documentary"></span>
                <span class="list-label-text small">Документация Light Diamond</span>
            </a>
            <a class="naked-link flex items-center" href="https://learn.microsoft.com/en-us/minecraft/creator/reference/content/entityreference/examples/componentlist">
                <span class="icon icon-microsoft-small"></span>
                <span class="list-label-text small">Документация Microsoft</span>
            </a>
            <a class="naked-link flex items-center" href="https://bedrock.dev/">
                <span class="icon icon-bedrock-dev-small"></span>
                <span class="list-label-text small">Документация Bedrock.Dev</span>
            </a>
            <a class="naked-link flex items-center" href="https://wiki.bedrock.dev/">
                <span class="icon icon-bedrock-wiki"></span>
                <span class="list-label-text small">Документация Bedrock Wiki</span>
            </a>
            <a class="naked-link flex items-center" href="https://github.com/Mojang/bedrock-samples/releases">
                <span class="icon icon-minecraft-materials"></span>
                <span class="list-label-text small">Материалы Minecraft</span>
            </a>

            <div class="unit-title flex justify-center text-[1.1rem]">Ссылки</div>

            <a class="naked-link flex items-center" href="https://vk.com/light.diamond">
                <span class="icon icon-vk"></span>
                <span class="list-label-text text-base">ВКонтакте</span>
            </a>
            <a class="naked-link flex items-center" href="https://t.me/light_diamond_channel">
                <span class="icon icon-telegram"></span>
                <span class="list-label-text text-base">Телеграм</span>
            </a>
            <a class="naked-link flex items-center" href="https://www.youtube.com/@grostlight3303">
                <span class="icon icon-youtube"></span>
                <span class="list-label-text text-base">YouTube</span>
            </a>
            <a class="naked-link flex items-center" href="#">
                <span class="icon icon-diamond"></span>
                <span class="list-label-text text-base">Обновления</span>
            </a>
            <a class="naked-link flex items-center" href="#">
                <span class="icon icon-hand"></span>
                <span class="list-label-text text-base">Правила Пользования</span>
            </a>
            <a class="naked-link flex items-center" href="#">
                <span class="icon icon-script"></span>
                <span class="list-label-text policy">Политика Конфиденциальности</span>
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

    <div class="h-[var(--header-height)]" id="header" :class="{'header-hidden': isHeaderHidden}">></div>

    <header class="h-[var(--header-height)] flex justify-center" :class="{'header-hidden': !designManager.isHeaderFixedVisible() && isHeaderHidden}">

        <nav class="header-wrap flex justify-between">
            <div class="div flex items-center">

                <button
                    :class="{'xl:hidden': !designManager.isDesktopSidebarVisible(), 'sm:opacity-0': isHeaderSidebar}"
                    class="opening-header-sidebar-button opacity-100 duration-1 flex justify-center items-center"
                    @click="openHeaderSidebar"
                    type="button">
                    <span class="icon icon-units"></span>
                </button>

                <a
                    :class="{'opacity-0': isHeaderSidebar}"
                    class="logo-wrap flex items-center duration-200"
                    href="#">
                    <span class="logo"></span>
                </a>

                <div class="header-dropdown inline-flex flex-col">
                    <button class="flex list-label items-center" type="button">
                        <span class="icon icon-down-arrow"></span>
                        <span class="list-label-text">Контент</span>
                    </button>
                    <div class="header-dropdown-content">
                        <a class="flex items-center" href="#">
                            <span class="icon icon-book"></span>
                            <span class="list-label-text">Каталог</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-apple flex"></span>
                            <span class="list-label-text">Аддон LD</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-axolotl-bucket"></span>
                            <span class="list-label-text">Ресурс-Паки</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-spawn-egg"></span>
                            <span class="list-label-text">Аддоны</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-skin"></span>
                            <span class="list-label-text">Скины</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-map"></span>
                            <span class="list-label-text">Карты</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-script"></span>
                            <span class="list-label-text">Статьи</span>
                        </a>
                    </div>
                </div>

                <div class="header-dropdown inline-flex flex-col">
                    <button class="flex list-label items-center" type="button">
                        <span class="icon icon-down-arrow"></span>
                        <span class="list-label-text">Материалы</span>
                    </button>
                    <div class="header-dropdown-content">
                        <a class="flex items-center" href="#">
                            <span class="icon icon-bestiary"></span>
                            <span class="list-label-text small">Бестиарий Light Diamond</span>
                        </a>
                        <a class="flex items-center" href="https://github.com/Mojang/bedrock-samples/releases">
                            <span class="icon icon-documentary"></span>
                            <span class="list-label-text small">Документация Light Diamond</span>
                        </a>
                        <a class="flex items-center" href="https://learn.microsoft.com/en-us/minecraft/creator/reference/content/entityreference/examples/componentlist">
                            <span class="icon icon-microsoft-small"></span>
                            <span class="list-label-text small">Документация Microsoft</span>
                        </a>
                        <a class="flex items-center" href="https://bedrock.dev/">
                            <span class="icon icon-bedrock-dev-small"></span>
                            <span class="list-label-text small">Документация Bedrock.Dev</span>
                        </a>
                        <a class="flex items-center" href="https://wiki.bedrock.dev/">
                            <span class="icon icon-bedrock-wiki"></span>
                            <span class="list-label-text small">Документация Bedrock Wiki</span>
                        </a>
                        <a class="flex items-center" href="https://github.com/Mojang/bedrock-samples/releases">
                            <span class="icon icon-minecraft-materials"></span>
                            <span class="list-label-text small">Материалы Minecraft</span>
                        </a>
                    </div>
                </div>

                <div class="header-dropdown inline-flex flex-col">
                    <button class="flex list-label items-center" type="button">
                        <span class="icon icon-down-arrow"></span>
                        <span class="list-label-text">Ссылки</span>
                    </button>
                    <div class="header-dropdown-content">
                        <a class="flex items-center" href="https://vk.com/light.diamond">
                            <span class="icon icon-vk"></span>
                            <span class="list-label-text">ВКонтакте</span>
                        </a>
                        <a class="flex items-center" href="https://t.me/light_diamond_channel">
                            <span class="icon icon-telegram"></span>
                            <span class="list-label-text">Телеграм</span>
                        </a>
                        <a class="flex items-center" href="https://www.youtube.com/@grostlight3303">
                            <span class="icon icon-youtube"></span>
                            <span class="list-label-text">YouTube</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-diamond"></span>
                            <span class="list-label-text">Обновления</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-hand"></span>
                            <span class="list-label-text">Правила Пользования</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-script"></span>
                            <span class="list-label-text policy">Политика Конфиденциальности</span>
                        </a>
                    </div>
                </div>

                <div class="header-dropdown inline-flex flex-col">
                    <button class="flex list-label items-center" type="button">
                        <span class="icon icon-down-arrow"></span>
                        <span class="list-label-text">Поддержка</span>
                    </button>
                    <div class="header-dropdown-content">
                        <a class="flex items-center" href="#">
                            <span class="icon icon-diamond-sword"></span>
                            <span class="list-label-text">Помощь</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-diamond-pickaxe"></span>
                            <span class="list-label-text">Гайды</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-faq"></span>
                            <span class="list-label-text">О Проекте</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="flex">

                <div class="search search-dropdown inline-flex flex-col">

                    <div class="search-button flex items-center" style="height: 100%;">
                        <button class="flex items-center" type="button">
                            <span class="icon icon-magnifier"></span>
                        </button>
                    </div>

                    <div class="search-dropdown-content flex flex-col items-center">

                        <form class="search-content-form flex justify-center items-center">
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
                            <span class="list-label-text">Контент-Студия</span>
                        </a>

                        <a v-if="authStore.isAuthenticated" class="naked-link flex items-center" href="#">
                            <span class="icon icon-download"></span>
                            <span class="list-label-text">Создать Материал</span>
                        </a>

                        <a v-if="authStore.isAuthenticated" class="naked-link flex items-center" href="#">
                            <span class="icon icon-gear"></span>
                            <span class="list-label-text">Настройки</span>
                        </a>

                        <button class="quick-settings-button button-link naked-link flex justify-between items-center" @click="designManager.switchHeaderFixed()">
                            <span v-if="designManager.isHeaderFixedVisible()" class="quick-settings-icon icon icon-fixed-header"></span>
                            <span v-if="!designManager.isHeaderFixedVisible()" class="quick-settings-icon icon icon-free-header"></span>
                            <span class="list-label-text text-sm">Свободная Шапка</span>
                            <span class="quick-settings-switcher icon icon-switcher-way flex justify-center items-center">
                                <span :class="{'off': designManager.isHeaderFixedVisible(), 'on': !designManager.isHeaderFixedVisible()}" class="handle icon icon-switcher-handle"></span>
                            </span>
                        </button>

                        <div class="line flex self-center"></div>

                        <button class="quick-settings-button button-link naked-link flex justify-between items-center" @click="designManager.switchDesktopSidebar()">
                            <span v-if="designManager.isDesktopSidebarVisible()" class="quick-settings-icon icon icon-units"></span>
                            <span v-if="!designManager.isDesktopSidebarVisible()" class="quick-settings-icon icon icon-cross"></span>
                            <span class="list-label-text text-[1.1rem]">Боковое Меню</span>
                            <span class="quick-settings-switcher icon icon-switcher-way flex justify-center items-center">
                                <span :class="{'off': !designManager.isDesktopSidebarVisible(), 'on': designManager.isDesktopSidebarVisible()}" class="handle icon icon-switcher-handle"></span>
                            </span>
                        </button>

                        <div class="line flex self-center"></div>

                        <button class="quick-settings-button button-link naked-link flex justify-between items-center" @click="switchLightTheme">
                            <span v-if="isLightTheme" class="quick-settings-icon icon icon-sun"></span>
                            <span v-if="!isLightTheme" class="quick-settings-icon icon icon-moon"></span>
                            <span class="list-label-text text-[1.1rem]">Тёмная Тема</span>
                            <span class="quick-settings-switcher icon icon-switcher-way flex justify-center items-center">
                                <span :class="{'off': isLightTheme, 'on': !isLightTheme}" class="handle icon icon-switcher-handle"></span>
                            </span>
                        </button>

                        <div class="line flex self-center"></div>

                        <button v-if="!authStore.isAuthenticated" class="button-link naked-link flex items-center">
                            <span class="icon icon-diamond"></span>
                            <span class="list-label-text">Войти</span>
                        </button>

                        <a v-if="authStore.isAuthenticated" class="button-link naked-link flex items-center" href="#">
                            <span class="icon icon-left-arrow"></span>
                            <span class="list-label-text">Выйти</span>
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
    user-select: none;
    position: fixed;
    transition: .3s;
    width: 100%;
    top: 0;
}
.header-hidden { transform: translateY(-100%); }
.header-wrap {
    height: var(--header-height);
    max-width: 1250px;
    width: 100%;
}
.header-blur {
    position: fixed;
    overflow-y: hidden;
    transition: 1s;
    height: 100vh;
    width: 100%;
    z-index: 1;
}
.header-blur aside {
    background-color: var(--primary-bg-color);
    position: absolute;
    overflow-y: scroll;
    max-width: 320px;
    transition: .5s;
    height: 100%;
}
.header-blur aside.fixed { height: calc(100% + 20px); }
.close-header-sidebar-background {
    height: 100%;
    width: 100%;
}
.header-blur aside .icon {
    height: 32px;
    width: 32px;
}
.header-blur aside a .list-label-text,
.header-blur aside .quick-settings-button .list-label-text,
.header-blur .sign-in-button .list-label-text { color: var(--primary-text-color); }
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
    margin: 0 8px 0 14px;
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
    font-size: 1.1rem;
    transition: .2s;
    padding: 4px;
}
.header-dropdown-content .list-label-text.small { font-size: 1rem; }
.header-blur aside .list-label-text.small { font-size: 0.9rem; }
.header-dropdown-content .list-label-text.policy { font-size: 0.9rem; }
.header-blur aside .list-label-text.policy { font-size: 0.8rem; }
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
.search-dropdown,
.profile-dropdown {
    position: relative;
    height: 100%;
}
.search-button button {
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
.profile-dropdown-content,
.search-dropdown-content {
    display: none;
    box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
    position: absolute;
    transition: 1s;
    opacity: 1;
    top: 72px;
}
.header-dropdown-content { width: 332px; }
.profile-dropdown-content { width: 320px; }
.header-dropdown-content { left: 0; }
.search-dropdown-content { right: 0; }
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

.search-dropdown:active .search-dropdown-content,
.search-dropdown:hover .search-dropdown-content,
.search-dropdown:focus .search-dropdown-content,

.profile-dropdown:active .profile-dropdown-content,
.profile-dropdown:hover .profile-dropdown-content,
.profile-dropdown:focus .profile-dropdown-content {
    display: flex;
    animation: smooth-appear-animation .5s ease;
    flex-direction: column;
    opacity: 1;
}
.search-dropdown:active .search-button,
.search-dropdown:hover .search-button,
.search-dropdown:focus .search-button {
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
.search-dropdown-content {
    top: var(--header-height);
    position: fixed;
    width: 100vw;
    z-index: 1;
}
.search-content-form {
    height: 120px;
    width: 100%;
}
.search-content-form label {
    max-width: 425px;
    height: 48px;
    width: 80%;
}
.search-content-form label input {
    color: var(--primary-text-color);
    height: 40px;
    width: 100%;
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

/* =============== [ Медиа-Запрос { ?px < 1025px } ] =============== */

@media screen and (max-width: 1024px)
{
    .header-dropdown { display: none; }
}

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px)
{
    .header-wrap .div {
        justify-content: space-between;
        width: 100%;
    }
    .header-wrap .div .logo-wrap { margin: 0 20% 0 0; }
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

/* =============== [ Медиа-Запрос { ?px < 321px } ] =============== */

@media screen and (max-width: 320px)
{
    .header-wrap .div .logo-wrap { margin: 0 10% 0 0; }
}
</style>
