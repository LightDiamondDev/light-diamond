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
        placeholder: 'Название материала',
    }),
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
    Image,
]
</script>

<template>
    <div class="post-version flex w-full p-2 gap-2 justify-end">
        <section class="post-container flex flex-col w-full gap-2">

            <slot name="title"></slot>

            <div class="interface flex flex-col">

                <RouterLink class="author-wrap flex items-center w-fit gap-2 p-4" :to="{name: 'home'}">
                <span class="icon-border flex justify-center items-center h-[48px] w-[48px]">
                    <UserAvatar :user="author"/>
                </span>
                    <span class="username duration-200">{{ author.username }}</span>
                </RouterLink>

                <div class="separator self-center w-[97%]"></div>

                <Editor
                    v-model="postVersion.title"
                    class="text-[1.2rem] pl-1"
                    :editable="editable"
                    :extensions="titleEditorExtensions"
                    placeholder="Название Материала"
                    plain-text
                    without-menus
                />

                <UploadImage
                    class="flex mb-6 ml-10 mr-10"
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

                <Editor
                    v-model="postVersion.content"
                    :editable="editable"
                    :extensions="contentEditorExtensions"
                    editor-class="post-content min-h-[3rem]"
                />
            </div>

            <div class="description flex flex-col">

                <Select
                    class="post-category flex m-6"
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

                <TextareaText
                    class="post-description mb-6 ml-6 mr-6 p-4"
                    v-model="postVersion.description"
                    :disabled="!editable"
                    id="post-description"
                    :max-length="360"
                    :min-length="12"
                    placeholder="Описание Материала"
                    rows="3"
                />

            </div>
        </section>
        <div class="hook"></div>
        <aside class="post-interaction flex flex-col items-center fixed gap-2 p-2">
            <slot name="sidebar"></slot>
        </aside>
    </div>

</template>

<style scoped>
.post-version {
    position: relative;
    max-width: 1280px;
}
.author-wrap:hover .username {
    color: var(--hover-text-color);
}
.post-version .interface .upload-image-container {
    aspect-ratio: 16 / 9;
    max-width: 100%;
}
.post-interaction, .hook {
    min-width: 280px;
    width: 280px;
}
.post-interaction .button {
    width: 100%;
}
.post-description {
    background-color: rgba(0, 0, 0, .2);
    min-height: 152px;
}
</style>
