<script setup lang='ts'>
import {useAuthStore} from '@/stores/auth'
import useDesignManager from '@/design-manager'
import {type RouteLocation, useRouter} from 'vue-router'
import {useGlobalModalStore} from '@/stores/global-modal'
import {usePostCategoryStore} from '@/stores/postCategory'
import {getHeaderHeight, lockGlobalScroll, unlockGlobalScroll} from '@/helpers'
import {computed, onMounted, onUnmounted, ref} from 'vue'
import Button from '@/components/elements/Button.vue'
import Dialog from '@/components/elements/Dialog.vue'
import DesignSettingsForm from '@/components/modals/DesignSettingsForm.vue'
import SearchForm from '@/components/modals/SearchForm.vue'
import Menu, {type MenuItem} from '@/components/elements/Menu.vue'
import axios from 'axios'

interface NavigationSection {
    label: string
    icon?: string
    url?: string
    route?: RouteLocation
    children?: NavigationSection[]
}

const router = useRouter()
const authStore = useAuthStore()
const categoryStore = usePostCategoryStore()
const globalModalStore = useGlobalModalStore()
const designManager = useDesignManager()

const headerHeight = getHeaderHeight()

const isDesignSettingsDialog = ref(false)
const isHeaderSidebar = ref(false)
const isHeaderHidden = ref(false)
const isSearchDialog = ref(false)

const userMenu = ref<InstanceType<typeof Menu>>()

const userMenuItems = computed<MenuItem[]>(() => [
    {
        label: 'Контент-студия',
        icon: 'icon-bottle',
        visible: authStore.isAuthenticated,
    },
    {
        label: 'Создать материал',
        icon: 'icon-download',
        visible: authStore.isAuthenticated,
    },
    {
        label: 'Настройки',
        icon: 'icon-gear',
        visible: authStore.isAuthenticated,
    },
    {
        separator: true,
        visible: authStore.isAuthenticated,
    },
    {
        label: 'Оформление',
        icon: 'icon-eye',
        action: () => isDesignSettingsDialog.value = true,
    },
    {
        separator: true,
    },
    {
        label: 'Войти',
        icon: 'icon-right-arrow',
        visible: !authStore.isAuthenticated,
        action: () => globalModalStore.isAuth = true,
    },
    {
        label: 'Выйти',
        icon: 'icon-left-arrow',
        visible: authStore.isAuthenticated,
        action: logout
    },
])

const navigationSections = computed<NavigationSection[]>(() => [
    {
        label: 'Материалы',
        children: [
            {
                label: 'Каталог',
                icon: 'icon-book',
            },
            {
                label: 'Аддон LD',
                icon: 'icon-apple',
            },
            {
                label: 'Ресурс-паки',
                icon: 'icon-axolotl-bucket',
            },
            {
                label: 'Аддоны',
                icon: 'icon-spawn-egg',
            },
            {
                label: 'Скины',
                icon: 'icon-skin',
            },
            {
                label: 'Карты',
                icon: 'icon-map',
            },
            {
                label: 'Статьи',
                icon: 'icon-script',
            },
        ]
    },
    {
        label: 'Полезное',
        children: [
            {
                label: 'Бестиарий Light Diamond',
                icon: 'icon-bestiary',
            },
            {
                label: 'Документация Light Diamond',
                icon: 'icon-documentary',
            },
            {
                label: 'Документация Microsoft',
                icon: 'icon-microsoft-small',
                url: 'https://learn.microsoft.com/en-us/minecraft/creator/reference/content/entityreference/examples/componentlist',
            },
            {
                label: 'Документация Bedrock.Dev',
                icon: 'icon-bedrock-dev-small',
                url: 'https://bedrock.dev',
            },
            {
                label: 'Материалы Minecraft',
                icon: 'icon-minecraft-materials',
                url: 'https://github.com/Mojang/bedrock-samples/releases',
            },
        ]
    },
    {
        label: 'Медиа',
        children: [
            {
                label: 'ВКонтакте',
                icon: 'icon-vk',
                url: 'https://vk.com/light.diamond',
            },
            {
                label: 'Телеграм',
                icon: 'icon-telegram',
                url: 'https://t.me/light_diamond_channel',
            },
            {
                label: 'YouTube',
                icon: 'icon-youtube',
                url: 'https://www.youtube.com/@grostlight3303',
            },
        ]
    },
    {
        label: 'Справки',
        children: [
            {
                label: 'Правила пользования',
                icon: 'icon-hand',
            },
            {
                label: 'Политика конфиденциальности',
                icon: 'icon-script',
            },
            {
                label: 'О проекте',
                icon: 'icon-faq',
            },
        ]
    },
])

let previousScroll = 0
let currentScroll = 0

onMounted(() => document.addEventListener('scroll', onPageScroll))

onUnmounted(() => document.removeEventListener('scroll', onPageScroll))

function onPageScroll() {
    if (!designManager.isHeaderFixedVisible()) {
        currentScroll = window.scrollY
        isHeaderHidden.value = currentScroll > previousScroll && currentScroll > headerHeight
        previousScroll = currentScroll

        if (isHeaderHidden.value) {
            userMenu.value?.hide()
        }
    }
}

function closeHeaderSidebar() {
    isHeaderSidebar.value = false
    unlockGlobalScroll()
}

function openHeaderSidebar() {
    isHeaderSidebar.value = true
    lockGlobalScroll()
}

function logout() {
    axios.post('/api/auth/logout').then(() => {
        router.go(0)
    })
}
</script>

<template>

    <Dialog
        v-model:visible="isDesignSettingsDialog"
        class="design-settings-form"
        title="Оформление"
    >
        <DesignSettingsForm/>
    </Dialog>

    <Dialog
        v-model:visible="isSearchDialog"
        animation="top-translate"
        class="search-dialog outer page-container"
        :header="false"
        position="top-center"
        title="Поиск"
    >
        <SearchForm/>
    </Dialog>

    <Menu ref="userMenu" :items="userMenuItems" align-right class="user-menu">
        <template v-slot:header>
            <a
                v-if="authStore.isAuthenticated" class="profile-link laminated-link flex items-center"
                href="#"
            >
                <span class="icon icon-outline flex justify-center items-center icon-border h-[42px] w-[42px]">
                    <img alt="" src="/images/users/content/funny-girl.png" class="h-[26px]">
                    <span class="notifications-counter flex justify-center items-center">15</span>
                </span>
                <div class="flex flex-col">
                    <span
                        :class="{
                            'text-sm': authStore.username!.length > 15
                        }"
                        class="title"
                    >
                        {{ authStore.username }}
                    </span>
                    <span class="subtitle text-sm opacity-70">Профиль</span>
                </div>
            </a>
        </template>
    </Menu>

    <div
        class="header-blur fixed overflow-y-hidden transition duration-500 h-[100vh] w-full z-[3] flex visible"
        :class="{'header-blur-on': isHeaderSidebar, 'header-blur-off': !isHeaderSidebar}"
    >
        <aside
            class="
                left-header-sidebar bg-[var(--primary-bg-color)]
                overflow-y-scroll max-w-[320px]
                transition duration-500 h-full
                flex flex-col fixed"
            :class="{
                'xl:hidden': !designManager.isDesktopSidebarVisible(),
                'translate-x-0': isHeaderSidebar,
                '-translate-x-full': !isHeaderSidebar
            }"
        >
            <div
                class="left-header-sidebar-interaction flex h-[var(--header-height)] w-full justify-between items-center px-3"
            >
                <div class="w-[32px]"></div>
                <RouterLink
                    :to="{name: 'home'}"
                    class="logo-wrap flex items-center transition-opacity duration-200 h-[70%]"
                    :class="{'opacity-0': !isHeaderSidebar}"
                >
                    <img src="/images/logo.png" alt="Logo" class="h-full"/>
                </RouterLink>
                <button
                    class="closing-header-sidebar-button flex justify-center items-center"
                    :class="{'closing-button': !isHeaderSidebar, 'opening-button': isHeaderSidebar}"
                    @click="closeHeaderSidebar">
                    <span class="icon icon-cross"></span>
                </button>
            </div>

            <template v-for="section of navigationSections">
                <div class="unit-title flex justify-center text-[1.1rem]">{{ section.label }}</div>

                <component
                    v-for="childSection of section.children"
                    :is="childSection.route ? 'RouterLink' : 'a'"
                    :to="childSection.route"
                    :href="childSection.url || '#'"
                    class="naked-link flex items-center"
                >
                    <span :class="`icon ${childSection.icon}`"/>
                    <span class="list-label-text">{{ childSection.label }}</span>
                </component>
            </template>
        </aside>
        <div class="close-header-sidebar-background h-full w-full" @click="closeHeaderSidebar"></div>
    </div>

    <div
        class="h-[var(--header-height)]"
        :class="{'-translate-y-full': !designManager.isHeaderFixedVisible() && isHeaderHidden}"
    />

    <header
        class="fixed transition-transform duration-300 top-0 left-0 w-full select-none h-[var(--header-height)] z-[1] flex"
        :class="{'-translate-y-full': !designManager.isHeaderFixedVisible() && isHeaderHidden}"
    >
        <nav class="header page-container flex justify-between">
            <div class="flex items-center gap-4">
                <button
                    class="duration-100 flex justify-center items-center h-full p-2 md:-ml-3"
                    :class="{'xl:hidden': !designManager.isDesktopSidebarVisible(), 'opacity-0': isHeaderSidebar}"
                    type="button"
                    @click="openHeaderSidebar"
                >
                    <span class="icon icon-units"></span>
                </button>

                <RouterLink
                    :to="{name: 'home'}"
                    class="logo-wrap flex items-center duration-200 h-[70%]"
                    :class="{'opacity-0': isHeaderSidebar}"
                >
                    <img src="/images/logo.png" alt="Logo" class="h-full"/>
                </RouterLink>

                <div class="hidden lg:flex items-center gap-2 h-full">
                    <div v-for="section of navigationSections" class="header-dropdown inline-flex flex-col">
                        <button class="list-label flex h-full items-center gap-1" type="button">
                            <span class="icon icon-down-arrow"/>
                            <span class="list-label-text">{{ section.label }}</span>
                        </button>

                        <div class="header-dropdown-content absolute hidden top-[var(--header-height)] min-w-[260px]">
                            <component
                                v-for="childSection of section.children"
                                :is="childSection.route ? 'RouterLink' : 'a'"
                                :to="childSection.route"
                                :href="childSection.url || '#'"
                                class="flex items-center"
                            >
                                <span :class="`icon ${childSection.icon}`"></span>
                                <span class="list-label-text">{{ childSection.label }}</span>
                            </component>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex md:-mr-2">
                <button
                    class="search-button flex justify-center items-center p-2"
                    @click="isSearchDialog = true"
                    type="button"
                >
                    <span class="icon icon-magnifier"></span>
                </button>

                <button
                    class="user-menu-button flex list-label justify-center items-center p-2"
                    type="button"
                    @click="userMenu?.toggle"
                >
                    <span
                        v-if="authStore.isAuthenticated"
                        class="profile-border icon icon-border flex justify-center items-center h-[42px] w-[42px]"
                    >
                        <img alt="" src="/images/users/content/funny-girl.png" class="h-[26px]">
                    </span>

                    <span v-else class="icon icon-border-profile h-[42px] w-[42px]"></span>
                </button>
            </div>
        </nav>
    </header>
</template>

<style scoped>
.header .logo-wrap img {
    min-width: 152px;
}

.header-dropdown {
    position: relative;
    height: 100%;
}

.header-dropdown-content {
    box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
}

header .list-label-text,
.header-dropdown-content .list-label-text {
    font-size: 1.1rem;
    transition: .2s;
    padding: 4px;
}

header .list-label:focus-visible .list-label-text, header .list-label:hover .list-label-text,
.header-dropdown-content a:focus-visible .list-label-text, .header-dropdown-content a:hover .list-label-text,
.left-header-sidebar .naked-link:focus-visible .list-label-text, .left-header-sidebar .naked-link:hover .list-label-text,
.user-menu .profile-link:focus-visible .title, .user-menu .profile-link:hover .title {
    color: var(--hover-text-color);
}

button.list-label .icon {
    transition: .2s;
}

button.list-label:focus-visible .icon, button.list-label:hover .icon,
.header-dropdown-content a:focus-visible .icon, .header-dropdown-content a:hover .icon,
.search-button:focus-visible .icon, .search-button:hover .icon,
.left-header-sidebar .naked-link:focus-visible .icon, .left-header-sidebar .naked-link:hover .icon,
.search-content-form button:focus-visible, .search-content-form button:hover {
    animation: icon-trigger-up-animation .3s ease;
}

.user-menu-button .notifications-counter,
.user-menu .notifications-counter {
    background-color: rgb(210, 10, 30);
    color: var(--primary-text-color);
    margin: 0 0 32px 32px;
    position: absolute;
    text-align: center;
    padding-left: 2px;
    font-size: .5rem;
    height: 16px;
    width: 16px;
}

.header-dropdown-content .icon {
    margin: 0 8px 0 24px;
}

.header-dropdown-content .list-label-text {
    max-width: 60%;
}

.header-dropdown:active .header-dropdown-content,
.header-dropdown:hover .header-dropdown-content,
.header-dropdown:focus .header-dropdown-content {
    display: flex;
    animation: smooth-appear-animation .5s ease;
    flex-direction: column;
    opacity: 1;
}


.left-header-sidebar .unit-title {
    color: var(--primary-text-color);
    background-size: 400% 100%;
    padding: 4px 0;
}

.left-header-sidebar .naked-link .icon {
    margin: 0 8px 0 32px;
}

.left-header-sidebar .naked-link .list-label-text {
    margin: 0 8px 0 8px;
    max-width: 60%;
}

.left-header-sidebar .naked-link .list-label-text {
    line-height: 1.2;
}

</style>
