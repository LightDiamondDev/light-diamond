<script setup lang='ts'>
import {useAuthStore} from '@/stores/auth'
import useThemeManager from '@/theme-manager'
import {useRouter} from 'vue-router'
import {useModalStore} from '@/stores/modal'
import {usePostCategoryStore} from '@/stores/postCategory'

const router = useRouter()
const authStore = useAuthStore()
const categoryStore = usePostCategoryStore()
const modalStore = useModalStore()
const themeManager = useThemeManager()
</script>

<template>
    <header class="flex justify-center">
        <div class="header-wrap flex justify-between">
            <div class="flex items-center">
                <a class="logo-wrap flex items-center" href="#">
                    <span class="logo"></span>
                </a>

                <div class="header-dropdown inline-flex flex-col">
                    <button class="flex list-label items-center" type="button">
                        <span class="icon icon-down-arrow"></span>
                        <span class="list-label-text  text-[1.1rem]">Контент</span>
                    </button>
                    <div class="header-dropdown-content">
                        <a class="flex items-center" href="#">
                            <span class="icon icon-book"></span>
                            <span class="list-label-text  text-[1.1rem]">Каталог</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-apple flex"></span>
                            <span class="list-label-text  text-[1.1rem]">Аддон LD</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-axolotl-bucket"></span>
                            <span class="list-label-text  text-[1.1rem]">Ресурс-Паки</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-spawn-egg"></span>
                            <span class="list-label-text  text-[1.1rem]">Аддоны</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-skin"></span>
                            <span class="list-label-text  text-[1.1rem]">Скины</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-map"></span>
                            <span class="list-label-text  text-[1.1rem]">Карты</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-script"></span>
                            <span class="list-label-text  text-[1.1rem]">Статьи</span>
                        </a>
                    </div>
                </div>

                <div class="header-dropdown inline-flex flex-col">
                    <button class="flex list-label items-center" type="button">
                        <span class="icon icon-down-arrow"></span>
                        <span class="list-label-text  text-[1.1rem]">Материалы</span>
                    </button>
                    <div class="header-dropdown-content">
                        <a class="flex items-center" href="#">
                            <span class="icon icon-bestiary"></span>
                            <span class="list-label-text  text-[1.1rem]">Бестиарий Light Diamond</span>
                        </a>
                        <a class="flex items-center" href="https://github.com/Mojang/bedrock-samples/releases">
                            <span class="icon icon-documentary"></span>
                            <span class="list-label-text  text-[1.1rem]">Документация Light Diamond</span>
                        </a>
                        <a class="flex items-center" href="https://learn.microsoft.com/en-us/minecraft/creator/reference/content/entityreference/examples/componentlist">
                            <span class="icon icon-microsoft-small"></span>
                            <span class="list-label-text  text-[1.1rem]">Документация Microsoft</span>
                        </a>
                        <a class="flex items-center" href="https://bedrock.dev/">
                            <span class="icon icon-bedrock-dev-small"></span>
                            <span class="list-label-text  text-[1.1rem]">Документация Bedrock.Dev</span>
                        </a>
                        <a class="flex items-center" href="https://wiki.bedrock.dev/">
                            <span class="icon icon-bedrock-wiki"></span>
                            <span class="list-label-text  text-[1.1rem]">Документация Bedrock Wiki</span>
                        </a>
                        <a class="flex items-center" href="https://github.com/Mojang/bedrock-samples/releases">
                            <span class="icon icon-minecraft-materials"></span>
                            <span class="list-label-text  text-[1.1rem]">Материалы Minecraft</span>
                        </a>
                    </div>
                </div>

                <div class="header-dropdown inline-flex flex-col">
                    <button class="flex list-label items-center" type="button">
                        <span class="icon icon-down-arrow"></span>
                        <span class="list-label-text text-[1.1rem]">Ссылки</span>
                    </button>
                    <div class="header-dropdown-content">
                        <a class="flex items-center" href="https://vk.com/light.diamond">
                            <span class="icon icon-diamond"></span>
                            <span class="list-label-text text-[1.1rem]">Группа VK</span>
                        </a>
                        <a class="flex items-center" href="https://www.youtube.com/@grostlight3303">
                            <span class="icon icon-eye"></span>
                            <span class="list-label-text text-[1.1rem]">YouTube</span>
                        </a>
                    </div>
                </div>

                <div class="header-dropdown inline-flex flex-col">
                    <button class="flex list-label items-center" type="button">
                        <span class="icon icon-down-arrow"></span>
                        <span class="list-label-text text-[1.1rem]">Поддержка</span>
                    </button>
                    <div class="header-dropdown-content">
                        <a class="flex items-center" href="#">
                            <span class="icon icon-diamond-sword"></span>
                            <span class="list-label-text text-[1.1rem]">Помощь</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-diamond-pickaxe"></span>
                            <span class="list-label-text text-[1.1rem]">Гайды</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-faq"></span>
                            <span class="list-label-text text-[1.1rem]">О Проекте</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-eye"></span>
                            <span class="list-label-text text-[1.1rem]">Правила Пользования</span>
                        </a>
                        <a class="flex items-center" href="#">
                            <span class="icon icon-script"></span>
                            <span class="list-label-text text-base">Политика Конфиденциальности</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="flex">
                <form class="search-content-form flex items-center">
                    <label class="flex items-center" for="search-content-input">
                        <input class="text-sm" id="search-content-input" placeholder="Поиск" type="text">
                    </label>
                    <button class="flex items-center" type="button">
                        <span class="icon icon-magnifier"></span>
                    </button>
                </form>

                <div class="profile-dropdown inline-flex flex-col">

                    <!-- Кнопка Входа (Выпадающий Список с Кнопкой Авторизации) -->
                    <div class="flex items-center" style="height: 100%;">
                        <button class="sign-in flex list-label items-center" type="button">
                            <span class="icon icon-border-profile"></span>
                        </button>
                    </div>

                    <!-- Кнопка Авторизованного Профиля (Выпадающий Список с Панелью Действий) -->
                    <div class="flex items-center" style="height: 100%;">
                        <button class="flex list-label items-center" type="button">
                            <span class="profile-border flex justify-center items-center icon icon-border">
                                <img alt="" src="/images/users/content/funny-girl.png" style="height: 26px;">
                            </span>
                        </button>
                    </div>

                    <div class="profile-dropdown-content">

                        <!-- Ссылка, доступная с Авторизацией -->
                        <a class="profile-link laminated-link flex items-center" href="#">
                            <span class="icon-outline flex justify-center items-center icon icon-border">
                                <img alt="" src="/images/users/content/funny-girl.png" style="height: 26px;">
                            </span>
                            <div class="flex flex-col">
                                <span class="title list-label-text text-sm">S3V3N1ce</span>
                                <span class="subtitle list-label-text text-[0.75rem]">Сайт Light Diamond</span>
                            </div>
                        </a>

                        <!-- Ссылка, доступная с Авторизацией -->
                        <a class="ld-pay-link laminated-link flex items-center" href="#">
                            <span class="flex justify-center items-center icon icon-diamond"></span>
                            <div class="flex flex-col">
                                <span class="title list-label-text text-sm">S3V3N1ce</span>
                                <span class="subtitle list-label-text text-[0.75rem]">Light Diamond Pay</span>
                            </div>
                        </a>

                        <!-- Ссылка, доступная с Авторизацией -->
                        <a class="naked-link flex items-center" href="#">
                            <span class="icon icon-bottle"></span>
                            <span class="list-label-text text-[1.1rem]">Контент-Студия</span>
                        </a>

                        <!-- Ссылка, доступная с Авторизацией -->
                        <a class="naked-link flex items-center" href="#">
                            <span class="icon icon-download"></span>
                            <span class="list-label-text text-[1.1rem]">Создать Материал</span>
                        </a>

                        <!-- Ссылка, доступная всегда, с и без Авторизации -->
                        <a class="naked-link flex items-center" href="#">
                            <span class="icon icon-faq"></span>
                            <span class="list-label-text text-[1.1rem]">Помощь</span>
                        </a>

                        <!-- Ссылка, доступная с Авторизацией -->
                        <div class="line flex self-center"></div>
                        <a class="naked-link flex items-center" href="#">
                            <span class="icon icon-gear"></span>
                            <span class="list-label-text text-[1.1rem]">Настройки</span>
                        </a>

                        <!-- Ссылка, доступная без Авторизации -->
                        <div class="line flex self-center"></div>
                        <a class="naked-link flex items-center" href="#">
                            <span class="icon icon-diamond"></span>
                            <span class="list-label-text text-[1.1rem]">Войти</span>
                        </a>

                        <!-- Ссылка, доступная с Авторизацией -->
                        <a class="naked-link flex items-center" href="#">
                            <span class="icon icon-left-arrow"></span>
                            <span class="list-label-text text-[1.1rem]">Выйти</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </header>
</template>

<style scoped>

/* =============== [ Структура ] =============== */

header {
    background-color: var(--primary-bg-color);
    user-select: none;
    position: fixed;
    transition: .3s;
    width: 100%;
    top: 0;
}
.header-wrap {
    max-width: 1200px;
    height: 72px;
    width: 100%;
}
.logo {
    background-image: url('/images/logo.png');
    background-size: 100% 100%;
    height: 48px;
    width: 162px;
}
header .logo-wrap { height: 100%; }
header .logo { margin-right: 12px; }
header .icon {
    margin-left: 4px;
    height: 32px;
    width: 32px;
}
header .list-label {
    cursor: pointer;
    height: 100%;
}
header .list-label-text,
.header-dropdown-content .list-label-text {
    font-family: "Minecraft Ten", sans-serif;
    color: var(--primary-text-color);
    transition: .2s;
    padding: 4px;
}
header .list-label:focus-visible .list-label-text, header .list-label:hover .list-label-text,
.header-dropdown-content a:focus-visible .list-label-text, .header-dropdown-content a:hover .list-label-text,
.profile-dropdown-content a:focus-visible .list-label-text, .profile-dropdown-content a:hover .list-label-text {
    color: var(--hover-text-color);
}
button.list-label .icon {
    transition: .2s;
}
button.list-label:focus-visible .icon, button.list-label:hover .icon,
.header-dropdown-content a:focus-visible .icon, .header-dropdown-content a:hover .icon,
.profile-dropdown-content .naked-link:focus-visible .icon, .profile-dropdown-content .naked-link:hover .icon,
.search-content-form button:focus-visible, .search-content-form button:hover {
    animation: icon-trigger-up-animation .3s ease;
}
.header-dropdown,
.profile-dropdown {
    position: relative;
    height: 100%;
}
.profile-dropdown button {
    margin-right: 8px;
    height: 48px;
}
.profile-dropdown button .profile-border {
    height: 42px;
    width: 42px;
    margin: 0;
}
.profile-dropdown .sign-in { margin-left: -4px; }
.profile-dropdown .sign-in .icon {
    height: 42px;
    width: 42px;
}
.header-dropdown-content,
.profile-dropdown-content {
    display: none;
    box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
    background-color: var(--primary-bg-color);
    position: absolute;
    transition: 1s;
    opacity: 1;
    top: 72px;
}
.header-dropdown-content {width: 332px; }
.profile-dropdown-content { width: 320px; }
.header-dropdown-content { left: 0; }
.profile-dropdown-content { right: 0; }
.header-dropdown-content a,
.profile-dropdown-content a {
    height: 72px;
}
.header-dropdown-content .icon,
.profile-dropdown-content .icon {
    margin: 0 4px 0 12px;
    height: 32px;
    width: 32px;
}
.header-dropdown:active .header-dropdown-content,
.header-dropdown:hover .header-dropdown-content,
.header-dropdown:focus .header-dropdown-content,

.header-dropdown .list-label:focus-visible .header-dropdown-content,
.header-dropdown .list-label:active .header-dropdown .header-dropdown-content,
.header-dropdown .list-label:hover .header-dropdown .header-dropdown-content,
.header-dropdown .list-label:focus .header-dropdown .header-dropdown-content,
.header-dropdown .list-label:focus-visible .header-dropdown .header-dropdown-content,

.profile-dropdown:active .profile-dropdown-content,
.profile-dropdown:hover .profile-dropdown-content,
.profile-dropdown:focus .profile-dropdown-content,

.profile-dropdown .list-label:focus-visible .profile-dropdown-content,
.profile-dropdown .list-label:active .profile-dropdown .profile-dropdown-content,
.profile-dropdown .list-label:hover .profile-dropdown .profile-dropdown-content,
.profile-dropdown .list-label:focus .profile-dropdown .profile-dropdown-content,
.profile-dropdown .list-label:focus-visible .profile-dropdown .profile-dropdown-content {
    display: flex;
    animation: smooth-appear-animation .5s ease;
    flex-direction: column;
    opacity: 1;
}
.profile-dropdown-content .profile-link,
.profile-dropdown-content .ld-pay-link {
    background-size: 400% 100%;
    height: 56px;
    margin: 8px;
}
.profile-dropdown-content .profile-link .icon-outline {
    height: 42px;
    width: 42px;
}
.profile-dropdown-content .profile-link .title,
.profile-dropdown-content .profile-link .subtitle,
.profile-dropdown-content .ld-pay-link .title,
.profile-dropdown-content .ld-pay-link .subtitle {
    font-family: "Monocraft", sans-serif;
    margin-left: 4px;
    padding: 0;
}
.profile-dropdown-content .profile-link .subtitle,
.profile-dropdown-content .ld-pay-link .subtitle { opacity: .7; }
.profile-dropdown-content .ld-pay-link .icon-diamond {
    height: 42px;
    width: 42px;
}
.search-content-form label { height: 48px; }
.search-content-form label input {
    color: var(--primary-text-color);
    height: 40px;
    width: 200px;
}
.search-content-form button .icon { margin: 6px; }
.profile-dropdown-content .naked-link .icon { margin-left: 20px; }
.profile-dropdown-content .line {
    opacity: .2;
    height: 2px;
    width: 95%;
}

/* =============== [ Медиа-Запрос { ?px < 1280px } ] =============== */

@media screen and (max-width: 1279px)
{
    .header-dropdown { display: none; }
}

/* =============== [ Медиа-Запрос { ?px < 500px } ] =============== */

@media screen and (max-width: 499px)
{
    header {
        height: 120px;
    }
    .header-wrap {
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
        margin-top: 12px;
    }
    .profile-dropdown-content {
        right: -8px;
        top: 56px;
    }
}



.green { background-color: green; }
.orange { background-color: orange; }
.yellowgreen { background-color: yellowgreen; }





/*


backdrop-filter: blur(2px);
.blurred-background-appear { background-color: rgba(0, 0, 0, .5); }



.header-profile-dropdown {
    display: inline-flex;
    position: relative;
}
.header-profile-dropdown-button {
    display: flex;
    align-items: center;
    padding: 5px 10px;
    color: #ffffff;
}
.header-profile-dropdown-button:hover span { color: #00ffff; }
.header-profile-dropdown-content a:hover .header-profile-span { color: #00ffff; }
.header-profile-dropdown-button .border, .header-profile-link .border { margin-right: 8px; }

.header-profile-dropdown-content {
    display: none;
    background-image: url('/images/elements/base-background.png');
    box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
    background-color: #005a6a;
    position: absolute;
    transition: 1s;
    width: 300px;
    opacity: 0;
    top: 72px;
    right: 0;
}
.header-dropdown-content a, .header-profile-dropdown-content a { height: 72px; }
.header-logout-form button {
    display: flex;
    align-items: center;
    transition: .2s;
    height: 72px;
    width: 100%;
}
.header-dropdown-content a .icon,
.header-profile-dropdown-content a .icon,
.header-logout-form button .icon {
    margin-left: 20px;
    margin-right: 8px;
    height: 40px;
}
.header-logout-form button:hover .icon, .header-logout-form button:focus-visible .icon {
    animation: icon-trigger-up-animation .3s ease;
}
.header-profile-dropdown-content .header-logout-form button:hover span { color: #00ffff; }























.header-profile-link {
    background: linear-gradient(90deg, rgba(0, 255, 255, .1), rgba(0, 255, 255, .3), rgba(0, 255, 255, .1), rgba(0, 255, 255, .3), rgba(0, 255, 255, .1));
    animation: transfusion-animation 10s linear infinite;
    border: 2px solid rgba(0, 255, 255, .3);
    background-size: 400% 100%;
    padding: 4px;
    margin: 12px;
}

.header-profile-link:hover { background-color: #00ffff; }
.header-profile-link .border {
    background-image: url('/images/items/diamond-border.png');
    background-size: 100% 100%;
    min-width: 48px;
    height: 48px;
}
.header-ld-pay-link {
    background: linear-gradient(90deg, rgba(0, 200, 0, .1), rgba(0, 200, 0, .3), rgba(0, 200, 0, .1), rgba(0, 200, 0, .3), rgba(0, 200, 0, .1));
    animation: transfusion-animation 10s linear infinite;
    border: 2px solid rgba(0, 200, 0, .3);
    background-size: 400% 100%;
    padding: 4px;
    margin: 12px;
}
.header-ld-pay-link:hover { background-color: #00ca00; }










.header-profile-link .border .avatar { height: 26px; }
.header-profile-link .meta, .header-ld-pay-link .meta { display: flex; flex-direction: column; }
.header-profile-dropdown-content .header-profile-link .meta span, .header-profile-dropdown-content .header-ld-pay-link .meta span { line-height: 1.5; }
.header-profile-link .meta .name, .header-ld-pay-link .meta .name { font-size: 16px; color: #00ffff; }
.header-profile-link .meta .id, .header-ld-pay-link .meta .ld-pay  {
    font-size: 12px;
    color: #dadada;
}
.header-profile-dropdown-content a:hover, .header-logout-form button:hover { background-color: rgba(0, 255, 255, .1) }
.header-profile-dropdown:active .header-profile-dropdown-content,
.header-profile-dropdown:hover .header-profile-dropdown-content,
.header-profile-dropdown:focus .header-profile-dropdown-content,
.header-profile-dropdown:focus-visible .header-profile-dropdown-content,
.header-profile-dropdown-button:active .header-profile-dropdown-content,
.header-profile-dropdown-button:hover .header-profile-dropdown-content,
.header-profile-dropdown-button:focus .header-profile-dropdown-content,
.header-profile-dropdown-button:focus-visible .header-profile-dropdown-content {
    display: block;
    animation: smooth-appear-animation .5s ease;
    opacity: 1;
}
.header-profile-dropdown:active .header-profile-dropdown-button,
.header-profile-dropdown:hover .header-profile-dropdown-button,
.header-profile-dropdown:focus .header-profile-dropdown-button,
.header-profile-dropdown:focus-visible .header-profile-dropdown-button {
    color: #00ffff;
}
.header-profile-dropdown:hover .header-profile-dropdown-button .border,
.header-profile-dropdown:focus-visible .header-profile-dropdown-button .border,
.header-profile-dropdown-content a:hover .icon,
#header-sliding-menu .units a:hover .icon {
    animation: icon-trigger-up-animation .3s ease;
}
.header-dropdown {
    display: inline-flex;
    position: relative;
}
.header-dropdown-button {
    display: flex;
    align-items: center;
    padding: 5px 10px;
    color: #ffffff;
}
.header-dropdown-button span {
    font-family: "Minecraft Ten", sans-serif;
    font-size: 20px;
}
.header-dropdown-button .icon { height: 40px; margin-right: 8px; }
.header-dropdown-button:hover span { color: #00ffff; }
.header-dropdown-button:hover .icon, .header-dropdown-button:focus-visible .icon {
    animation: icon-trigger-up-animation .3s ease;
}
.header-dropdown-button .border { margin-right: 8px; }
.header-dropdown-content {
    display: none;
    background-image: url('/images/elements/base-background.png');
    box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
    background-color: #005a6a;
    position: absolute;
    transition: 1s;
    width: 300px;
    opacity: 1;
    top: 72px;
    left: 0;
}
.header-dropdown-content a span { line-height: 1.5; }
.header-dropdown-content a:hover { background-color: rgba(0, 255, 255, .1) }
.header-dropdown:active .header-dropdown-content,
.header-dropdown:hover .header-dropdown-content,
.header-dropdown:focus .header-dropdown-content,
.header-dropdown:focus-visible .header-dropdown-content,
.header-dropdown-button:active .header-dropdown-content,
.header-dropdown-button:hover .header-dropdown-content,
.header-dropdown-button:focus .header-dropdown-content,
.header-dropdown-button:focus-visible .header-dropdown-content {
    display: block;
    animation: smooth-appear-animation .5s ease;
    opacity: 1;
}
.header-dropdown:active .header-dropdown-button,
.header-dropdown:hover .header-dropdown-button,
.header-dropdown:focus .header-dropdown-button,
.header-dropdown:focus-visible .header-dropdown-button {
    color: #00ffff;
}
.header-dropdown:hover .header-dropdown-button .border,
.header-dropdown:focus-visible .header-dropdown-button .border,
.header-profile-dropdown-button:hover .border {
    animation: icon-trigger-up-animation .3s ease;
}
.smooth-disappear-animation { animation: smooth-disappear-animation .3s ease }
.smooth-appear-animation { animation: smooth-appear-animation .3s ease; }
.rotate-right-360-animation { animation: rotate-right-360-animation .5s ease; }
.rotate-left-360-animation { animation: rotate-left-360-animation .5s ease; }
.rotate-left-360-animation:focus-visible, .rotate-right-360-animation:focus-visible { border-radius: 50%; }
#header-sliding-menu .units {
    display: flex;
    flex-direction: column;
    width: 100%;
}
#header-sliding-menu .units a:hover, #header-sliding-menu .units a:focus-visible {
    background-color: rgba(255, 255, 255, .15);
    color: #00ffff;
}













footer {
    display: flex;
    background-image: url('/images/elements/base-background.png');
    flex-direction: column;
    align-items: center;
    user-select: none;
    width: 100%;
}
.footer-general-menu {
    display: flex;
    justify-content: space-between;
    max-width: 1200px;
    width: 100%;
}
.footer-general-menu a { padding: 5px 10px; }
.footer-general-menu a .logo {
    height: 50px;
    margin: 4px;
}





 =============== [ Анимации Элементов ] ===============
@keyframes footer-anchor-icon-animation
{
    0% { margin-top: -10px; }
    50% { margin-top: 10px; }
    100% { margin-top: -10px; }
}
@keyframes footer-link-icon-animation
{
    0% { margin-top: 0; }
    50% { margin-top: -10px; }
    100% { margin-top: 0; }
}





*/
</style>
