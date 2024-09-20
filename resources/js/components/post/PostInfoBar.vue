<script setup lang="ts">
import {useAuthStore} from '@/stores/auth'
import {getFullPresentableDate, getRelativeDate} from '@/helpers'
import {type Post} from '@/types'

import UserAvatar from '@/components/user/UserAvatar.vue'
import {type PropType, ref} from 'vue'

const props = defineProps({
    post: {
        type: Object as PropType<Post>,
        required: true,
    }
})

const authStore = useAuthStore()
</script>

<template>
    <div class="post-info-bar flex">

        <RouterLink class="author-wrap flex items-center w-fit border-0 sm:gap-0 gap-2 order-1" :to="{name: 'home'}">
            <span class="flex justify-center items-center mr-2">
                <UserAvatar
                    border-class-list="md:h-10 md:w-10 h-8 w-8"
                    icon-class-list="md:h-7 md:w-7 h-6 w-6"
                    :user="post.version!.author!"
                />
            </span>
            <div class="date-action author flex flex-col sm:items-start items-center">
                <span class="date-action-subtitle sm:flex hidden">Автор</span>
                <span>{{ post.version!.author!.username }}</span>
            </div>
        </RouterLink>

        <div v-if="post.updated_at === post.created_at" class="date-update flex items-center sm:gap-0 gap-2 xl:order-3 order-2">
            <span class="icon-refresh icon flex mr-1"/>
            <div class="date-action flex flex-col">
                <span class="date-action-subtitle sm:flex hidden">Обновлено</span>
                <span class="flex xs:items-start items-center text-center sm:max-w-none max-w-[90px]"
                      v-tooltip.top="getFullPresentableDate(post.created_at)"
                >
                    {{ getRelativeDate(post.updated_at) }}
                </span>
            </div>
        </div>

        <div class="date-publication flex items-center sm:gap-0 gap-2 xl:order-2 order-3">
            <span class="icon-apple icon flex mr-1"/>
            <div class="date-action flex flex-col">
                <span class="date-action-subtitle sm:flex hidden">Опубликовано</span>
                <span class="flex xs:items-start items-center text-center sm:max-w-none max-w-[90px]"
                      v-tooltip.top="getFullPresentableDate(post.updated_at)"
                >
                    {{ getRelativeDate(post.created_at) }}
                </span>
            </div>
        </div>

    </div>
</template>

<style scoped>
.post-info-bar span {
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
