<script setup lang="ts">
import type {PropType} from 'vue'
import {
    type PostVersionAction,
    type PostVersionActionAssignModerator,
    type PostVersionActionReject,
    type PostVersionActionRequestChanges,
    PostVersionActionType
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
                <span class="icon-tick icon text-confirm"/>
                <span>Принято</span>
            </span>
            <span v-else-if="action.type === PostVersionActionType.REJECT">
                <span class="icon-small-cross icon"/>
                <span>Отклонено: </span>
                <span class="text-muted">{{ (action.details as PostVersionActionReject).reason }}</span>
            </span>
            <span v-else-if="action.type === PostVersionActionType.REQUEST_CHANGES">
                <span class="icon-refresh icon"/>
                <span>Возвращено на доработку: </span>
                <span class="text-muted">{{ (action.details as PostVersionActionRequestChanges).message }}</span>
            </span>
            <span v-else-if="action.type === PostVersionActionType.ASSIGN_MODERATOR">
                <span class="icon-eye icon"/>
                Назначен Модератор
                <span class="text-[var(--primary-color)]">
                    {{ (action.details as PostVersionActionAssignModerator).moderator!.username}}
                </span>
            </span>
        </span>
    </div>
    <div v-else class="post-version-action flex flex-col xs:flex-row gap-1 xs:gap-2">

        <div class="flex items-center w-full gap-2 md:gap-4">
            <div class="flex self-start gap-3">
                <UserAvatar v-if="action.type === PostVersionActionType.SUBMIT" :user="action.user!"/>
                <span
                    v-else
                    class="icon-eye icon text-[var(--surface-500)] m-2"
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
            <p
                :title="getFullDate(action.created_at!)"
                class="text-muted text-xs text-center md:text-end md:whitespace-nowrap leading-6"
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
    font-size: 14px;
}

/* =============== [ Медиа-Запрос { ?px < 451px } ] =============== */

@media screen and (max-width: 450px) {
    .post-version-action-message,
    .post-version-action span {
        font-size: 12px;
    }
}
</style>
