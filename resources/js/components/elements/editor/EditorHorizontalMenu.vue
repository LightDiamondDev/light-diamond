<script setup lang="ts">
import {type PropType, reactive} from 'vue'
import type {EditorMenuItem} from '@/components/elements/editor/types'
import EditorVerticalMenu from '@/components/elements/editor/EditorVerticalMenu.vue'
import ItemButton from '@/components/elements/ItemButton.vue'

defineProps({
    items: {
        type: Object as PropType<EditorMenuItem[]>,
        required: true
    }
})

const verticalMenus = reactive<{ [key: string]: InstanceType<typeof EditorVerticalMenu> }>({})
</script>

<template>
    <div class="editor-horizontal-menu flex"> <!-- @mousedown.prevent -->
        <template v-for="(menuItem, id) in items">
            <template v-if="menuItem.isVisible !== false">
                <template v-if="menuItem.isSeparator">
                    <div class="separator flex self-center h-[40px] w-[2px]"/>
                </template>

                <template v-else-if="menuItem.children">
                    <EditorVerticalMenu
                        :ref="(val: InstanceType<typeof EditorVerticalMenu>) => verticalMenus[`editor-vertical-menu-${id}`] = val"
                        :id="`editor-vertical-menu-${id}`"
                        :items="menuItem.children"
                        :title="menuItem.displayName"
                    />

                    <ItemButton
                        v-if="verticalMenus[`editor-vertical-menu-${id}`]"
                        :severity="menuItem.isActive ? 'primary' : 'secondary'"
                        icon="icon-down-arrow"
                        aria-haspopup="true"
                        :aria-controls="`editor-vertical-menu-${id}`"
                        class="flex-shrink-0"
                        @click="verticalMenus[`editor-vertical-menu-${id}`].toggle"
                    >
                        <span :class="menuItem.icon"/>
                    </ItemButton>
                </template>

                <template v-else>
                    <ItemButton
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
.content .item-button {
    justify-content: center;
    min-height: 48px;
    width: 48px;
}
</style>
