<script setup lang="ts">
    import Processing from '@/components/elements/Processing.vue'

    const props = defineProps({
        buttonType: String,
        icon: {
            type: String,
            default: ''
        },
        loading: {
            type: Boolean,
            default: false
        },
        text: String
    });
</script>

<template>
    <button class="button-container flex justify-center items-center" :type="buttonType">
        <span class="action-button flex flex-col">
            <span class="press flex justify-center items-center">
                <Processing v-if="loading" class="mr-4" height="28px" width="28px"/>
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

</style>
