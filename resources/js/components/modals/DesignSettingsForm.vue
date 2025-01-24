<script setup lang="ts">
import Button from '@/components/elements/Button.vue'
import Switcher from '@/components/elements/Switcher.vue'
import usePreferenceManager from '@/preference-manager'
const preferenceManager = usePreferenceManager()
</script>

<template>
    <div class="flex flex-col items-center p-1">
        <button
            class="settings-button flex justify-between items-center m-1 p-2"
            @click="preferenceManager.switchTheme()"
        >
            <span
                class="settings-icon icon"
                :class="{
                    'icon-sun': preferenceManager.isLightTheme(),
                    'icon-moon': !preferenceManager.isLightTheme()
                }"
            />
            <span class="text-base">Тёмная Тема</span>
            <Switcher :active=preferenceManager.isLightTheme() />
        </button>

        <button
            class="settings-button xl:flex justify-between items-center hidden m-1 p-2"
            @click="preferenceManager.switchDesktopSidebar()"
        >
            <span
                class="settings-icon icon"
                :class="{
                    'icon-units': preferenceManager.isDesktopSidebarVisible(),
                    'icon-cross': !preferenceManager.isDesktopSidebarVisible()
                }"
            />
            <span class="text-base">Боковое Меню</span>
            <Switcher :active=!preferenceManager.isDesktopSidebarVisible() />
        </button>

        <button class="settings-button flex justify-between items-center m-1 p-2"
                @click="preferenceManager.switchHeaderFixed()"
        >
            <span
                class="settings-icon icon"
                :class="{
                    'icon-fixed-header': preferenceManager.isHeaderFixedVisible(),
                    'icon-free-header': !preferenceManager.isHeaderFixedVisible()
                }"
            />
            <span class="text-base">Свободная Шапка</span>
            <Switcher :active=preferenceManager.isHeaderFixedVisible() />
        </button>

        <button class="settings-button flex justify-between items-center m-1 p-2"
                @click="preferenceManager.switchMaterialCategoryInPreview()"
        >
            <span
                class="settings-icon icon"
                :class="{
                    'icon-brilliant': preferenceManager.isMaterialCategoryInPreviewVisible(),
                    'icon-cross': !preferenceManager.isMaterialCategoryInPreviewVisible()
                }"
            />
            <span class="text-base">Категория в Превью</span>
            <Switcher :active=!preferenceManager.isMaterialCategoryInPreviewVisible() />
        </button>
    </div>
</template>

<style>
.design-settings-form .settings-button {
    height: 64px;
    width: 98%;
}
</style>
