<script setup lang="ts">
import ProcessingMovingItems from '@/components/elements/ProcessingMovingItems.vue'

const props = defineProps({
    buttonType: {
        type: String,
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
        class="shine-button flex items-center option w-full"
        :class="{ 'disabled': disabled }"
        :disabled="disabled"
        :type="buttonType"
    >
        <span class="press flex">
            <span class="preset flex items-center gap-2">
                <ProcessingMovingItems v-if="loading" :item="loadingItem" height="28px" width="28px"/>
                <span
                    v-if="!loading && icon !== ''"
                    :class="icon"
                    class="icon"
                />
                <span class="text text-center flex items-center duration-200">{{ text }}</span>
            </span>
        </span>
    </button>
</template>

<style scoped>
.disabled {
    opacity: .5;
}
</style>
