<script setup lang="ts">
const props = defineProps({
    as: {
        type: String,
        default: 'button'
    },
    icon: String,
    text: String
});
</script>

<template>
    <Component
        class="item-button flex items-center gap-3"
        type="button"
        :is="as"
    >
        <div>
            <slot name="icon">
                <span v-if="icon" :class="icon" class="icon flex min-w-[2rem]"/>
            </slot>
        </div>
        <span class="text text-start duration-200">{{ text }}</span>

    </Component>
</template>

<style scoped>
.header-dropdown-content .item-button {
    font-size: 1.1rem;
}

.item-button {
    min-height: 72px;
}

.item-button:focus-visible .icon, .item-button:hover .icon {
    animation: icon-trigger-up-animation .3s ease;
}

.item-button:focus-visible .text, .item-button:hover .text {
    color: var(--hover-text-color);
}

.left-header-sidebar .item-button .text,
.right-header-sidebar .item-button .text {
    line-height: 1.2;
    max-width: 60%;
}

/* =============== [ Медиа-Запрос { ?px < 1024px } ] =============== */

@media screen and (max-width: 1023px) {
    .item-button {
        min-height: 64px;
    }
}
</style>
