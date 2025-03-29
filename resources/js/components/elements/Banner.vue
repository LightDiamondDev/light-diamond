<script setup lang="ts">
import {onBeforeMount, ref} from 'vue'

const props = defineProps({
    imagesSrc: {
        type: Array,
        required: true
    },
    timeInterval: {
        type: Number,
        default: 15000
    }
})

enum BannerParticles {
    AUTUMN_LEAVES = 'AUTUMN_LEAVES',
    SPRING_FLOWERS = 'SPRING_FLOWERS',
    SUMMER_LEAVES = 'SUMMER_LEAVES',
    WINTER_SNOWFLAKES = 'WINTER_SNOWFLAKES'
}

const bannerImagesSrc = ref(props.imagesSrc)
const timeInterval = ref(props.timeInterval)
const currentBannerImageSrc = ref(0)

const isBannerAnimation = ref(true)

const isBannerAutumnLeavesParticles = ref(false)
const isBannerSpringFlowersParticles = ref(false)
const isBannerSummerLeavesParticles = ref(false)
const isBannerWinterSnowflakesParticles = ref(false)

function launchBannerSwitchSlidesAnimation() {
    if (bannerImagesSrc.value.length > 1) {
        setInterval(function () {
            if (currentBannerImageSrc.value < bannerImagesSrc.value.length - 1) {
                currentBannerImageSrc.value++
            } else {
                currentBannerImageSrc.value = 0
            }
        }, timeInterval.value)
    }
}

function launchBannerParticles(type: string) {
    if (type === BannerParticles.AUTUMN_LEAVES) {
        isBannerAutumnLeavesParticles.value = true
    }
    else if (type === BannerParticles.SPRING_FLOWERS) {
        isBannerSpringFlowersParticles.value = true
    }
    else if (type === BannerParticles.SUMMER_LEAVES) {
        isBannerSummerLeavesParticles.value = true
    }
    else if (type === BannerParticles.WINTER_SNOWFLAKES) {
        isBannerWinterSnowflakesParticles.value = true
    }
}

async function loadImages() {
    Object.keys(bannerImagesSrc.value).map((key, _index) => {
        const img = new Image();
        img.src = bannerImagesSrc.value[key];
    });
}

launchBannerSwitchSlidesAnimation()
launchBannerParticles(BannerParticles.SPRING_FLOWERS)

onBeforeMount(() => {
    loadImages()
})
</script>

<template>
    <div class="banner flex justify-center max-w-[100vw] w-full overflow-hidden">
        <div
            class="banner-slide bg-fixed flex h-full w-full duration-1000"
            style="background-position-x: center; background-repeat: no-repeat"
            :style="{ 'background-image': `url(${bannerImagesSrc[currentBannerImageSrc]})` }"
        >
            <div class="banner-effects flex justify-center snowflakes h-full w-full relative" :class="{ 'effects-off': !isBannerAnimation }">

                <div class="banner-content flex justify-center w-full">
                    <slot name="banner-content"/>
                </div>

                <div class="flex justify-end w-full absolute">
                    <button class="flex max-h-[52px] opacity-50 duration-100000" @click="isBannerAnimation = !isBannerAnimation">
                        <span
                            class="icon flex m-2"
                            :class="{ 'icon-eye': isBannerAnimation, 'icon-eye-cross': !isBannerAnimation }"
                        />
                    </button>
                </div>

                <template v-if="isBannerAutumnLeavesParticles">
                    <span class="particle icon particle-leave-gold medium n1"/>
                    <span class="particle icon particle-leave-lime medium n2"/>
                    <span class="sm:block hidden particle icon particle-leave-orange medium n3"/>
                    <span class="particle icon particle-leave-red medium n4"/>
                    <span class="particle icon particle-leave-yellow medium n5"/>
                    <span class="sm:block hidden particle icon particle-leave-gold medium n6"/>
                    <span class="particle icon particle-leave-lime medium n7"/>
                    <span class="particle icon particle-leave-orange medium n8"/>
                    <span class="sm:block hidden particle icon particle-leave-red medium n9"/>
                    <span class="particle icon particle-leave-yellow medium n10"/>
                    <span class="particle icon particle-leave-gold medium n11"/>
                    <span class="sm:block hidden particle icon particle-leave-lime medium n12"/>
                    <span class="particle icon particle-leave-orange medium n13"/>
                    <span class="particle icon particle-leave-red medium n14"/>
                    <span class="sm:block hidden particle icon particle-leave-yellow medium n15"/>
                </template>

                <template v-if="isBannerSpringFlowersParticles">
                    <span class="particle icon particle-flower-cherry small n1"/>
                    <span class="particle icon particle-flower-lightblue small n2"/>
                    <span class="sm:block hidden particle icon particle-flower-purple small n3"/>
                    <span class="particle icon particle-flower-white medium n4"/>
                    <span class="particle icon particle-flower-yellow small n5"/>
                    <span class="sm:block hidden particle icon particle-flower-cherry medium n6"/>
                    <span class="particle icon particle-flower-lightblue medium n7"/>
                    <span class="particle icon particle-flower-purple medium n8"/>
                    <span class="sm:block hidden particle icon particle-flower-white small n9"/>
                    <span class="particle icon particle-flower-yellow medium n10"/>
                    <span class="particle icon particle-flower-cherry small n11"/>
                    <span class="sm:block hidden particle icon particle-flower-lightblue medium n12"/>
                    <span class="particle icon particle-flower-purple small n13"/>
                    <span class="particle icon particle-flower-white medium n14"/>
                    <span class="sm:block hidden particle icon particle-flower-yellow small n15"/>
                </template>

                <template v-if="isBannerSummerLeavesParticles">
                    <span class="particle icon particle-leave-forestgreen medium n1"/>
                    <span class="particle icon particle-leave-green medium n2"/>
                    <span class="sm:block hidden particle icon particle-leave-greeny medium n3"/>
                    <span class="particle icon particle-leave-lime medium n4"/>
                    <span class="particle icon particle-leave-tropic medium n5"/>
                    <span class="sm:block hidden particle icon particle-leave-forestgreen medium n6"/>
                    <span class="particle icon particle-leave-green medium n7"/>
                    <span class="particle icon particle-leave-greeny medium n8"/>
                    <span class="sm:block hidden particle icon particle-leave-lime medium n9"/>
                    <span class="particle icon particle-leave-tropic medium n10"/>
                    <span class="particle icon particle-leave-forestgreen medium n11"/>
                    <span class="sm:block hidden particle icon particle-leave-green medium n12"/>
                    <span class="particle icon particle-leave-greeny medium n13"/>
                    <span class="particle icon particle-leave-lime medium n14"/>
                    <span class="sm:block hidden particle icon particle-leave-tropic medium n15"/>
                </template>

                <template v-if="isBannerWinterSnowflakesParticles">
                    <span class="particle icon particle-snowflake-flower medium n1"/>
                    <span class="particle icon particle-snowflake-star medium n2"/>
                    <span class="sm:block hidden particle icon article-snowflake-square medium n3"/>
                    <span class="particle icon particle-snowflake-star medium n4"/>
                    <span class="particle icon particle-snowflake-wheel medium n5"/>
                    <span class="sm:block hidden particle icon particle-snowflake-flower medium n6"/>
                    <span class="particle icon particle-snowflake-round medium n7"/>
                    <span class="particle icon article-snowflake-square medium n8"/>
                    <span class="sm:block hidden particle icon particle-snowflake-star medium n9"/>
                    <span class="particle icon particle-snowflake-wheel medium n10"/>
                    <span class="particle icon particle-snowflake-flower medium n11"/>
                    <span class="sm:block hidden particle icon particle-snowflake-star medium n12"/>
                    <span class="particle icon article-snowflake-square medium n13"/>
                    <span class="particle icon particle-snowflake-star medium n14"/>
                    <span class="sm:block hidden particle icon particle-snowflake-wheel medium n15"/>
                </template>

            </div>
        </div>
    </div>
</template>

<style>
.particle {
    pointer-events: none;
    position: absolute;
    transition: .5s;
}
.particle.big {
    height: 32px;
    width: 32px;
}
.particle.medium {
    height: 24px;
    width: 24px;
}
.particle.small {
    height: 16px;
    width: 16px;
}
.effects-off .particle {
    opacity: 0;
}

.leaves .n1 { animation: falling-leave-animation1 5s infinite linear; top: -20%; }
.leaves .n2 { animation: falling-leave-animation2 8s infinite linear; animation-delay: 1s; top: -20%; }
.leaves .n3 { animation: falling-leave-animation3 10s infinite linear; animation-delay: 3s; top: -20%; }
.leaves .n4 { animation: falling-leave-animation4 5s infinite linear; animation-delay: 5s; top: -20%; }
.leaves .n5 { animation: falling-leave-animation5 8s infinite linear; top: -20%; }
.leaves .n6 { animation: falling-leave-animation6 10s infinite linear; animation-delay: 1s; top: -20%; }
.leaves .n7 { animation: falling-leave-animation7 5s infinite linear; animation-delay: 3s; top: -20%; }
.leaves .n8 { animation: falling-leave-animation8 8s infinite linear; animation-delay: 5s; top: -20%; }
.leaves .n9 { animation: falling-leave-animation9 8s infinite linear; top: -20%; }
.leaves .n10 { animation: falling-leave-animation10 10s infinite linear; animation-delay: 1s; top: -20%; }
.leaves .n11 { animation: falling-leave-animation11 10s infinite linear; animation-delay: 3s; top: -20%; }
.leaves .n12 { animation: falling-leave-animation12 8s infinite linear; animation-delay: 5s; top: -20%; }
.leaves .n13 { animation: falling-leave-animation13 5s infinite linear; top: -20%; }
.leaves .n14 { animation: falling-leave-animation14 5s infinite linear; animation-delay: 1s; top: -20%; }
.leaves .n15 { animation: falling-leave-animation15 5s infinite linear; animation-delay: 3s; top: -20%; }

@keyframes falling-leave-animation1 {
    from { transform: rotate(0); left: 100%; top: -5%; }
    to { transform: rotate(720deg); left: 50%; top: 100%; }
}
@keyframes falling-leave-animation2 {
    from { transform: rotate(0); left: 80%; top: -5%; }
    to { transform: rotate(720deg); left: 20%; top: 100%; }
}
@keyframes falling-leave-animation3 {
    from { transform: rotate(0); left: 60%; top: -5%; }
    to { transform: rotate(720deg); left: 0; top: 100%; }
}
@keyframes falling-leave-animation4 {
    from { transform: rotate(0); left: 120%; top: -20%; }
    to { transform: rotate(720deg); left: 40%; top: 100%; }
}
@keyframes falling-leave-animation5 {
    from { transform: rotate(0); left: 70%; top: -5%; }
    to { transform: rotate(720deg); left: 20%; top: 100%; }
}
@keyframes falling-leave-animation6 {
    from { transform: rotate(0); left: 50%; top: -20%; }
    to { transform: rotate(720deg); left: 0; top: 100%; }
}
@keyframes falling-leave-animation7 {
    from { transform: rotate(0); left: 30%; top: -5%; }
    to { transform: rotate(720deg); left: 0; top: 100%; }
}
@keyframes falling-leave-animation8 {
    from { transform: rotate(0); left: 130%; top: -20%; }
    to { transform: rotate(720deg); left: 20%; top: 100%; }
}
@keyframes falling-leave-animation9 {
    from { transform: rotate(0); left: 40%; top: -5%; }
    to { transform: rotate(720deg); left: 10%; top: 100%; }
}
@keyframes falling-leave-animation10 {
    from { transform: rotate(0); left: 110%; top: -20%; }
    to { transform: rotate(720deg); left: 80%; top: 100%; }
}
@keyframes falling-leave-animation11 {
    from { transform: rotate(0); left: 120%; top: -5%; }
    to { transform: rotate(720deg); left: 60%; top: 100%; }
}
@keyframes falling-leave-animation12 {
    from { transform: rotate(0); left: 90%; top: -20%; }
    to { transform: rotate(720deg); left: 20%; top: 100%; }
}
@keyframes falling-leave-animation13 {
    from { transform: rotate(0); left: 80%; top: -5%; }
    to { transform: rotate(720deg); left: 20%; top: 100%; }
}
@keyframes falling-leave-animation14 {
    from { transform: rotate(0); left: 45%; top: -20%; }
    to { transform: rotate(720deg); left: -20%; top: 100%; }
}
@keyframes falling-leave-animation15 {
    from { transform: rotate(0); left: 80%; top: -5%; }
    to { transform: rotate(720deg); left: 40%; top: 100%; }
}

.snowflakes .n1 { animation: falling-snowflake-animation1 5s infinite linear; top: -20%; }
.snowflakes .n2 { animation: falling-snowflake-animation2 7s infinite linear; animation-delay: 1s; top: -20%; }
.snowflakes .n3 { animation: falling-snowflake-animation3 9s infinite linear; animation-delay: 3s; top: -20%; }
.snowflakes .n4 { animation: falling-snowflake-animation4 4s infinite linear; animation-delay: 5s; top: -20%; }
.snowflakes .n5 { animation: falling-snowflake-animation5 7s infinite linear; top: -20%; }
.snowflakes .n6 { animation: falling-snowflake-animation6 9s infinite linear; animation-delay: 1s; top: -20%; }
.snowflakes .n7 { animation: falling-snowflake-animation7 4s infinite linear; animation-delay: 3s; top: -20%; }
.snowflakes .n8 { animation: falling-snowflake-animation8 7s infinite linear; animation-delay: 5s; top: -20%; }
.snowflakes .n9 { animation: falling-snowflake-animation9 7s infinite linear; top: -20%; }
.snowflakes .n10 { animation: falling-snowflake-animation10 9s infinite linear; animation-delay: 1s; top: -20%; }
.snowflakes .n11 { animation: falling-snowflake-animation11 9s infinite linear; animation-delay: 3s; top: -20%; }
.snowflakes .n12 { animation: falling-snowflake-animation12 7s infinite linear; animation-delay: 5s; top: -20%; }
.snowflakes .n13 { animation: falling-snowflake-animation13 4s infinite linear; top: -20%; }
.snowflakes .n14 { animation: falling-snowflake-animation14 4s infinite linear; animation-delay: 1s; top: -20%; }
.snowflakes .n15 { animation: falling-snowflake-animation15 4s infinite linear; animation-delay: 3s; top: -20%; }

@keyframes falling-snowflake-animation1 {
    from { transform: rotate(0); left: 100%; top: -5%; }
    to { transform: rotate(720deg); left: 70%; top: 100%; }
}
@keyframes falling-snowflake-animation2 {
    from { transform: rotate(0); left: 80%; top: -5%; }
    to { transform: rotate(720deg); left: 40%; top: 100%; }
}
@keyframes falling-snowflake-animation3 {
    from { transform: rotate(0); left: 60%; top: -5%; }
    to { transform: rotate(720deg); left: 20%; top: 100%; }
}
@keyframes falling-snowflake-animation4 {
    from { transform: rotate(0); left: 120%; top: -20%; }
    to { transform: rotate(720deg); left: 60%; top: 100%; }
}
@keyframes falling-snowflake-animation5 {
    from { transform: rotate(0); left: 70%; top: -5%; }
    to { transform: rotate(720deg); left: 40%; top: 100%; }
}
@keyframes falling-snowflake-animation6 {
    from { transform: rotate(0); left: 50%; top: -20%; }
    to { transform: rotate(720deg); left: 20%; top: 100%; }
}
@keyframes falling-snowflake-animation7 {
    from { transform: rotate(0); left: 30%; top: -5%; }
    to { transform: rotate(720deg); left: 10%; top: 100%; }
}
@keyframes falling-snowflake-animation8 {
    from { transform: rotate(0); left: 130%; top: -20%; }
    to { transform: rotate(720deg); left: 40%; top: 100%; }
}
@keyframes falling-snowflake-animation9 {
    from { transform: rotate(0); left: 40%; top: -5%; }
    to { transform: rotate(720deg); left: 20%; top: 100%; }
}
@keyframes falling-snowflake-animation10 {
    from { transform: rotate(0); left: 110%; top: -20%; }
    to { transform: rotate(720deg); left: 70%; top: 100%; }
}
@keyframes falling-snowflake-animation11 {
    from { transform: rotate(0); left: 120%; top: -5%; }
    to { transform: rotate(720deg); left: 80%; top: 100%; }
}
@keyframes falling-snowflake-animation12 {
    from { transform: rotate(0); left: 90%; top: -20%; }
    to { transform: rotate(720deg); left: 40%; top: 100%; }
}
@keyframes falling-snowflake-animation13 {
    from { transform: rotate(0); left: 80%; top: -5%; }
    to { transform: rotate(720deg); left: 40%; top: 100%; }
}
@keyframes falling-snowflake-animation14 {
    from { transform: rotate(0); left: 45%; top: -20%; }
    to { transform: rotate(720deg); left: -10%; top: 100%; }
}
@keyframes falling-snowflake-animation15 {
    from { transform: rotate(0); left: 80%; top: -5%; }
    to { transform: rotate(720deg); left: 50%; top: 100%; }
}

/* =============== [ Медиа-Запрос { 1920px > ?px } ] =============== */

@media screen and (min-width: 1920px) {
    .banner-slide {
        background-size: contain;
    }
    .banner-full .banner-slide {
        background-size: cover;
    }
}
</style>
