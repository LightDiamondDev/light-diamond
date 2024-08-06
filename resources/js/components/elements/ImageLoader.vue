<script setup lang="ts">

const props = defineProps({
    fillerIcon: String,
    id: String,
    imagePath: String
})
</script>

<template>
    <label
        class="image-loader flex items-center locked"
        :style="{ 'background-image': `url(${imagePath})` }"
        :for="id"
    >
        <span class="filler flex justify-center items-center w-full">
            <span :class="fillerIcon" class="icon"></span>
        </span>
        <input
            class="hidden"
            :id="id"
            type="file"
        >
    </label>
</template>

<style scoped>
.image-loader {
    background-size: 100% 100%;
    position: relative;
    cursor: pointer;
}
.image-loader .filler {
    background-color: rgba(0, 0, 0, .7);
    position: absolute;
    transition: .2s;
    bottom: 0;
    height: 0;
}
.image-loader:hover .filler {
    height: 100%;
}
.image-loader .filler .icon {
    transition: .2s;
    opacity: 0;
}
.image-loader:hover .filler .icon {
    transition: 1s;
    opacity: 1;
}
</style>
