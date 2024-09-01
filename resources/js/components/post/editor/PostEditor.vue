<script setup lang="ts">
import {computed, type PropType, ref} from 'vue'
import {GameEdition, type PostVersion, type User} from '@/types'
import {usePostCategoryStore} from '@/stores/postCategory'
import usePreferenceManager from '@/preference-manager'

import {Blockquote} from '@tiptap/extension-blockquote'
import {Document} from '@tiptap/extension-document'
import {Heading} from '@tiptap/extension-heading'
import {History} from '@tiptap/extension-history'
import {Image} from '@tiptap/extension-image'
import {Link} from '@tiptap/extension-link'
import {Placeholder} from '@tiptap/extension-placeholder'
import {StarterKit} from '@tiptap/starter-kit'
import {Text} from '@tiptap/extension-text'
import {Underline} from '@tiptap/extension-underline'

import CharacterCount from '@tiptap/extension-character-count'
import Editor from '@/components/elements/editor/Editor.vue'
import Select from '@/components/elements/Select.vue'
import UploadImage from '@/components/elements/UploadImage.vue'
import UploadFile from '@/components/elements/UploadFile.vue'
import UserAvatar from '@/components/user/UserAvatar.vue'
import Textarea from '@/components/elements/Textarea.vue'

defineProps({
    author: {
        type: Object as PropType<User>,
        required: true,
    },
    editable: {
        type: Boolean,
        default: true,
    },
    errors: {
        type: Object,
        required: true,
    },
    derrors2: {
        type: Object as PropType<{ [key: string]: string[] }>,
        required: false
    }
})

const preferenceManager = usePreferenceManager()
const postCategoryStore = usePostCategoryStore()
// const errors = ref<string[][]>([])
const postVersion = defineModel<PostVersion>({default: {}})
const gameEdition = ref<GameEdition|null>(preferenceManager.getEdition())
const categories = computed(() => postCategoryStore.categories.filter(
    (category) => category.edition === gameEdition.value || category.edition === null)
)

const gameEditions = [
    {
        icon: 'icon-diamond',
        label: 'Любое Издание',
        value: null
    },
    {
        icon: 'icon-bedrock-dev-small',
        label: 'Бедрок',
        value: GameEdition.BEDROCK
    },
    {
        icon: 'icon-minecraft-materials',
        label: 'Джава',
        value: GameEdition.JAVA
    }
]

const titleEditorExtensions = [
    Text,
    CharacterCount.configure({
        limit: 100
    }),
    History,
    Heading.configure({
        HTMLAttributes: {
            class: 'post-title',
        },
        levels: [1],
    }),
    Document.extend({
        content: 'heading',
    }),
    Placeholder.configure({
        placeholder: 'Название',
    })
]

const contentEditorExtensions = [
    StarterKit.configure({
        heading: false,
        blockquote: false,
    }),
    Heading
        .extend({
            marks: ''
        })
        .configure({
            levels: [1, 2, 3],
        }),
    Blockquote.extend({
        priority: 101,
    }),
    Placeholder.configure({
        placeholder: ({node}) => {
            switch (node.type.name) {
                case 'heading':
                    return 'Заголовок'
                case 'codeBlock':
                    return '// Код'
                default:
                    return 'Какой-нибудь текст...'
            }
        },
    }),
    Underline,
    Link.configure({
        openOnClick: 'whenNotEditable',
    }),
    Image
]

function onEditionChange(editionOption: any) {
    postVersion.value.category_id = undefined
}

</script>

<template>

    <slot name="banner"/>

    <slot name="header"/>

    <div class="first-section bg-lighter w-full">
        <section class="page-container flex justify-center">

            <div class="content w-full">

                <div class="interface flex flex-col">

                    <div class="origin-info flex justify-between">
                        <RouterLink class="author-wrap flex items-center w-fit gap-2 ml-[-2px] pb-2 pt-2" :to="{name: 'home'}">
                            <span class="icon-border flex justify-center items-center h-[48px] w-[48px]">
                                <UserAvatar :user="author"/>
                            </span>
                            <span class="username duration-200">{{ author.username }}</span>
                        </RouterLink>
                        <slot name="mark"/>
                    </div>

                    <Editor
                        v-model="postVersion.title"
                        :class="{'red-overlay': errors['title']}"
                        class="material-name flex justify-center max-w-[1280px]"
                        :editable="editable"
                        :extensions="titleEditorExtensions"
                        plain-text
                        without-menus
                    />

                    <p class="error mb-6">{{ errors['title']?.[0] || ' ' }}</p>

                    <UploadImage
                        :class="{'red-overlay': errors['cover_file']}"
                        class="upload-post-preview flex"
                        :editable="editable"
                        icon="icon-download"
                        id="upload-post-preview"
                        :image-src="postVersion.cover_url"
                        title="Загрузить обложку"
                        @upload="(file) => postVersion.cover_file = file"
                        :max-size-in-megabytes="5"
                        :min-height="432"
                        :min-width="768"
                    />

                    <p class="error mb-6 mt-4">{{ errors['cover_file']?.[0] || ' ' }}</p>

                </div>
            </div>

        </section>

        <section class="page-container flex justify-center">
            <div class="content w-full">
                <Editor
                    v-model="postVersion.content"
                    :class="{'red-overlay': errors['content']}"
                    editor-class="post-content min-h-[12rem]"
                    :extensions="contentEditorExtensions"
                    :editable="editable"
                />

                <p class="error">{{ errors['content']?.[0] || ' ' }}</p>
            </div>
        </section>


        <section class="last-section page-container flex relative">
            <div class="flex justify-center w-full">
                <div class="content flex justify-center w-full">
                    <div class="description flex flex-col w-full">

                        <div class="separator self-center w-full my-4"></div>

                        <Select
                            class="post-edition flex my-4"
                            v-model="gameEdition"
                            :disabled="!editable"
                            input-id="edition"
                            :options="gameEditions"
                            option-label-key="label"
                            option-icon-key="icon"
                            option-value-key="value"
                            @change="onEditionChange"
                        >
                            <template #option-icon/>
                        </Select>

                        <Select
                            :class="{'red-overlay': errors['category_id']}"
                            class="post-category flex"
                            v-model="postVersion.category_id"
                            :disabled="!editable"
                            input-id="category"
                            :options="categories"
                            option-label-key="name"
                            option-value-key="id"
                            placeholder="Выберите Категорию"
                        >
                            <template #option-icon/>
                        </Select>

                        <p class="error my-2">{{ errors['category_id']?.[0] || ' ' }}</p>

                        <Textarea
                            :class="{'red-overlay': errors['description']}"
                            class="post-description"
                            v-model="postVersion.description"
                            :disabled="!editable"
                            id="post-description"
                            :max-length="165"
                            :min-length="15"
                            placeholder="Описание"
                            rows="3"
                        />

                        <p class="error mb-3 mt-2">{{ errors['description']?.[0] || ' ' }}</p>

                        <UploadFile
                            class="upload-post-preview flex mb-5"
                            :editable="editable"
                            icon="icon-download"
                            id="upload-post-preview"
                            :image-src="postVersion.cover_url"
                            title="Загрузить Файл Материала"
                            @upload="(file) => postVersion.cover_file = file"
                            :max-size-in-megabytes="20"
                        />

                    </div>
                </div>
            </div>
            <aside class="post-interaction flex flex-col items-center">
                <slot name="sidebar"></slot>
            </aside>
        </section>
    </div>

</template>

<style>
.banner-title {
    position: relative;
    height: 208px;
}
.banner-title h1 {
    line-height: 1.1;
    font-size: 3rem;
}
.material-name h1 {
    word-wrap: anywhere;
    line-break: strict;
    white-space: wrap;
}
.content textarea {
    text-shadow: none;
}
.content .material-name .is-empty,
.post-category,
.post-edition {
    color: var(--primary-text-color);
}
.post-interaction .shine-button.confirm .text {
    white-space: nowrap;
}

/* =============== [ Медиа-Запрос { ?px < 451px } ] =============== */

@media screen and (max-width: 450px) {
    .banner-title {
        height: 108px;
    }
    .banner-title h1 {
        font-size: 2rem;
    }
}
</style>

<style scoped>
.first-section {
    background-attachment: fixed;
    position: relative;
}
.content {
    max-width: 800px;
}
.content .separator {
    background-color: var(--secondary-text-color);
    opacity: .5;
}
.content ol li {
    list-style-type: decimal;
}
.content ul li {
    list-style-type: square;
}
.interface .upload-image-container {
    background-position: center;
    object-position: center;
    aspect-ratio: 16 / 9;
    object-fit: cover;
    width: 100%;
}
.post-interaction {
    height: fit-content;
    min-width: 220px;
    font-size: 12px;
    position: fixed;
    width: 220px;
    top: 104px;
}
.post-description {
    min-height: 128px;
}
.post-help {
    max-width: 280px;
}
.post-help * {
    line-height: 1.8;
    opacity: .9;
}
.last-section {
    justify-content: flex-end;
}

/* =============== [ Медиа-Запрос { ?px < 1281px } ] =============== */

@media screen and (max-width: 1280px) {
    .last-section {
        flex-direction: column;
        align-items: center;
    }
    .post-interaction {
        margin-bottom: 1rem;
        max-width: 800px;
        position: static;
        width: 100%;
    }
}

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px) {
    .origin-info {
        flex-direction: column;
    }
}
</style>
