<script setup lang="ts">
import {computed, nextTick, type PropType, ref} from 'vue'
import {absolutePosition, isElementInOverflowedContainer} from '@/helpers'
import type {RouteLocation} from 'vue-router'
import ItemButton from '@/components/elements/ItemButton.vue'

export interface MenuItem {
    label?: string
    icon?: string
    visible?: boolean
    separator?: boolean
    route?: RouteLocation
    action?: () => void
    closesMenu?: boolean
}

const props = defineProps({
    items: {
        type: Object as PropType<MenuItem[]>,
        required: true
    },
    alignRight: {
        type: Boolean,
        default: false
    },
    class: {
        type: String,
        default: ''
    },
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
const visibleItems = computed(() => props.items!.filter((item) => item.visible || item.visible === undefined))

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

function onItemClick(item: MenuItem) {
    if (item.action) {
        item.action()
    }

    if (item.closesMenu || item.closesMenu === undefined) {
        hide()
    }
}
</script>

<template>
    <Teleport :to="useTargetPositionContext ? target.parentElement : 'body'">
        <Transition name="smooth-opacity">
            <div
                v-if="isVisible && target"
                ref="container"
                class="menu absolute z-0 flex flex-col overflow-auto"
                :class="props.class"
                @mousedown="isContainerMouseDown = true"
            >
                <slot name="header"/>

                <template v-for="item in visibleItems">

                    <div v-if="item.separator" class="menu-separator self-center w-[90%]"/>

                    <ItemButton
                        v-else
                        :as="item.route ? 'RouterLink' : 'button'"
                        @click="onItemClick(item)"
                        :text="item.label"
                        :icon="item.icon"
                        :to="item.route"
                        class="pl-6 pr-4"
                    />

                </template>

            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.smooth-opacity-enter-active, .smooth-opacity-leave-active {
    transition: all 0.15s ease-in-out;
}

.smooth-opacity-enter-from, .smooth-opacity-leave-to {
    opacity: 0;
}

.user-menu .menu-item:focus-visible .icon,
.user-menu .menu-item:hover .icon {
    animation: icon-trigger-up-animation .3s ease;
}
</style>
