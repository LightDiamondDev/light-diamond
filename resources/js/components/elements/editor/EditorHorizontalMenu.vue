<script setup lang="ts">
import {type PropType, reactive} from 'vue'
import EditorVerticalMenu from '@/components/elements/editor/EditorVerticalMenu.vue'
import type {EditorMenuItem} from '@/components/elements/editor/types'

defineProps({
    items: {
        type: Object as PropType<EditorMenuItem[]>,
        required: true
    }
})

const verticalMenus = reactive<{ [key: string]: InstanceType<typeof EditorVerticalMenu> }>({})
</script>

<template>
    <div class="bg-[var(--surface-overlay)] h-[2.5rem] overflow-x-auto flex" @mousedown.prevent>
        <template v-for="(menuItem, id) in items">
            <template v-if="menuItem.isVisible !== false">
                <template v-if="menuItem.isSeparator">
                    <div class="w-[1px] bg-[var(--surface-border)] inline-block ml-1 mr-1 flex-shrink-0"/>
                </template>

                <template v-else-if="menuItem.children">
                    <EditorVerticalMenu
                        :ref="(val: InstanceType<typeof EditorVerticalMenu>) => verticalMenus[`editor-vertical-menu-${id}`] = val"
                        :id="`editor-vertical-menu-${id}`"
                        :items="menuItem.children"
                        :title="menuItem.displayName"
                    />

                    <Button
                        text
                        v-if="verticalMenus[`editor-vertical-menu-${id}`]"
                        :severity="menuItem.isActive ? 'primary' : 'secondary'"
                        aria-haspopup="true"
                        :aria-controls="`editor-vertical-menu-${id}`"
                        class="flex-shrink-0"
                        @click="verticalMenus[`editor-vertical-menu-${id}`].toggle"
                    >
                        <span :class="menuItem.icon"/>
                        <span class="ml-3 fa-solid fa-angle-down"/>
                    </Button>
                </template>

                <template v-else>
                    <Button
                        text
                        :icon="menuItem.icon"
                        :severity="menuItem.isActive ? 'primary' : 'secondary'"
                        class="flex-shrink-0"
                        @click="menuItem.callback"
                    />
                </template>
            </template>
        </template>
    </div>
</template>

<style scoped>

</style>
