<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {computed, type PropType, ref, toRaw, watch} from 'vue'

import {convertDateToString, withCaptcha, getErrorMessageByCode} from '@/helpers'
import {useAuthStore} from '@/stores/auth'
import usePreferenceManager from '@/preference-manager'
import {deepClone, pick} from '@/utils/object'
import useCategoryRegistry from '@/categoryRegistry'
import {useToastStore} from '@/stores/toast'

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

import {
    GameEdition,
    type MaterialFile,
    type MaterialFileState,
    type MaterialFileSubmission,
    type MaterialSubmission,
    type MaterialVersion,
    type MaterialVersionSubmission,
    SubmissionType
} from '@/types'

import Button from '@/components/elements/Button.vue'
import Editor from '@/components/elements/editor/Editor.vue'
import InputError from '@/components/elements/InputError.vue'
import Input from '@/components/elements/Input.vue'
import Select, {type SelectOptionChangeEvent} from '@/components/elements/Select.vue'
import ShineButton from '@/components/elements/ShineButton.vue'
import Textarea from '@/components/elements/Textarea.vue'
import UploadImage from '@/components/elements/UploadImage.vue'
import UploadFile from '@/components/elements/UploadFile.vue'

import MaterialFileSubmissionComponent, {
    type FileUpdateEvent
} from '@/components/material/editor/MaterialFileSubmission.vue'

import Checkbox from '@/components/elements/Checkbox.vue'
import Dialog from '@/components/elements/Dialog.vue'
import MaterialVersionSubmissionSelect from '@/components/material/editor/MaterialVersionSubmissionSelect.vue'
import ProfileLink from '@/components/elements/ProfileLink.vue'
import UserAvatar from '@/components/user/UserAvatar.vue'

defineProps({
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

defineExpose({
    hasAnyOfFieldsFilled,
    hasAllFieldsFilled
})

const emit = defineEmits(['edit'])

const materialSubmission = defineModel<MaterialSubmission>({required: true})

const categoryRegistry = useCategoryRegistry()
const preferenceManager = usePreferenceManager()
const toastStore = useToastStore()

const currentLocalization = computed(() => materialSubmission.value.material_state!.localizations![0])

const versions = computed<MaterialVersion[]>(() => {
    let versions = [...materialSubmission.value.material!.versions!]
    materialSubmission.value.version_submissions!.forEach((versionSubmission) => {
        let version: MaterialVersion | undefined
        switch (versionSubmission.type) {
            case SubmissionType.CREATE:
                version = {
                    id: versionSubmission.version_id,
                    state: versionSubmission.version_state,
                    files: []
                }
                versions.push(version)
                break
            case SubmissionType.DELETE:
                versions = versions.filter((version) => version.id !== versionSubmission.version_id)
                break
        }
    })
    return versions
})
const sortedVersionSubmissions = computed(() => materialSubmission.value!.version_submissions!.sort((a, b) => b.version!.created_at!.localeCompare(a.version!.created_at!)))

const currentVersionSubmissionIndex = ref(0)
const currentVersionSubmission = computed<MaterialVersionSubmission | undefined>(() => {
    return sortedVersionSubmissions.value[currentVersionSubmissionIndex.value]
})

const fileSubmissionForRemove = ref<MaterialFileSubmission>()

const versionFormData = ref()
const versionFormErrors = ref<{ [key: string]: string[] }>({})
const newFileUrlName = ref('')
const newFileUrl = ref('')

const isMaterialFileUploading = ref(false)
const isNewFileUrlNameError = ref(false)
const isNewFileUrlError = ref(false)
const isRemovingFile = ref(false)
const isVersionFormVisible = ref(false)
const isVersionRemoveFormVisible = ref(false)

const currentFiles = computed<MaterialFile[]>(() => {
    if (!currentVersionSubmission.value) {
        return []
    }

    let files: MaterialFile[] = currentVersionSubmission.value.version?.files ? [...currentVersionSubmission.value.version!.files] : []

    currentVersionSubmission.value.file_submissions?.forEach((fileSubmission) => {
        switch (fileSubmission.type) {
            case SubmissionType.CREATE:
                let file = fileSubmission.file_id ? files.find((file) => file.id === fileSubmission.file_id) : undefined
                if (!file) {
                    file = {...fileSubmission.file}
                    files.push(file)
                }
                file.state = fileSubmission.file_state
                break
            case SubmissionType.DELETE:
                files = files.filter((file: MaterialFile) => file.id !== fileSubmission.file!.id)
                break
        }
    })

    return files
})

const categories = computed(() => categoryRegistry.getByEdition(materialSubmission.value.material!.edition!))

const showVersionBlock = computed(() => {
    const category = materialSubmission.value.material!.category
    return category
        ? categoryRegistry.get(category).isDownloadable
        : false
})

const canCreateVersion = computed(() => {
    if (useAuthStore().isModerator) {
        return true
    }
    const createSubmissions = materialSubmission.value.version_submissions!.filter(
        (submission) => submission.type === SubmissionType.CREATE
    )
    return createSubmissions.length < 1
})
const canCreateFile = computed(() => {
    if (useAuthStore().isModerator) {
        return true
    }
    return currentFiles.value.length < 3
})

const gameEditions = [
    {
        icon: 'icon-diamond',
        label: 'Любое Издание',
        value: null
    },
    {
        icon: 'icon-bedrock-flat',
        label: 'Бедрок',
        value: GameEdition.BEDROCK
    },
    {
        icon: 'icon-grass-flat',
        label: 'Джава',
        value: GameEdition.JAVA
    }
]

const titleEditorExtensions = [
    CharacterCount.configure({
        limit: 100
    }),
    Document.extend({
        content: 'heading',
    }),
    History,
    Heading.configure({
        HTMLAttributes: {
            class: 'material-title',
        },
        levels: [1],
    }),
    Placeholder.configure({
        placeholder: 'Название',
    }),
    Text
]

const contentEditorExtensions = [
    Blockquote.extend({
        priority: 101,
    }),
    Heading.extend({ marks: '' }).configure({ levels: [2, 3, 4], }),
    Image,
    Link.configure({
        openOnClick: 'whenNotEditable',
    }),
    Placeholder.configure({
        placeholder: ({node}) => {
            switch (node['type']['name']) {
                case 'heading':
                    return 'Заголовок'
                case 'codeBlock':
                    return '// Код'
                default:
                    return 'Какой-нибудь текст...'
            }
        },
    }),
    StarterKit.configure({
        heading: false,
        blockquote: false
    }),
    Underline
]

function loadVersionSubmissions() {
    if (!materialSubmission.value.material!.versions) {
        return
    }

    materialSubmission.value!.material!.versions!.forEach((version) => {
        let versionSubmission = materialSubmission.value.version_submissions!.find(
            (versionSubmission) => versionSubmission.version_id === version.id
        )
        if (versionSubmission) {
            versionSubmission.version = version
            return
        }

        if (!version.deleted_at) {
            const versionStateClone: MaterialFile = deepClone(version.state)

            materialSubmission.value.version_submissions!.push({
                type: SubmissionType.UPDATE,
                version: version,
                version_id: version.id,
                version_state: versionStateClone,
                file_submissions: []
            })
        }
    })
    loadFileSubmissions()
}

function loadFileSubmissions() {
    if (!currentVersionSubmission.value?.version?.files) {
        return
    }

    currentVersionSubmission.value!.version!.files!.forEach((file) => {
        let fileSubmission = currentVersionSubmission.value!.file_submissions!.find(
            (fileSubmission) => fileSubmission.file_id === file.id
        )
        if (fileSubmission) {
            fileSubmission.file = file
            return
        }

        const fileStateClone: MaterialFileState = pick(file.state, [
            'localizations.*.language', 'localizations.*.name'
        ])
        const nowDate = new Date().toISOString()
        fileStateClone.created_at = nowDate
        fileStateClone.updated_at = nowDate

        currentVersionSubmission.value!.file_submissions!.push({
            type: SubmissionType.UPDATE,
            file: file,
            file_id: file.id,
            file_state: fileStateClone
        })
    })
}

function onCoverUpload(imagePath: string, imageUrl: string) {
    currentLocalization.value.cover = imagePath
    currentLocalization.value.cover_url = imageUrl
    emit('edit')
}

function onEditionChange() {
    if (materialSubmission.value!.material!.category) {
        const category = categoryRegistry.get(materialSubmission.value!.material!.category)

        if (category.edition !== null && category.edition !== materialSubmission.value.material!.edition) {
            materialSubmission.value.material!.category = undefined
        }
    }

    emit('edit')
}

function onCategoryChange() {
    const category = categoryRegistry.get(materialSubmission.value!.material!.category!)

    if (category.isDownloadable && versions.value.length === 0) {
        versionFormData.value = {
            type: SubmissionType.CREATE,
            number: '1.0',
            name: ''
        }
        saveVersion()
    }

    emit('edit')
}

function uploadFile(file: File) {
    withCaptcha(() => {
        const formData = new FormData()
        formData.append('file', file)

        isMaterialFileUploading.value = true

        axios.post('/api/upload-material-file', formData).then((response) => {
            if (response.data.success) {
                toastStore.success('Файл успешно загружен!')
                const filePath = response.data.file_path
                const nowDate = new Date().toISOString()

                currentVersionSubmission.value!.file_submissions?.push({
                    type: SubmissionType.CREATE,
                    file: {
                        name: file.name,
                        path: filePath,
                        size: file.size,
                        extension: filePath.slice(filePath.lastIndexOf('.') + 1),
                        created_at: nowDate,
                        updated_at: nowDate,
                    },
                    file_state: {
                        localizations: [
                            {language: 'ru', name: file.name}
                        ],
                    },
                })
                emit('edit')
            } else {
                if (response.data.errors) {
                    toastStore.error(response.data.errors['file'][0])
                }
            }
        }).catch((error: AxiosError) => {
            toastStore.error(getErrorMessageByCode(error.response!.status))
        }).finally(() => {
            isMaterialFileUploading.value = false
        })
    })
}

function addFileUrl() {
    isNewFileUrlNameError.value = false
    isNewFileUrlError.value = false

    if (!validateFileName(newFileUrlName.value)) {
        isNewFileUrlNameError.value = true
        return
    }
    if (!validateFileUrl(newFileUrl.value)) {
        isNewFileUrlError.value = true
        return
    }

    const nowDate = convertDateToString(new Date())

    currentVersionSubmission.value!.file_submissions?.push({
        type: SubmissionType.CREATE,
        file: {
            url: newFileUrl.value,
            created_at: nowDate,
            updated_at: nowDate
        },
        file_state: {
            localizations: [
                {language: 'ru', name: newFileUrlName.value}
            ],
        }
    })

    newFileUrlName.value = ''
    newFileUrl.value = ''

    emit('edit')
}

function validateFileName(name: string) {
    if (name.length < 3) {
        toastStore.error('Название файла должно содержать не менее 3-х символов!')
        return false
    }

    return true
}

function validateFileUrl(url: string) {
    const urlRegex = new RegExp(/^(http|https):\/\/[^ "]+$/)
    if (!urlRegex.test(url)) {
        toastStore.error('Указанный текст не является ссылкой!')
        return false
    }
    if (currentFiles.value.find((file) => file.url === url)) {
        toastStore.error('Такая ссылка уже прикреплена!')
        return false
    }

    return true
}

function onFileUpdate(event: FileUpdateEvent) {
    const fileSubmission = event.fileSubmission

    if ((fileSubmission.file_state!.localizations![0].name !== event.data.name && !validateFileName(event.data.name!)) ||
        (fileSubmission.file!.url !== event.data.url && (!event.data.url || !validateFileUrl(event.data.url)))
    ) {
        event.cancel = true
        return
    }

    fileSubmission.file_state!.localizations![0].name = event.data.name
    fileSubmission.file!.url = event.data.url
    fileSubmission.file!.size = event.data.size
    fileSubmission.file!.extension = event.data.extension

    emit('edit')
}

function removeFile() {
    if (fileSubmissionForRemove.value!.type === SubmissionType.CREATE) {
        currentVersionSubmission.value!.file_submissions = currentVersionSubmission.value!.file_submissions!.filter(
            (fileSubmission) => toRaw(fileSubmission) !== toRaw(fileSubmissionForRemove.value)
        )
    } else {
        fileSubmissionForRemove.value!.type = SubmissionType.DELETE
        delete fileSubmissionForRemove.value!.id
    }
    fileSubmissionForRemove.value = undefined
    isRemovingFile.value = false
    emit('edit')
}

function onRemoveFileClick(fileSubmission: MaterialFileSubmission) {
    fileSubmissionForRemove.value = fileSubmission
    isRemovingFile.value = true
}

function onVersionCreateClick() {
    isVersionFormVisible.value = true
    versionFormData.value = {
        type: SubmissionType.CREATE,
        number: '1.0',
        name: '',
        withOldFiles: false
    }
}

function onVersionEditClick() {
    isVersionFormVisible.value = true
    versionFormData.value = {
        type: SubmissionType.UPDATE,
        number: currentVersionSubmission.value!.version_state!.number,
        name: currentVersionSubmission.value!.version_state!.localizations![0].name,
    }
}

function onVersionRemoveClick() {
    isVersionRemoveFormVisible.value = true
}

function saveVersion() {
    if (versionFormData.value.type === SubmissionType.CREATE) {
        const nowDate = new Date().toISOString()
        materialSubmission.value.version_submissions!.push({
            type: SubmissionType.CREATE,
            version: {
                files: [],
                created_at: nowDate,
            },
            version_state: {
                number: versionFormData.value.number,
                localizations: [{language: 'ru', name: versionFormData.value.name || null}],
            },
            file_submissions: versionFormData.value.withOldFiles ? currentFiles.value?.map((file) => {
                const fileClone: MaterialFile = pick(file, [
                    'path', 'url', 'size', 'extension', 'state.localizations.*.language', 'state.localizations.*.name'
                ])

                fileClone.created_at = nowDate
                fileClone.updated_at = nowDate
                return {
                    type: SubmissionType.CREATE,
                    file: fileClone,
                    file_state: fileClone.state
                }
            }) ?? [] : []
        })
        currentVersionSubmissionIndex.value = 0
    } else {
        currentVersionSubmission.value!.version_state!.number = versionFormData.value.number
        currentVersionSubmission.value!.version_state!.localizations![0].name = versionFormData.value.name || null
    }

    isVersionFormVisible.value = false
    emit('edit')
}

function removeVersion() {
    if (currentVersionSubmission.value!.type === SubmissionType.CREATE) {
        materialSubmission.value.version_submissions!.splice(currentVersionSubmissionIndex.value, 1)
    } else {
        currentVersionSubmission.value!.type = SubmissionType.DELETE
    }

    isVersionRemoveFormVisible.value = false
    emit('edit')
}

function hasAnyOfFieldsFilled() {
    return currentLocalization.value.title?.length > 0
        || currentLocalization.value.cover
        || currentLocalization.value.content?.length > 0
        || currentLocalization.value.description?.length > 0
        || materialSubmission.value.material!.category !== undefined
        // TODO: Добавить проверку на количество файлов в каждой версии
        || versions.value.length > 0
}

function hasAllFieldsFilled() {
    return currentLocalization.value.title?.length > 0
        && currentLocalization.value.cover
        && currentLocalization.value.content?.length > 0
        && currentLocalization.value.description?.length > 0
        && materialSubmission.value.material!.category !== undefined
        // TODO: Добавить проверку на количество файлов в каждой версии
        && (!categoryRegistry.get(materialSubmission.value.material!.category).isDownloadable || versions.value.length > 0)
}

function onVersionSubmissionSelect(event: SelectOptionChangeEvent) {
    currentVersionSubmissionIndex.value = event.optionIndex
    loadFileSubmissions()
}

watch(() => materialSubmission.value, loadVersionSubmissions)

loadVersionSubmissions()
</script>

<template>
    <div
        v-if="materialSubmission" class="smooth-dark-background flex flex-col items-center w-full duration-500"
        :class="{'wide': preferenceManager.isMaterialFullViewVisible()}"
    >
        <slot name="banner"/>
        <section
            class="section flex justify-between xl:items-start items-center
                xl:max-w-[1280px] max-w-[832px] w-full gap-4 lg:mt-4"
        >
            <aside class="xl-left-material-interaction xl:flex hidden xl:flex-col sticky text-[12px] mb-12"/>

            <div
                class="material center-interaction bright-background ld-fixed-background
                    flex flex-col items-center max-w-[832px] w-full xs:px-4 px-2"
                ref="materialContent"
            >
                <aside
                    class="left-material-interaction xl-left-material-interaction upper-interaction
                        xl:hidden flex flex-col text-[12px] w-full mt-4"
                >
                    <slot name="sidebar"/>
                </aside>

                <div class="material-info-dates xl:hidden flex lg:justify-between justify-center w-full">
                    <button class="lg:flex hidden items-start" @click="preferenceManager.switchMaterialFullView()">
                        <span
                            class="icon flex my-4"
                            :class="{
                                'icon-right-direction-arrow': preferenceManager.isMaterialFullViewVisible(),
                                'icon-left-direction-arrow': !preferenceManager.isMaterialFullViewVisible()
                            }"
                        />
                    </button>
                </div>

                <div class="origin-info flex justify-between w-full">
                    <ProfileLink
                        class="author-wrap flex items-center w-fit gap-2 ml-[-2px] pb-2 pt-2"
                        :user="materialSubmission.material_state!.author"
                    >
                        <UserAvatar
                            border-class-list="h-10 w-10"
                            icon-class-list="h-7 w-7"
                            :user="materialSubmission.material_state!.author"
                        />
                        <span class="username duration-200">
                            {{
                                materialSubmission.material_state!.author !== null ? materialSubmission.material_state!.author.username : 'Некто'
                            }}
                        </span>
                    </ProfileLink>
                    <slot name="mark"/>
                </div>

                <Editor
                    v-model="currentLocalization.title"
                    :class="{'red-overlay': errors['material_state.localizations.0.title']}"
                    class="material-name ld-secondary-text text-center
                        flex justify-center max-w-[1280px] md:text-[2rem]
                        text-[1.5rem] mb-2"
                    :extensions="titleEditorExtensions"
                    :editable="editable"
                    plain-text
                    without-menus
                    @edit="emit('edit')"
                />

                <p class="error mb-2 mt-0">{{ errors['material_state.localizations.0.title']?.[0] || ' ' }}</p>

                <div class="preview-wrap flex w-full mt-0">
                    <UploadImage
                        class="upload-material-preview flex"
                        :class="{'red-overlay': errors['material_state.localizations.0.cover']}"
                        :editable="editable"
                        icon="icon-download"
                        id="upload-material-preview"
                        :image-src="currentLocalization.cover_url"
                        title="Загрузить обложку"
                        :max-size-in-megabytes="5"
                        :min-height="432"
                        :min-width="768"
                        @upload="onCoverUpload"
                    />
                </div>

                <p class="error my-2">{{ errors['material_state.localizations.0.cover']?.[0] || ' ' }}</p>

                <div class="w-full">
                    <Editor
                        v-model="currentLocalization.content"
                        :class="{'red-overlay': errors['material_state.localizations.0.content']}"
                        class="ld-secondary-text"
                        editor-class="material-content min-h-[20rem]"
                        :extensions="contentEditorExtensions"
                        :editable="editable"
                        @edit="emit('edit')"
                    />
                </div>

                <p class="error">{{ errors['material_state.localizations.0.content']?.[0] || ' ' }}</p>

                <div
                    class="separator self-center w-full opacity-40 my-2"
                    style="background-color: var(--secondary-text-color);"
                />

                <div class="w-full">
                    <Textarea
                        class="material-description ld-primary-background ld-primary-border ld-red-bordered md:text-[14px] text-[12px]"
                        :class="{'red-border': errors['material_state.localizations.0.description']}"
                        text-area-classes="ld-tinted-background min-h-[108px]"
                        v-model="currentLocalization.description"
                        :editable="editable"
                        id="material-description"
                        :max-length="165"
                        :min-length="15"
                        placeholder="Описание"
                        rows="3"
                        @edit="emit('edit')"
                    />
                </div>

                <p class="error my-2">{{ errors['material_state.localizations.0.description']?.[0] || ' ' }}</p>

                <Select
                    button-classes="ld-primary-background ld-primary-border ld-title-font w-full"
                    options-classes="ld-primary-background ld-primary-border md:top-[66px]
                        top-[50px]"
                    option-classes="md:min-h-[64px] min-h-[48px] gap-4 pl-6"
                    class="material-edition flex items-center w-full my-4"
                    v-model="materialSubmission.material!.edition"
                    :editable="editable && materialSubmission.type === SubmissionType.CREATE"
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
                    options-classes="ld-primary-background ld-primary-border md:top-[66px] top-[50px]"
                    option-classes="md:min-h-[64px] min-h-[48px] gap-4 pl-6"
                    class="material-category flex items-center w-full my-4"
                    :class="{'red-border': errors['material.category']}"
                    v-model="materialSubmission.material!.category"
                    :editable="editable && materialSubmission.type === SubmissionType.CREATE"
                    input-id="category"
                    :options="categories"
                    option-label-key="name"
                    option-icon-key="icon"
                    option-value-key="type"
                    placeholder="Выберите Категорию"
                    @change="onCategoryChange"
                >
                    <template #option-icon/>
                </Select>

                <p class="error my-2">{{ errors['material.category']?.[0] || ' ' }}</p>

                <template v-if="showVersionBlock">
                    <div
                        class="separator self-center w-full opacity-40 my-2"
                        style="background-color: var(--secondary-text-color);"
                    />

                    <div class="material-version-editor-buttons w-full flex items-center">
                        <MaterialVersionSubmissionSelect
                            :current-version-submission="currentVersionSubmission!"
                            :version-submissions="sortedVersionSubmissions"
                            @change="onVersionSubmissionSelect"
                        />

                        <button
                            class="flex justify-center items-center h-full lg:min-w-[48px] min-w-[36px] border-0"
                            :class="{ 'opacity-50': !editable || !canCreateVersion }"
                            :disabled="!editable || !canCreateVersion"
                            @click="onVersionCreateClick"
                        >
                            <span class="icon-plus icon flex" v-tooltip.top="'Добавить'"/>
                        </button>
                        <button
                            class="flex justify-center items-center h-full lg:min-w-[48px] min-w-[36px] border-0"
                            :class="{ 'opacity-50': !editable || !currentVersionSubmission || currentVersionSubmission!.type === SubmissionType.DELETE }"
                            :disabled="!editable || !currentVersionSubmission || currentVersionSubmission!.type === SubmissionType.DELETE"
                            @click="onVersionEditClick"
                        >
                            <span class="icon-small-pencil icon flex" v-tooltip.top="'Редактировать'"/>
                        </button>
                        <button
                            class="flex justify-center items-center h-full lg:min-w-[48px] min-w-[36px] border-0"
                            :class="{ 'opacity-50': !editable || versions.length <= 1 || currentVersionSubmission!.type === SubmissionType.DELETE }"
                            :disabled="!editable || versions.length <= 1 || currentVersionSubmission!.type === SubmissionType.DELETE"
                            @click="onVersionRemoveClick"
                        >
                            <span class="icon-trash icon flex" v-tooltip.top="'Удалить'"/>
                        </button>
                    </div>

                    <div
                        v-if="currentVersionSubmission"
                        class="w-full outline-offset-[5px]"
                        :class="{'red-overlay': errors[`version_submissions.${currentVersionSubmissionIndex}.file_submissions`]}"
                    >
                        <div class="flex flex-col w-full gap-3 mb-4">
                            <h4 class="ld-secondary-text flex xs:flex-row flex-col justify-center text-center xs:gap-2 mt-0">
                                <span>Файлы на скачивание</span>
                                <span>{{ ' [ ' + currentFiles.length + ' / 3 ]' }}</span>
                            </h4>
                            <template
                                v-for="(fileSubmission, i) in currentVersionSubmission.file_submissions"
                                :key="fileSubmission.file!.path || fileSubmission.file!.url"
                            >
                                <MaterialFileSubmissionComponent
                                    :file-submission="fileSubmission"
                                    :version-submission="currentVersionSubmission"
                                    :editable="editable"
                                    @update="onFileUpdate"
                                    @remove="onRemoveFileClick"
                                />
                                <InputError class="text-[0.8rem] m-2"
                                            :error="errors[`version_submissions.${currentVersionSubmissionIndex}.file_submissions.${i}`]?.[0]"/>
                                <InputError class="text-[0.8rem] m-2"
                                            :error="errors[`version_submissions.${currentVersionSubmissionIndex}.file_submissions.${i}.file_state.localizations.0.name`]?.[0]"
                                            fieldName="Название"/>
                                <InputError class="text-[0.8rem] m-2"
                                            :error="errors[`version_submissions.${currentVersionSubmissionIndex}.file_submissions.${i}.file.path`]?.[0]"
                                            fieldName="Путь"/>
                                <InputError class="text-[0.8rem] m-2"
                                            :error="errors[`version_submissions.${currentVersionSubmissionIndex}.file_submissions.${i}.file.url`]?.[0]"
                                            fieldName="URL"/>
                                <InputError class="text-[0.8rem] m-2"
                                            :error="errors[`version_submissions.${currentVersionSubmissionIndex}.file_submissions.${i}.file.extension`]?.[0]"
                                            fieldName="Расширение"/>
                                <InputError class="text-[0.8rem] m-2"
                                            :error="errors[`version_submissions.${currentVersionSubmissionIndex}.file_submissions.${i}.file.size`]?.[0]"
                                            fieldName="Размер"/>
                            </template>
                        </div>

                        <div
                            v-if="editable && canCreateFile"
                            class="ld-secondary-text w-full mt-2"
                        >
                            <UploadFile
                                class="upload-material-preview flex mb-5 mt-2.5"
                                :editable="editable"
                                icon="icon-download"
                                id="upload-material-file"
                                :is-uploading="isMaterialFileUploading"
                                title="Загрузить Файл"
                                :max-size-in-megabytes="20"
                                @upload="uploadFile"
                            />
                            <p class="flex justify-center w-full mb-2">или</p>
                            <em class="upload-file-heading flex justify-center gap-2">
                                <span class="icon-link-square icon"/>
                                <span
                                    class="head-font ld-secondary-text text-center md:text-[16px] text-[14px] duration-200">Указать ссылку</span>
                            </em>
                            <form class="flex flex-col gap-4 mt-4" @submit.prevent="addFileUrl">
                                <Input
                                    v-model="newFileUrlName"
                                    class="ld-primary-background ld-primary-border ld-primary-text
                                sm:text-[14px] text-[12px] md:h-[48px] h-[40px] w-full"
                                    :class="{ 'red-border': isNewFileUrlNameError }"
                                    id="new-file-url-name"
                                    input-classes="ld-tinted-background"
                                    :max-length="255"
                                    :min-length="1"
                                    placeholder="Название"
                                />
                                <Input
                                    v-model="newFileUrl"
                                    class="ld-primary-background ld-primary-border ld-primary-text
                                sm:text-[14px] text-[12px] md:h-[48px] h-[40px] w-full"
                                    :class="{ 'red-border': isNewFileUrlError }"
                                    id="new-file-url"
                                    input-classes="ld-tinted-background"
                                    :max-length="255"
                                    :min-length="1"
                                    placeholder="Ссылка на Файл"
                                />
                                <ShineButton
                                    button-type="submit"
                                    class="flex items-center"
                                    class-wrap="ld-primary-background"
                                    class-preset="ld-primary-text ld-title-font justify-center xs:text-[14px] text-[12px] gap-1 py-0.5"
                                    label="Добавить Ссылку"
                                    icon="icon-tick"
                                />
                            </form>
                        </div>
                    </div>
                    <p class="error flex justify-center w-full my-2">
                        {{
                            errors[`version_submissions.${currentVersionSubmissionIndex}.file_submissions`]?.[0] || ' '
                        }}
                    </p>
                </template>

                <aside
                    class="right-material-interaction xl-right-material-interaction
                        xl:hidden flex flex-col text-[12px] w-full"
                >
                    <div
                        class="separator self-center w-full opacity-40 my-2"
                        style="background-color: var(--secondary-text-color);"
                    />
                    <slot name="sidebar"/>
                </aside>
            </div>

            <aside class="right-material-interaction xl-right-material-interaction xl:flex hidden xl:flex-col xl:sticky
                text-[12px] xl:max-w-[336px] gap-4"
            >
                <div class="first-bright-block bright-background flex flex-col p-1 gap-2">
                    <button class="flex justify-end" @click="preferenceManager.switchMaterialFullView()">
                        <span
                            class="icon flex"
                            :class="{
                                'icon-right-direction-arrow': preferenceManager.isMaterialFullViewVisible(),
                                'icon-left-direction-arrow': !preferenceManager.isMaterialFullViewVisible()
                            }"
                        />
                    </button>
                    <slot name="sidebar"/>
                </div>
                <!-- xl:flex -->
                <div class="next-bright-block bright-background hidden flex-col overflow-hidden">
                    <div class="material-addition-content flex flex-col w-full p-4 duration-500" style="color: dimgray">
                        Дополнительный Контент
                    </div>
                </div>
            </aside>
        </section>

        <Dialog
            v-model:visible="isVersionFormVisible"
            :title="versionFormData?.type === SubmissionType.CREATE ? 'Добавление версии' : 'Редактирование версии'"
        >
            <form class="flex flex-col items-center w-full" @submit.prevent="saveVersion">
                <fieldset class="flex flex-col items-center w-full">
                    <div class="group flex flex-col w-[85%] gap-5">
                        <div>
                            <span
                                class="subtitle md:text-[14px] text-[12px]"
                                :class="{ 'error': versionFormErrors['number'] }"
                            >
                                Номер
                            </span>
                            <Input
                                v-model="versionFormData.number"
                                class="h-[48px]"
                                id="version-number"
                            />
                            <span
                                v-if="versionFormErrors['number']"
                                class="status md:text-[12px] text-[10px]"
                                :class="{ 'error': versionFormErrors['number'] }"
                            >
                                {{ versionFormErrors['number']?.[0] }}
                            </span>
                        </div>

                        <div>
                            <span
                                class="subtitle md:text-[14px] text-[12px]"
                                :class="{ 'error': versionFormErrors['name'] }"
                            >
                                Название
                            </span>

                            <Input
                                v-model="versionFormData.name"
                                class="h-[48px]"
                                id="version-name"
                            />

                            <span
                                v-if="versionFormErrors['name']"
                                class="status md:text-[12px] text-[10px]"
                                :class="{ 'error': versionFormErrors['name'] }"
                            >
                                {{ versionFormErrors['name']?.[0] }}
                            </span>
                        </div>

                        <label v-if="versionFormData.type === SubmissionType.CREATE"
                               class="flex items-center gap-3 bg-none border-none cursor-pointer" for="with-old-files">
                            <Checkbox v-model="versionFormData.withOldFiles" id="with-old-files"/>
                            <span class="text-sm">С файлами предыдущей версии</span>
                        </label>

                        <div class="flex justify-center w-[85%] gap-2 mb-6">
                            <Button
                                button-type="submit"
                                :label="versionFormData.type === SubmissionType.CREATE ? 'Добавить' : 'Применить'"
                                class="success w-full"
                            />

                            <Button
                                label="Отмена"
                                class="secondary w-full"
                                @click="isVersionFormVisible = false"
                            />
                        </div>
                    </div>
                </fieldset>
            </form>
        </Dialog>

        <Dialog
            v-model:visible="isVersionRemoveFormVisible"
            title="Удаление версии"
        >
            <form action="" class="flex flex-col items-center sm:min-w-[390px] max-w-[390px] p-2">
                <p class="subtitle md:text-base text-sm text-center mb-4">
                    Вы действительно хотите удалить версию {{ currentVersionSubmission.version_state.number }}?
                </p>
                <p v-if="currentVersionSubmission!.type === SubmissionType.UPDATE"
                   class="text-xs text-center mb-4 opacity-80">
                    Также удалятся все комментарии, связанные с этой версией.
                </p>

                <div class="flex justify-center w-[85%] gap-2 mb-6">
                    <Button
                        button-type="submit"
                        label="Удалить"
                        class="danger min-w-[140px]"
                        @click.prevent="removeVersion"
                    />

                    <Button
                        button-type="submit"
                        label="Отмена"
                        class="secondary min-w-[140px]"
                        @click.prevent="isVersionRemoveFormVisible = false"
                    />
                </div>
            </form>
        </Dialog>

        <Dialog
            v-model:visible="isRemovingFile"
            title="Удаление файла"
        >
            <form action="" class="flex flex-col items-center sm:min-w-[390px] max-w-[390px]">
                <p class="subtitle md:text-[14px] text-[12px] text-center mb-4">
                    Вы действительно хотите удалить {{ fileSubmissionForRemove.file.path ? '' : 'ссылочный ' }}файл
                    «{{ fileSubmissionForRemove!.file_state.localizations![0].name }}»?
                </p>

                <div class="flex justify-center w-[85%] gap-2 mb-6">
                    <Button
                        button-type="submit"
                        label="Удалить"
                        class="danger min-w-[140px]"
                        @click.prevent="removeFile"
                    />

                    <Button
                        button-type="submit"
                        label="Отмена"
                        class="secondary min-w-[140px]"
                        @click.prevent="isRemovingFile = false"
                    />
                </div>
            </form>
        </Dialog>
    </div>
</template>

<style>
.editor-plus-button {
    margin-left: 8px;
}

.wide .editor-plus-button {
    margin-right: 8px;
}

.material-title {
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

.xl-left-material-interaction,
.xl-right-material-interaction {
    transition: .5s;
}

.material-addition-content {
    transform: translateX(100%);
    opacity: 0;
}

.wide .material-addition-content {
    transform: translateX(0);
    opacity: 1;
}

.wide .center-interaction {
    margin-bottom: 1rem;
}

/* =============== [ Медиа-Запрос { 1281px > ?px } ] =============== */

@media screen and (min-width: 1281px) {
    .xl-left-material-interaction,
    .xl-right-material-interaction {
        width: 208px;
        top: 96px;
    }

    .wide .xl-right-material-interaction {
        width: 336px;
        top: 80px;
    }

    .xl-left-material-interaction {
        margin-top: 0;
    }

    .wide .xl-left-material-interaction {
        width: 80px;
    }

    .right-material-info-bar {
        padding-left: 3rem;
    }

    .wide .right-material-info-bar {
        padding: 0 1rem 1rem 1rem;
    }

    #comments {
        transition: .5s;
    }

    .wide #comments {
        margin-right: 256px;
    }

    .first-bright-block {
        margin-bottom: 3rem;
    }

    .wide .first-bright-block {
        margin-bottom: 1rem;
    }

    .next-bright-block {
        margin-bottom: 1rem;
    }

    .wide .next-bright-block {
        margin-bottom: 3rem;
    }
}

/* =============== [ Медиа-Запрос { ?px < 1281px } ] =============== */

@media screen and (max-width: 1280px) {
    .xl-right-material-interaction {
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
