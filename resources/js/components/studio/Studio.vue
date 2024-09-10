<script setup lang="ts">
import {useRoute} from 'vue-router'
import {ref, watch} from 'vue'
import ProfileSettings from '@/components/settings/ProfileSettings.vue'
import SecuritySettings from '@/components/settings/SecuritySettings.vue'
import ItemButton from '@/components/elements/ItemButton.vue'

enum Section {
    MATERIALS,
    REQUESTS
}
const route = useRoute()

let currentSection = ref(Section.MATERIALS)
let isMobileMenu = ref(true)
let sectionTitle;

watch(route, () => {
    updateCurrentSectionByRoute()
})

function updateCurrentSectionByRoute() {
    switch (route.name) {
        case 'studio.materials':
            currentSection.value = Section.MATERIALS
            break
        case 'studio.requests':
            currentSection.value = Section.REQUESTS
            break
    }
}

function setMobileMenu() {
    isMobileMenu.value = true
}

function setMaterialsSection() {
    currentSection.value = Section.MATERIALS
    sectionTitle = 'Материалы'
    isMobileMenu.value = false
}

function setRequestsSection() {
    currentSection.value = Section.REQUESTS
    sectionTitle = 'Заявки на публикацию'
    isMobileMenu.value = false
}

updateCurrentSectionByRoute()
</script>

<template>
<div class="content-studio flex flex-col w-full">
    <div class="title ld-primary-background ld-primary-border ld-shadow-text flex justify-center h-[72px] mb-2 mt-2">
        <h1 class="text-[1.2rem] md:text-[2rem] flex items-center">Контент-Студия</h1>
    </div>
    <div class="interface flex mb-2">
        <aside class="ld-primary-background ld-primary-border flex flex-col">
            <RouterLink
                :class="{ 'transfusion': currentSection === Section.MATERIALS }"
                class="h-fit flex"
                :to="{ name: 'studio.materials' }"
            >
                <ItemButton
                    class="pl-8 pr-4 w-full text-[1.1rem]"
                    @click="setRequestsSection()"
                    icon="icon-news"
                    label="Материалы"
                    tabindex="-1"
                />
            </RouterLink>

            <RouterLink
                :class="{ 'transfusion': currentSection === Section.REQUESTS }"
                class="h-fit flex"
                :to="{name: 'studio.requests'}"
            >
                <ItemButton
                    class="pl-8 pr-4 w-full text-[1.1rem]"
                    @click="setMaterialsSection()"
                    icon="icon-letter"
                    label="Заявки на публикацию"
                    tabindex="-1"
                />
            </RouterLink>
        </aside>

        <div
            :class="{ 'on': !isMobileMenu }"
            class="content-studio-container ld-primary-background ld-primary-border ld-shadow-text flex flex-col w-full"
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

<style scoped>
.content-studio {
    max-width: 1280px;
}
.content-studio aside {
    height: fit-content;
    margin-right: .5rem;
    min-width: 280px;
}
.content-studio aside a {
    background-size: 400% 100%;
    border: none;
}
.content-studio .title {
    justify-content: center;
    height: 72px;
}
.content-studio .title .arrow {
    display: none;
}
.content-studio .interface {
    overflow: hidden;
}
.content-studio .content-studio-container {
    position: relative;
    min-height: 800px;
    overflow: hidden;
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
    .content-studio {
        margin: 0 .5rem;
    }
    .content-studio .title {
        justify-content: space-between;
        height: 64px;
    }
    .content-studio .title .arrow {
        display: flex;
    }
    .content-studio .interface aside {
        transform: translateX(-100%);
        transition: .5s;
        margin-right: 0;
        min-width: 0;
        opacity: 0;
        width: 0;
    }
    .content-studio .interface aside.on {
        transform: translateX(0);
        max-width: 100%;
        width: 100%;
        opacity: 1;
    }
    .content-studio .interface .content-studio-container {
        transform: translateX(100%);
        min-height: 1024px;
        transition: .5s;
        opacity: 0;
        border: 0;
        width: 0;
    }
    .content-studio .interface .content-studio-container.on {
        transform: translateX(0);
        width: 100%;
        opacity: 1;
        right: 2px;
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
    .content-studio {
        margin: 0 .5rem;
    }
}
</style>
