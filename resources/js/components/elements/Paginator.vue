<script setup lang="ts">

</script>

<template>
    <div class="paginator flex justify-center items-center">
        <button class="flex relative" type="button">
            <span class="icon icon-left-arrow absolute" style="left: .8rem;"></span>
            <span class="icon icon-left-arrow"></span>
        </button>
        <button class="flex" type="button">
            <span class="icon icon-left-arrow flex"></span>
        </button>
        <div class="flex">1</div>
        <button class="flex" type="button">
            <span class="icon icon-right-arrow flex"></span>
        </button>
        <button class="flex relative" type="button">
            <span class="icon icon-right-arrow absolute" style="right: .8rem;"></span>
            <span class="icon icon-right-arrow"></span>
        </button>
    </div>
</template>

<style scoped>
.icon {
    min-width: 24px;
    height: 24px;
}
</style>
