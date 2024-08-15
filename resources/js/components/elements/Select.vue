<script setup lang="ts">
import {computed, nextTick, type PropType, ref} from 'vue'
import ItemButton from '@/components/elements/ItemButton.vue'
import {absolutePosition, isElementInOverflowedContainer} from '@/helpers'

export interface SelectOption {
    label?: string
    icon?: string
}

const props = defineProps({
    id: String,
    icon: {
        type: String,
        default: 'icon-down-arrow'
    },
    alignRight: {
        type: Boolean,
        default: false
    },
    options: {
        type: Object as PropType<any[]>,
        required: true
    },
})

const model = defineModel<string>({default: ''})

defineExpose({
    show,
    hide,
    toggle,
})

const isVisible = ref(false)
const isContainerMouseDown = ref(false)
const container = ref<HTMLElement>()
const target = ref<HTMLElement>()

const useTargetPositionContext = computed(() => target.value && !isElementInOverflowedContainer(target.value))

function show(targetOrEvent: HTMLElement | Event) {
    if (targetOrEvent instanceof Event && !(targetOrEvent.currentTarget instanceof HTMLElement)) {
        return
    }

    target.value = targetOrEvent instanceof HTMLElement ? targetOrEvent : targetOrEvent.currentTarget as HTMLElement
    isVisible.value = true

    nextTick(() => {
        alignOverlay()
        addEventListener('mouseup', onDocumentMouseUp)
        addEventListener('resize', alignOverlay)
    })
}

function hide() {
    isVisible.value = false
    removeEventListener('mouseup', onDocumentMouseUp)
    removeEventListener('resize', alignOverlay)
}

function toggle(targetOrEvent: HTMLElement | Event) {
    isVisible.value ? hide() : show(targetOrEvent)
}

function alignOverlay() {
    absolutePosition(container!.value!, target.value!, useTargetPositionContext.value, props.alignRight)
}

function onDocumentMouseUp(event: MouseEvent) {
    if (!(event.target instanceof HTMLElement)) {
        return
    }

    const isContainerMouseUp = container.value?.contains(event.target)
    const isTargetMouseUp = target.value?.contains(event.target)
    const isOutsideClicked = !isContainerMouseDown.value && !isContainerMouseUp && !isTargetMouseUp

    if (isOutsideClicked) {
        hide()
    }
    isContainerMouseDown.value = false
}

function onOptionClick(option: SelectOption) {
    model.value = option
    if (option.action) {
        option.action()
    }


}

const currentValue = ref('')
const isSelectOpened = ref(false)

function setValue() {

}
</script>

<template>
    <div :class="{ 'opened': isSelectOpened }" class="select flex flex-col">
        <button @click="isSelectOpened = !isSelectOpened" class="select-button flex justify-between" type="button">
            <input
                class="current-option flex items-center w-full pl-6"
                v-model="model"
                @change="setValue"
                :id="id" type="text"
                placeholder="Выберите Категорию"
            >
            <span class="button-arrow flex justify-center items-center">
                <span class="icon icon-down-arrow flex"></span>
            </span>
        </button>
        <div class="options flex flex-col">
            <template v-for="option in props.options">

                <ItemButton
                    @click="onOptionClick"
                    :text="props.options[option]"
                    class="flex pl-6"
                />

            </template>
        </div>
    </div>
</template>

<style scoped>
.current-option {
    height: 72px;
}
.select .options {
    transition: .2s;
    opacity: 0;
    height: 0;
}
.select.opened .options {
    height: 72px;
    opacity: 1;
}
.select-button {
    cursor: pointer;
}
.select-button .button-arrow {
    min-width: 72px;
    height: 72px;
}
.select-button .icon {
    transition: .2s;
    height: 32px;
    width: 32px;
}
.select.opened .button-arrow .icon {
    transform: rotate(-180deg);
}
</style>
