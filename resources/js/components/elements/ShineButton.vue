<script setup lang="ts">
import ProcessingMovingItems from '@/components/elements/ProcessingMovingItems.vue'

const props = defineProps({
    buttonType: {
        type: String,
        default: 'button'
    },
    classPreset: String,
    classPress: String,
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
    label: String
});
</script>

<template>
    <button
        class="ld-shine-button flex items-center option"
        :class="{ 'disabled': disabled }"
        :disabled="disabled"
        :type="buttonType"
    >
        <span class="press ld-shadow-text flex" :class="classPress">
            <span class="preset flex items-center" :class="classPreset">
                <ProcessingMovingItems v-if="loading" class="loading-icon" :item="loadingItem" height="28px" width="28px"/>
                <span
                    v-if="!loading && icon !== ''"
                    :class="icon"
                    class="icon"
                />
                <span class="label text-center flex items-center duration-200">{{ label }}</span>
            </span>
        </span>
    </button>
</template>

<style scoped>
.disabled {
    opacity: .5;
}
.ld-shine-button .icon {
    min-width: 32px;
}
</style>
