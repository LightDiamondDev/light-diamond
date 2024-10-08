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
import UploadedPostVersionFile from '@/components/post/editor/UploadedPostVersionFile.vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode} from '@/helpers'
import {useToastStore} from '@/stores/toast'

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
        type: Object as PropType<{ [key: string]: string[] }>,
        required: true,
    }
})

const preferenceManager = usePreferenceManager()
const postCategoryStore = usePostCategoryStore()
const toastStore = useToastStore()
const postVersion = defineModel<PostVersion>({default: {}})
const gameEdition = ref<GameEdition|null>(
    postVersion.value.category
        ? postVersion.value.category.edition!
        : preferenceManager.getEdition()
)
const categories = computed(() => postCategoryStore.categories.filter(
    (category) => category.edition === gameEdition.value || category.edition === null)
)
const files = computed(() => postVersion.value.files ?? [])

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

function onEditionChange() {
    postVersion.value.category_id = undefined
}

function uploadFile(file: File) {
    const formData = new FormData()
    formData.append('file', file)

    axios.post('/api/upload-post-file', formData).then((response) => {
        if (response.data.success) {
            toastStore.success('Файл успешно загружен!')
            let files = postVersion.value.files ?? []
            files.push({name: file.name, path: response.data.file_path, size: file.size})
            postVersion.value.files = files
        } else {
            if (response.data.errors) {
                toastStore.error(response.data.errors['file'][0])
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    })
}

</script>

<template>

    <div class="post-editor-banner">
        <slot name="banner"/>
    </div>

    <div class="post-editor-header flex w-full">
        <slot name="header"/>
    </div>

    <div class="first-section ld-secondary-background w-full">
        <div class="page-container xl">
            <section class="flex flex-col justify-center items-center">

                <aside class="post-interaction
                    ld-primary-background
                    ld-primary-border-section
                    upper-interaction
                    flex-col
                    items-center"
                >
                    <slot name="sidebar"></slot>
                </aside>

                <div class="content page-container w-full">

                    <div class="interface flex flex-col">

                        <div class="origin-info flex justify-between">
                            <RouterLink class="author-wrap flex items-center w-fit gap-2 ml-[-2px] pb-2 pt-2" :to="{name: 'home'}">
                            <span class="icon-border flex justify-center items-center h-[48px] w-[48px]">
                                <UserAvatar
                                    border-class-list="h-12 w-12"
                                    icon-class-list="h-8 w-8"
                                    :user="author"
                                />
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

            <section class="flex justify-center">
                <div class="content page-container w-full">
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

            <section class="last-section flex relative">
                <div class="page-container flex justify-center w-full">
                    <div class="content flex justify-center w-full">
                        <div class="description flex flex-col w-full">

                            <div class="separator self-center w-full my-4"></div>

                            <Select
                                button-classes="ld-primary-background ld-primary-border ld-title-font"
                                options-classes="ld-primary-background ld-primary-border mt-[-2px]"
                                class="post-edition flex my-4"
                                v-model="gameEdition"
                                :disabled="!editable"
                                input-id="edition"
                                :options="gameEditions"
                                option-classes="pl-6"
                                option-label-key="label"
                                option-icon-key="icon"
                                option-value-key="value"
                                @change="onEditionChange"
                            >
                                <template #option-icon/>
                            </Select>

                            <Select
                                button-classes="ld-primary-background ld-primary-border ld-title-font"
                                options-classes="ld-primary-background ld-primary-border mt-[-2px]"
                                :class="{'red-border': errors['category_id']}"
                                class="post-category flex my-4"
                                v-model="postVersion.category_id"
                                :disabled="!editable"
                                input-id="category"
                                :options="categories"
                                option-classes="pl-6"
                                option-label-key="name"
                                option-value-key="id"
                                placeholder="Выберите Категорию"
                            >
                                <template #option-icon/>
                            </Select>

                            <p class="error my-2">{{ errors['category_id']?.[0] || ' ' }}</p>

                            <Textarea
                                :class="{'red-border': errors['description']}"
                                class="post-description ld-primary-background ld-primary-border"
                                v-model="postVersion.description"
                                :editable="editable"
                                id="post-description"
                                :max-length="165"
                                :min-length="15"
                                placeholder="Описание"
                                rows="3"
                            />

                            <p class="error mt-2">{{ errors['description']?.[0] || ' ' }}</p>

                            <UploadedPostVersionFile
                                v-for="file in files"
                                :key="file.path || file.url"
                                :file="file"
                                :disabled="!editable"
                                class="my-2"
                                @remove="files.splice(files.indexOf(file), 1)"
                            />

                            <UploadFile
                                v-if="files.length < 3"
                                class="upload-post-preview flex mb-5 mt-2.5"
                                :editable="editable"
                                icon="icon-download"
                                id="upload-post-file"
                                :image-src="postVersion.cover_url"
                                title="Загрузить Файл Материала"
                                @upload="uploadFile"
                                :max-size-in-megabytes="20"
                            />
                        </div>
                    </div>
                </div>
                <aside class="post-interaction
                    lower-interaction
                    ld-primary-background
                    ld-primary-border-section
                    flex flex-col items-center"
                >
                    <slot name="sidebar"></slot>
                </aside>
            </section>
        </div>
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
.content .material-name h1 {
    font-size: 2rem;
}
.content textarea {
    text-shadow: none;
}
.content .material-name .is-empty,
.post-category,
.post-edition {
    color: var(--primary-text-color);
}
.post-interaction .ld-shine-button.confirm .text {
    white-space: nowrap;
}
.interface .upload-image-container {
    background-position: center;
    background-size: cover;
    aspect-ratio: 16 / 9;
}

/* =============== [ Медиа-Запрос { ?px < 451px } ] =============== */

@media screen and (max-width: 450px) {
    .content .material-name h1 {
        font-size: 1.5rem;
    }
    #post-description {
        min-height: 216px;
        font-size: 12px;
    }
    .post-editor-banner .banner-container,
    .post-editor-banner .banner {
        height: 256px;
    }
    .banner-title {
        height: 168px;
    }
    .banner-title h1 {
        font-size: 2rem;
    }
}
/* =============== [ Медиа-Запрос { ?px < 401px } ] =============== */

@media screen and (max-width: 400px) {
    .post-editor-banner .banner-container,
    .post-editor-banner .banner {
        height: 232px;
    }
    .banner-title {
        height: 160px;
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
.content h1,
.content h2,
.content h3,
.content img {
    line-height: 1.1;
    margin: 1rem 0;
}
.content h4,
.content h5,
.content h6 {
    text-shadow: none;
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
    font-size: 14px;
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
.lower-interaction {
    margin-bottom: 1rem;
    z-index: 1;
}
.upper-interaction {
    display: none;
}

/* =============== [ Медиа-Запрос { ?px < 1281px } ] =============== */

@media screen and (max-width: 1280px) {
    .last-section {
        flex-direction: column;
        align-items: center;
    }
    .post-interaction {
        max-width: 800px;
        position: static;
        width: 100%;
    }
    .upper-interaction {
        display: flex;
    }
}

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px) {
    .origin-info {
        flex-direction: column;
    }
    .post-description {
        min-height: 168px;
    }
}
</style>
