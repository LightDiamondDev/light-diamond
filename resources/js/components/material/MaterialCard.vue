<script setup lang="ts">
import {computed, type PropType} from 'vue'
import {type Material} from '@/types'

import {getFullPresentableDate, getRelativeDate} from '@/helpers'
import useCategoryRegistry from '../../categoryRegistry'

import UserAvatar from '@/components/user/UserAvatar.vue'
import MaterialActionBar from '@/components/material/MaterialActionBar.vue'
import ProfileLink from '@/components/elements/ProfileLink.vue'

const props = defineProps({
    isHorizontalDirection: {
        type: Boolean,
        default: false
    },
    material: {
        type: Object as PropType<Material>,
        required: true
    }
})

const materialRoute = computed(() => ({
    name: 'material',
    params: {
        edition: props.material!.edition?.toLowerCase(),
        category: useCategoryRegistry().get(props.material!.category)!.slug,
        slug: props.material!.slug
    }
}))

const wasMaterialUpdated = computed(() => props.material!.published_at !== props.material!.version!.published_at)
</script>

<template>
    <div
        class="material-card ld-primary-background flex"
        :class="{ 'flex-col': !isHorizontalDirection, 'horizontal md:flex-row flex-col': isHorizontalDirection }"
    >
        <div class="inner flex flex-grow"
             :class="{ 'flex-col': !isHorizontalDirection, 'xs:flex-row flex-col': isHorizontalDirection }">
            <RouterLink
                class="preview-wrap flex overflow-hidden"
                :to="materialRoute"
            >
                <img
                    alt="Превью Материала"
                    class="preview flex w-full full-locked duration-200"
                    :class="{ 'max-h-[288px]': isHorizontalDirection }"
                    :src="material.state!.localization!.cover_url"
                    style="aspect-ratio: 16/9; object-fit: cover"
                >
            </RouterLink>
            <div class="description-wrap flex flex-col flex-grow justify-between text-[12px] w-full">
                <div class="flex flex-col">
                    <RouterLink class="material-title-wrap ld-default-link transfusion p-2" :to="materialRoute">
                        <h2
                            class="material-title text-[15px] duration-300"
                            :class="{ 'sm:text-[15px] xs:text-[12px] text-[14px]': isHorizontalDirection }"
                        >
                            {{ material.state.localization.title }}
                        </h2>
                    </RouterLink>
                    <p
                        class="description ld-tinted-background flex text-[11px] p-2"
                        :class="{ 'md:flex hidden': isHorizontalDirection }"
                    >
                        {{ material.state.localization.description }}
                    </p>
                </div>
                <div class="flex flex-col">
                    <div
                        class="flex"
                        :class="{
                        'flex-wrap flex-row-reverse lg:justify-between justify-end items-center': isHorizontalDirection,
                        'flex-col': !isHorizontalDirection
                    }"
                    >
                        <div
                            class="material-info info flex flex-wrap justify-between px-2"
                            :class="{'sm:flex hidden w-full gap-8': isHorizontalDirection, 'gap-2': !isHorizontalDirection }"
                        >
                            <div class="types flex flex-wrap gap-3 opacity-80">
                                <p class="type flex items-center">
                                    <span class="icon flex" :class="useCategoryRegistry().get(material.category).icon"/>
                                    <span>{{ useCategoryRegistry().get(material.category).singularName }}</span>
                                </p>
                                <!--                                <p class="type flex items-center">16x16</p>-->
                                <!--                                <p class="type flex items-center">1.19+</p>-->
                            </div>
                            <!--
                            <div class="progress-bar flex h-[24px] w-[64px] relative">
                                <div class="progress flex items-center transfusion" style="width: 35%">
                                    <p class="text-[11px] absolute pl-1">35%</p>
                                </div>
                            </div>
                            -->
                        </div>
                        <div
                            class="author-info info flex justify-between p-2"
                            :class="{ 'sm:flex-row xs:flex-col flex-row w-full sm:gap-8 gap-2': isHorizontalDirection }"
                        >
                            <ProfileLink
                                class="author-wrap flex flex-wrap gap-1"
                                :user="material.state!.author"
                            >
                                <UserAvatar
                                    border-class-list="h-10 w-10"
                                    icon-class-list="h-7 w-7"
                                    :user="material.state!.author"
                                />
                                <p class="author-username ld-default-link flex items-center duration-200">
                                    {{ material.state.author.username ?? 'Некто' }}
                                </p>
                            </ProfileLink>
                            <p
                                v-tooltip="`${wasMaterialUpdated ? 'Обновлено' : 'Опубликовано'} ${getFullPresentableDate(material.version!.published_at)}`"
                                class="type ago flex items-center text-end opacity-80 cursor-pointer gap-1 ml-1"
                            >
                                <span class="icon-clock icon flex"/>
                                <span class="xs:w-auto w-[60px]">
                                    {{
                                        getRelativeDate(material.version!.published_at)
                                    }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="menu-separator flex self-center w-[95%]"
                         :class="{ 'md:flex hidden w-[98%]': isHorizontalDirection }"/>
                    <div
                        class="actions ld-primary-background-container flex gap-2 p-2 overflow-x-auto overflow-y-hidden"
                        :class="{ 'md:flex hidden': isHorizontalDirection }"
                        style="scrollbar-width: thin"
                    >
                        <MaterialActionBar class="xs:gap-4 gap-2" :material="material"/>
                    </div>
                </div>
            </div>
        </div>
        <div
            v-if="isHorizontalDirection"
            class="actions inner ld-primary-background ld-primary-background-container ld-tinted-background ld-primary-border-bottom
            ld-primary-border-right ld-primary-border-left gap-2 p-2 overflow-x-auto overflow-y-hidden md:hidden flex"
            style="scrollbar-width: thin"
        >
            <MaterialActionBar class="xs:gap-4 gap-2" :material="material"/>
        </div>
    </div>
</template>

<style scoped>
.tooltip::before {
    height: 3.5rem;
    width: 5rem;
    left: 4.5rem;
}

.tooltip::after {
    display: none;
}

.horizontal {
    min-width: 100%;
}

.preview-wrap:focus-visible .preview,
.material-card:hover .preview {
    transform: scale(1.1);
}

.material-card .inner {
    transition: .2s;
}

.paginator-refresh .material-card .inner {
    opacity: .5;
}
</style>
