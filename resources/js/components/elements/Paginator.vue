<script setup lang="ts">
import {computed, ref} from 'vue'

export interface PageChangeEvent {
    pageNumber: number
}

const props = defineProps({
    totalRecords: {
        type: Number,
        required: true
    },
    recordsAtPage: {
        type: Number,
        required: true
    }
})

const model = defineModel<number>({default: 1})
// HACK: computed `pageRange` cannot be updated immediately when the model changes,
// so we had to add another variable of the current page number.
const currentPageNumber = ref(model.value)
const maxPageNumber = computed(() => Math.ceil(props.totalRecords / props.recordsAtPage))

const pageRange = computed(() => {
    const rangeSize = Math.min(maxPageNumber.value, 5)
    let start = Math.max(currentPageNumber.value - Math.floor(rangeSize / 2), 1)
    let end = start + rangeSize - 1
    if (end > maxPageNumber.value) {
        end = maxPageNumber.value
        start = Math.max(end - rangeSize + 1, 1)
    }
    return Array.from({length: end - start + 1}, (_, i) => start + i)
})

enum PaginatorTransition {
    SLIDE_RIGHT = 'slide-right',
    SLIDE_LEFT = 'slide-left',
}

const transition = ref<PaginatorTransition>(PaginatorTransition.SLIDE_RIGHT)
const isTransitionEnabled = ref(true)

function changePage(pageNumber: number) {
    const oldFirstPageRangeNumber = pageRange.value[0]

    transition.value = pageNumber > currentPageNumber.value
        ? PaginatorTransition.SLIDE_RIGHT
        : PaginatorTransition.SLIDE_LEFT

    currentPageNumber.value = pageNumber
    model.value = pageNumber
    isTransitionEnabled.value = pageRange.value[0] !== oldFirstPageRangeNumber
}
</script>

<template>
    <div class="paginator flex justify-center items-center xs:text-[14px] text-[10px]">
        <button
            class="flex relative"
            :class="{ 'disabled': currentPageNumber < 2 }"
            @click="changePage(1)"
            :disabled="currentPageNumber < 2"
            type="button"
        >
            <span class="icon icon-left-arrow"/>
            <span class="icon icon-left-arrow absolute" style="right: .8rem"/>
        </button>
        <button
            class="flex"
            :class="{ 'disabled': currentPageNumber < 2 }"
            @click="changePage(currentPageNumber - 1)"
            :disabled="currentPageNumber < 2"
            type="button"
        >
            <span class="icon icon-left-arrow flex"/>
        </button>

        <div class="flex justify-center overflow-hidden">
            <TransitionGroup :name="transition" :css="isTransitionEnabled">
                <template v-for="item in [pageRange]" :key="item">
                    <div class="paginator-switching flex justify-center items-center">
                        <button
                            v-for="i in pageRange"
                            :key="i + Math.random()"
                            class="page-number flex"
                            :class="{ 'selector-color-animation': i === currentPageNumber }"
                            @click="changePage(i)"
                        >
                            {{ i }}
                        </button>
                    </div>
                </template>
            </TransitionGroup>
        </div>

        <button
            class="flex"
            :class="{ 'disabled': currentPageNumber >= maxPageNumber }"
            @click="changePage(currentPageNumber + 1)"
            :disabled="currentPageNumber >= maxPageNumber"
            type="button"
        >
            <span class="icon icon-right-arrow flex max-w-[24px]"/>
        </button>
        <button
            class="flex relative"
            :class="{ 'disabled': currentPageNumber >= maxPageNumber }"
            @click="changePage(maxPageNumber)"
            :disabled="currentPageNumber >= maxPageNumber"
            type="button"
        >
            <span class="icon icon-right-arrow absolute" style="left: .8rem"/>
            <span class="icon icon-right-arrow"/>
        </button>
    </div>
</template>

<style scoped>
.page-number {
    justify-content: center;
    min-width: 48px;
}

.icon {
    min-width: 24px;
    height: 24px;
}

.disabled {
    opacity: .5;
}

.paginator-switching {
    transform: translateX(0);
}

.paginator-one-left-switching {
    transform: translateX(48px);
    transition: .2s;
}

.paginator-one-right-switching {
    transform: translateX(-48px);
    transition: .2s;
}

.paginator-two-left-switching {
    transform: translateX(96px);
    transition: .2s;
}

.paginator-two-right-switching {
    transform: translateX(-96px);
    transition: .2s;
}

.paginator-switching.one-left-end {
    transform: translateX(-48px);
    transition: 0s;
}

.paginator-switching.one-right-end {
    transform: translateX(48px);
    transition: 0s;
}

.paginator-switching.two-left-end {
    transform: translateX(-96px);
    transition: 0s;
}

.paginator-switching.two-right-end {
    transform: translateX(96px);
    transition: 0s;
}

/* =============== [ Медиа-Запрос { ?px < 425px } ] =============== */

@media screen and (max-width: 424px) {
    .page-number {
        min-width: 40px;
    }

    .paginator-one-left-switching,
    .paginator-switching.one-right-end {
        transform: translateX(40px);
    }

    .paginator-one-right-switching,
    .paginator-switching.one-left-end {
        transform: translateX(-40px);
    }

    .paginator-two-left-switching,
    .paginator-switching.two-right-end {
        transform: translateX(80px);
    }

    .paginator-two-right-switching,
    .paginator-switching.two-left-end {
        transform: translateX(-80px);
    }
}

.slide-right-enter-active, .slide-right-leave-active,
.slide-left-enter-active, .slide-left-leave-active {
    transition: transform 0.5s ease;
}

.slide-right-leave-active, .slide-left-leave-active {
    position: absolute;
}

.slide-right-enter-from {
    transform: translateX(48px);
}

.slide-right-leave-to {
    opacity: 0;
    transform: translateX(-48px);
}

.slide-left-enter-from {
    transform: translateX(-48px); /* Начальная позиция для входа */
}

.slide-left-leave-to {
    transform: translateX(48px); /* Позиция для ухода */
    opacity: 0;
}

</style>
