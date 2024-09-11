<script setup lang="ts">
import type {PropType} from 'vue'
import {
    type PostVersionAction,
    type PostVersionActionAssignModerator,
    type PostVersionActionReject,
    type PostVersionActionRequestChanges,
    PostVersionActionType, UserRole
} from '@/types'
import {getFullDate, getRelativeDate} from '@/helpers'
import UserAvatar from '@/components/user/UserAvatar.vue'
const props = defineProps({
    action: {
        type: Object as PropType<PostVersionAction>,
        required: true
    },
    minimized: {
        type: Boolean,
        default: false
    }
})
</script>

<template>
    <div v-if="minimized" class="post-version-action-minimized minimized-action">
        <span v-if="action.type === PostVersionActionType.SUBMIT">
            <span class="icon-letter icon text-muted mr-1.5"/>
            <span>Отправлена заявка</span>
        </span>
        <span v-else>
            <span v-if="action.type === PostVersionActionType.ACCEPT">
                <span class="icon-tick icon flex text-confirm"/>
                <span>Принято</span>
            </span>
            <span v-else-if="action.type === PostVersionActionType.REJECT" class="flex items-center">
                <span class="icon-red-cross icon flex min-w-[2rem] mr-1"/>
                <span class="flex flex-col w-full">
                    <span>Отклонено: </span>
                    <span class="text-muted truncate ld-lightgray-text max-w-[90%] xl:max-w-[160px]">
                        {{ (action.details as PostVersionActionReject).reason }}
                    </span>
                </span>
            </span>
            <span v-else-if="action.type === PostVersionActionType.REQUEST_CHANGES" class="flex items-center">
                <span class="icon-white-rotate-left icon flex min-w-[2rem] mr-1"/>
                <span class="flex flex-col w-full">
                    <span>Возвращено на доработку: </span>
                    <span class="text-muted truncate ld-lightgray-text max-w-[90%] xl:max-w-[160px]">
                        {{ (action.details as PostVersionActionRequestChanges).message }}
                    </span>
                </span>
            </span>
            <span v-else-if="action.type === PostVersionActionType.ASSIGN_MODERATOR">
                <span class="icon-eye icon flex"/>
                Назначен Модератор
                <span class="text-[var(--primary-color)]">
                    {{ (action.details as PostVersionActionAssignModerator).moderator!.username}}
                </span>
            </span>
        </span>
    </div>
    <div v-else class="post-version-action flex flex-col xs:flex-row gap-1 xs:gap-2">

        <div class="flex xs:flex-row flex-col items-center w-full xs:gap-2 md:gap-4">

            <div class="flex items-center w-full gap-2">
                <div class="flex self-start gap-3">
                    <div v-if="action.type === PostVersionActionType.SUBMIT" class="relative">
                    <span
                        :class="{
                            'icon-charoit-crown': action.user?.role === UserRole.ADMIN,
                            'icon-emerald-dagger': action.user?.role === UserRole.MODERATOR
                        }"
                        class="icon-role icon flex absolute xs:h-8 h-6 xs:w-8 w-6 z-[1] xs:right-[-12px] xs:top-[-12px] right-[-8px] top-[-8px]"
                    />
                        <UserAvatar
                            border-class-list="xs:h-12 xs:w-12 h-9 w-9"
                            icon-class-list="xs:h-8 xs:w-8 h-6 w-6"
                            :user="action.user!"
                        />
                    </div>
                    <span
                        v-else
                        :class="{
                            'icon-charoit-crown': action.user?.role === UserRole.ADMIN,
                            'icon-emerald-dagger': action.user?.role === UserRole.MODERATOR
                        }"
                        class="icon text-[var(--surface-500)] xs:h-12 xs:w-12 h-8 w-8"
                    />
                </div>

                <div class="flex flex-col gap-2 w-full text-sm xs:text-base">
                    <div class="inline-block">
                    <span class="text-[var(--hover-text-color)]">
                        {{
                            action.user === undefined
                                ? 'Модератор'
                                : (action.user === null
                                        ? 'Удалённый Пользователь'
                                        : action.user!.username
                                )
                        }}
                    </span>

                        <span v-if="action.type === PostVersionActionType.SUBMIT"> отправил заявку на публикацию.</span>
                        <span v-else-if="action.type === PostVersionActionType.ACCEPT" class="text-confirm"> принял заявку.</span>
                        <span v-else-if="action.type === PostVersionActionType.REJECT" class="text-error">
                        отклонил заявку:
                    </span>
                        <span v-else-if="action.type === PostVersionActionType.REQUEST_CHANGES" class="text-revision">
                        вернул заявку на доработку:
                    </span>
                        <span v-else-if="action.type === PostVersionActionType.ASSIGN_MODERATOR">
                        назначил Модератора
                        <span class="text-[var(--hover-text-color)]">
                            {{ (action.details as PostVersionActionAssignModerator).moderator!.username }}
                        </span>
                        <span>.</span>
                    </span>
                    </div>

                    <p
                        v-if="action.type === PostVersionActionType.REJECT"
                        class="post-version-action-message px-3 py-2 w-full"
                    >
                        {{ (action.details as PostVersionActionReject).reason }}
                    </p>

                    <p
                        v-if="action.type === PostVersionActionType.REQUEST_CHANGES"
                        class="post-version-action-message px-3 py-2 w-full"
                    >
                        {{ (action.details as PostVersionActionRequestChanges).message }}
                    </p>
                </div>
            </div>

            <p
                :title="getFullDate(action.created_at!)"
                class="text-muted xs:text-[12px] text-[10px] text-center md:text-end md:whitespace-nowrap leading-6"
            >
                {{ getRelativeDate(action.created_at!) }}
            </p>
        </div>
    </div>
</template>

<style scoped>
.minimized-action {
    @apply text-sm line-clamp-2
}
.post-version-action-message,
.post-version-action span {
    font-size: 12px;
}

/* =============== [ Медиа-Запрос { ?px < 451px } ] =============== */

@media screen and (max-width: 450px) {
    .post-version-action-message,
    .post-version-action span {
        font-size: 10px;
    }
}
</style>
