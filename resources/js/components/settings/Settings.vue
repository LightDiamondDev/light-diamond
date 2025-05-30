<script setup lang="ts">
import {useRoute} from 'vue-router'
import {computed, ref, watch} from 'vue'

import ItemButton from '@/components/elements/ItemButton.vue'

interface SettingsMenuItem {
    label: string
    icon: string
    route?: string
    visible?: boolean | ((...args: any) => boolean)
}

const route = useRoute()
const isMobileMenu = ref(true)

const menuItems = ref<SettingsMenuItem[]>([
    {
        label: 'Профиль',
        icon: 'icon-diamond',
        route: 'settings.profile',
    },
    {
        label: 'Безопасность',
        icon: 'icon-apple',
        route: 'settings.security',
    }
])

const activeMenuItem = ref<SettingsMenuItem>(getActualActiveMenuItem())

watch(route, () => {
    activeMenuItem.value = getActualActiveMenuItem()
})

function getActualActiveMenuItem() {
    return menuItems.value.find(item => item.route === route.name)!
}

function onSectionSelect(item: SettingsMenuItem) {
    isMobileMenu.value = false
    activeMenuItem.value = item
}

const visibleMenuItems = computed<SettingsMenuItem[]>(() => {
    return menuItems.value.filter(
        (item) => typeof item.visible === 'function' ? item.visible() : item.visible !== false
    )
})

function setMobileMenu() {
    isMobileMenu.value = true
}

</script>

<template>
    <div class="manager ld-fixed-background flex flex-col max-w-[1280px] w-full">
        <div class="title-header w-full">
            <div class="title flex justify-between items-center w-full p-2">
                <button
                    class="justify-center items-center arrow-tap locked"
                    :class="{'invisible': isMobileMenu}"
                    @click="setMobileMenu()"
                >
                    <span class="flex icon icon-left-arrow"/>
                </button>
                <div class="ld-title-font ld-shadow-text flex items-center text-[1.2rem] md:text-[2rem] gap-2">
                    <h1 :class="{ 'sm-hidden': !isMobileMenu }" class="ld-brilliant-text text-center">Настройки</h1>
                    <p class="opacity-80 sm:flex hidden">></p>
                    <p class="text-center" :class="{ 'sm-hidden': isMobileMenu }">{{ activeMenuItem.label }}</p>
                </div>
                <div class="h-[32px] w-[32px] arrow-tap"></div>
            </div>
        </div>
        <div class="interface flex">
            <aside class="manager-aside md:min-w-[280px]" :class="{ 'on': isMobileMenu }">
                <div class="units">

                    <Component
                        v-for="(item) of visibleMenuItems"
                        :class="{ 'transfusion': activeMenuItem.route === item.route }"
                        class="flex border-0"
                        :is="item.route ? 'RouterLink' : 'a'"
                        :to="{ name: item.route }"
                        @click="onSectionSelect(item)"
                    >
                        <ItemButton
                            class="ld-title-font h-[64px] md:text-[1rem] text-[14px] w-full gap-2 pl-6 pr-8 whitespace-nowrap"
                            plain :text="item !== activeMenuItem"
                            :label="item.label"
                            :icon="item.icon"
                            tabindex="-1"
                        />
                    </Component>

                </div>
            </aside>

            <div
                :class="{ 'on': !isMobileMenu }"
                class="manager-container ld-fixed-background ld-primary-border
                    flex flex-col w-full relative"
            >

                <RouterView v-slot="{ Component }">
                    <Transition name="smooth-manager-switch">
                        <Component class="w-full" :is="Component"/>
                    </Transition>
                </RouterView>

            </div>
        </div>
    </div>
</template>

<style scoped>
.manager-container,
.manager-aside,
.title-header {
    background-color: var(--primary-bg-color);
    background-image: var(--base-bg-image);
}
.manager {
    padding: .5rem;
    gap: .5rem;
}
.title {
    border: var(--primary-border);
}
.arrow-tap {
    visibility: hidden;
}
.interface {
    gap: .5rem;
}
.manager-aside {
    border: var(--primary-border);
}

/* =============== [ Медиа-Запрос { 769px > ? } ] =============== */

@media screen and (min-width: 769px) {
    .manager .interface {
        overflow: clip;
    }
}
/* =============== [ Медиа-Запрос { ?px < 769px } ] =============== */

@media screen and (max-width: 768px) {
    .manager-container,
    .manager-aside,
    .title-header {
        background-color: transparent;
        background-image: none;
    }
    .manager {
        background-color: var(--primary-bg-color);
        background-image: var(--base-bg-image);
        padding: 0;
        gap: 0;
    }
    .title {
        background: var(--tinted-bg-color);
        border: none;
    }
    .arrow-tap {
        visibility: visible;
    }
    .interface {
        gap: 0;
    }
    .manager-aside {
        border-bottom: var(--primary-border);
        border-top: var(--primary-border);
        border-right: none;
        border-left: none;
    }
    .invisible {
        visibility: hidden;
    }
    .interface .manager-aside {
        transition: .5s;
        min-width: 0;
        opacity: 0;
        width: 0;
    }
    .interface .manager-aside.on {
        transform: translateX(0);
        max-width: 100%;
        width: 100%;
        opacity: 1;
    }
    .interface .manager-container {
        transform: translateX(100%);
        transition: .5s;
        opacity: 0;
        border: 0;
        width: 0;
    }
    .interface .manager-container.on {
        transform: translateX(0);
        width: 100%;
        opacity: 1;
    }
}

/* =============== [ Медиа-Запрос { ?px < 641px } ] =============== */

@media screen and (max-width: 640px) {
    .sm-hidden {
        display: none;
    }
}
</style>
