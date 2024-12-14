<script setup lang="ts">
import {computed, type PropType, ref, watch} from 'vue'
import {type RouteLocationRaw, useRoute} from 'vue-router'

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
    itemLabelClasses: String,
    itemClasses: String,
    menuClasses: String
})

const emit = defineEmits<{ (e: 'tab-change', event: TabMenuChangeEvent): void }>()

const visibleItems = computed(() => props.items!.filter((item) => item.visible || item.visible === undefined))
const route = useRoute()

const currentTabIndex = ref(getActiveTabIndex())

watch(route, () => { currentTabIndex.value = getActiveTabIndex() })

function getActiveTabIndex() {
    return props.items?.findIndex(item => !item.routes || item.routes.includes(route.name))
}

function onTabClick(index) {
    currentTabIndex.value = index
    emit('tab-change', { tabIndex: currentTabIndex.value })
}

</script>

<template>
    <template v-for="(item, index) in visibleItems">
        <div v-if="item.separator" class="menu-separator self-center w-[90%]"/>
        <ItemButton
            v-else
            :as="item.route ? 'RouterLink' : 'button'"
            @click="onTabClick(index)"
            :label-classes="itemLabelClasses"
            class="tab-menu-button"
            :label="item.label"
            :icon="item.icon"
            :to="item.route"
            :classes="itemClasses"
            :class="{'active': currentTabIndex === index }"
        />
    </template>
</template>

<style scoped>

</style>

