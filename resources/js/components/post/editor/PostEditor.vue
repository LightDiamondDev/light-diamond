<script setup lang="ts">
import {type PropType, ref} from 'vue'
import type {PostVersion, User} from '@/types'
import {usePostCategoryStore} from '@/stores/postCategory'

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

import Editor from '@/components/elements/editor/Editor.vue'
import Select from '@/components/elements/Select.vue'
import UploadImage from '@/components/elements/UploadImage.vue'
import UploadFile from '@/components/elements/UploadFile.vue'
import UserAvatar from '@/components/user/UserAvatar.vue'
import TextareaText from '@/components/elements/TextareaText.vue'

defineProps({
    author: {
        type: Object as PropType<User>,
        required: true,
    },
    editable: {
        type: Boolean,
        default: true,
    },
})

const postCategoryStore = usePostCategoryStore()
const errors = ref<string[][]>([])
const postVersion = defineModel<PostVersion>({default: {}})

const titleEditorExtensions = [
    Text,
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
        placeholder: 'Название Материала',
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
            levels: [1, 2],
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
</script>

<template>

        <slot name="title"></slot>

        <section class="page-container flex justify-center">

            <div class="content w-full">

                <div class="interface flex flex-col">

                    <div class="separator self-center w-full"></div>

                    <RouterLink class="author-wrap flex items-center w-fit gap-2 ml-[-2px] pb-2 pt-2" :to="{name: 'home'}">
                        <span class="icon-border flex justify-center items-center h-[48px] w-[48px]">
                            <UserAvatar :user="author"/>
                        </span>
                        <span class="username duration-200">{{ author.username }}</span>
                    </RouterLink>

                    <div class="separator self-center w-full"></div>

                    <Editor
                        v-model="postVersion.title"
                        class="material-name text-[1.2rem] mb-4 mt-4"
                        :editable="editable"
                        :extensions="titleEditorExtensions"
                        placeholder="Название Материала"
                        plain-text
                        without-menus
                    />

                    <p class="text-[0.8rem] opacity-80">
                        Название Материала (например, Ресурс-Пака, Аддона или Мира) может содержать
                        версию обновления, например:
                    </p>

                    <ul class="text-[0.8rem] opacity-80 py-2">
                        <li>Better Craft [2.0.12]</li>
                        <li>Blue Skies [0.1.2]</li>
                        <li>Escape Temple [3.1]</li>
                    </ul>

                    <UploadImage
                        class="upload-post-preview flex my-4"
                        :editable="editable"
                        icon="icon-download"
                        id="upload-post-preview"
                        :image-src="postVersion.cover_url"
                        title="Загрузить обложку Материала"
                        @upload="(file) => postVersion.cover_file = file"
                        :max-size-in-megabytes="5"
                        :min-height="432"
                        :min-width="768"
                    />

                    <p class="text-[0.8rem] opacity-80">
                        Красивая обложка притягивает больше внимания! Рекомендуется использовать изображения с соотношением сторон 16 : 9.
                    </p>

                    <h1 class="flex justify-center text-[2.5rem] mt-2">Основной Контент Материала</h1>

                    <div class="separator self-center w-full my-4"></div>

                </div>
            </div>

        </section>

    <section class="page-container flex justify-center">
        <div class="content w-full">
            <Editor
                v-model="postVersion.content"
                editor-class="post-content"
                :extensions="contentEditorExtensions"
                :editable="editable"
            />
        </div>
    </section>


        <section class="page-container flex justify-end relative">
            <div class="flex justify-center w-full">
                <div class="content flex justify-center w-full">
                    <div class="description flex flex-col w-full">

                        <div class="separator self-center w-full my-4"></div>

                        <Select
                            class="post-category flex my-4"
                            v-model="postVersion.category_id"
                            :disabled="!editable"
                            input-id="category"
                            :options="postCategoryStore.categories"
                            option-label-key="name"
                            option-icon-key="icon"
                            placeholder="Выберите Категорию"
                        >
                            <template #option-icon/>
                        </Select>

                        <p class="text-[0.8rem] opacity-80">
                            Категория Материала должна точно соответствовать своему содержанию!
                        </p>

                        <TextareaText
                            class="post-description p-4"
                            v-model="postVersion.description"
                            :disabled="!editable"
                            id="post-description"
                            :max-length="165"
                            :min-length="15"
                            placeholder="Описание Материала"
                            rows="3"
                        />

                        <p class="text-[0.8rem] opacity-80 mb-2">
                            Описание Материала должно быть лаконичным, содержать основную суть и не превышать лимит
                            в 165 символов!
                        </p>

                        <UploadFile
                            class="upload-post-preview flex mt-4"
                            :editable="editable"
                            icon="icon-download"
                            id="upload-post-preview"
                            :image-src="postVersion.cover_url"
                            title="Загрузить Файл Материала"
                            @upload="(file) => postVersion.cover_file = file"
                            :max-size-in-megabytes="20"
                        />

                        <p class="text-[0.8rem] opacity-80 my-6">
                            К Материалу о Ресурс-Паке, Аддоне или Шаблоне Мира разрешается прикреплять до 5 Файлов, не
                            превышающих лимит по размеру!
                        </p>

                    </div>
                </div>
            </div>
            <aside class="post-interaction flex flex-col items-center fixed gap-2 p-2">
                <slot name="sidebar"></slot>
            </aside>
        </section>
</template>

<style>
.content textarea {
    text-shadow: none;
}
.content .material-name .is-empty,
.post-category {
    color: var(--primary-text-color);
}
.post-interaction .shine-button.confirm .text {
    white-space: nowrap;
}
</style>

<style scoped>
.primary-background {
    background-attachment: fixed;
    width: 100%;
}
.content {
    max-width: 800px;
}
.content .separator {
    background-color: var(--secondary-text-color);
    opacity: .5;
}
.content ol {
    list-style-type: decimal;
}
.content ul {
    list-style-type: square;
}
.interface .upload-image-container {
    aspect-ratio: 16 / 9;
    max-width: 100%;
}
.author-wrap:hover .username {
    color: var(--hover-text-color);
}
.post-interaction {
    height: fit-content;
    min-width: 220px;
    font-size: 12px;
    width: 220px;
    top: 96px;
}
.post-description {
    min-height: 120px;
}
.post-help {
    max-width: 280px;
}
.post-help * {
    line-height: 1.8;
    opacity: .9;
}
</style>
