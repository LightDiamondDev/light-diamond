<script setup lang="ts">
import type {PropType} from 'vue'
import {
    type MaterialSubmissionAction,
    type MaterialSubmissionActionAssignModerator, type MaterialSubmissionActionMessage,
    type MaterialSubmissionActionReject,
    type MaterialSubmissionActionRequestChanges,
    MaterialSubmissionActionType,
    UserRole
} from '@/types'
import {getFullPresentableDate, getRelativeDate} from '@/helpers'
import UserAvatar from '@/components/user/UserAvatar.vue'
import ProfileLink from '@/components/elements/ProfileLink.vue'

const props = defineProps({
    action: {
        type: Object as PropType<MaterialSubmissionAction>,
        required: true
    },
    minimized: {
        type: Boolean,
        default: false
    }
})
</script>

<template>
    <div v-if="minimized" class="material-submission-action-minimized minimized-action">
        <span v-if="action.type === MaterialSubmissionActionType.SUBMIT">
            <span class="icon-letter icon text-muted mr-1.5"/>
            <span>Отправлена заявка</span>
        </span>
        <span v-else>
            <span v-if="action.type === MaterialSubmissionActionType.ACCEPT"
                  class="flex items-center md:text-[12px] text-[10px]">
                <span class="icon-round-tick icon flex text-confirm mr-1"/>
                <span>Принято</span>
            </span>
            <span v-else-if="action.type === MaterialSubmissionActionType.REJECT"
                  class="line-height-small flex items-center md:text-[12px] text-[10px]">
                <span class="icon-round-cross icon flex min-w-[2rem] mr-1"/>
                <span class="flex flex-col w-full">
                    <span class="hot-subtitle">Отклонено: </span>
                    <span class="text-muted truncate ld-lightgray-text max-w-[90%]">
                        {{ (action.details as MaterialSubmissionActionReject).reason }}
                    </span>
                </span>
            </span>
            <span v-else-if="action.type === MaterialSubmissionActionType.REQUEST_CHANGES" class="flex items-center">
                <span class="icon-round-eye icon flex min-w-[2rem] mr-1"/>
                <span class="flex flex-col w-full md:text-[12px] text-[10px]">
                    <span class="hot-subtitle">Возвращено на доработку: </span>
                    <span class="text-muted truncate ld-lightgray-text max-w-[90%] xl:max-w-[160px]">
                        {{ (action.details as MaterialSubmissionActionRequestChanges).message }}
                    </span>
                </span>
            </span>
            <span v-else-if="action.type === MaterialSubmissionActionType.ASSIGN_MODERATOR">
                <span class="icon-eye icon flex"/>
                Назначен Модератор
                <span class="text-[var(--primary-color)]">
                    {{
                        (action.details as MaterialSubmissionActionAssignModerator).moderator ?
                            (action.details as MaterialSubmissionActionAssignModerator).moderator!.username :
                            'Некто'
                    }}
                </span>
            </span>
        </span>
    </div>
    <div v-else class="material-submission-action flex flex-col xs:flex-row gap-1 xs:gap-2">

        <div class="flex xs:flex-row flex-col-reverse items-center w-full xs:gap-2 md:gap-4">

            <div class="flex items-center w-full gap-2">
                <div class="flex self-start gap-3">
                    <div v-if="action.user" class="relative">
                        <span
                            :class="{
                                'icon-charoit-crown': action.user?.role === UserRole.ADMIN,
                                'icon-emerald-dagger': action.user?.role === UserRole.MODERATOR
                            }"
                            class="icon-role icon flex absolute
                                xs:h-8 h-6 xs:w-8 w-6 z-[1] xs:right-[-12px]
                                xs:top-[-12px] right-[-8px] top-[-8px]"
                        />
                        <UserAvatar
                            border-class-list="h-10 w-10"
                            icon-class-list="h-7 w-7"
                            :user="action.user"
                        />
                    </div>
                    <span
                        v-else-if="action.type !== MaterialSubmissionActionType.SUBMIT"
                        :class="{
                            'icon-charoit-crown': action.user?.role === UserRole.ADMIN,
                            'icon-emerald-dagger': action.user?.role === UserRole.MODERATOR || !action.user
                        }"
                        class="icon text-[var(--surface-500)] xs:h-12 xs:w-12 h-8 w-8"
                    />
                </div>

                <div class="flex flex-col gap-2 w-full text-sm xs:text-base">
                    <div class="inline-block">
                    <span class="text-[var(--hover-text-color)]">
                        <ProfileLink :user="action.user">
                            {{
                                action.user === undefined
                                    ? 'Модератор'
                                    : (action.user === null
                                            ? 'Некто'
                                            : action.user!.username
                                    )
                            }}
                        </ProfileLink>
                    </span>
                        <span
                            v-if="action.type === MaterialSubmissionActionType.SUBMIT"> отправил заявку на публикацию.</span>
                        <span v-else-if="action.type === MaterialSubmissionActionType.ACCEPT" class="text-confirm"> принял заявку.</span>
                        <span v-else-if="action.type === MaterialSubmissionActionType.REJECT" class="text-error">
                        отклонил заявку:
                    </span>
                        <span v-else-if="action.type === MaterialSubmissionActionType.REQUEST_CHANGES"
                              class="text-revision">
                        вернул заявку на доработку:
                    </span>
                        <span v-else-if="action.type === MaterialSubmissionActionType.ASSIGN_MODERATOR">
                    назначил модератора
                        <span class="text-[var(--hover-text-color)]">
                            {{
                                (action.details as MaterialSubmissionActionAssignModerator).moderator ?
                                    (action.details as MaterialSubmissionActionAssignModerator).moderator.username :
                                    'Некто'
                            }}
                        </span>
                        <span>.</span>
                    </span>
                    </div>

                    <p
                        v-if="action.type === MaterialSubmissionActionType.REJECT"
                        class="material-submission-action-message px-3 py-2 max-w-[480px] w-full"
                    >
                        {{ (action.details as MaterialSubmissionActionReject).reason }}
                    </p>

                    <p
                        v-if="action.type === MaterialSubmissionActionType.REQUEST_CHANGES"
                        class="material-submission-action-message px-3 py-2 max-w-[480px] w-full"
                    >
                        {{ (action.details as MaterialSubmissionActionRequestChanges).message }}
                    </p>

                    <p
                        v-if="action.type === MaterialSubmissionActionType.MESSAGE"
                        class="material-submission-action-message px-3 py-2 max-w-[480px] w-full"
                    >
                        {{ (action.details as MaterialSubmissionActionMessage).message }}
                    </p>
                </div>
            </div>

            <p
                class="text-muted xs:text-[12px] text-[10px]
                    text-center md:text-end md:whitespace-nowrap leading-6
                    ld-lightgray-text"
                v-tooltip.top="getFullPresentableDate(action.created_at!)"
            >
                {{ getRelativeDate(action.created_at!) }}
            </p>
        </div>
    </div>
</template>

<style scoped>
.ld-secondary-background-action .hot-subtitle {
    color: var(--secondary-text-color);
}

.ld-secondary-background-action .ld-lightgray-text {
    color: var(--trinity-text-color);
}

.minimized-action {
    @apply text-sm line-clamp-2
}

.material-submission-action-message,
.material-submission-action span {
    font-size: 12px;
}

.tooltip::before {
    height: 2rem;
    left: -2rem;
}

.line-height-small {
    line-height: 1.2;
}

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px) {
    .tooltip::before {
        min-width: 200px;
        left: -4rem;
    }
}

/* =============== [ Медиа-Запрос { ?px < 451px } ] =============== */

@media screen and (max-width: 450px) {
    .material-submission-action-message,
    .material-submission-action span {
        font-size: 10px;
    }

    .tooltip::before {
        left: 0;
    }
}
</style>
