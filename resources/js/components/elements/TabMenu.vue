<script setup lang="ts">
import {computed, type PropType, ref} from 'vue'
import type {RouteLocationRaw} from 'vue-router'
import ItemButton from '@/components/elements/ItemButton.vue'

export interface TabMenuItem {
    label?: string
    icon?: string
    visible?: boolean
    separator?: boolean
    route?: RouteLocationRaw
    action?: () => void
    closesMenu?: boolean
}

export interface TabMenuChangeEvent {
    tabIndex: number
}

const props = defineProps({
    items: {
        type: Object as PropType<TabMenuItem[]>,
        required: true
    },
    itemClasses: String,
    menuClasses: String
})

const emit = defineEmits<{
    (e: 'tab-change'): TabMenuChangeEvent
}>()

const visibleItems = computed(() => props.items!.filter((item) => item.visible || item.visible === undefined))

</script>

<template>
    <template v-for="item in visibleItems">
        <div v-if="item.separator" class="menu-separator self-center w-[90%]"/>
        <ItemButton
            v-else
            :as="item.route ? 'RouterLink' : 'button'"
            @click="emit('tab-change', { tabIndex: visibleItems.indexOf(item) })"
            :label="item.label"
            :icon="item.icon"
            :to="item.route"
            :class="itemClasses"
        />
    </template>
</template>

<style scoped>

</style>

