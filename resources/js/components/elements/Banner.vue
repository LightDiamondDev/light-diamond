<script setup lang="ts">
const props = defineProps({
    title: {
        type: String,
        default: 'Заголовок'
    },
    isTitleVisible: {
        type: Boolean,
        default: true
    }
})
</script>

<template>
    <div class="banner-container flex justify-center fixed w-full">
        <div class="banner flex justify-center items-end">
            <div :class="{ 'opacity-0': !isTitleVisible }" class="title page-container flex flex-col justify-center items-center">
                <h1 class="text-center absolute">{{ title }}</h1>
            </div>
        </div>
        <div class="banner-content page-container flex justify-center absolute w-full bottom-0">
            <slot name="banner-content"/>
        </div>
    </div>
</template>

<style scoped>
.banner-container {
    overflow: hidden;
    height: 280px;
    left: 0;
    top: 0;
}
.banner {
    background-image: url('/images/elements/catalog-banner1.png');
    background-repeat: repeat;
    min-width: 1920px;
    height: 280px;
    width: 100%;
    left: 0;
}
.banner .title,
.catalog-container .title {
    position: relative;
    line-height: 1.1;
    font-size: 3rem;
    height: 208px
}
.banner .title .title {
    line-height: 1.1;
    font-size: 3rem;
}
.banner .title h1 {
    text-shadow: 4px 4px 48px black;
}

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px) {
    .banner .title {
        font-size: 2.5rem;
    }
}

/* =============== [ Медиа-Запрос { ?px < 451px } ] =============== */

@media screen and (max-width: 450px) {
    .banner-container {
        height: 178px;
    }
    .banner {
        height: 178px;
    }
    .banner .title {
        font-size: 1.8rem;
        max-width: 214px;
        height: 106px
    }
}
</style>
