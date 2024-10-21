<script setup lang="ts">
const props = defineProps ({
    icon: {
        type: String,
        required: true
    }
})
</script>

<template>
    <span class="set-mark icon-group flex justify-center items-center relative">
        <span :class="icon" class="effect-set-sub-like icon flex absolute sm:h-[32px] h-[24px] sm:w-[32px] w-[24px]"/>
        <span :class="icon" class="effect-set-like icon flex sm:h-[32px] h-[24px] sm:w-[32px] w-[24px]"/>
        <span class="particle n1"/>
        <span class="particle n2"/>
        <span class="particle n3"/>
        <span class="particle n4"/>
        <span class="particle n5"/>
        <span class="particle n6"/>
        <span class="particle n7"/>
        <span class="particle n8"/>
        <span class="particle n9"/>
        <span class="particle n10"/>
        <span class="particle n11"/>
        <span class="particle n12"/>
    </span>
</template>

<style scoped>
.effect-set-like {
    filter: grayscale(100%);
    transition: .5s;
}
.effect-set-like:hover {
    filter: grayscale(60%);
}
.effect-set-sub-like {
    opacity: 0;
}
.active .icon-group .effect-set-like {
    animation: effect-set-like-change-animation 1s ease;
    filter: grayscale(0);
}
.active .icon-group .effect-set-sub-like {
    animation: effect-set-like-fall-animation .3s ease;
}
.icon-group .effect-set-sub-like {
    animation: effect-set-like-fly-animation .3s ease;
}
.particle {
    position: absolute;
    transition: .5s;
    height: 4px;
    width: 4px;
    opacity: 0;
}
.active .particle {
    animation: effect-set-like-particle-animation .8s ease;
}
.n1, .n5, .n9 {
    background-color: rgba(0, 255, 255, 1);
}
.n2, .n6, .n10 {
    background-color: rgba(255, 255, 50, 1);
}
.n3, .n7, .n11 {
    background-color: rgba(50, 150, 50, 1);
}
.n4, .n8, .n12 {
    background-color: rgba(200, 50, 50, 1);
}
.active .icon-group .n1 { margin-bottom: 3.5rem; margin-right: 2rem; }
.active .icon-group .n2 { margin-bottom: 4rem; }
.active .icon-group .n3 { margin-bottom: 3.5rem; margin-left: 2rem; }

.active .icon-group .n4 { margin-left: 3.5rem; margin-bottom: 2rem; }
.active .icon-group .n5 { margin-left: 4rem; }
.active .icon-group .n6 { margin-left: 3.5rem; margin-top: 2rem; }

.active .icon-group .n7 { margin-top: 3.5rem; margin-left: 2rem; }
.active .icon-group .n8 { margin-top: 4rem; }
.active .icon-group .n9 { margin-top: 3.5rem; margin-right: 2rem; }

.active .icon-group .n10 { margin-right: 3.5rem; margin-top: 2rem; }
.active .icon-group .n11 { margin-right: 4rem; }
.active .icon-group .n12 { margin-right: 3.5rem; margin-bottom: 2rem; }

@keyframes effect-set-like-fall-animation
{
    0% {
        bottom: 2rem;
        opacity: 0;
    }
    100% {
        bottom: 0;
        opacity: 1;
    }
}

@keyframes effect-set-like-fly-animation
{
    0% {
        bottom: 0;
        opacity: 1;
    }
    100% {
        bottom: 2rem;
        opacity: 0;
    }
}

@keyframes effect-set-like-change-animation
{
    0% { transform: scale(1); filter: grayscale(100%); }
    20% { transform: scale(1); }
    50% { transform: scale(1.2); }
    60% { transform: scale(1); }
    70% { transform: scale(1.2); }
    100% { transform: scale(1); filter: grayscale(0); }
}

@keyframes effect-set-like-particle-animation
{
    0% { opacity: 0; transform: scale(1) }
    20% { opacity: 1; }
    50% { opacity: 1; transform: scale(1.2) }
    100% { opacity: 0; transform: scale(.5) }
}
</style>
