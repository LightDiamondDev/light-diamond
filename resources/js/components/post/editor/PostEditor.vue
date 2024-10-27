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
import Input from '@/components/elements/Input.vue'
import Select from '@/components/elements/Select.vue'
import Textarea from '@/components/elements/Textarea.vue'
import Button from '@/components/elements/Button.vue'
import UploadImage from '@/components/elements/UploadImage.vue'
import UploadFile from '@/components/elements/UploadFile.vue'
import UploadedPostVersionFile from '@/components/post/editor/UploadedPostVersionFile.vue'
import UserAvatar from '@/components/user/UserAvatar.vue'

import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode} from '@/helpers'
import {useToastStore} from '@/stores/toast'
import ShineButton from '@/components/elements/ShineButton.vue'

defineProps({
    author: {
        type: Object as PropType<User>,
        required: true,
    },
    editable: {
        type: Boolean,
        default: true
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

const currentFileUrlName = ref('')
const currentFileUrl = ref('')

const isWide = ref(true)
const isCurrentFileUrlNameError = ref(false)
const isCurrentFileUrlError = ref(false)

function addFileUrl() {
    isCurrentFileUrlNameError.value = false
    isCurrentFileUrlError.value = false
    if (currentFileUrlName.value.length < 3 || currentFileUrl.value.length < 3) {
        toastStore.error('Название Материала и Ссылка должны содержать не менее 3-х символов!')
        isCurrentFileUrlNameError.value = true
        isCurrentFileUrlError.value = true
        return
    }
    const checkUrl = new RegExp(/^(ftp|http|https):\/\/[^ "]+$/);
    if (!checkUrl.test(currentFileUrl.value)) {
        toastStore.error('Указанный текст не является ссылкой!')
        return
    }
    if (postVersion.value.files?.find((file) => file.url === currentFileUrl.value)) {
        toastStore.error('Такая Ссылка уже прикреплена!')
        return
    }
    toastStore.success('Ссылка успешно указана!')
    let urls = postVersion.value.files ?? []
    urls.push({name: currentFileUrlName.value, url: currentFileUrl.value})
    postVersion.value.files = urls
    currentFileUrlName.value = ''
    currentFileUrl.value = ''
}

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
    <div
        v-if="postVersion" class="smooth-dark-background flex flex-col items-center w-full duration-500"
        :class="{'wide': isWide}"
    >
        <slot name="banner"/>
        <section
            class="section flex justify-between xl:items-start items-center
                xl:max-w-[1280px] max-w-[832px] w-full gap-4 lg:mt-4"
        >
            <aside class="xl-left-post-interaction xl:flex hidden xl:flex-col sticky text-[12px] mb-12"/>

            <div
                class="post center-interaction bright-background ld-fixed-background
                    flex flex-col items-center max-w-[832px] w-full"
                ref="postContent"
            >

                <aside
                    class="left-post-interaction xl-left-post-interaction upper-interaction
                        xl:hidden flex flex-col text-[12px] w-full mt-4"
                >
                    <slot name="sidebar"/>
                </aside>

                <div class="post-info-dates xl:hidden flex lg:justify-between justify-center w-full xs:px-4 px-2">
                    <button class="lg:flex hidden items-start" @click="isWide = !isWide">
                        <span class="icon flex my-4" :class="{'icon-right-arrow': isWide, 'icon-left-arrow': !isWide}"/>
                    </button>
                </div>

                <div class="origin-info flex justify-between w-full xs:px-4 px-2">
                    <RouterLink class="author-wrap flex items-center w-fit gap-2 ml-[-2px] pb-2 pt-2" :to="{name: 'home'}">
                        <UserAvatar
                            border-class-list="h-10 w-10"
                            icon-class-list="h-7 w-7"
                            :user="author"
                        />
                        <span class="username duration-200">{{ author.username }}</span>
                    </RouterLink>
                    <slot name="mark"/>
                </div>

                <Editor
                    v-model="postVersion.title"
                    :class="{'red-overlay': errors['title']}"
                    class="post-name ld-secondary-text text-center
                        flex justify-center max-w-[1280px] md:text-[2rem]
                        text-[1.5rem] mb-2 xs:mx-4 mx-2"
                    :extensions="titleEditorExtensions"
                    :editable="editable"
                    plain-text
                    without-menus
                />

                <p class="error mb-2 mt-0">{{ errors['title']?.[0] || ' ' }}</p>

                <div class="preview-wrap flex w-full mt-0 xs:mx-4 xs:px-4 px-2">
                    <UploadImage
                        class="upload-post-preview flex"
                        :class="{'red-overlay': errors['cover_file']}"
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
                </div>

                <p class="error my-2">{{ errors['cover_file']?.[0] || ' ' }}</p>

                <div class="xs:px-4 px-2 w-full">
                    <Editor
                        v-model="postVersion.content"
                        :class="{'red-overlay': errors['content']}"
                        class="ld-secondary-text"
                        editor-class="post-content min-h-[12rem]"
                        :extensions="contentEditorExtensions"
                        :editable="editable"
                    />
                </div>

                <p class="error">{{ errors['content']?.[0] || ' ' }}</p>

                <div
                    class="separator self-center w-[96.2%] opacity-40 my-2"
                    style="background-color: var(--secondary-text-color);"
                />

                <Select
                    button-classes="ld-primary-background ld-primary-border ld-title-font w-full"
                    options-classes="ld-primary-background ld-primary-border md:top-[66px]
                        top-[50px] md:w-[96.2%] sm:w-[95.2%] xs:w-[93.2%] w-[96%]"
                    option-classes="md:min-h-[64px] min-h-[48px] gap-4 pl-6"
                    class="post-edition flex items-center w-full my-4 xs:px-4 px-2"
                    v-model="gameEdition"
                    :editable="editable"
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
                    button-classes="ld-primary-background ld-primary-border ld-title-font w-full"
                    options-classes="ld-primary-background ld-primary-border md:top-[66px]
                        top-[50px] md:w-[96.2%] sm:w-[95.2%] xs:w-[93.2%] w-[96%]"
                    option-classes="md:min-h-[64px] min-h-[48px] gap-4 pl-6"
                    class="post-category flex items-center w-full my-4 xs:px-4 px-2"
                    :class="{'red-border': errors['category_id']}"
                    v-model="postVersion.category_id"
                    :editable="editable"
                    input-id="category"
                    :options="categories"
                    option-label-key="name"
                    option-icon-key="icon"
                    option-value-key="id"
                    placeholder="Выберите Категорию"
                    @change="postVersion.category = postCategoryStore.getById(postVersion.category_id)"
                >
                    <template #option-icon/>
                </Select>

                <p class="error my-2">{{ errors['category_id']?.[0] || ' ' }}</p>

                <div class="xs:px-4 px-2 w-full">
                    <Textarea
                        class="post-description ld-primary-background ld-primary-border ld-red-bordered md:text-[14px] text-[12px]"
                        :class="{'red-border': errors['description']}"
                        text-area-classes="ld-tinted-background min-h-[108px]"
                        v-model="postVersion.description"
                        :editable="editable"
                        id="post-description"
                        :max-length="165"
                        :min-length="15"
                        placeholder="Описание"
                        rows="3"
                    />
                </div>

                <p class="error my-2">{{ errors['description']?.[0] || ' ' }}</p>


                <div v-if="!postVersion.category?.is_article" class="flex flex-col w-full gap-3 mb-4 xs:px-4 px-2">
                    <h4 class="ld-secondary-text flex xs:flex-row flex-col justify-center text-center xs:gap-2 mt-0">
                        <span>Прикреплённые Материалы</span>
                        <span>{{ ' [ ' + files.length + ' / 3 ]' }}</span>
                    </h4>
                    <UploadedPostVersionFile
                        v-for="file in files"
                        :key="file.path || file.url"
                        :file="file"
                        :disabled="!editable"
                        :editable="editable"
                        @remove="files.splice(files.indexOf(file), 1)"
                    />
                </div>

                <div v-if="!postVersion.category?.is_article && editable && files.length < 3" class="ld-secondary-text w-full mt-2 xs:px-4 px-2">
                    <UploadFile
                        v-model="postVersion.files"
                        class="upload-post-preview flex mb-5 mt-2.5"
                        :editable="editable"
                        icon="icon-download"
                        id="upload-post-file"
                        title="Загрузить Файл Материала"
                        @upload="uploadFile"
                        :max-size-in-megabytes="20"
                    />
                    <p class="flex justify-center w-full mb-2">или</p>
                    <em class="upload-file-heading flex justify-center gap-2">
                        <span class="icon-link-square icon"/>
                        <span class="head-font ld-secondary-text text-center md:text-[16px] text-[14px] duration-200">Указать Ссылку Материала</span>
                    </em>
                    <div class="flex flex-col gap-4 mt-4">
                        <Input
                            v-model="currentFileUrlName"
                            class="ld-primary-background ld-primary-border ld-primary-text
                                sm:text-[14px] text-[12px] md:h-[48px] h-[40px] w-full"
                            :class="{ 'red-border': isCurrentFileUrlNameError }"
                            id="post-version-file-name"
                            input-classes="ld-tinted-background"
                            :max-length="255"
                            :min-length="1"
                            placeholder="Название Материала по Ссылке"
                        />
                        <Input
                            v-model="currentFileUrl"
                            class="ld-primary-background ld-primary-border ld-primary-text
                                sm:text-[14px] text-[12px] md:h-[48px] h-[40px] w-full"
                            :class="{ 'red-border': isCurrentFileUrlError }"
                            id="post-version-file-name"
                            input-classes="ld-tinted-background"
                            :max-length="255"
                            :min-length="1"
                            placeholder="Ссылка на Файл"
                        />
                        <ShineButton
                            class="flex items-center"
                            class-wrap="ld-primary-background"
                            class-preset="ld-primary-text ld-title-font justify-center xs:text-[14px] text-[12px] gap-1 py-0.5"
                            label="Добавить Ссылку"
                            icon="icon-tick"
                            @click="addFileUrl"
                        />
                    </div>
                    <p class="error flex justify-center w-full my-2">{{ errors['files']?.[0] || ' ' }}</p>
                </div>

                <aside
                    class="right-post-interaction xl-right-post-interaction
                        xl:hidden flex flex-col text-[12px] w-full"
                >
                    <slot name="sidebar"/>
                </aside>

            </div>

            <aside class="right-post-interaction xl-right-post-interaction xl:flex hidden xl:flex-col xl:sticky
                text-[12px] xl:max-w-[336px] gap-4"
            >
                <div class="bright-background flex flex-col">
                    <button class="flex justify-end p-[4px]" @click="isWide = !isWide">
                        <span class="icon flex"
                              :class="{'icon-right-direction-arrow': isWide, 'icon-left-direction-arrow': !isWide}"
                        />
                    </button>
                    <slot name="sidebar"/>
                </div>
                <!-- xl:flex -->
                <div class="last-bright-block bright-background hidden flex-col overflow-hidden">
                    <div class="post-addition-content flex flex-col w-full p-4 duration-500" style="color: dimgray">
                        Дополнительный Контент
                    </div>
                </div>
            </aside>

        </section>
    </div>
</template>

<style>
.post-title {
    overflow-wrap: anywhere;
}
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
