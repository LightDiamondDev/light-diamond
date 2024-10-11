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
import LoadedFile from '@/components/elements/LoadedFile.vue'
import ProcessingDiggingBlocks from '@/components/elements/ProcessingDiggingBlocks.vue'
import PostInfoBar from '@/components/post/PostInfoBar.vue'
import Button from '@/components/elements/Button.vue'
import PostCommentComponent from '@/components/post/comment/PostComment.vue'
import PostCommentEditor from '@/components/post/comment/PostCommentEditor.vue'
import PostActionBar from '@/components/post/PostActionBar.vue'

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
    },
    isUpperSidebar: {
        type: Boolean,
        default: true,
    },
    lowerSidebarClasses: String,
    upperSidebarClasses: String
})

const preferenceManager = usePreferenceManager()
const postCategoryStore = usePostCategoryStore()
const postVersion = defineModel<PostVersion>({default: {}})
const gameEdition = ref<GameEdition|null>(
    postVersion.value.category
        ? postVersion.value.category.edition!
        : preferenceManager.getEdition()
)
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

function onEditionChange() {
    postVersion.value.category_id = undefined
}

</script>

<template>
    <div class="smooth-dark-background flex flex-col items-center w-full duration-500">

        <section class="section flex justify-between xl:flex-row flex-col-reverse xl:items-start items-center
            xl:max-w-[1280px] max-w-[832px] w-full gap-4 lg:mt-4">

            <aside class="left-post-interaction xl-left-post-interaction
                xl:flex hidden xl:flex-col sticky text-[12px] mb-12"
            >
                <p>PostActionBar</p>
            </aside>

            <div class="post center-interaction bright-background ld-fixed-background flex flex-col items-center max-w-[832px] w-full" ref="postContent">

                <div class="post-info-dates xl:hidden flex lg:justify-between justify-center w-full xs:px-4 px-2">
                    <p>PostActionBar</p>
                </div>

                <Editor
                    v-model="postVersion.title"
                    :class="{'red-overlay': errors['title']}"
                    class="material-name flex justify-center max-w-[1280px]"
                    :extensions="titleEditorExtensions"
                    :editable="editable"
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

                <p class="error my-2">{{ errors['cover_file']?.[0] || ' ' }}</p>

                <Editor
                    v-model="postVersion.content"
                    :class="{'red-overlay': errors['content']}"
                    editor-class="post-content min-h-[12rem]"
                    :extensions="contentEditorExtensions"
                    :editable="editable"
                />

                <p class="error">{{ errors['content']?.[0] || ' ' }}</p>

                <Select
                    button-classes="ld-primary-background ld-primary-border ld-title-font"
                    options-classes="ld-primary-background ld-primary-border mt-[-2px]"
                    class="post-edition flex my-4"
                    v-model="gameEdition"
                    :disabled="!editable"
                    input-id="edition"
                    :options="gameEditions"
                    option-classes="h-[64] pl-6"
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
                    option-classes="h-[64] pl-6"
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

                <LoadedFile
                    class="my-2"
                    file-name="Light Diamond Addon — Craft & Survive [0.1.0]"
                    file-format=".MCADDON"
                    file-size="12,5 Мб
                        "/>

                <LoadedFile
                    class="my-2"
                    file-name="LD Better Ores [0.2.1]"
                    file-format=".MCPACK"
                    file-size="587 Кб"
                />

                <LoadedFile
                    class="my-2"
                    file-name="Escape The Tower [2.3.0]"
                    file-format=".MCWORLD"
                    file-size="1,51 Мб"
                />

                <UploadFile
                    class="upload-post-preview flex mb-5 mt-2.5"
                    :editable="editable"
                    icon="icon-download"
                    id="upload-post-preview"
                    :image-src="postVersion.cover_url"
                    title="Загрузить Файл Материала"
                    @upload="(file) => postVersion.cover_file = file"
                    :max-size-in-megabytes="20"
                />

                <div class="ld-secondary-background ld-fixed-background ld-trinity-border-top xl:hidden
                    flex sm:justify-start justify-center sticky w-full bottom-0 mt-2 xs:px-4 px-2 py-2"
                >
                    <p>PostActionBar</p>
                </div>

            </div>

            <aside class="right-post-interaction xl-right-post-interaction xl:flex hidden xl:flex-col xl:sticky
                text-[12px] xl:max-w-[336px] gap-4"
            >
                <div class="bright-background flex flex-col">
                    <p>PostInfoBar</p>
                </div>
                <div class="last-bright-block bright-background xl:flex hidden flex-col overflow-hidden">
                    <div class="post-addition-content flex flex-col w-full p-4 duration-500" style="color: dimgray">
                        Дополнительный Контент
                    </div>
                </div>
            </aside>

        </section>
    </div>
</template>

<style>
.tooltip::before {
    margin-left: -100px;
}
</style>

<style scoped>
.preview-wrap,
.preview {
    object-position: center;
    aspect-ratio: 16/9;
    object-fit: cover;
}
.smooth-dark-background.wide {
    background-color: rgba(0, 0, 0, .2);
}
.section {
    transition: flex 0.5s ease;
}
.bright-background {
    border: 2px solid transparent;
    transition: .5s;
}
.wide .bright-background {
    background-image: url('/images/elements/base-background-code.png');
    background-color: var(--secondary-bg-color);
    border: var(--secondary-border);
}
.xl-left-post-interaction,
.xl-right-post-interaction {
    transition: .5s;
}
.post-addition-content {
    transform: translateX(100%);
    opacity: 0;
}
.wide .post-addition-content {
    transform: translateX(0);
    opacity: 1;
}
.wide .center-interaction {
    margin-bottom: 1rem;
}

/* =============== [ Медиа-Запрос { 1281px > ?px } ] =============== */

@media screen and (min-width: 1281px) {
    .xl-left-post-interaction,
    .xl-right-post-interaction {
        width: 208px;
        top: 96px;
    }
    .wide .xl-right-post-interaction {
        width: 336px;
        top: 80px;
    }
    .xl-left-post-interaction {
        margin-top: 0;
    }
    .wide .xl-left-post-interaction {
        width: 80px;
    }
    .right-post-info-bar {
        padding-left: 3rem;
    }
    .wide .right-post-info-bar {
        padding: 0 1rem 1rem 1rem;
    }
    #comments {
        transition: .5s;
    }
    .wide #comments {
        margin-right: 256px;
    }
    .last-bright-block {
        margin-bottom: .5rem;
    }
    .wide .last-bright-block {
        margin-bottom: 3rem;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1281px } ] =============== */

@media screen and (max-width: 1280px) {
    .xl-right-post-interaction {
        width: 100%;
    }
    .right-date-info {
        width: 100%;
    }
}

/* =============== [ Медиа-Запрос { ?px < 767px } ] =============== */

@media screen and (max-width: 768px) {
    .smooth-dark-background.wide {
        background-color: transparent;
    }
    .wide .bright-background {
        background-color: transparent;
        background-image: none;
        border: none;
    }
    .wide .center-interaction {
        margin-bottom: 0;
    }
}
</style>
