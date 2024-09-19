<script setup lang="ts">
const props = defineProps({
    as: {
        type: String,
        default: 'button'
    },
    label: String,
    icon: String,
});
</script>

<template>
    <Component
        class="item-button ld-shadow-text flex items-center"
        :class="{ 'gap-3': label }"
        type="button"
        :is="as"
    >
        <div>
            <slot name="icon">
                <span v-if="icon" :class="icon" class="icon flex min-w-[2rem]"/>
            </slot>
        </div>
        <span class="label text-start duration-200">{{ label }}</span>

    </Component>
</template>

<style scoped>
.left-header-sidebar .item-button .text,
.right-header-sidebar .item-button .text {
    line-height: 1.2;
    max-width: 90%;
}
</style>
