<script setup lang="ts">
import {type PropType, ref} from 'vue'
import {GameEdition} from '@/types'
import {getRandomColor, getRandomSplash} from '@/stores/splashes'
import usePreferenceManager from '@/preference-manager'
import {useAuthStore} from '@/stores/auth'
import {useRouter} from 'vue-router'
import Posts from '@/components/catalog/Posts.vue'
import Banner from '@/components/elements/Banner.vue'

const props = defineProps({
    edition: {
        type: String as PropType<GameEdition>,
        required: true
    },
    categorySlug: String
})

const authStore = useAuthStore()
const router = useRouter()

const isFresh = ref(true)
const isFilters = ref(false)

const timePeriod = [
    'За сутки',
    'За неделю',
    'За месяц',
    'За год',
    'За всё время'
]
let timePeriodCounter = 0;
let currentTimePeriod = ref('За всё время')
const activeSplash = getRandomSplash()
const activeColor = getRandomColor()

function switchTimePeriod() {
    timePeriodCounter++
    if (timePeriodCounter == timePeriod.length) timePeriodCounter = 0;
    currentTimePeriod.value = timePeriod[timePeriodCounter]
}

function switchEdition() {
    const edition = props.edition === GameEdition.BEDROCK ? GameEdition.JAVA : GameEdition.BEDROCK
    router.push({name: `catalog.${edition.toLowerCase()}`})
    usePreferenceManager().setEdition(edition)
}
</script>

<template>
<Banner title="Каталог Light Diamond" :is-title-visible="false"/>
<div class="catalog-container ld-secondary-background flex justify-center w-full">
    <section class="catalog flex flex-col items-center w-full">

        <div class="title flex flex-col justify-center items-center w-full">
            <RouterLink
                class="logo-wrap flex items-center full-locked relative orange"
                :to="{ name: `catalog.${edition?.toLowerCase()}` }"
            >
                <img alt="Logo" class="materials-logo h-[48px] md:h-[100px]" src="/images/elements/light-diamond-materials-logo.png"/>
                <span class="base flex justify-center items-center">
                    <span
                        class="splash flex justify-center"
                        :class="{
                            'bottom-splash': activeSplash.length > 20,
                            'large-splash': activeSplash.length > 30,
                            'small-splash': activeSplash.length < 31
                        }"
                        :style="{ 'color': activeColor }"
                    >
                        {{ activeSplash }}
                    </span>
                </span>
            </RouterLink>
        </div>

        <form class="catalog-panel ld-primary-background ld-primary-border flex flex-col w-full self-center" name="catalog">

            <nav class="flex flex-col">

                <div class="line flex flex-wrap justify-between gap-4 p-4">
                    <div class="sub-line flex justify-center flex-wrap gap-4">
                        <button
                            :class="{ 'active': isFresh }"
                            class="ld-shine-button flex items-center option"
                            type="button"
                            @click="isFresh = true"
                        >
                            <span class="press flex">
                                <span class="preset flex items-center gap-1">
                                    <span class="icon icon-clock"/>
                                    <span>Свежие</span>
                                </span>
                            </span>
                        </button>
                        <button
                            :class="{ 'active': !isFresh }"
                            class="ld-shine-button flex items-center option"
                            type="button"
                            @click="isFresh = false"
                        >
                            <span class="press flex">
                                <span class="preset flex items-center gap-1">
                                    <span class="icon icon-crown"/>
                                    <span>Популярные</span>
                                </span>
                            </span>
                        </button>
                        <button
                            v-if="!isFresh"
                            class="ld-shine-button active flex items-center option w-[200px]"
                            type="button"
                            @click="switchTimePeriod()"
                        >
                            <span class="press flex">
                                <span class="preset flex items-center gap-1">
                                    <span class="icon icon-crown"/>
                                    <span style="white-space: nowrap;">{{ currentTimePeriod }}</span>
                                </span>
                            </span>
                        </button>
                    </div>
                    <div class="sub-line flex flex-wrap justify-center gap-4">
                        <button
                            class="ld-shine-button flex items-center option edition"
                            type="button"
                            @click="switchEdition"
                        >
                            <span class="press flex">
                                <span class="preset flex items-center gap-1">
                                    <span
                                        :class="{ 'icon-bedrock-dev-small': edition === GameEdition.BEDROCK,
                                        'icon-minecraft-materials': edition === GameEdition.JAVA }"
                                        class="icon"
                                    />
                                    <span>{{ edition === GameEdition.BEDROCK ? 'Bedrock' : 'Java' }}</span>
                                </span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="menu-separator flex self-center"></div>

                <div class="line flex flex-wrap gap-4 p-4">

                    <RouterLink class="ld-shine-button flex items-center" :to="{ name: `catalog.${edition?.toLowerCase()}` }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-brilliant"/>
                                <span>Все</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="ld-shine-button flex items-center" :to="{ name: `catalog.${edition?.toLowerCase()}` }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-news"/>
                                <span>Новости</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="ld-shine-button flex items-center" :to="{ name: `catalog.${edition?.toLowerCase()}` }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-axolotl-bucket"/>
                                <span>Ресурс-Паки</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="ld-shine-button flex items-center" :to="{ name: `catalog.${edition?.toLowerCase()}` }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-spawn-egg"/>
                                <span>Аддоны</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="ld-shine-button flex items-center" :to="{ name: `catalog.${edition?.toLowerCase()}` }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-map"/>
                                <span>Карты</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="ld-shine-button flex items-center" :to="{ name: `catalog.${edition?.toLowerCase()}` }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-skin"/>
                                <span>Скины</span>
                            </span>
                        </span>
                    </RouterLink>
                    <RouterLink class="ld-shine-button flex items-center" :to="{ name: `catalog.${edition?.toLowerCase()}` }">
                        <span class="press flex">
                            <span class="preset flex items-center gap-1">
                                <span class="icon icon-script"/>
                                <span>Статьи</span>
                            </span>
                        </span>
                    </RouterLink>
                </div>

                <div class="menu-separator flex self-center"></div>

                <button
                    class="filters-button line flex items-center gap-4 p-4"
                    @click="isFilters = !isFilters"
                    type="button">
                    <span :class="{ 'down-arrow-up': isFilters }" class="icon icon-down-arrow"></span>
                    <span>Фильтры</span>
                </button>

                <div v-if="isFilters" class="filters gap-4 p-4">
                    <p>Фильтры всякие там да</p>
                    <p>Ну вот Ресус Пики да</p>
                    <p>ДА Резус Факи да</p>
                    <p>Всякие там да филтры</p>
                    <p>А когда ВЫайП?</p>
                    <p>АбоБс, Это тебе не Сервер</p>
                    <p>А да Фильтры всякие там да</p>
                </div>

            </nav>
        </form>

        <Posts/>

    </section>
</div>
</template>

<style scoped>
footer {
    margin-top: 208px;
}
.catalog-container {
    background-attachment: fixed;
    position: relative;
    margin-top: 208px;
}
.catalog-container .title {
    position: relative;
    line-height: 1.1;
    font-size: 3rem;
    height: 208px
}
.catalog-container .title {
    margin-top: -208px;
    overflow: hidden;
    height: 208px
}
.catalog-container .base {
    transform: rotate(-15deg);
    transform-origin: center;
    position: absolute;
    height: 10px;
    width: 10px;
    bottom: -8%;
    right: 5%;
}
.catalog-container .splash {
    animation: splash-animation 1s infinite;
    text-shadow: 2px 2px black;
    position: absolute;
    text-align: center;
    width: 240px;
}
.catalog-container .splash.bottom-splash {
    bottom: -1.5rem;
    width: 230px;
}
.large-splash {
    font-size: .8rem;
}
.small-splash {
    font-size: .9rem;
}
section.catalog {
    max-width: 1280px;
}
.catalog-panel .line a,
.catalog-panel .line button {
    max-width: 268px;
    flex-grow: 1;
    border: none;
}
.catalog-panel .line .option {
    flex-grow: 0;
}
.catalog-panel .line a .press,
.catalog-panel .line button .press {
    width: 100%;
}
.catalog-panel .line a .preset,
.catalog-panel .line button .preset {
    justify-content: center;
    padding: 0 1.5rem;
    height: 40px;
    width: 100%;
}
.catalog-panel .line a:hover .press .icon,
.catalog-panel .line button:hover .press .icon {
    animation: icon-trigger-up-animation .3s ease;
}
.menu-separator {
    width: 98%;
}
.catalog-panel .filters-button {
    font-size: 1.1rem;
}
.catalog-panel .filters {
    height: 240px;
}
.icon-down-arrow {
    transition: .5s;
}
.down-arrow-up {
    transform: rotate(-180deg);
}

/* =============== [ Анимации ] =============== */

.smooth-settings-switch-enter-active,
.smooth-auth-switch-leave-active {
    transition: .8s ease;
    position: absolute;
}

.smooth-settings-switch-enter-from {
    transform: translateY(100%);
    transition: .8s;
    opacity: 0;
}

.smooth-settings-switch-leave-to {
    transform: translateY(-100%);
    transition: .8s;
    opacity: 0;
}

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px) {
    .logo-wrap .materials-logo {
        height: 72px;
    }
    .catalog-container .base {
        bottom: -12%;
        right: 5%;
    }
    .catalog-container .splash {
        width: 230px;
    }
    .catalog-container .splash.bottom-splash {
        bottom: -2rem;
    }
    .catalog-panel .line .option,
    .catalog-panel .sub-line {
        flex-grow: 1;
    }
    .menu-separator {
        width: 95%;
    }
}

/* =============== [ Медиа-Запрос { ?px < 451px } ] =============== */

@media screen and (max-width: 450px) {
    .catalog-container .title {
        height: 104px;
    }
    .catalog-container .splash {
        max-width: 150px;
    }
    .catalog-container .splash.bottom-splash {
        bottom: -1rem;
        right: -80px;
    }
    .logo-wrap .materials-logo {
        height: 48px;
    }
    .large-splash {
        font-size: .5rem;
    }
    .small-splash {
        font-size: .6rem;
    }
    .catalog-panel .line a,
    .catalog-panel .line button {
        max-width: 180px;
    }
    .catalog-panel .line a .preset,
    .catalog-panel .line button .preset {
        padding: 0 .2rem;
        height: 36px;
    }
    .catalog-panel .line .preset span {
        font-size: .8rem;
    }
    .menu-separator {
        width: 95%;
    }
}
</style>
