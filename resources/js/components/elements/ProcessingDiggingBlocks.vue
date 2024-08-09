<script setup lang="ts">
const props = defineProps({
    block: {
        type: String,
        default: 'block-diamond-ore-block'
    },
    loot: {
        type: String,
        default: 'item-diamond'
    },
    item: {
        type: String,
        default: 'item-diamond-pickaxe'
    },
    height: String,
    width: String
});
</script>

<template>
    <div class="processing flex flex-wrap" :style="{ 'height': height, 'width': width, }">
        <div :class="block" class="block"></div>
        <div class="destroying"></div>
        <div :class="loot" class="loot"></div>
        <div :class="item" class="item"></div>
    </div>
</template>

<style scoped>
.processing {
    position: relative;
}
.processing .block,
.processing .destroying,
.processing .item {
    background-size: 100% 100%;
    position: absolute;
    height: 50%;
    width: 50%;
}
/* | b6 | d6 | l6 | i1.5 */
.block {
    animation: processing-breaking-animation 2s infinite;
    bottom: 0;
    left: 0;
}

.destroying {
    animation: processing-destroying-animation 2s infinite ease;
    bottom: 0;
    left: 0;
}

.loot {
    animation: processing-loot-animation 2s infinite ease;
    background-size: 100% 100%;
    position: absolute;
    height: 33%;
    width: 33%;
    left: 12%;
}
.item {
    animation: processing-digging-animation .5s infinite ease;
    transform-origin: bottom left;
    left: 70%;
    top: 10%;
}


@keyframes processing-breaking-animation
{
    0% { opacity: 1; }
    85% { opacity: 1; }
    86% { opacity: 0; }
    100% { opacity: 0; }
}

@keyframes processing-digging-animation
{
    0% { transform: rotate(30deg); }
    50% { transform: rotate(-100deg); }
    75% { transform: rotate(30deg); }
    100% { transform: rotate(30deg); }
}

@keyframes processing-destroying-animation
{
    0% { background-image: none; }
    25% { background-image: url('/images/blocks/destroy-stage-1.png'); }
    50% { background-image: url('/images/blocks/destroy-stage-2.png'); }
    75% { background-image: url('/images/blocks/destroy-stage-3.png'); }
    99% { background-image: none; }
    100% { background-image: none; }
}

@keyframes processing-loot-animation
{
    0% { opacity: 1; top: 0;  }
    8% { opacity: 1; top: 5%;  }
    15% { opacity: 1; top: 0;  }
    20% { opacity: 0; top: 5%;  }
    85% { opacity: 0; top: 50%;  }
    100% { opacity: 1; top: 5%;  }
}
</style>
