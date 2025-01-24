<script setup lang="ts">
import Button from '@/components/elements/Button.vue'
import Switcher from '@/components/elements/Switcher.vue'
import usePreferenceManager from '@/preference-manager'
const preferenceManager = usePreferenceManager()
</script>

<template>
    <div class="flex flex-col items-center p-1">
        <button class="settings-button flex justify-between items-center m-1 p-2"
                @click="preferenceManager.switchTheme()"
        >
            <span v-if="preferenceManager.isLightTheme()" class="settings-icon icon icon-sun"></span>
            <span v-else class="settings-icon icon icon-moon"></span>
            <span class="text-base">Тёмная Тема</span>
            <Switcher :active=preferenceManager.isLightTheme() />
        </button>

        <button class="settings-button flex justify-between items-center m-1 p-2"
            @click="preferenceManager.switchHeaderFixed()"
        >
            <span v-if="preferenceManager.isHeaderFixedVisible()" class="settings-icon icon icon-fixed-header"></span>
            <span v-else class="settings-icon icon icon-free-header"></span>
            <span class="text-base">Свободная Шапка</span>
            <Switcher :active=preferenceManager.isHeaderFixedVisible() />
        </button>

        <button class="settings-button xl:flex justify-between items-center hidden m-1 p-2" @click="preferenceManager.switchDesktopSidebar()">
            <span v-if="preferenceManager.isDesktopSidebarVisible()" class="icon icon-units"></span>
            <span v-else class="settings-icon icon icon-cross"></span>
            <span class="text-base">Боковое Меню</span>
            <Switcher :active=!preferenceManager.isDesktopSidebarVisible() />
        </button>
    </div>
</template>

<style>
.design-settings-form .settings-button {
    height: 64px;
    width: 98%;
}
</style>
