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

const emit = defineEmits<{
    (e: 'page-change', event: PageChangeEvent): void
}>()

const maxPageNumber = computed(() => 15)
const currentPageNumber = ref(1)

const isOneLeftSwitching = ref(false)
const isOneRightSwitching = ref(false)
const isTwoLeftSwitching = ref(false)
const isTwoRightSwitching = ref(false)
const isPageChanging = ref(false)

function setOnePageStep(number: number) {
    if (isPageChanging.value) return
    isPageChanging.value = true
    if (currentPageNumber.value < number && currentPageNumber.value < maxPageNumber.value - 1) {
        isOneRightSwitching.value = true
    }
    else if (currentPageNumber.value > number && currentPageNumber.value > 2) {
        isOneLeftSwitching.value = true
    }
    pageChange(number)
}

function setPageNumberOneStep(number: number) {
    console.log('Какого-то хера вызываюсь именно я, а не 2.')
    if (isPageChanging.value) return
    isPageChanging.value = true
    if (currentPageNumber.value < number && currentPageNumber.value < maxPageNumber.value - 2) {
        isOneRightSwitching.value = true
    }
    else if (currentPageNumber.value > number && currentPageNumber.value > 3) {
        isOneLeftSwitching.value = true
    }
    pageChange(number)
}

function setPageNumberTwoSteps(number: number) {
    if (isPageChanging.value) return
    isPageChanging.value = true
    if (currentPageNumber.value < number && currentPageNumber.value < maxPageNumber.value - 2) {
        isTwoRightSwitching.value = true
    }
    else if (currentPageNumber.value > number && currentPageNumber.value > 3) {
        isTwoLeftSwitching.value = true
    }
    pageChange(number)
}

function pageChange(number: number) {
    setTimeout(() => {
        currentPageNumber.value = number
        emit('page-change', { pageNumber: currentPageNumber.value })
        isOneLeftSwitching.value = false
        isOneRightSwitching.value = false
        isTwoLeftSwitching.value = false
        isTwoRightSwitching.value = false
        isPageChanging.value = false
    }, 200)
}

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
            @click="maxPageNumber > 9 ? setPageNumberOneStep(currentPageNumber - 1) : setOnePageStep(currentPageNumber - 1)"
            :disabled="currentPageNumber < 2"
            type="button"
        >
            <span class="icon icon-left-arrow flex"/>
        </button>

        <div v-if="maxPageNumber > 9" class="flex justify-center xs:max-w-[240px] max-w-[200px] overflow-hidden">
            <div class="paginator-switching flex justify-center items-center"
                 :class="{
                    'paginator-one-left-switching': isOneLeftSwitching,
                    'paginator-one-right-switching': isOneRightSwitching,
                    'paginator-two-left-switching': isTwoLeftSwitching,
                    'paginator-two-right-switching': isTwoRightSwitching,
                    'one-left-end': currentPageNumber == 2,
                    'two-left-end': currentPageNumber == 1,
                    'one-right-end': maxPageNumber - currentPageNumber == 1,
                    'two-right-end': currentPageNumber == maxPageNumber
                }"
            >
                <button class="page-number flex" @click="setPageNumberTwoSteps(currentPageNumber - 4)">{{ currentPageNumber - 4 }}</button>
                <button class="page-number flex" @click="setPageNumberTwoSteps(currentPageNumber - 3)">{{ currentPageNumber - 3 }}</button>
                <button
                    class="page-number flex"
                    @click="setPageNumberTwoSteps(currentPageNumber - 2)"
                    :disabled="currentPageNumber < 2"
                    type="button"
                >
                    {{ currentPageNumber - 2 }}
                </button>
                <button
                    class="page-number flex"
                    :class="{ 'text-[var(--hover-text-color)]': isOneRightSwitching }"
                    @click="setPageNumberOneStep(currentPageNumber - 1)"
                    :disabled="currentPageNumber < 2"
                    type="button"
                >
                    {{ currentPageNumber - 1 }}
                </button>
                <button
                    class="page-number flex"
                    :class="{
                        'text-[var(--hover-text-color)]': !isOneLeftSwitching && !isOneRightSwitching &&
                        !isTwoLeftSwitching && !isTwoRightSwitching
                    }"
                    @click="pageChange(currentPageNumber)"
                >
                    {{ currentPageNumber }}
                </button>
                <button
                    class="page-number flex"
                    :class="{ 'text-[var(--hover-text-color)]': isOneLeftSwitching }"
                    @click="setPageNumberOneStep(currentPageNumber + 1)"
                    :disabled="currentPageNumber >= maxPageNumber"
                >
                    {{ currentPageNumber + 1 }}
                </button>
                <button
                    class="page-number flex"
                    :class="{ 'text-[var(--hover-text-color)]': isTwoLeftSwitching }"
                    @click="setPageNumberTwoSteps(currentPageNumber + 2)"
                    :disabled="currentPageNumber >= maxPageNumber"
                >
                    {{ currentPageNumber + 2 }}
                </button>
                <button class="page-number flex" @click="setPageNumberTwoSteps(currentPageNumber + 3)">{{ currentPageNumber + 3 }}</button>
                <button class="page-number flex" @click="setPageNumberTwoSteps(currentPageNumber + 4)">{{ currentPageNumber + 4 }}</button>
            </div>
        </div>

        <div v-else-if="maxPageNumber > 2 && maxPageNumber < 10" class="flex justify-center xs:max-w-[144px] max-w-[120px] overflow-hidden">
            <div class="paginator-switching flex justify-center items-center"
                 :class="{
                    'paginator-one-left-switching': isOneLeftSwitching,
                    'paginator-one-right-switching': isOneRightSwitching,
                    'one-left-end': currentPageNumber < 2,
                    'one-right-end': currentPageNumber >= maxPageNumber
                }"
            >
                <div class="page-number flex">{{ currentPageNumber - 2 }}</div>
                <button
                    class="page-number flex"
                    :class="{ 'text-[var(--hover-text-color)]': isOneRightSwitching }"
                    @click="setOnePageStep(currentPageNumber - 1)"
                    :disabled="currentPageNumber < 2"
                    type="button"
                >
                    {{ currentPageNumber - 1 }}
                </button>
                <button
                    class="page-number flex"
                    :class="{ 'text-[var(--hover-text-color)]': !isOneLeftSwitching && !isOneRightSwitching }"
                    @click="setOnePageStep(currentPageNumber)"
                >
                    {{ currentPageNumber }}
                </button>
                <button
                    class="page-number flex"
                    :class="{ 'text-[var(--hover-text-color)]': isOneLeftSwitching }"
                    @click="setOnePageStep(currentPageNumber + 1)"
                    :disabled="currentPageNumber >= maxPageNumber"
                >
                    {{ currentPageNumber + 1 }}
                </button>
                <div class="page-number flex">{{ currentPageNumber + 2 }}</div>
            </div>
        </div>

        <div v-else-if="maxPageNumber < 3" class="flex justify-center xs:max-w-[48px] max-w-[40px] overflow-hidden">
            <div class="paginator-switching flex justify-center items-center"
                 :class="{
                    'paginator-one-left-switching': isOneLeftSwitching,
                    'paginator-one-right-switching': isOneRightSwitching
                }"
            >
                <div class="page-number flex">1</div>
                <button
                    class="page-number flex text-[var(--hover-text-color)]"
                    @click="setPageNumberOneStep(currentPageNumber)"
                >
                    {{ currentPageNumber }}
                </button>
                <div class="page-number flex">2</div>
            </div>
        </div>

        <button
            class="flex"
            :class="{ 'disabled': currentPageNumber >= maxPageNumber }"
            @click="maxPageNumber > 9 ? setPageNumberOneStep(currentPageNumber + 1) : setOnePageStep(currentPageNumber + 1)"
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
