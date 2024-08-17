<script setup lang="ts">
    import ProcessingMovingItems from '@/components/elements/ProcessingMovingItems.vue'

    const props = defineProps({
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
        loading: {
            type: Boolean,
            default: false
        },
        loadingItem: {
            type: String,
            default: 'item-diamond'
        },
        text: String
    });
</script>

<template>
    <button
        class="button-container flex justify-center items-center"
        :class="{ 'disabled': disabled }"
        :disabled="disabled"
        :type="buttonType"
    >
        <span class="action-button flex flex-col">
            <span class="press flex justify-center items-center">
                <ProcessingMovingItems v-if="loading" class="mr-4" :item="loadingItem" height="28px" width="28px"/>
                <span
                    v-if="!loading && icon !== ''"
                    :class="icon"
                    class="icon mr-2"
                />
                <span class="text duration-200">{{ text }}</span>
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
