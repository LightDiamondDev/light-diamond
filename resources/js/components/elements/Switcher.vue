<script setup lang="ts">
const props = defineProps({
    activeCondition: Boolean,
    inactiveCondition: Boolean,
    iconSwitcherHandle: String,
    iconSwitcherWay: String,
    iconSwitcherHandleSize: String,
    iconSwitcherWaySize: String
});
</script>

<template>
    <span :class="iconSwitcherWay"
        class="settings-switcher icon flex justify-center items-center"
        :style="`height: ` + `${iconSwitcherWaySize}` + `; width: ` + `${iconSwitcherWaySize}`"
    >
        <span :class="{
            iconSwitcherHandle,
            'off': activeCondition,
            'on': inactiveCondition
            }"
            class="handle icon icon-switcher-handle"
            :style="`height: ` + `${iconSwitcherHandleSize}` + `; width: ` + `${iconSwitcherHandleSize}`"
        />
    </span>
</template>

<style scoped>

</style>
