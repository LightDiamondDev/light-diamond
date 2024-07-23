<script setup lang="ts">
    import {computed} from 'vue'
    const props = defineProps({
        active: Boolean|undefined
    });
    const model = defineModel<boolean>({default: false})
    const active = computed(() => props.active ?? model.value)
    function onClick() {
        if (props.active === undefined) {}
        model.value = !model.value
    }
</script>

<template>
    <button :class="iconSwitcherWay"
          class="settings-switcher icon-switcher-way icon flex justify-center items-center"
          @click="onClick"
    >
        <span :class="{ iconSwitcherHandle, 'on': active }"
            class="handle icon-switcher-handle icon flex justify-center items-center"
    />
    </button>
    </template>

    <style scoped>
    .settings-switcher .handle {
        background-size: 100% 100%;
        transform: translateX(40%);
        position: relative;
        transition: .5s;
        height: 32px;
        width: 32px;
        margin: 0;
    }
    .settings-switcher .handle.on { transform: translateX(-40%); }

</style>
