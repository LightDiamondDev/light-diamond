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

const currentPageNumber = defineModel<number>({ default: 1 })
const maxPageNumber = computed(() => Math.ceil(props.totalRecords / props.recordsAtPage))

const pageRange = computed(() => {
    const rangeSize = Math.min(maxPageNumber.value, 5);
    let start = Math.max(currentPageNumber.value - Math.floor(rangeSize / 2), 1)
    let end = start + rangeSize - 1
    if (end > maxPageNumber.value) {
        end = maxPageNumber.value;
        start = Math.max(end - rangeSize + 1, 1);
    }
    return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})

</script>

<template>
    <div class="paginator flex justify-center items-center xs:text-[14px] text-[10px]">
        <button
            class="flex relative"
            :class="{ 'disabled': currentPageNumber < 2 }"
            @click="currentPageNumber = 1"
            :disabled="currentPageNumber < 2"
            type="button"
        >
            <span class="icon icon-left-arrow"/>
            <span class="icon icon-left-arrow absolute" style="right: .8rem"/>
        </button>
        <button
            class="flex"
            :class="{ 'disabled': currentPageNumber < 2 }"
            @click="currentPageNumber--"
            :disabled="currentPageNumber < 2"
            type="button"
        >
            <span class="icon icon-left-arrow flex"/>
        </button>

        <div class="flex justify-center overflow-hidden">
            <div class="paginator-switching flex justify-center items-center">
                <button
                    v-for="i in pageRange"
                    class="page-number flex"
                    :class="{ 'selector-color-animation': i === currentPageNumber }"
                    @click="currentPageNumber = i"
                >
                    {{ i }}
                </button>
            </div>
        </div>

        <button
            class="flex"
            :class="{ 'disabled': currentPageNumber >= maxPageNumber }"
            @click="currentPageNumber++"
            :disabled="currentPageNumber >= maxPageNumber"
            type="button"
        >
            <span class="icon icon-right-arrow flex max-w-[24px]"/>
        </button>
        <button
            class="flex relative"
            :class="{ 'disabled': currentPageNumber >= maxPageNumber }"
            @click="currentPageNumber = maxPageNumber"
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
</style>
