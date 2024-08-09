<script setup lang="ts">
import {ref} from 'vue'

const isBedrock = ref(true)
</script>

<template>
<div class="catalog-banner flex justify-center w-full">
    <div class="banner flex justify-center items-end">
        <div class="title flex justify-center items-center">
            <h1 class="text-center text-[2.5rem] md:text-[3rem]">Каталог Light Diamond</h1>
        </div>
    </div>
</div>
<div class="catalog-container flex justify-center w-full mt-[168px]">
    <section class="catalog flex flex-col items-center w-full">
        <form class="catalog-panel flex flex-col w-full self-center" name="catalog">

            <nav class="flex flex-col">

                <div class="line flex flex-wrap justify-between gap-4 p-4">
                    <div class="sub-line flex flex-wrap gap-4">
                        <button class="flex items-center option" type="button">
                            <span class="press flex">
                                <span class="preset flex items-center gap-1">
                                    <span class="icon icon-clock"/>
                                    <span>Свежие</span>
                                </span>
                            </span>
                        </button>
                        <button class="flex items-center option" type="button">
                            <span class="press flex">
                                <span class="preset flex items-center gap-1">
                                    <span class="icon icon-crown"/>
                                    <span>Популярные</span>
                                </span>
                            </span>
                        </button>
                    </div>
                    <div class="sub-line flex flex-wrap justify-center gap-4">
                        <button class="flex items-center option edition" type="button" @click="isBedrock = !isBedrock">
                            <span class="press flex">
                                <span class="preset flex items-center gap-1 min-w-[172px]">
                                    <span
                                        :class="{ 'icon-bedrock-dev-small': isBedrock, 'icon-minecraft-materials': !isBedrock }"
                                        class="icon"
                                    />
                                    <span>{{ isBedrock ? 'Bedrock' : 'Java' }}</span>
                                </span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="menu-separator flex self-center"></div>

                <div class="line flex flex-wrap gap-4 p-4">
                    <RouterLink class="flex items-center" :to="{ name: 'catalog' }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-news"/>
                                <span>Новости</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="flex items-center" :to="{ name: 'catalog' }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-axolotl-bucket"/>
                                <span>Ресурс-Паки</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="flex items-center" :to="{ name: 'catalog' }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-spawn-egg"/>
                                <span>Аддоны</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="flex items-center" :to="{ name: 'catalog' }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-skin"/>
                                <span>Скины</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="flex items-center" :to="{ name: 'catalog' }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-map"/>
                                <span>Карты</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="flex items-center" :to="{ name: 'catalog' }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-script"/>
                                <span>Статьи</span>
                            </span>
                        </span>
                    </RouterLink>
                </div>

            </nav>
        </form>
        <div class="a"></div>
    </section>
</div>
</template>

<style scoped>
.a {
    height: 500px;
    background-color: rgba(0, 150, 150, .5);
}
.catalog-banner {
    overflow: hidden;
    position: fixed;
    height: 240px;
    top: 0;
}
.banner {
    background-image: url('/images/elements/catalog-banner1.png');
    background-repeat: no-repeat;
    height: 240px;
    width: 1920px;
}
.banner .title {
    height: 168px
}
.banner .title h1 {
    text-shadow: 4px 4px 48px black;
}
.catalog-container {
    position: relative;
}
section.catalog {
    max-width: 1280px;
}
.catalog-panel .line a,
.catalog-panel .line button {
    max-width: 400px;
    flex-grow: 1;
    border: none;
}
.catalog-panel .line .option {
    flex-grow: 0;
}
.catalog-panel .line a .press,
.catalog-panel .line button .press {
    width: 100%;
}
.catalog-panel .line a .preset,
.catalog-panel .line button .preset {
    justify-content: center;
    padding: 0 2rem;
    height: 48px;
    width: 100%;
}
.catalog-panel .line a:hover .press .icon,
.catalog-panel .line button:hover .press .icon {
    animation: icon-trigger-up-animation .3s ease;
}
.menu-separator {
    width: 98%;
}

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px) {
    .catalog-panel .sub-line {
        flex-grow: 1;
    }
    .catalog-panel .line .option {
        flex-grow: 1;
    }
    .menu-separator {
        width: 95%;
    }
}
</style>
