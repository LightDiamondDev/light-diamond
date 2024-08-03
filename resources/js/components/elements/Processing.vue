<script setup lang="ts">
const props = defineProps({
    height: String,
    width: String
});
</script>

<template>
    <div class="processing flex flex-wrap" :style="{ 'height': height, 'width': width, }">
        <div class="block1 block-diamond-block"></div>
        <div class="block2 block-diamond-block"></div>
        <div class="block3 block-diamond-block"></div>
        <div class="block4 block-diamond-block"></div>
    </div>
</template>

<style scoped>
.processing {
    position: relative;
}
.processing div {
    background-size: 100% 100%;
    position: absolute;
    height: 50%;
    width: 50%;
}
.block1 {
    animation: processing-cycle-animation1 .5s infinite ease;
    left: 0;
    top: 0;
}
.block2 {
    animation: processing-cycle-animation2 .5s infinite ease;
    right: 0;
    top: 0;
}
.block3 {
    animation: processing-cycle-animation3 .5s infinite ease;
    bottom: 0;
    left: 0;
}
.block4 {
    animation: processing-cycle-animation4 .5s infinite ease;
    bottom: 0;
    right: 0;
}

@keyframes processing-cycle-animation1
{
    0% { top: 0; left: 0; }
    16% { top: -25%; left: 0; }
    32% { top: -25%; left: 50%; }
    48% { top: 0; left: 50%; }
    64% { top: 0; left: 75%; }
    80% { top: 50%; left: 75%; }
    100% { top: 50%; left: 50%; }
}
@keyframes processing-cycle-animation2
{
    0% { top: 0; right: 0; }
    16% { top: 25%; right: 0; }
    32% { top: 25%; right: 0; }
    48% { top: 50%; right: 0; }
    64% { top: 50%; right: 25%; }
    80% { top: 50%; right: 25%; }
    100% { top: 50%; right: 50%; }
}
@keyframes processing-cycle-animation3
{
    0% { bottom: 0; left: 0; }
    16% { bottom: 25%; left: 0; }
    32% { bottom: 25%; left: 0; }
    48% { bottom: 50%; left: 0; }
    64% { bottom: 50%; left: 25%; }
    80% { bottom: 50%; left: 25%; }
    100% { bottom: 50%; left: 50%; }
}
@keyframes processing-cycle-animation4
{
    0% { bottom: 0; right: 0; }
    16% { bottom: -25%; right: 0; }
    32% { bottom: -25%; right: 50%; }
    48% { bottom: 0; right: 50%; }
    64% { bottom: 0; right: 75%; }
    80% { bottom: 50%; right: 75%; }
    100% { bottom: 50%; right: 50%; }
}
</style>
