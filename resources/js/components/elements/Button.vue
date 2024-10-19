<script setup lang="ts">
    import ProcessingMovingItems from '@/components/elements/ProcessingMovingItems.vue'

    const props = defineProps({
        actionButtonClasses: String,
        buttonType: {
            type: String,
            validator: (val) => [ 'button', 'submit', 'reset' ].includes(val),
            default: 'button'
        },
        disabled: {
            type: Boolean,
            default: false
        },
        icon: {
            type: String,
            default: ''
        },
        labelClasses: String,
        pressClasses: String,
        loading: {
            type: Boolean,
            default: false
        },
        loadingItem: {
            type: String,
            default: 'item-diamond'
        },
        label: String
    });
</script>

<template>
    <button
        class="ld-press-button button-container flex justify-center items-center"
        :class="{ 'disabled': disabled }"
        :disabled="disabled"
        :type="buttonType"
    >
        <span class="action-button ld-shadow-text flex flex-col" :class="actionButtonClasses">
            <span class="press flex justify-center items-center" :class="pressClasses">
                <ProcessingMovingItems v-if="loading" class="mr-4" :item="loadingItem" height="28px" width="28px"/>
                <span
                    v-if="!loading && icon !== ''"
                    :class="icon"
                    class="icon mr-2"
                />
                <span class="label duration-200" :class="labelClasses">{{ label }}</span>
            </span>
            <span class="base"></span>
        </span>
    </button>
</template>

<style scoped>
.disabled {
    opacity: .5;
}
</style>
