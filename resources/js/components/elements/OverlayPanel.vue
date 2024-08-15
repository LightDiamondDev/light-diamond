<script setup lang="ts">
import {computed, nextTick, ref} from 'vue'
import {absolutePosition, isElementInOverflowedContainer} from '@/helpers'

const props = defineProps({
    alignRight: {
        type: Boolean,
        default: false
    },
    class: {
        type: String,
        default: ''
    }
})

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
</script>

<template>
    <Teleport :to="useTargetPositionContext ? target!.parentElement : 'body'">
        <Transition name="smooth-opacity">
            <div
                v-if="isVisible && target"
                @mousedown="isContainerMouseDown = true"
                class="menu absolute z-0 flex flex-col overflow-auto"
                :class="props.class"
                ref="container"
            >
                <slot/>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>

</style>
