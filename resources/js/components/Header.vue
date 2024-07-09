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
                <a class="flex content-center" href="#">
                    <span class="logo"></span>
                </a>
                <div class="flex list-label items-center">
                    <span class="icon icon-down-arrow"></span>
                    <span class="list-label-text">Контент</span>
                </div>
                <div class="flex list-label items-center">
                    <span class="icon icon-down-arrow"></span>
                    <span class="list-label-text">Материалы</span>
                </div>
                <div class="flex list-label items-center">
                    <span class="icon icon-down-arrow"></span>
                    <span class="list-label-text">Ссылки</span>
                </div>
                <div class="flex list-label items-center">
                    <span class="icon icon-down-arrow"></span>
                    <span class="list-label-text">Поддержка</span>
                </div>
            </div>
            <div class="flex"></div>
        </div>
    </header>
</template>

<style scoped>

/* =============== [ Структура ] =============== */

header {
    background-color: var(--primary-color);
    background-image: var(--bg-image);
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
header .logo {
    margin-right: 12px;
}
header .icon {
    margin-left: 4px;
    height: 32px;
    width: 32px;
}
header .icon-down-arrow {
    background-image: url('/images/items/diamond-down-arrow.png');
}
header .list-label {
    cursor: pointer;
    height: 100%;
}
header .list-label-text {
    font-family: "Minecraft Ten", sans-serif;
    font-size: 18px;
    padding: 4px;
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
