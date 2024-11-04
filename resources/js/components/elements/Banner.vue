<script setup lang="ts">
import {ref} from 'vue'

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

const bannerImagesSrc = ref(props.imagesSrc)
const timeInterval = ref(props.timeInterval)
const currentBannerImageSrc = ref(0)

const isBannerAnimation = ref(true)

function launchBannerSwitchSlidesAnimation() {
    if (bannerImagesSrc.value.length > 1) {
        setInterval(function () {
            if (currentBannerImageSrc.value < bannerImagesSrc.value.length - 2) {
                currentBannerImageSrc.value++
            } else {
                currentBannerImageSrc.value = 0
            }
        }, timeInterval.value)
    }
}

launchBannerSwitchSlidesAnimation()
</script>

<template>
    <div class="banner flex justify-center max-w-[100vw] w-full overflow-hidden">
        <div
            class="banner-slide bg-fixed flex h-full w-full duration-1000"
            :style="{ 'background-image': `url(${bannerImagesSrc[currentBannerImageSrc]})` }"
        >
            <div class="banner-effects flex justify-center leaves h-full w-full relative" :class="{ 'effects-off': !isBannerAnimation }">

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
                <span class="particle icon item-leave-gold n1"/>
                <span class="particle icon item-leave-lime n2"/>
                <span class="sm:block hidden particle icon item-leave-orange n3"/>
                <span class="particle icon item-leave-red n4"/>
                <span class="particle icon item-leave-yellow n5"/>
                <span class="sm:block hidden particle icon item-leave-gold n6"/>
                <span class="particle icon item-leave-lime n7"/>
                <span class="particle icon item-leave-orange n8"/>
                <span class="sm:block hidden particle icon item-leave-red n9"/>
                <span class="particle icon item-leave-yellow n10"/>
                <span class="particle icon item-leave-gold n11"/>
                <span class="sm:block hidden particle icon item-leave-lime n12"/>
                <span class="particle icon item-leave-orange n13"/>
                <span class="particle icon item-leave-red n14"/>
                <span class="sm:block hidden particle icon item-leave-yellow n15"/>
            </div>
        </div>
    </div>
</template>

<style>
.particle {
    pointer-events: none;
    position: absolute;
    transition: .5s;
    height: 32px;
    width: 32px;
}
.effects-off .particle {
    opacity: 0;
}

.leaves .n1 { animation: falling-leave-animation1 5s infinite linear; }
.leaves .n2 { animation: falling-leave-animation2 8s infinite linear; animation-delay: 1s; }
.leaves .n3 { animation: falling-leave-animation3 10s infinite linear; animation-delay: 3s; }
.leaves .n4 { animation: falling-leave-animation4 5s infinite linear; animation-delay: 5s; }
.leaves .n5 { animation: falling-leave-animation5 8s infinite linear; }
.leaves .n6 { animation: falling-leave-animation6 10s infinite linear; animation-delay: 1s; }
.leaves .n7 { animation: falling-leave-animation7 5s infinite linear; animation-delay: 3s; }
.leaves .n8 { animation: falling-leave-animation8 8s infinite linear; animation-delay: 5s; }
.leaves .n9 { animation: falling-leave-animation9 8s infinite linear; }
.leaves .n10 { animation: falling-leave-animation10 10s infinite linear; animation-delay: 1s; }
.leaves .n11 { animation: falling-leave-animation11 10s infinite linear; animation-delay: 3s; }
.leaves .n12 { animation: falling-leave-animation12 8s infinite linear; animation-delay: 5s; }
.leaves .n13 { animation: falling-leave-animation13 5s infinite linear; }
.leaves .n14 { animation: falling-leave-animation14 5s infinite linear; animation-delay: 1s; }
.leaves .n15 { animation: falling-leave-animation15 5s infinite linear; animation-delay: 3s; }

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
</style>

<style scoped>
.banner-slide {
    background-repeat: no-repeat;
}

/* =============== [ Медиа-Запрос { 1920px < ?px } ] =============== */

@media screen and (min-width: 1920px) {
    .banner-slide {
        background-size: cover;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1920px } ] =============== */

@media screen and (max-width: 1919px) {
    .banner-slide {
        background-position-x: center;
    }
}
</style>
