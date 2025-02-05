<script setup lang="ts">
import {computed, type PropType} from 'vue'
import {RouterLink} from 'vue-router'
import {getFullPresentableDate, getRelativeDate} from '@/helpers'
import useCategoryRegistry from '@/categoryRegistry'
import usePreferenceManager from '@/preference-manager'

import {type MaterialSubmission, MaterialSubmissionActionType as ActionType, UserRole} from '@/types'

import MaterialSubmissionAction from '@/components/material/MaterialSubmissionAction.vue'
import UserAvatar from '@/components/user/UserAvatar.vue'
import ProfileLink from '@/components/elements/ProfileLink.vue'

const props = defineProps({
    materialSubmission: {
        type: Object as PropType<MaterialSubmission>,
        required: true
    },
    showAuthor: {
        type: Boolean,
        default: true
    }
})

const preferenceManager = usePreferenceManager()

const wasUpdated = computed(() => props.materialSubmission!.updated_at !== props.materialSubmission!.created_at)
const actions = computed(() => props.materialSubmission!.actions!.sort((a, b) => a.created_at!.localeCompare(b.created_at!)))
const lastAction = computed(() => actions.value!.at(actions.value!.length - 1))
</script>

<template>
    <div class="material-submission-card ld-primary-border-top flex sm:flex-row flex-col">
        <div class="flex w-full gap-2 p-2">
            <RouterLink
                class="preview-wrap ld-primary-border flex
                    sm:h-[112px] sm:max-w-[196px] sm:min-w-[196px]
                    xs:h-[76px] xs:max-w-[132px] xs:min-w-[132px]
                    h-[58px] max-w-[100px] min-w-[100px]
                    relative overflow-hidden"
                :to="{ name: 'material-submission', params: {id: materialSubmission.id} }"
            >
                <div
                    v-if="preferenceManager.isMaterialCategoryInPreviewVisible()"
                    class="material-preview-category ld-dark-blur flex pr-2 py-1"
                >
                    <p class="type flex items-center text-[10px]">
                        <span
                            class="icon flex sm:h-[32px] sm:w-[32px] h-[24px] w-[24px] z-[1]"
                            :class="useCategoryRegistry().get(materialSubmission.material?.category).icon"
                        />
                        <span class="text-2xs sm:text-xs z-[1]">{{ useCategoryRegistry().get(materialSubmission.material.category).singularName }}</span>
                    </p>
                </div>
                <img alt="Превью" class="preview flex w-full full-locked duration-500" :src="materialSubmission.material_state!.localization!.cover_url">
            </RouterLink>
            <div class="description-wrap flex flex-col w-full">
                <RouterLink
                    class="material-title-wrap border-0"
                    :to="{ name: 'material-submission', params: {id: materialSubmission.id} }"
                >
                    <h1 class="material-title md:text-[14px] text-[12px]">
                        {{ materialSubmission.material_state.localization.title }}
                    </h1>
                </RouterLink>
                <p class="description md:flex hidden text-[10px] opacity-80 mb-0.5">
                    {{ materialSubmission.material_state.localization.description }}
                </p>
                <div class="flex flex-wrap items-center md:text-[12px] text-[10px] sm:gap-4 gap-2 mt-0.5">
                    <ProfileLink
                        class="author-wrap flex flex-wrap border-0 gap-1"
                        :user="materialSubmission.material_state!.author"
                    >
                        <UserAvatar
                            border-class-list="h-7 w-7"
                            icon-class-list="h-5 w-5"
                            :user="materialSubmission.material_state!.author"
                        />
                        <p class="author-username flex items-center">
                            {{ materialSubmission.material_state!.author?.username ?? 'Некто' }}
                        </p>
                    </ProfileLink>
                    <div v-if="!preferenceManager.isMaterialCategoryInPreviewVisible()" class="flex border-0 gap-1">
                        <span
                            class="icon flex"
                            :class="useCategoryRegistry().get(materialSubmission.material!.category).icon"
                        />
                        <p class="author-username flex items-center">
                            {{ useCategoryRegistry().get(materialSubmission.material!.category).singularName }}
                        </p>
                    </div>
                    <p
                        class="time-ago time flex items-center md:text-[10px] text-[8px] h-fit gap-1 locked tooltip whitespace-nowrap"
                        v-tooltip.top="(wasUpdated ? 'Обновлено ' : 'Создано ') + getFullPresentableDate(materialSubmission!.updated_at!)"
                    >
                        <span class="icon-clock icon flex"/>
                        <span class="opacity-80">{{ getRelativeDate(materialSubmission!.updated_at!) }}</span>
                    </p>
                </div>
                <MaterialSubmissionAction
                    v-if="[ActionType.ACCEPT, ActionType.REJECT, ActionType.REQUEST_CHANGES].includes(lastAction?.type!)"
                    class="sm-show w-full mt-0.5"
                    :action="lastAction"
                    :minimized="true"
                />
            </div>
        </div>
        <div class="sm-wrap flex xs:flex-row flex-col">
            <div
                class="flex justify-end items-center sm:w-fit w-full"
            >
                <MaterialSubmissionAction
                    v-if="[ActionType.ACCEPT, ActionType.REJECT, ActionType.REQUEST_CHANGES].includes(lastAction?.type!)"
                    class="sm-hidden w-full m-2"
                    :action="lastAction"
                    :minimized="true"
                />
                <div v-if="materialSubmission.assigned_moderator" class="flex relative m-2">
                    <span
                        :class="{
                            'icon-charoit-crown': materialSubmission.assigned_moderator?.role === UserRole.ADMIN,
                            'icon-emerald-dagger': materialSubmission.assigned_moderator?.role === UserRole.MODERATOR
                        }"
                        class="icon-role icon flex absolute
                            sm:h-8 h-6 sm:w-8 w-6 z-[1] sm:right-[-10px]
                            sm:top-[-10px] right-[-8px] top-[-8px]"
                    />
                    <UserAvatar
                        v-tooltip.top="`Закреплено за ${materialSubmission.assigned_moderator.username}`"
                        :user="materialSubmission.assigned_moderator"
                        class="moder"
                        border-class-list="sm:h-10 sm:w-10 h-7 w-7"
                        icon-class-list="sm:h-7 sm:w-7 h-5 w-5"
                    />
                </div>
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

.sm-show {
    display: none;
}

.sm-wrap {
    background: var(--tinted-bg-color);
}

/* =============== [ Медиа-Запрос { 640px < ?px } ] =============== */

@media screen and (min-width: 640px) {
    .sm-wrap {
        background: none;
    }

    .sm-hidden {
        display: none;
    }

    .sm-show {
        display: flex;
    }
}
</style>
