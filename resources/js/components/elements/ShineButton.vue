<script setup lang="ts">
import ProcessingMovingItems from '@/components/elements/ProcessingMovingItems.vue'

const props = defineProps({
    as: {
        type: String,
        default: 'button'
    },
    buttonType: {
        type: String,
        default: 'button'
    },
    classPreset: String,
    classPress: String,
    classWrap: String,
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
    <Component
        class="ld-shine-button flex items-center option"
        :class="{ 'opacity-50': disabled }"
        :disabled="disabled"
        :type="buttonType"
        :is="as"
    >
        <span class="wrap flex w-full" :class="classWrap">
            <span class="press ld-shadow-text flex w-full" :class="classPress">
                <span class="preset flex items-center w-full" :class="classPreset">
                    <ProcessingMovingItems v-if="loading" class="loading-icon" :item="loadingItem" height="28px" width="28px"/>
                    <span
                        v-if="!loading && icon !== ''"
                        class="icon min-w-[2rem]"
                        :class="icon"
                    />
                    <span class="label text-center flex items-center duration-200">{{ label }}</span>
                </span>
            </span>
        </span>

    </Component>
</template>

<style scoped>

</style>
