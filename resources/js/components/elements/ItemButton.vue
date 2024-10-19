<script setup lang="ts">
const props = defineProps({
    as: {
        type: String,
        default: 'button'
    },
    classes: String,
    labelClasses: String,
    label: String,
    icon: String,
});
</script>

<template>
    <Component
        class="item-button ld-shadow-text flex items-center"
        :class="classes"
        type="button"
        :is="as"
    >
        <slot name="icon">
            <span v-if="icon" :class="icon" class="icon flex min-w-[2rem]"/>
        </slot>
        <span class="label text-start duration-200" :class="labelClasses">{{ label }}</span>

    </Component>
</template>

<style scoped>

</style>
