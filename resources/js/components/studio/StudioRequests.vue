<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {useAuthStore} from '@/stores/auth'
import {useToastStore} from '@/stores/toast'
import Button from '@/components/elements/Button.vue'
import MaterialLine from '@/components/post/PostRowCard.vue'

const authStore = useAuthStore()
const toastStore = useToastStore()

</script>

<template>
    <div class="section flex flex-col w-full">
        <form action="" class="flex flex-col h-full w-full">

            <div class="line flex items-center gap-4 pl-4">
                <RouterLink class="shine-button flex items-center" :to="{ name: 'post-create' }">
                    <span class="press flex w-full">
                        <span class="preset flex items-center gap-1">
                            <span class="icon icon-brilliant"/>
                            <span>Создать</span>
                        </span>
                    </span>
                </RouterLink>
            </div>

            <div class="separator self-center w-[98%]"></div>

            <div class="types flex gap-4 pb-2 pl-4 pr-4 pt-2">
                <button class="unit flex items-center gap-2">
                    <span class="icon icon-script flex"></span>
                    <span class="text">Черновики</span>
                </button>
                <button class="unit flex items-center gap-2">
                    <span class="icon icon-clock flex"></span>
                    <span class="text">Проверяются</span>
                </button>
                <button class="unit flex items-center gap-2">
                    <span class="icon icon-tick flex"></span>
                    <span class="text">Принятые</span>
                </button>
                <button class="unit flex items-center gap-2">
                    <span class="icon icon-small-cross flex"></span>
                    <span class="text">Отклонённые</span>
                </button>
            </div>

            <div class="separator self-center w-[98%]"></div>

            <MaterialLine
                title="Ресурс-Пак Дядя Шнюк [Шлюх 2.0] [1.2.46]" class="h-[114px] pl-4 pr-4 gap-4"
                cover="/images/users/boba.png" :likes="585" :subscribes="365" :comments="78"
                :downloads="876" :views="965" time="1 нед. назад"
            />

            <div class="separator self-center w-[98%]"></div>

            <MaterialLine
                title="Аддон Light Diamond [0.1.2] Scary Nightmare" class="h-[114px] pl-4 pr-4 gap-4"
                cover="/images/users/hoba.png" :likes="361" :subscribes="216" :comments="158"
                :downloads="781" :views="812" time="3 дн. назад"
            />

            <div class="separator self-center w-[98%]"></div>

            <MaterialLine
                title="Аддон Barygi Atakyut [1.1]" class="h-[114px] pl-4 pr-4 gap-4"
                cover="/images/users/hobana.png" :likes="31" :subscribes="23" :comments="56"
                :downloads="68" :views="341" time="1 нед. назад"
            />

            <div class="separator self-center w-[98%]"></div>

            <MaterialLine
                title="Ресурс-Пак Дядя Шнюк [Шлюх 2.0] [1.2.46]" class="h-[114px] pl-4 pr-4 gap-4"
                cover="/images/users/content/cg.png" :likes="585" :subscribes="365" :comments="78"
                :downloads="876" :views="965" time="1 нед. назад"
            />

            <div class="separator self-center w-[98%]"></div>

            <MaterialLine
                title="Ресурс-Пак Дядя Шнюк [Шлюх 2.0] [1.2.46]" class="h-[114px] pl-4 pr-4 gap-4"
                cover="/images/users/boba.png" :likes="585" :subscribes="365" :comments="78"
                :downloads="876" :views="965" time="1 нед. назад"
            />

            <div class="separator self-center w-[98%]"></div>

            <div class="pages flex justify-center items-center h-[96px] gap-6">
                <button class="flex min-w-[32px] relative" type="button">
                    <span class="icon icon-left-arrow absolute" style="left: .8rem;"></span>
                    <span class="icon icon-left-arrow"></span>
                </button>
                <button class="flex" type="button">
                    <span class="icon icon-left-arrow flex"></span>
                </button>
                <div class="flex">1</div>
                <button class="flex" type="button">
                    <span class="icon icon-right-arrow flex"></span>
                </button>
                <button class="flex min-w-[32px] relative" type="button">
                    <span class="icon icon-right-arrow absolute" style="right: .8rem;"></span>
                    <span class="icon icon-right-arrow"></span>
                </button>
            </div>

        </form>
    </div>
</template>

<style scoped>
.section .line {
    height: 72px;
}
.shine-button .preset {
    padding: 0.2rem 1.5rem;
}
.unit {
    transition: .2s;
}
.unit:hover {
    color: var(--hover-text-color);
}
</style>
