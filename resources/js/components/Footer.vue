<script setup lang="ts">
function scrollToTop() {
    window.scrollTo(0, 0)
}
</script>

<template>

    <footer class="ld-primary-background ld-primary-border-top ld-shadow-text flex flex-col items-center">
        <nav class="flex justify-between">

            <div class="sm:hidden ml-4 w-12"></div>

            <RouterLink
                :to="{name: 'home'}"
                class="logo-wrap flex items-center duration-200 h-[72px] sm:ml-4"
            >
                <img alt="Logo" class="hidden xs:flex h-[52px] min-w-152" src="/images/elements/light-diamond-logo.png"/>
                <img alt="Logo" class="flex xs:hidden h-[52px]" src="/images/elements/light-diamond-logo-mobile.png"/>
            </RouterLink>

            <a class="xl:flex hidden items-center" href="#">
                <span class="icon icon-apple"></span>
                <span class="text-[1.1rem]">Аддон LD</span>
            </a>
            <a class="sm:flex hidden items-center" href="#">
                <span class="icon icon-diamond"></span>
                <span class="text-[1.1rem]">Обновления</span>
            </a>
            <a class="md:flex hidden items-center" href="#">
                <span class="icon icon-diamond-sword"></span>
                <span class="text-[1.1rem]">Помощь</span>
            </a>
            <a class="sm:flex hidden items-center" href="#">
                <span class="icon icon-faq"></span>
                <span class="text-[1.1rem]">О Проекте</span>
            </a>
            <a class="footer-anchor mr-4 flex justify-center items-center self-center" @click="scrollToTop">
                <span class="icon icon-top-arrow"></span>
            </a>
        </nav>
        <div class="ld-tinted-background harder ld-primary-border-top flex flex-col items-center w-full">
            <div class="media mt-6 mb-6 flex justify-between">
                <a class="flex justify-center items-center" href="https://vk.com/light.diamond">
                    <span class="icon icon-vk"></span>
                </a>
                <a class="flex justify-center items-center" href="https://t.me/light_diamond_channel">
                    <span class="icon icon-telegram"></span>
                </a>
                <a class="flex justify-center items-center" href="https://www.youtube.com/@grostlight3303">
                    <span class="icon icon-youtube"></span>
                </a>
            </div>
            <div class="row mb-1 flex sm:flex-row flex-col items-center">
                <a class="flex items-center p-2" href="#">
                    <span class="text-sm">Политика Конфиденциальности</span>
                </a>
                <div class="sm:flex hidden h-8 w-0.5"></div>
                <a class="flex items-center p-2" href="#">
                    <span class="text-sm">Условия Пользования</span>
                </a>
            </div>
            <div class="row mt-1 mb-1 flex items-center">
                <p class="flex items-center p-2" href="#">
                    <span class="text-sm">Minecraft защищён Авторским Правом Mojang Studios, данный Сайт не связан с ним официально.</span>
                </p>
            </div>
            <div class="row mt-1 mb-8 flex items-center">
                <p class="flex items-center p-2" href="#">
                    <span class="text-sm">© 2023 - 2024 Light Diamond. Все права защищены.</span>
                </p>
            </div>
        </div>
    </footer>
</template>

<style scoped>
/* =============== [ Структура ] =============== */
footer {
    background-color: var(--primary-bg-color);
    transition-property: background-color;
    user-select: none;
    width: 100%;
}

footer nav {
    height: var(--header-height);
    max-width: 1200px;
    width: 100%;
}

footer nav .logo-wrap {
    height: 100%;
}

footer nav a {
    cursor: pointer;
    height: 100%;
}

footer nav a span {
    color: var(--primary-text-color);
    transition: .2s;
    padding: 4px;
}

footer nav a .icon {
    transition: .2s;
    height: 32px;
    width: 32px;
}

footer nav a:focus-visible span, footer nav a:hover span {
    color: var(--hover-text-color);
}

footer nav .footer-anchor {
    height: 48px;
    width: 48px;
}

footer nav a:focus-visible .icon, footer nav a:hover .icon,
footer .media a:focus-visible .icon, footer .media a:hover .icon {
    animation: icon-trigger-up-animation .3s ease;
}

footer .footer-anchor .icon-top-arrow {
    animation: footer-anchor-icon-animation 1s infinite;
}

footer .media {
    max-width: 320px;
    height: 48px;
    width: 75%;
}

footer .media a {
    height: 48px;
    width: 48px;
}

footer .media a .icon {
    height: 32px;
    width: 32px;
}

footer .row a,
footer .row p {
    color: var(--primary-text-color);
    text-align: center;
    transition: .2s;
    opacity: .8;
}

footer .row div {
    background-color: var(--primary-text-color);
    opacity: .8;
}

footer .row a:hover {
    color: var(--hover-text-color);
}

footer .row p:hover {
    opacity: 1;
}

/* =============== [ Анимации Элементов ] =============== */
@keyframes footer-anchor-icon-animation {
    0% {
        margin-top: -10px;
    }
    50% {
        margin-top: 10px;
    }
    100% {
        margin-top: -10px;
    }
}
</style>
