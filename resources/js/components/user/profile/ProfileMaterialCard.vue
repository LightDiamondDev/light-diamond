<script setup lang="ts">
import {computed, type PropType} from 'vue'
import {RouterLink} from 'vue-router'
import {getFullPresentableDate, getRelativeDate} from '@/helpers'
import useCategoryRegistry from '../../../categoryRegistry'

import {type Material} from '@/types'

import UserAvatar from '@/components/user/UserAvatar.vue'
import MaterialActionBar from '@/components/material/MaterialActionBar.vue'
import ProfileLink from '@/components/elements/ProfileLink.vue'

const props = defineProps({
    material: {
        type: Object as PropType<Material>,
        required: true
    },
    showAuthor: {
        type: Boolean,
        default: true
    }
})

const wasMaterialUpdated = computed(() => props.material!.published_at !== props.material!.version!.published_at)

const materialRoute = computed(() => ({
    name: 'material',
    params: {
        edition: props.material!.edition?.toLowerCase(),
        category: useCategoryRegistry().get(props.material!.category)!.slug,
        slug: props.material!.slug
    }
}))
</script>

<template>
    <div class="material-submission-card flex md:flex-row flex-col"
         style="border-bottom: 2px solid rgba(255, 255, 255, .5)">
        <div class="flex w-full gap-2 py-2">
            <RouterLink
                class="preview-wrap ld-primary-border flex
                    sm:h-[112px] sm:max-w-[196px] sm:min-w-[196px]
                    xs:h-[76px] xs:max-w-[132px] xs:min-w-[132px]
                    h-[58px] max-w-[100px] min-w-[100px]
                    overflow-hidden"
                :to="materialRoute"
            >
                <img alt="" class="preview flex w-full full-locked duration-500"
                     :src="material.state!.localization!.cover_url">
            </RouterLink>
            <div class="description-wrap flex flex-col w-full">
                <RouterLink class="material-title-wrap border-0" :to="materialRoute">
                    <h1 class="material-title md:text-[14px] text-[12px]">{{ material.state.localization.title }}</h1>
                </RouterLink>
                <p class="description md:flex hidden text-[10px] opacity-80 mb-0.5">
                    {{ material.state.localization.description }}</p>
                <div class="flex flex-wrap items-center md:text-[12px] text-[10px] sm:gap-4 gap-2 mt-0.5">
                    <ProfileLink
                        v-if="material.state!.author"
                        class="author-wrap flex flex-wrap border-0 gap-1"
                        :user="material.state!.author"
                    >
                        <UserAvatar
                            border-class-list="h-7 w-7"
                            icon-class-list="h-5 w-5"
                            :user="material.state!.author"
                        />
                        <p class="author-username flex items-center">{{ material.state.author.username }}</p>
                    </ProfileLink>
                    <div v-else class="author-wrap flex flex-wrap border-0 gap-1">
                        <span class="icon-border-profile icon flex"/>
                        <p class="author-username flex items-center">Некто</p>
                    </div>
                    <div class="xs:flex hidden border-0 gap-1">
                        <span class="icon flex" :class="useCategoryRegistry().get(material.category).icon"/>
                        <p class="author-username flex items-center">{{
                                useCategoryRegistry().get(material.category).singularName
                            }}</p>
                    </div>
                    <p
                        class="time-ago time xs:flex hidden items-center md:text-[10px] text-[8px] h-fit gap-1 locked tooltip whitespace-nowrap"
                        v-tooltip.top="(wasMaterialUpdated ? 'Обновлено ' : 'Создано ') + getFullPresentableDate(material.version!.published_at)"
                    >
                        <span class="icon-clock icon flex"/>
                        <span class="opacity-80">{{ getRelativeDate(material.version!.published_at) }}</span>
                    </p>
                </div>
                <MaterialActionBar class="ld-secondary-background-container sm:flex hidden gap-4 mt-2"
                                   :material="material"/>
            </div>
        </div>
        <div class="xs:hidden flex justify-between md:mx-0 mb-1.5 mx-[6px]">
            <div class="flex items-center text-[12px] border-0 gap-1">
                <span class="icon flex" :class="useCategoryRegistry().get(material.category).icon"/>
                <p class="author-username flex items-center">{{
                        useCategoryRegistry().get(material.category).singularName
                    }}</p>
            </div>
            <p
                class="time-ago time flex items-center md:text-[10px] text-[8px] h-fit gap-1 locked tooltip whitespace-nowrap"
                v-tooltip.top="(wasMaterialUpdated ? 'Обновлено ' : 'Создано ') + getFullPresentableDate(material!.version!.published_at!)"
            >
                <span class="icon-clock icon flex"/>
                <span class="opacity-80">{{ getRelativeDate(material!.version!.published_at) }}</span>
            </p>
        </div>
        <div
            class="sm-wrap sm:hidden flex xs:flex-row flex-col p-1 overflow-x-auto overflow-y-hidden"
            style="scrollbar-width: thin"
        >
            <div class="flex justify-between items-center sm:w-fit w-full">
                <MaterialActionBar class="ld-secondary-background-container gap-4" :material="material"/>
            </div>
        </div>
    </div>
</template>

<style>
.tooltip::before {
    top: -2rem;
}

.moder.tooltip::before {
    height: 2.5rem;
}
</style>

<style scoped>
.preview-wrap,
.preview {
    object-position: center;
    aspect-ratio: 16/9;
    object-fit: cover;
}

.material-submission-card:hover .preview {
    transform: scale(1.2);
}
</style>
