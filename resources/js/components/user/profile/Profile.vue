<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {computed, ref, watch} from 'vue'
import {RouterLink, useRoute} from 'vue-router'

import {changeTitle, getCounterLabel, getErrorMessageByCode, getTitle} from '@/helpers'
import {useToastStore} from '@/stores/toast'

import {type Material, type User} from '@/types'

import TabMenu, {type TabMenuChangeEvent} from '@/components/elements/TabMenu.vue'
import Button from '@/components/elements/Button.vue'
import ProcessingDiggingBlocks from '@/components/elements/ProcessingDiggingBlocks.vue'

interface MaterialSubmissionLoadResponseData {
    success: boolean
    message?: string
    errors?: object
    records?: Material[]
    pagination?: {
        total_records: number
        current_page: number
        total_pages: number
    }
}

const props = defineProps({
    username: {
        type: String,
        required: true
    }
})

const route = useRoute()

watch(route, () => {
    updateTitle()
})

watch(() => props.username, () => {
    loadUser()
})

const toastStore = useToastStore()
const user = ref<User>()

const page = ref(1)
const perPage = ref(8)

const loadRequestData = computed(() => ({
    status: props.status,
    page: page.value,
    per_page: perPage.value
}))

const isLoading = ref(false)

const tabMenuItems = computed(() => [
    {
        label: `Материалы [${user.value?.materials_count}]`,
        icon: 'icon-news',
        route: { name: 'profile.materials' },
        routes: [ 'profile.materials' ]
    },
    {
        label: `Избранное [${user.value?.favourite_materials_count}]`,
        icon: 'icon-diamond',
        route: { name: 'profile.favourite-materials' },
        routes: [ 'profile.favourite-materials' ]
    },
    {
        label: `Комментарии [${user.value?.comments_count}]`,
        icon: 'icon-comment',
        route: { name: 'profile.comments' },
        routes: [ 'profile.comments' ]
    }
])

function loadUser() {
    isLoading.value = true

    axios.get(`/api/users/${props.username}`).then((response) => {
        const responseData: User = response.data
        if (responseData) {
            user.value = responseData
        } else {
            toastStore.error('Произошла ошибка!')
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

function onTabChange(event: TabMenuChangeEvent) {
    const selectedStatus = tabMenuItems.value[event.tabIndex].status
    if (loadRequestData.value.status !== selectedStatus) {
        loadRequestData.value.status = selectedStatus
        loadRequestData.value.page = 1
        totalRecords.value = 0
        loadMaterialSubmissions()
    }
}

function updateTitle() {
    changeTitle(getTitle().replace(/Профиль/g, `Профиль ${props.username}`))
}

const profileBackgroundSrc = ref('')
const profileAvatarSrc = ref('')

// profileBackgroundSrc.value = '/images/elements/home-banner-trial-chamber.png'
profileBackgroundSrc.value = '/images/elements/default.png'
profileAvatarSrc.value = '/images/users/avatars/avatar-light-diamond.png'
// const activeColor = ref('#00ffff')

loadUser()
updateTitle()
</script>

<template>
    <div
        class="profile-background leaves flex justify-center min-h-[100vh] w-full relative"
        :style="{ 'background-image': `url(${profileBackgroundSrc})`  }"
        style="background-attachment: fixed; background-position-x: center; overflow: clip"
    >
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
        <div v-if="isLoading" class="flex justify-center items-center overflow-hidden h-[85vh] w-full">
            <ProcessingDiggingBlocks processing-classes="md:h-[192px] md:w-[192px] h-[128px] w-[128px]"/>
        </div>
        <div v-else-if="user" class="profile-wrap ld-secondary-blur-background flex max-w-[1024px] w-full gap-4 p-4">
            <section class="materials ld-secondary-text  flex-col text-[12px] w-full">
                <div class="flex sm:flex-row flex-col items-center">
                    <RouterLink
                        class="profile-avatar max-w-fit min-w-fit h-fit overflow-hidden cursor-pointer"
                        style="border: 2px solid rgba(255, 255, 255, .5)"
                        :to="{name: 'home'}"
                    >
                        <!-- max-h-[160px] max-w-[160px] min-w-[160px] -->
                        <img alt="Аватар" class="max-h-[128px] max-w-[128px] min-w-[128px] duration-200" :src="profileAvatarSrc">
                    </RouterLink>
                    <div class="flex flex-col gap-2 px-4">
                        <div class="flex sm:flex-row flex-col items-center sm:gap-4 sm:mt-0 mt-4">
                            <p class="flex text-[24px] border-0 w-fit">{{ user.username }}</p>
                            <p v-if="user.materials_count > 0" class="ld-trinity-text ld-title-font text-[24px]">Автор</p>
                        </div>
                        <!-- <p>Россия, 21 год</p> -->
                        <div v-if="user.materials_count > 0" class="flex flex-wrap justify-center text-[14px] gap-8 mt-2">

                            <div class="flex flex-col items-center">
                                <div class="ld-trinity-text flex gap-1">
                                    <span class="icon-heart icon flex"/>
                                    <span class="ld-title-font text-[24px]">{{
                                            getCounterLabel(user.collected_likes_count)
                                        }}</span>
                                </div>
                                <p>Лайки</p>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="ld-trinity-text flex gap-1">
                                    <span class="icon-download icon flex"/>
                                    <span class="ld-title-font text-[24px]">{{
                                            getCounterLabel(user.collected_downloads_count)
                                        }}</span>
                                </div>
                                <p>Скачивания</p>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="ld-trinity-text flex gap-1">
                                    <span class="icon-eye icon flex"/>
                                    <span class="ld-title-font text-[24px]">{{
                                        getCounterLabel(user.collected_views_count)
                                      }}</span>
                                </div>
                                <p>Просмотры</p>
                            </div>
                        </div>
                        <!-- <p class="mt-2">Исследователь творческих просторов! Разработчик Аддона Light Diamond.</p> -->
                    </div>
                </div>

                <div class="units flex pt-2" style="border-bottom: 2px solid rgba(255, 255, 255, .5)">
                    <TabMenu
                        item-classes="ld-title-font justify-center h-[48px] min-w-[64px] md:text-[1rem] text-[14px] gap-1 lg:px-4 px-1"
                        item-label-classes="xs:flex hidden"
                        :items="tabMenuItems"
                        @tab-change="onTabChange"
                    />
                </div>
                <div class="content relative">
                    <RouterView v-slot="{ Component }">
                        <Transition name="smooth-manager-horizontal-switch">
                            <Component
                                class="w-full"
                                :is="Component"
                                :userId="user.id"
                                :username="user.username"
                            />
                        </Transition>
                    </RouterView>
                </div>
            </section>
            <!-- Score -->
        </div>
        <div v-else class="profile-wrap ld-secondary-blur-background flex justify-center max-w-[1024px] w-full p-4">
            <div class="ld-secondary-text flex flex-col items-center mt-[8rem]">
                <h1 class="ld-title-font text-4xl text-center mt-8">Пользователь не найден</h1>
                <p class="text-muted text-center mt-4">Похоже, что запрашиваемого Пользователя не существует!</p>
                <div class="mob phantom flex justify-center items-center
                    sm:h-[200px] h-[100px] max-w-[320] w-full full-locked"
                >
                    <div
                        class="animation-flying-phantom sm:h-[160px] h-[80px] sm:w-[320px] w-[160px]"
                        style="background-size: 100% 100%"
                    />
                </div>
                <RouterLink class="flex justify-center" :to="{ name: 'home' }">
                    <Button
                        button-type="submit"
                        icon="item-ender-pearl"
                        icon-size="32px"
                        press-classes="px-4"
                        label="Телепортироваться домой"
                    />
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<style scoped>
.profile-background {
    background-size: cover;
}
.profile-avatar:hover img {
    transform: scale(1.2);
}
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
</style>
