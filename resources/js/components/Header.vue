<script setup lang='ts'>
import {useAuthStore} from '@/stores/auth'
import usePreferenceManager from '@/preference-manager'
import {type RouteLocationRaw, useRouter} from 'vue-router'
import {useGlobalModalStore} from '@/stores/global-modal'
import {usePostCategoryStore} from '@/stores/postCategory'
import {getHeaderHeight, lockGlobalScroll, unlockGlobalScroll} from '@/helpers'
import {computed, onMounted, onUnmounted, ref} from 'vue'
import ItemButton from '@/components/elements/ItemButton.vue'
import Button from '@/components/elements/Button.vue'
import Dialog from '@/components/elements/Dialog.vue'
import DesignSettingsForm from '@/components/modals/DesignSettingsForm.vue'
import SearchForm from '@/components/modals/SearchForm.vue'
import Menu, {type MenuItem} from '@/components/elements/Menu.vue'
import axios from 'axios'
import UserAvatar from '@/components/user/UserAvatar.vue'

interface NavigationSection {
    label: string
    icon?: string
    url?: string
    route?: RouteLocationRaw
    children?: NavigationSection[]
}

const router = useRouter()
const authStore = useAuthStore()
const categoryStore = usePostCategoryStore()
const globalModalStore = useGlobalModalStore()
const preferenceManager = usePreferenceManager()

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
        route: {name: 'studio'}
    },
    {
        label: 'Создать материал',
        icon: 'icon-plus',
        visible: authStore.isAuthenticated,
        route: {name: 'create-post'}
    },
    {
        label: 'Настройки',
        icon: 'icon-gear',
        visible: authStore.isAuthenticated,
        route: {name: 'settings'}
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
                route: {name: `catalog.${preferenceManager.getEdition().toLowerCase()}`},
            },
            {
                label: 'Новости',
                icon: 'icon-news',
            },
            {
                label: 'Ресурс-Паки',
                icon: 'icon-axolotl-bucket',
            },
            {
                label: 'Аддоны',
                icon: 'icon-spawn-egg',
            },
            {
                label: 'Карты',
                icon: 'icon-map',
            },
            {
                label: 'Скины',
                icon: 'icon-skin',
            },
            {
                label: 'Статьи',
                icon: 'icon-script',
            },
            {
                label: 'Аддон LD',
                icon: 'icon-apple',
            }
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
            }
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
            }
        ]
    },
    {
        label: 'Помощь',
        children: [
            {
                label: 'Правила Пользования',
                icon: 'icon-hand',
            },
            {
                label: 'Политика Конфиденциальности',
                icon: 'icon-script',
            },
            {
                label: 'О Проекте',
                icon: 'icon-faq',
            }
        ]
    },
])

let previousScroll = 0
let currentScroll = 0

onMounted(() => document.addEventListener('scroll', onPageScroll))

onUnmounted(() => document.removeEventListener('scroll', onPageScroll))

function onPageScroll() {
    if (!preferenceManager.isHeaderFixedVisible()) {
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
        class="search-dialog outer max-w-[1280px] w-full"
        :header="false"
        position="top-center"
        title="Поиск"
    >
        <SearchForm/>
    </Dialog>

    <Menu
        item-classes="h-[64px] md:text-[1rem] text-[14px] gap-3 pl-6 pr-4"
        :items="userMenuItems"
        align-right
        class="user-menu mt-[-4px]
            ld-primary-background
            ld-primary-border-bottom
            ld-primary-border-right
            ld-primary-border-left"
        ref="userMenu"
    >
        <template v-slot:header>
            <a
                v-if="authStore.isAuthenticated"
                class="profile-link laminated-link transfusion bordered flex items-center min-h-[64px] m-1"
                href="#"
            >
                <span class="notifications-counter flex justify-center items-center absolute">15</span>
                <UserAvatar
                    v-if="authStore.isAuthenticated"
                    border-class-list="h-10 w-10"
                    icon-class-list="h-7 w-7"
                    :user="authStore.user!"
                />
                <div class="flex flex-col">
                    <span class="title text-sm">{{ authStore.username }}</span>
                    <span class="subtitle text-[12px] opacity-70">Профиль</span>
                </div>
            </a>
        </template>
    </Menu>

    <div
        class="header-blur fixed overflow-y-hidden transition duration-500 h-[100vh] w-full z-[3] left-0 flex visible"
        :class="{'header-blur-on': isHeaderSidebar, 'header-blur-off': !isHeaderSidebar}"
    >
        <aside
            class="
                left-header-sidebar  ld-primary-background ld-shadow-text
                overflow-y-scroll max-w-[300px]
                transition duration-500 h-full
                flex flex-col fixed"
            :class="{
                'xl:hidden': !preferenceManager.isDesktopSidebarVisible(),
                'translate-x-0': isHeaderSidebar,
                '-translate-x-full': !isHeaderSidebar
            }"
        >
            <div
                class="left-header-sidebar-interaction flex h-[var(--header-height)] w-full justify-between items-center px-3"
            >
                <div class="w-[32px]"/>
                <RouterLink
                    :to="{name: 'home'}"
                    class="logo-wrap flex items-center transition-opacity duration-200 h-[70%]"
                    :class="{'opacity-0': !isHeaderSidebar}"
                >
                    <img src="/images/elements/light-diamond-logo.png" alt="Logo" class="h-full"/>
                </RouterLink>
                <button
                    class="closing-header-sidebar-button flex justify-center items-center"
                    :class="{'closing-button': !isHeaderSidebar, 'opening-button': isHeaderSidebar}"
                    @click="closeHeaderSidebar">
                    <span class="icon icon-cross"/>
                </button>
            </div>

            <template v-for="section of navigationSections">
                <div class="unit-title flex justify-center transfusion md:text-[1rem] text-[14px]">{{ section.label }}</div>

                <template v-for="childSection of section.children">
                    <ItemButton
                        v-if="childSection.route"
                        as="RouterLink"
                        :label="childSection.label"
                        :icon="childSection.icon"
                        :to="childSection.route"
                        class="min-h-[64px] text-[14px] gap-3 pl-12 pr-4"
                    />
                    <ItemButton
                        v-else
                        :as="childSection.url ? 'a' : 'button'"
                        :label="childSection.label"
                        :icon="childSection.icon"
                        :href="childSection.url"
                        class="min-h-[64px] text-[14px] gap-3 pl-12 pr-4"
                    />
                </template>

            </template>
        </aside>
        <div class="close-header-sidebar-background h-full w-full" @click="closeHeaderSidebar"></div>
    </div>

    <header
        class="ld-primary-background ld-primary-border-bottom ld-shadow-text transition-transform
        flex justify-center duration-300 select-none sticky
        h-[var(--header-height)] z-[2] w-full top-0 left-0"
        :class="{'-translate-y-full': !preferenceManager.isHeaderFixedVisible() && isHeaderHidden}"
    >
        <nav class="header page-container flex justify-between xl:px-0 xs:px-4 px-2">
            <div class="flex items-center xs:gap-2">
                <button
                    class="burger duration-100 flex justify-center items-center h-full"
                    :class="{'xl:hidden': !preferenceManager.isDesktopSidebarVisible(), 'opacity-0': isHeaderSidebar}"
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
                    <img alt="Logo" class="hidden xs:flex h-full min-w-152"
                         src="/images/elements/light-diamond-logo.png"/>
                    <img alt="Logo" class="flex xs:hidden h-full" src="/images/elements/light-diamond-logo-mobile.png"/>
                </RouterLink>

                <div class="hidden lg:flex items-center gap-2 h-full">
                    <div v-for="section of navigationSections" class="header-dropdown inline-flex flex-col">
                        <button class="list-label flex h-full items-center gap-1" type="button">
                            <span class="icon icon-down-arrow"/>
                            <span class="list-label-text">{{ section.label }}</span>
                        </button>

                        <div
                            class="header-dropdown-content
                                ld-primary-background
                                ld-primary-border-bottom
                                ld-primary-border-right
                                ld-primary-border-left
                                absolute
                                hidden
                                top-[var(--header-height)]
                                min-w-[260px]"
                        >
                            <template v-for="childSection of section.children">
                                <ItemButton
                                    class="h-[64px] md:text-[1rem] text-[14px] gap-3 pl-8 pr-4"
                                    v-if="childSection.route"
                                    as="RouterLink"
                                    :label="childSection.label"
                                    :icon="childSection.icon"
                                    :to="childSection.route"

                                />
                                <ItemButton
                                    v-else
                                    class="h-[64px] md:text-[1rem] text-[14px] gap-3 pl-8 pr-4"
                                    :as="childSection.url ? 'a' : 'button'"
                                    :label="childSection.label"
                                    :icon="childSection.icon"
                                    :href="childSection.url"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center">
                <button
                    class="search-button flex justify-center items-center p-2"
                    @click="isSearchDialog = true"
                    type="button"
                >
                    <span class="icon icon-magnifier"></span>
                </button>

                <button
                    class="user-menu-button flex list-label justify-center items-center"
                    type="button"
                    @click="userMenu?.toggle"
                >
                    <span
                        v-if="authStore.isAuthenticated"
                        class="notifications-counter flex justify-center items-center absolute"
                    >
                        15
                    </span>

                    <UserAvatar
                        v-if="authStore.isAuthenticated"
                        border-class-list="h-10 w-10"
                        icon-class-list="h-7 w-7"
                        :user="authStore.user!"
                    />

                    <span v-else class="icon icon-border-profile h-[42px] w-[42px]"></span>
                </button>
            </div>
        </nav>
    </header>

</template>

<style scoped>
.list-label:focus-visible + .header-dropdown-content {
    display: flex;
    flex-direction: column;
}

.header-dropdown {
    position: relative;
    height: 100%;
}

.header-dropdown-content {
    box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
}

header .list-label-text {
    transition: .2s;
    padding: 4px;
}

header .list-label:focus-visible .list-label-text, header .list-label:hover .list-label-text,
.user-menu .profile-link:focus-visible .title, .user-menu .profile-link:hover .title {
    color: var(--hover-text-color);
}

button.list-label .icon {
    transition: .2s;
}
.icon-magnifier,
.icon-units {
    min-width: 2rem;
}

button.list-label:focus-visible .icon, button.list-label:hover .icon,
.search-button:focus-visible .icon, .search-button:hover .icon,
.search-content-form button:focus-visible, .search-content-form button:hover {
    animation: icon-trigger-up-animation .3s ease;
}

.header .burger,
.header .search-button {
    height: 70px;
}

.header .user-menu-button {
    height: 70px;
    width: 52px;
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

.notifications-counter {
    background-color: rgb(210, 10, 30);
    color: var(--primary-text-color);
    margin: 0 0 28px 28px;
    text-align: center;
    font-size: .5rem;
    z-index: 1;
}

</style>
