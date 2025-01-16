<script setup lang="ts">
import {ref} from 'vue'

import ShineButton from '@/components/elements/ShineButton.vue'

const isCookieAccepted = ref(localStorage.getItem('is-cookie-accepted') === 'true')

function acceptCookie() {
    localStorage.setItem('is-cookie-accepted', 'true')
    isCookieAccepted.value = true
}
</script>

<template>
    <div class="cookie-notice flex justify-end items-end h-[100vh] w-[100vw] fixed z-[3] pointer-events-none">
        <Transition appear name="animation-cookies-notice">
            <div
                v-if="!isCookieAccepted"
                class="ld-primary-background ld-primary-border flex flex-col
                items-center max-w-[480px] sm:my-4 lg:mx-7 sm:mx-4 m-2"
                style="pointer-events: visible"
                ref="elementCookieMessage"
            >
                <div class="flex xs:flex-row flex-col-reverse items-center gap-2 p-2">
                    <span class="icon-cookie icon flex xs:min-w-[5rem] min-w-[4rem] xs:h-[5rem] h-[4rem]"/>
                    <div class="flex flex-col xs:items-start items-center xs:text-start text-center gap-2">
                        <h3 class="ld-title-font xs:text-[20px] text-[16px]">Мы используем Cookie</h3>
                        <p class="xs:text-[14px] text-[12px]">Файлы Cookie помогают нашему Сайту улучшать Ваш пользовательский опыт!</p>
                    </div>
                </div>
                <div class="flex w-full gap-2 p-2">
                    <ShineButton
                        class="confirm flex items-center w-full"
                        class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                        label="Здорово!"
                        icon="icon-tick"
                        @click="acceptCookie"
                    />
                </div>
            </div>
        </Transition>
    </div>
</template>
