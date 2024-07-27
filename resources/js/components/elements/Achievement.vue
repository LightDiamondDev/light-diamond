<script setup lang="ts">
const props = defineProps({
    achievementDescription: String,
    achievementTitle: String,
    icon: String
});
</script>

<template>
    <div class="achievement flex locked" >
        <div class="indicator flex">
            <div class="cover">
                <div class="set icon icon-border flex justify-center items-center">
                    <span class="icon" :class="icon"></span>
                </div>
            </div>
        </div>
        <div class="content flex justify-between pl-2 pt-1">
            <div class="description flex flex-col">
                <h1 class="title">Получено Достижение</h1>
                <h1 class="name">{{ achievementTitle }}</h1>
                <p>
                    Поздравляем, Вы заработали Достижение
                    «<span>{{ achievementTitle }}</span>»
                    — {{ achievementDescription }}!
                </p>
            </div>
            <button class="close flex justify-center items-center">
                <span class="icon icon-cross"></span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.achievement {
    margin: .5rem .5rem 0 .5rem;
    min-height: 72px;
}
.achievement .content {
    width: 100%;
}
.achievement .indicator {
    background: linear-gradient(
        90deg,
        rgba(0, 255, 255, .8),
        rgba(105, 255, 155, .8),
        rgba(255, 210, 255, .8),
        rgba(0, 255, 255, .8),
        rgba(105, 255, 155, .8),
        rgba(255, 205, 100, .8),
        rgba(105, 255, 155, .8),
        rgba(0, 255, 255, .8)
    );
    animation: transfusion-animation 15s linear infinite;
    background-size: 400% 100%;
}
.achievement .indicator .cover {
    background-image: url('/images/elements/base-background.png');
    width: 100%;
}
.achievement .indicator .set {
    animation: icon-set-animation 1s infinite;
    margin-right: .5rem;
    margin-left: .4rem;
    transition: .5s;
    height: 32px;
    width: 32px;
}
.achievement .set span.icon {
    height: 24px;
    width: 24px;
}
.achievement button.close {
    cursor: pointer;
    min-width: 32px;
    height: 32px;
}
.achievement button.close .icon {
    height: 24px;
    width: 24px;
}
.achievement h1 {
    font-size: 1.1rem;
}
.achievement p {
    color: var(--primary-text-color);
    font-size: .8rem;
    opacity: .8;
}
.achievement h1.title {
    color: var(--primary-text-color);
}
.achievement h1.name,
.achievement p span {
    animation: achievement-name-animation 10s infinite ease;
}

/* =============== [ Анимации Элементов ] =============== */

@keyframes icon-set-animation
{
    0% { margin-top: .5rem; }
    50% { margin-top: 1rem; }
    100% { margin-top: .5rem; }
}
@keyframes achievement-name-animation
{
    0% { color: rgb(0, 255, 255); }
    15% { color: rgb(105, 255, 155); }
    30% { color: rgb(255, 210, 255); }
    45% { color: rgb(0, 255, 255); }
    60% { color: rgb(105, 255, 155); }
    75% { color: rgb(255, 205, 100); }
    90% { color: rgb(105, 255, 155); }
    100% { color: rgb(0, 255, 255); }
}
</style>
