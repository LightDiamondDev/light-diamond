<script setup lang='ts'>
    import {useAuthStore} from '@/stores/auth'
    import useDesignManager from '@/design-manager'
    import {useRouter} from 'vue-router'
    import {useGlobalModalStore} from '@/stores/global-modal'
    import {usePostCategoryStore} from '@/stores/postCategory'

    const router = useRouter()
    const authStore = useAuthStore()
    const categoryStore = usePostCategoryStore()
    const globalModalStore = useGlobalModalStore()
    const designManager = useDesignManager()
</script>

<template>

    <footer class="flex flex-col items-center">
        <nav class="flex justify-between">

            <div class="sm:hidden ml-4 w-12"></div>

            <a class="sm:ml-4 logo-wrap flex items-center" href="#">
                <span class="logo"></span>
            </a>
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
            <a class="footer-anchor mr-4 flex justify-center items-center self-center" href="#header">
                <span class="icon icon-top-arrow"></span>
            </a>
        </nav>
        <div class="tinted-gradient flex flex-col items-center">
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
    user-select: none;
    transition: .3s;
    width: 100%;
}
footer nav {
    height: var(--header-height);
    max-width: 1200px;
    width: 100%;
}
.logo {
    background-image: url('/images/logo.png');
    background-size: 100% 100%;
    height: 48px;
    width: 162px;
}
footer nav .logo-wrap { height: 100%; }
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
footer .media a:focus-visible .icon, footer .media a:hover .icon { animation: icon-trigger-up-animation .3s ease; }
footer .footer-anchor .icon-top-arrow { animation: footer-anchor-icon-animation 1s infinite; }
footer .tinted-gradient {
    width: 100%;
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
footer .row a:hover { color: var(--hover-text-color); }
footer .row p:hover { opacity: 1; }


/* =============== [ Медиа-Запрос { 1280px > ?px } ] =============== */

@media screen and (min-width: 1280px)
{
}

/* =============== [ Медиа-Запрос { ?px < 1280px } ] =============== */

@media screen and (max-width: 1279px)
{
    .header-dropdown { display: none; }
    .opening-header-sidebar-button { display: flex; }
    .desktop-profile-dropdown { display: none; }
}

/* =============== [ Медиа-Запрос { 500px > ?px } ] =============== */

@media screen and (min-width: 500px)
{
    .mobile-search { display: none; }
}

/* =============== [ Медиа-Запрос { ?px < 500px } ] =============== */

@media screen and (max-width: 499px)
{
    .desktop-search { display: none; }
}



.green { background-color: green; }
.orange { background-color: orange; }
.yellowgreen { background-color: yellowgreen; }

/* =============== [ Анимации Элементов ] =============== */
@keyframes footer-anchor-icon-animation
{
    0% { margin-top: -10px; }
    50% { margin-top: 10px; }
    100% { margin-top: -10px; }
}
</style>
