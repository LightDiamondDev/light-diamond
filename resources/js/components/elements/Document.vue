<script setup lang="ts">
import {ref} from 'vue'
import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'
import usePreferenceManager from '@/preference-manager'

import {RouterLink, useRoute} from 'vue-router'

import Button from '@/components/elements/Button.vue'
import ProcessingDiggingBlocks from '@/components/elements/ProcessingDiggingBlocks.vue'

defineProps({
    title: {
        type: String,
        required: true
    }
})

const authStore = useAuthStore()
const preferenceManager = usePreferenceManager()
const toastStore = useToastStore()
const route = useRoute()

const isLoading = ref(true)
</script>

<template>
    <div v-if="!isLoading" class="flex justify-center items-center overflow-hidden h-[85vh] w-full">
        <ProcessingDiggingBlocks processing-classes="md:h-[192px] md:w-[192px] h-[128px] w-[128px]"/>
    </div>
    <div v-else-if="true" class="smooth-dark-background flex flex-col items-center w-full duration-500"
         :class="{'wide': preferenceManager.isMaterialFullViewVisible()}"
    >
        <div/>
        <section class="section flex justify-between xl:flex-row flex-col-reverse xl:items-start items-center
            xl:max-w-[1280px] max-w-[832px] w-full gap-4 lg:mt-4">

            <aside class="left-material-interaction xl-left-material-interaction
                xl:flex hidden xl:flex-col sticky text-[12px] mb-12"
            >
                <!--                <MaterialActionBar class="ld-secondary-background-container flex-col gap-4" :material="material"/>-->
            </aside>

            <div
                class="material center-interaction bright-background ld-fixed-background flex flex-col items-center max-w-[832px] w-full"
                ref="materialContent">

                <div class="material-info-dates xl:hidden flex lg:justify-between justify-center w-full xs:px-4 px-2">

                    <div
                        class="material-info-bar ld-secondary-text flex xl:flex-col flex-wrap justify-center gap-4 lg:mt-0 mt-4 duration-500">
                        <span class="author-wrap flex items-center w-fit border-0 sm:gap-0 gap-2">

                            <span class="flex justify-center items-center mr-2">
                                <span
                                    class="icon icon-border icon-outline flex justify-center items-center md:h-10 md:w-10 h-8 w-8">
                                    <img alt="" class="md:h-7 md:w-7 h-6 w-6 mt-0"
                                         src="/images/users/default-avatar.png">
                                </span>
                            </span>

                            <span class="date-action author flex flex-col sm:items-start items-center text-[14px]">
                                <span class="date-action-subtitle text-[var(--trinity-text-color)] sm:flex hidden" style="line-height: 1.4">Автор</span>
                                <span style="line-height: 1.4">Light Diamond</span>
                            </span>
                        </span>

                        <div class="date-publication flex items-center sm:gap-0 gap-2">
                            <span class="icon-apple icon flex mr-1"/>
                            <div class="date-action flex flex-col text-[14px]">
                                <span class="date-action-subtitle text-[var(--trinity-text-color)] sm:flex hidden" style="line-height: 1.4">Опубликовано</span>
                                <span
                                    class="flex xs:items-start items-center text-center sm:max-w-none"
                                    style="line-height: 1.4"
                                >
                                    25 января 2025
                                </span>
                            </div>
                        </div>
                    </div>

                    <button class="lg:flex hidden items-start" @click="preferenceManager.switchMaterialFullView()">
                        <span
                            class="icon flex my-4"
                            :class="{
                                'icon-right-arrow': preferenceManager.isMaterialFullViewVisible(),
                                'icon-left-arrow': !preferenceManager.isMaterialFullViewVisible()
                            }"
                        />
                    </button>
                </div>

                <h1 class="material-name ld-secondary-text text-center
                    md:text-[3rem] sm:text-[2rem] text-[1.5rem] my-4 xs:px-4 px-2"
                >
                    {{ title }}
                </h1>

                <!--                <div class="preview-wrap flex w-full mt-0 xs:mx-4 xs:px-4 px-2">-->
                <!--                    <img alt="" class="preview w-full mt-0" :src="material!.version!.cover_url">-->
                <!--                </div>-->

                <div class="ld-secondary-text material max-w-full pb-8 xs:px-4 px-2 py-2">
                    <slot name="content"/>
                </div>

                <div
                    class="ld-secondary-background ld-fixed-background ld-trinity-border-top xl:hidden
                        flex sm:justify-start justify-center sticky w-full bottom-0 mt-2 xs:px-4 px-2 py-2"
                >
                    <!--                    <MaterialActionBar class="ld-secondary-background-container xs:gap-4 gap-2" :material="material"/>-->
                </div>

            </div>

            <aside class="right-material-interaction xl-right-material-interaction xl:flex hidden xl:flex-col xl:sticky
                text-[12px] xl:max-w-[336px] gap-4"
            >
                <div class="first-bright-block bright-background flex flex-col">
                    <button class="flex justify-end p-[4px]" @click="preferenceManager.switchMaterialFullView()">
                        <span
                            class="icon flex"
                            :class="{
                                'icon-right-direction-arrow': preferenceManager.isMaterialFullViewVisible(),
                                'icon-left-direction-arrow': !preferenceManager.isMaterialFullViewVisible()
                            }"
                        />
                    </button>

                    <!-- MaterialInfoBar -->
                    <div
                        class="material-info-bar ld-secondary-text flex flex-wrap justify-center lg:mt-0 mt-4
                            right-material-info-bar xl:flex-col gap-4 duration-500">
                        <span class="author-wrap flex items-center w-fit border-0 sm:gap-0 gap-2">

                            <span class="flex justify-center items-center mr-2">
                                <span
                                    class="icon icon-border icon-outline flex justify-center items-center md:h-10 md:w-10 h-8 w-8">
                                    <img alt="" class="md:h-7 md:w-7 h-6 w-6 mt-0"
                                         src="/images/users/default-avatar.png">
                                </span>
                            </span>

                            <span
                                class="date-action author flex flex-col sm:items-start items-center whitespace-nowrap">
                                <span class="date-action-subtitle text-[var(--trinity-text-color)] sm:flex hidden">Автор</span>
                                <span>Light Diamond</span>
                            </span>
                        </span>


                        <!-- v-if="material.updated_at !== material.created_at" -->
                        <!--                        <div  class="date-update flex items-center sm:gap-0 gap-2">-->
                        <!--                            <span class="icon-refresh icon flex mr-1"/>-->
                        <!--                            <div class="date-action flex flex-col">-->
                        <!--                                <span class="date-action-subtitle sm:flex hidden">Обновлено</span>-->
                        <!--                                <span class="flex xs:items-start items-center text-center sm:max-w-none max-w-[90px]"-->
                        <!--                                      v-tooltip.top="getFullPresentableDate(material.updated_at)"-->
                        <!--                                >-->
                        <!--                                    {{ getRelativeDate(material.updated_at) }}-->
                        <!--                                </span>-->
                        <!--                            </div>-->
                        <!--                        </div>-->
                        <div class="date-publication flex items-center sm:gap-0 gap-2">
                            <span class="icon-apple icon flex mr-1"/>
                            <div class="date-action flex flex-col">
                                <span class="date-action-subtitle text-[var(--trinity-text-color)] sm:flex hidden">Опубликовано</span>
                                <span class="flex xs:items-start items-center text-center sm:max-w-none max-w-[90px]">
                                    25 января 2025
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- xl:flex -->
                <div class="next-bright-block bright-background hidden flex-col overflow-hidden">
                    <div class="material-addition-content flex flex-col w-full p-4 duration-500" style="color: dimgray">
                        Дополнительный Контент
                    </div>
                </div>
            </aside>
        </section>
    </div>

    <div v-else class="unavailable-material flex justify-center items-center">
        <div class="unavailable-material-container flex flex-col items-center">
            <h1 class="text-4xl font-bold text-center mt-8 mb-4">Страница не найдена</h1>
            <div class="mob phantom flex justify-center items-center full-locked">
                <div class="animation-flying-phantom"/>
            </div>
            <RouterLink class="flex justify-center max-w-[480px] w-full mb-8" :to="{ name: 'home' }">
                <Button
                    press-classes="px-4"
                    button-type="submit"
                    icon="item-ender-pearl"
                    icon-size="32px"
                    label="Телепортироваться Домой"
                />
            </RouterLink>
        </div>
    </div>
</template>

<style>
.tooltip::before {
    margin-left: -100px;
}
</style>

<style scoped>
.preview-wrap,
.preview {
    object-position: center;
    aspect-ratio: 16/9;
    object-fit: cover;
}

.smooth-dark-background.wide {
    background-color: rgba(0, 0, 0, .2);
}

.section {
    transition: flex 0.5s ease;
}

.bright-background {
    border: 2px solid transparent;
    transition: .5s;
}

.wide .bright-background {
    background-image: url('/images/elements/base-background-code.png');
    background-color: var(--secondary-bg-color);
    border: var(--secondary-border);
}

.xl-left-material-interaction,
.xl-right-material-interaction {
    transition: .5s;
}

.material-addition-content {
    transform: translateX(100%);
    opacity: 0;
}

.wide .material-addition-content {
    transform: translateX(0);
    opacity: 1;
}

.wide .center-interaction {
    margin-bottom: 1rem;
}

.unavailable-material {
    height: 720px;
    width: 100%;
}

.mob.phantom {
    overflow: hidden;
    max-width: 320px;
    height: 200px;
    width: 100%;
}

.mob.phantom div {
    background-size: 100% 100%;
    height: 160px;
    width: 320px;
}

/* =============== [ Медиа-Запрос { ?px < 1024px + desktop-height } ] =============== */

@media screen and (max-width: 1023px) and (min-height: 654px) {
    .unavailable-material {
        height: 540px;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1024px + mobile-height } ] =============== */

@media screen and (max-width: 1023px) and (max-height: 653px) {
    .unavailable-material {
        height: 380px;
    }

    .mob.phantom {
        height: 100px;
    }

    .mob.phantom div {
        height: 80px;
        width: 160px;
    }
}

/* =============== [ Медиа-Запрос { 1281px > ?px } ] =============== */

@media screen and (min-width: 1281px) {
    .xl-left-material-interaction,
    .xl-right-material-interaction {
        width: 208px;
        top: 96px;
    }

    .wide .xl-right-material-interaction {
        width: 336px;
        top: 80px;
    }

    .xl-left-material-interaction {
        margin-top: 0;
    }

    .wide .xl-left-material-interaction {
        width: 80px;
    }

    .right-material-info-bar {
        padding-left: 3rem;
    }

    .wide .right-material-info-bar {
        padding: 0 1rem 1rem 1rem;
    }

    #comments {
        transition: .5s;
    }

    .wide #comments {
        margin-right: 256px;
    }

    .first-bright-block {
        margin-bottom: 3rem;
    }

    .wide .first-bright-block {
        margin-bottom: 1rem;
    }

    .next-bright-block {
        margin-bottom: 1rem;
    }

    .wide .next-bright-block {
        margin-bottom: 3rem;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1281px } ] =============== */

@media screen and (max-width: 1280px) {
    .xl-right-material-interaction {
        width: 100%;
    }

    .right-date-info {
        width: 100%;
    }
}

/* =============== [ Медиа-Запрос { ?px < 767px } ] =============== */

@media screen and (max-width: 768px) {
    .smooth-dark-background.wide {
        background-color: transparent;
    }

    .wide .bright-background {
        background-color: transparent;
        background-image: none;
        border: none;
    }

    .wide .center-interaction {
        margin-bottom: 0;
    }
}
</style>
