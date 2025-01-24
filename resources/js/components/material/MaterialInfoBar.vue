<script setup lang="ts">
import {useAuthStore} from '@/stores/auth'
import {getFullPresentableDate, getRelativeDate} from '@/helpers'
import {type Material} from '@/types'
import UserAvatar from '@/components/user/UserAvatar.vue'
import {computed, type PropType} from 'vue'
import ProfileLink from '@/components/elements/ProfileLink.vue'

const props = defineProps({
    material: {
        type: Object as PropType<Material>,
        required: true,
    }
})

const authStore = useAuthStore()

const wasMaterialUpdated = computed(() => props.material!.published_at !== props.material!.version!.published_at)
</script>

<template>
    <div class="material-info-bar flex">
        <ProfileLink
            class="author-wrap flex items-center w-fit border-0 sm:gap-0 gap-2"
            :user="material.state!.author"
        >
            <span class="flex justify-center items-center mr-2">
                <UserAvatar
                    border-class-list="md:h-10 md:w-10 h-8 w-8"
                    icon-class-list="md:h-7 md:w-7 h-6 w-6"
                    :user="material.state!.author!"
                />
            </span>
            <div class="date-action author flex flex-col sm:items-start items-center">
                <span class="date-action-subtitle sm:flex hidden" style="line-height: 1.4">Автор</span>
                <span style="line-height: 1.4">{{ material.state!.author?.username ?? 'Некто' }}</span>
            </div>
        </ProfileLink>

        <div v-if="wasMaterialUpdated" class="date-update flex items-center sm:gap-0 gap-2">
            <span class="icon-refresh icon flex mr-1"/>
            <div class="date-action flex flex-col">
                <span class="date-action-subtitle sm:flex hidden" style="line-height: 1.4">Обновлено</span>
                <span
                    class="flex xs:items-start items-center text-center sm:max-w-none"
                    v-tooltip.top="getFullPresentableDate(material.version!.published_at)"
                    style="line-height: 1.4"
                >
                    {{ getRelativeDate(material.version!.published_at) }}
                </span>
            </div>
        </div>

        <div class="date-publication flex items-center sm:gap-0 gap-2">
            <span class="icon-apple icon flex mr-1"/>
            <div class="date-action flex flex-col">
                <span class="date-action-subtitle sm:flex hidden" style="line-height: 1.4">Опубликовано</span>
                <span
                    class="flex xs:items-start items-center text-center sm:max-w-none"
                    v-tooltip.top="getFullPresentableDate(material.published_at)"
                    style="line-height: 1.4"
                >
                    {{ getRelativeDate(material.published_at) }}
                </span>
            </div>
        </div>

    </div>

</template>

<style scoped>
.material-info-bar span {
    font-size: 12px;
}

.date-action span {
    color: var(--secondary-text-color);
    align-items: center;
}

.date-action .date-action-subtitle {
    color: var(--trinity-text-color);
}

.tooltip::before {
    min-width: 200px;
}

/* =============== [ Медиа-Запрос { ?px < 601px } ] =============== */

@media screen and (max-width: 600px) {
    .author-wrap, .date-publication, .date-update {
        flex-direction: column;
    }
}

/* =============== [ Медиа-Запрос { ?px < 511px } ] =============== */

@media screen and (max-width: 510px) {
    .date-action.author {
        width: 100%;
    }

    .date-publication .tooltip::before {
        left: -.5rem;
    }
}
</style>
