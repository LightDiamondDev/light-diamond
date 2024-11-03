<script setup lang="ts">
function scrollToTop() {
    window.scrollTo(0, 0)
}
</script>

<template>

    <footer class="ld-primary-background ld-primary-border-top ld-shadow-text flex flex-col items-center w-full">
        <nav class="flex justify-between max-w-[1200px] w-full">
            <div class="sm:hidden ml-4 w-12"/>
            <RouterLink
                :to="{name: 'home'}"
                class="logo-wrap flex items-center self-center duration-200 h-[70%] sm:ml-4"
            >
                <img alt="Logo" class="hidden xs:flex h-full min-w-152" src="/images/elements/light-diamond-logo.png"/>
                <img alt="Logo" class="flex xs:hidden h-[52px]" src="/images/elements/light-diamond-logo-mobile.png"/>
            </RouterLink>

            <a class="xl:flex hidden items-center" href="#">
                <span class="icon icon-apple static"/>
                <span>Аддон LD</span>
            </a>
            <a class="sm:flex hidden items-center" href="#">
                <span class="icon icon-diamond static"/>
                <span>Обновления</span>
            </a>
            <a class="md:flex hidden items-center" href="#">
                <span class="icon icon-dagger static"/>
                <span>Помощь</span>
            </a>
            <a class="sm:flex hidden items-center" href="#">
                <span class="icon icon-faq static"/>
                <span>О Проекте</span>
            </a>
            <button class="footer-anchor flex justify-center items-center self-center mr-4" @click="scrollToTop">
                <span class="icon icon-top-arrow"/>
            </button>
        </nav>
        <div class="ld-tinted-background darker ld-primary-border-top flex flex-col items-center w-full">
            <div class="media flex justify-between h-[48px] max-w-[320px] w-[75%] mt-4 mb-4">
                <a class="flex justify-center items-center" href="https://vk.com/light.diamond">
                    <span class="icon icon-vk"/>
                </a>
                <a class="flex justify-center items-center" href="https://t.me/light_diamond_channel">
                    <span class="icon icon-telegram"/>
                </a>
                <a class="flex justify-center items-center" href="https://www.youtube.com/@grostlight3303">
                    <span class="icon icon-youtube"/>
                </a>
            </div>
            <div class="row flex sm:flex-row flex-col items-center mb-1">
                <a class="flex items-center p-2" href="#">
                    <span class="text-[12px]">Политика Конфиденциальности</span>
                </a>
                <div class="sm:flex hidden h-8 w-0.5"></div>
                <a class="flex items-center p-2" href="#">
                    <span class="text-[12px]">Условия Пользования</span>
                </a>
            </div>
            <div class="row flex items-center mt-1 mb-1">
                <p class="flex items-center p-2" href="#">
                    <span class="text-[12px]">Minecraft защищён Авторским Правом Mojang Studios, данный Сайт не связан с ним официально.</span>
                </p>
            </div>
            <div class="row flex items-center mt-1 mb-8">
                <p class="flex items-center p-2" href="#">
                    <span class="text-[12px]">© 2023 - 2024 Light Diamond. Все права защищены.</span>
                </p>
            </div>
        </div>
    </footer>
</template>

<style scoped>

/* =============== [ Структура ] =============== */

footer {
    transition-property: background-color;
    user-select: none;
}

footer nav {
    height: var(--header-height);
}

footer nav a span {
    transition: .2s;
    padding: 4px;
}

footer nav a:focus-visible span, footer nav a:hover span {
    color: var(--hover-text-color);
}

footer nav a:focus-visible .icon, footer nav a:hover .icon,
footer .media a:focus-visible .icon, footer .media a:hover .icon {
    animation: icon-trigger-up-animation .3s ease;
}

footer .footer-anchor .icon-top-arrow {
    animation: footer-anchor-icon-animation 1s infinite;
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
