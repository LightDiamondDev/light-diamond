<script setup lang="ts">

import Paginator from '@/components/elements/Paginator.vue'
import UserAvatar from '@/components/user/UserAvatar.vue'
import {getFullDate, getRelativeDate} from '@/helpers'
</script>

<template>
<!--    <div class="posts flex flex-wrap w-full gap-2">-->
<!--        <PostCard/>-->
<!--        <PostCard/>-->
<!--        <PostCard/>-->
<!--        <PostCard/>-->
<!--        <PostCard/>-->
<!--        <PostCard/>-->
<!--        <PostCard/>-->
<!--        <PostCard/>-->
<!--        <PostCard/>-->
<!--    </div>-->

    <form class="catalog-panel ld-primary-background ld-primary-border flex flex-col w-full self-center" name="catalog">

        <nav class="flex flex-col">

            <div class="line flex flex-wrap justify-between gap-3 p-3">
                <div class="sub-line flex justify-center flex-wrap gap-3">
                    <ShineButton
                        class="flex items-center"
                        class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                        :class="{ 'active': isFresh }"
                        label="Свежие"
                        icon="icon-clock"
                        @click="isFresh = true"
                    />
                    <ShineButton
                        class="flex items-center"
                        class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                        :class="{ 'active': !isFresh }"
                        label="Популярные"
                        icon="icon-crown"
                        @click="isFresh = false"
                    />
                    <ShineButton
                        v-if="!isFresh"
                        class="active flex items-center option w-[200px]"
                        class-preset="ld-title-font justify-center sm:text-[16px]
                                    xs:text-[14px] text-[12px] gap-1 px-6 py-0.5 whitespace-nowrap"
                        :label="currentTimePeriod"
                        icon="icon-crown"
                        @click="switchTimePeriod()"
                    />
                </div>
                <div class="sub-line flex flex-wrap justify-center gap-3">
                    <ShineButton
                        class="flex items-center option edition xl:min-w-[158px]"
                        class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                        :label="edition === GameEdition.BEDROCK ? 'Bedrock' : 'Java'"
                        :icon="edition === GameEdition.BEDROCK ? 'icon-bedrock-dev-small' : 'icon-minecraft-materials'"
                        @click="switchEdition"
                    />
                </div>
            </div>

            <div class="menu-separator flex self-center"></div>

            <div class="line flex flex-wrap sm:justify-start justify-center gap-3 p-3">
                <ShineButton
                    as="RouterLink"
                    class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                    label="Все"
                    icon="icon-brilliant"
                    :to="{ name: `catalog.${edition?.toLowerCase()}` }"
                />
                <ShineButton
                    as="RouterLink"
                    class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                    label="Новости"
                    icon="icon-news"
                    :to="{ name: `catalog.${edition?.toLowerCase()}` }"
                />
                <ShineButton
                    as="RouterLink"
                    class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                    label="Ресурс-Паки"
                    icon="icon-axolotl-bucket"
                    :to="{ name: `catalog.${edition?.toLowerCase()}` }"
                />
                <ShineButton
                    as="RouterLink"
                    class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                    label="Аддоны"
                    icon="icon-spawn-egg"
                    :to="{ name: `catalog.${edition?.toLowerCase()}` }"
                />
                <ShineButton
                    as="RouterLink"
                    class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                    label="Карты"
                    icon="icon-map"
                    :to="{ name: `catalog.${edition?.toLowerCase()}` }"
                />
                <ShineButton
                    as="RouterLink"
                    class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                    label="Скины"
                    icon="icon-skin"
                    :to="{ name: `catalog.${edition?.toLowerCase()}` }"
                />
                <ShineButton
                    as="RouterLink"
                    class-preset="ld-title-font justify-center sm:text-[16px] xs:text-[14px] text-[12px] gap-1 px-6 py-0.5"
                    label="Статьи"
                    icon="icon-script"
                    :to="{ name: `catalog.${edition?.toLowerCase()}` }"
                />
            </div>

            <div class="menu-separator flex self-center"></div>

            <button
                class="filters-button line flex items-center gap-3 p-3"
                @click="isFilters = !isFilters"
                type="button">
                <span :class="{ 'down-arrow-up': isFilters }" class="icon icon-down-arrow"></span>
                <span>Фильтры</span>
            </button>

            <div v-if="isFilters" class="filters gap-3 p-3">
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



    <div class="flex flex-col gap-4 surface-overlay rounded-lg border p-4 mb-3">
        <slot name="title">
            <p class="text-xl">Материалы</p>
        </slot>
        <div class="flex flex-col xs:flex-row gap-4">
            <div class="flex gap-2">
                <ShineButton
                    label="Свежие"
                    icon="fa-regular fa-clock"
                    size="small"
                    :severity="loadData.sort_type === PostSortType.LATEST ? 'primary' : 'secondary'"
                    @click="onSortTypeSelect(PostSortType.LATEST)"
                />
                <ShineButton
                    label="Популярные"
                    icon="fa-solid fa-fire"
                    size="small"
                    :severity="loadData.sort_type === PostSortType.POPULAR ? 'primary' : 'secondary'"
                    @click="onSortTypeSelect(PostSortType.POPULAR)"
                />
            </div>
<!--            <Dropdown-->
<!--                v-if="loadData.sort_type === PostSortType.POPULAR"-->
<!--                v-model="loadData.period"-->
<!--                :options="periodDropdownItems"-->
<!--                option-label="label"-->
<!--                option-value="value"-->
<!--                scroll-height="14rem"-->
<!--                :pt="periodDropdownPassThroughOptions"-->
<!--                class="self-start xs:self-center"-->
<!--                @update:model-value="loadPosts"-->
<!--            />-->
        </div>
    </div>

    <div v-if="isLoading" class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
<!--        <div v-for="i in 6" class="flex flex-col surface-overlay rounded-lg border p-4 gap-3">-->
<!--            <Skeleton height="auto" class="aspect-video"/>-->
<!--            <div class="flex flex-col gap-4">-->
<!--                <div class="flex gap-2 items-center">-->
<!--                    <Skeleton shape="circle" size="2rem"/>-->
<!--                    <Skeleton height="0.6rem" width="6rem"/>-->
<!--                    <div class="flex gap-1 ml-auto items-center">-->
<!--                        <Skeleton shape="circle" size="1rem"/>-->
<!--                        <Skeleton height="0.6rem" width="4.5rem"/>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="flex flex-col gap-2">-->
<!--                    <Skeleton height="1.25rem"/>-->
<!--                    <Skeleton height="1.25rem" width="60%"/>-->
<!--                </div>-->
<!--                <div class="flex flex-col gap-2">-->
<!--                    <Skeleton height="0.7rem" width="100%"/>-->
<!--                    <Skeleton height="0.7rem" width="95%"/>-->
<!--                    <Skeleton height="0.7rem" width="90%"/>-->
<!--                    <Skeleton height="0.7rem" width="80%"/>-->
<!--                    <Skeleton height="0.7rem" width="85%"/>-->
<!--                    <Skeleton height="0.7rem" width="95%"/>-->
<!--                    <Skeleton height="0.7rem" width="40%"/>-->
<!--                </div>-->
<!--                <div class="flex mt-3 gap-2">-->
<!--                    <div v-for="i in 2" class="flex gap-1 items-center">-->
<!--                        <Skeleton shape="circle" size="1.25rem"/>-->
<!--                        <Skeleton height="0.6rem" width="2rem"/>-->
<!--                    </div>-->
<!--                    <div class="flex gap-1 ml-auto items-center">-->
<!--                        <Skeleton shape="circle" size="1.25rem"/>-->
<!--                        <Skeleton height="0.6rem" width="3rem"/>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
    </div>
    <template v-else>
        <div v-if="posts.length === 0" class="px-4">
            <p class="text-muted">Материалы не найдены.</p>
        </div>
        <div class="posts flex flex-wrap w-full gap-2">
        </div>

        <div v-else class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="post in posts"
                class="flex flex-col surface-overlay rounded-lg border px-4 pt-4 gap-3"
            >
                <RouterLink :to="{name: 'post', params: {slug: post.slug}}">
                    <img :src="post.version!.cover_url" alt="" class="post-cover"/>
                </RouterLink>

                <div class="flex gap-2 items-center">
                    <UserAvatar :user="post.version!.author"/>
                    <p class="text-sm">{{ post.version!.author!.username }}</p>
                    <div
                        :title="`${wasPostUpdated(post) ? 'Обновлено' : 'Опубликовано'} ${getFullDate(post.updated_at)}`"
                        class="text-muted text-xs lg:text-sm flex items-center gap-1.5 ml-auto"
                    >
                        <span
                            class="text-[var(--gray-400)]"
                            :class="{'fa-regular fa-calendar': !wasPostUpdated(post),
                                     'fa-solid fa-clock-rotate-left': wasPostUpdated(post)}"
                        />
                        <p>{{ getRelativeDate(post.updated_at) }}</p>
                    </div>
                </div>
                <!-- :to="{name: 'post', params: {slug: post.slug}}" -->
                <div class="flex flex-col gap-3">
                    <div class="text-xl font-bold transition-colors">
                        {{ post.version!.title }}
                    </div>
                    <p>{{ post.version!.description }}</p>
                </div>

            </div>
        </div>
    </template>
    <Paginator
        class="ld-primary-background ld-fixed-background
            ld-primary-border-bottom ld-primary-border-left
            ld-primary-border-right h-[48px] w-full"
        :class="{'hidden': isLoading || posts.length === 0}"
        :records-at-page="loadData.per_page"
        :totalRecords="totalRecordsCount"
        @page="onPageChange"
    />
</template>

<style scoped>
.posts {
    padding: .5rem 0;
}
/* =============== [ Медиа-Запрос { ?px < 1280px } ] =============== */

@media screen and (max-width: 1279px) {
    .posts {
        padding: .5em;
    }
}
</style>
