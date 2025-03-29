<script setup lang="ts">
import {ref} from 'vue'

import Banner from '@/components/elements/Banner.vue'
import Button from '@/components/elements/Button.vue'

const bannerImagesSrc = [
    //'/images/elements/home-banner-snowy-taiga.png',
    //'/images/elements/home-banner-cherry-forest.png',
    //'/images/elements/home-banner-christmas-village.png',
    //'/images/elements/home-banner-christmas-village.png'
    '/images/elements/home-banner-plains.png',
    '/images/elements/home-banner-cherry-forest.png',
    '/images/elements/home-banner-nether-portal.png',
    '/images/elements/home-banner-nether-portal.png'
]

const titles = [
    'Добро пожаловать!',
    'Грандиозный проект',
    'В поиске талантов...',
    'В поиске ценителей...',
    'В предвкушении красоты!'
]

const subtitles = [
    'Приветствуем тебя на нашей официальной платформе Light Diamond, Искатель!',
    'Сообщество, целиком и полностью посвящённое Контенту для нашей любимой Игры!',
    'Невероятно рады видеть новых Авторов, воплощающих свои смелые мечты в Игре!',
    'Как и тех, кто жаждит новых открытий и готов по достоинству оценить их творения!',
    'Мы движемся вперёд, на встречу к новым свершениям — и приглашаем Вас с собой!'
]

const currentTitle = ref(titles[0])

const currentSubtitle = ref(subtitles[0])

const titleCounter = ref(0)

const subtitleCounter = ref(0)

function switchTitle() {
    titleCounter.value < titles.length - 1 ? titleCounter.value++ : titleCounter.value = 0
    currentTitle.value = titles[titleCounter.value]
}

function switchSubtitle() {
    subtitleCounter.value < subtitles.length - 1 ? subtitleCounter.value++ : subtitleCounter.value = 0
    currentSubtitle.value = subtitles[subtitleCounter.value]
}

const titleSwitcherInterval = setInterval(switchTitle, 8000)

const subtitleSwitcherInterval = setTimeout(setInterval(switchSubtitle, 8000), 300)
</script>

<template>
    <Banner class="banner-full h-[94vh]" :images-src="bannerImagesSrc">
        <template v-slot:banner-content>
            <div class="banner-tinted-content flex flex-col justify-center lg:items-start items-center w-full">
                <div class="flex flex-col lg:items-start items-center max-w-[640px] lg:ml-[5%] lg:mt-[25%]">
                    <div class="ld-shadow-text flex flex-col lg:items-start items-center md:gap-4 gap-1">
                        <h2
                            class="landing-title ld-title-font flex items-center xl:text-[4rem] sm:text-[3rem]
                                text-[2rem] lg:text-start text-center sm:whitespace-nowrap relative h-[72px]"
                            style="line-height: 1.1"
                        >
                            {{ currentTitle }}
                        </h2>
                        <p class="landing-subtitle flex items-center xl:text-[1.5rem] sm:text-[1.2rem] text-[1rem]
                            lg:text-start text-center relative h-[72px] pl-1">
                            {{ currentSubtitle }}
                        </p>
                    </div>
                    <Button
                        as="RouterLink"
                        class="xs:min-w-[60%] min-w-[80%] mt-8"
                        label-classes="lg:text-[1.4rem] text-[1.2rem]"
                        press-classes="transfusion gap-2 px-4"
                        icon="icon-bestiary lg:h-[48px] lg:w-[48px]"
                        :to="{name: 'catalog'}"
                        label="Смотреть Каталог"
                    />
                </div>
            </div>
        </template>
    </Banner>
</template>

<style scoped>
.banner-tinted-content {
    background: linear-gradient(45deg, black, rgba(0, 0, 0, .9), rgba(0, 0, 0, .4), transparent, transparent, transparent)
}

.landing-title {
    animation: landing-text-animation 8s ease infinite;
}

.landing-subtitle {
    animation: landing-text-animation 8s ease infinite;
    animation-delay: .3s;
}

/* =============== [ Анимации ] =============== */

@keyframes landing-text-animation {
    0% {
        transform: translateY(-200%);
        opacity: 0;
    }
    10% {
        transform: translateY(0);
    }
    50% {
        opacity: 1;
    }
    80% {
        opacity: 1;
    }
    95% {
        opacity: 0;
    }
    100% {
        opacity: 0;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1024px } ] =============== */

@media screen and (max-width: 1023px) {
    .banner-tinted-content {
        background: linear-gradient(transparent, rgba(0, 0, 0, .7), black)
    }
}
</style>

<style>
/* =============== [ Медиа-Запрос { 1920px > ?px } ] =============== */

@media screen and (min-width: 1920px) {
    .banner-slide {
        background-size: contain;
    }
}
</style>
