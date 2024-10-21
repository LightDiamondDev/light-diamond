<script setup lang="ts">
import {type PropType, ref} from 'vue'
import type {EditorMenuItem} from '@/components/elements/editor/types'
import type {MenuItem} from '@/components/elements/Menu.vue'
import Menu from '@/components/elements/Menu.vue'

defineProps({
    title: String,
    items: {
        type: Object as PropType<EditorMenuItem[]>,
        required: true
    }
})

const menu = ref<InstanceType<typeof Menu>>()

defineExpose({
    show,
    hide,
    toggle,
})

function convertToMenuItems(itemsToConvert: EditorMenuItem[]): MenuItem[] {
    return itemsToConvert.map((item) => ({
        separator: item.isSeparator,
        label: item.displayName,
        icon: item.icon,
        visible: item.isVisible,
        action: item.callback,
    }))
}

function show(event: Event) {
    menu.value?.show(event)
}

function hide() {
    menu.value?.hide()
}

function toggle(event: Event) {
    menu.value?.toggle(event)
}
</script>

<template>
    <Menu
        class="ld-primary-background ld-primary-border max-w-[100vw]"
        item-classes="ld-title-font md:h-[64px] h-[48px] gap-3 px-4"
        :items="convertToMenuItems(items)"
        @mousedown.prevent
        ref="menu"
    >
        <template #header v-if="title">
            <p class="p-2 text-muted md:text-[14px] text-[12px]">{{ title }}</p>
        </template>
    </Menu>
</template>

<style scoped>

</style>
