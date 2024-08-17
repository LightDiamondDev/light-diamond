<script setup lang="ts">
import {computed, type PropType, ref} from 'vue'
import type {RouteLocationRaw} from 'vue-router'
import ItemButton from '@/components/elements/ItemButton.vue'
import OverlayPanel from '@/components/elements/OverlayPanel.vue'

export interface MenuItem {
    label?: string
    icon?: string
    visible?: boolean
    separator?: boolean
    route?: RouteLocationRaw
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
    }
})

defineExpose({
    show,
    hide,
    toggle,
})

const overlayPanel = ref<InstanceType<typeof OverlayPanel>>()

const visibleItems = computed(() => props.items!.filter((item) => item.visible || item.visible === undefined))

function show(targetOrEvent: HTMLElement | Event) {
    overlayPanel.value?.show(targetOrEvent)
}

function hide() {
    overlayPanel.value?.hide()
}

function toggle(targetOrEvent: HTMLElement | Event) {
    overlayPanel.value?.toggle(targetOrEvent)
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
    <OverlayPanel
        ref="overlayPanel"
        :align-right="alignRight"
        :class="props.class"
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
                class="pl-6 pr-4 lg:text-[1.1rem]"
            />

        </template>
    </OverlayPanel>
</template>

<style scoped>

</style>
