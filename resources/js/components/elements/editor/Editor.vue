<script setup lang="ts">
import axios, {type AxiosError} from 'axios'
import {computed, onUnmounted, type PropType, reactive, ref, watch} from 'vue'
import {BubbleMenu, EditorContent, type Extensions, FloatingMenu, useEditor} from '@tiptap/vue-3'
import {getErrorMessageByCode, countHTMLTag} from '@/helpers'
import {EditorState} from 'prosemirror-state'
import {useToastStore} from '@/stores/toast'

import Input from '@/components/elements/Input.vue'
import ItemButton from '@/components/elements/ItemButton.vue'

import type {EditorMenuItem} from '@/components/elements/editor/types'
import EditorHorizontalMenu from '@/components/elements/editor/EditorHorizontalMenu.vue'
import EditorVerticalMenu from '@/components/elements/editor/EditorVerticalMenu.vue'
import OverlayPanel from '@/components/elements/OverlayPanel.vue'

const addNodeMenu = ref<InstanceType<typeof EditorVerticalMenu>>()
const linkOverlayPanel = ref<InstanceType<typeof OverlayPanel>>()

const props = defineProps({
    editable: {
        type: Boolean,
        default: true
    },
    editorClass: String,
    extensions: {
        type: Object as PropType<Extensions>,
        required: true
    },
    isCommentEditor: {
        type: Boolean,
        default: false
    },
    plainText: {
        type: Boolean,
        default: false
    },
    withoutMenus: {
        type: Boolean,
        default: false
    }
})

interface EditorNodeInfo {
    name: string
    attributes?: object
    displayName: string
    shortcut?: string
    icon: string
    callback: (event: Event) => void
}

const toastStore = useToastStore()
const contentModel = defineModel<string>()
let currentEditorContent = contentModel.value

const currentLink = reactive({
    text: '',
    href: ''
})

const htmlContent = ref('')

const editor = useEditor({
    editorProps: {
        attributes: {
            class: `focus:outline-none ${props.editorClass}`
        }
    },

    editable: props.editable,
    enableInputRules: false,
    extensions: props.extensions,
    content: contentModel.value,
    onUpdate: ({editor}) => {
        currentEditorContent = props.plainText ? editor.getText() : editor.getHTML()
        contentModel.value = currentEditorContent
        htmlContent.value = editor.getHTML()
    }
})

const nodes: { [key: string]: EditorNodeInfo } = {
    'heading-1': {
        name: 'heading',
        attributes: {level: 1},
        displayName: 'Заголовок 1',
        shortcut: 'Ctrl+Alt+1',
        icon: 'icon-title-1',
        callback: () => editor.value?.chain().focus()?.setHeading({level: 1}).run(),
    },
    'heading-2': {
        name: 'heading',
        attributes: {level: 2},
        displayName: 'Заголовок 2',
        shortcut: 'Ctrl+Alt+2',
        icon: 'icon-title-2',
        callback: () => editor.value?.chain().focus().setHeading({level: 2}).run(),
    },
    'heading-3': {
        name: 'heading',
        attributes: {level: 3},
        displayName: 'Заголовок 3',
        shortcut: 'Ctrl+Alt+3',
        icon: 'icon-title-3',
        callback: () => editor.value?.chain().focus().setHeading({level: 3}).run(),
    },
    'bulletList': {
        name: 'bulletList',
        displayName: 'Маркерованный список',
        shortcut: 'Ctrl+⇧+8',
        icon: 'icon-unordered-list',
        callback: () => editor.value?.chain().focus().toggleBulletList().run(),
    },
    'orderedList': {
        name: 'orderedList',
        displayName: 'Нумерованный список',
        shortcut: 'Ctrl+⇧+7',
        icon: 'icon-ordered-list',
        callback: () => editor.value?.chain().focus().toggleOrderedList().run(),
    },
    'paragraph': {
        name: 'paragraph',
        displayName: 'Абзац',
        shortcut: 'Ctrl+Alt+0',
        icon: 'icon-paragraph',
        callback: () => editor.value?.chain().focus().setParagraph().run(),
    },

    'codeBlock': {
        name: 'codeBlock',
        displayName: 'Код',
        shortcut: 'Ctrl+Alt+C',
        icon: 'icon-code',
        callback: () => editor.value?.chain().focus().setCodeBlock().run(),
    },
    'blockquote': {
        name: 'blockquote',
        displayName: 'Цитата',
        shortcut: 'Ctrl+⇧+B',
        icon: 'icon-quote',
        callback: () => editor.value?.chain().focus().toggleBlockquote().run(),
    },
    'horizontalRule': {
        name: 'horizontalRule',
        displayName: 'Разделитель',
        icon: 'icon-minus',
        callback: () => editor.value?.chain().focus().setHorizontalRule().run(),
    },
    'image': {
        name: 'image',
        displayName: 'Изображение',
        icon: 'icon-image',
        callback: () => openImageDialog((image) => {
            uploadImage(image, (imageUrl) => {
                editor.value?.chain().focus().setImage({src: imageUrl}).run()
            })
        }),
    },
}

const marks: { [key: string]: EditorNodeInfo } = {
    'bold': {
        name: 'bold',
        displayName: 'Жирный',
        shortcut: 'Ctrl+B',
        icon: 'icon-bold',
        callback: () => editor.value?.chain().focus().toggleBold().run(),
    },
    'italic': {
        name: 'italic',
        displayName: 'Курсив',
        shortcut: 'Ctrl+I',
        icon: 'icon-italic',
        callback: () => editor.value?.chain().focus().toggleItalic().run(),
    },
    'underline': {
        name: 'underline',
        displayName: 'Подчёркнутый',
        shortcut: 'Ctrl+U',
        icon: 'icon-underlined',
        callback: () => editor.value?.chain().focus().toggleUnderline().run(),
    },
    'strike': {
        name: 'strike',
        displayName: 'Зачёркнутый',
        shortcut: 'Ctrl+⇧+S',
        icon: 'icon-strike-through',
        callback: () => editor.value?.chain().focus().toggleStrike().run(),
    },
    'code': {
        name: 'code',
        displayName: 'Код',
        shortcut: 'Ctrl+E',
        icon: 'icon-code',
        callback: () => editor.value?.chain().focus().toggleCode().run(),
    },
    'link': {
        name: 'link',
        displayName: 'Ссылка',
        icon: 'icon-link-round',
        callback: (event: Event) => linkOverlayPanel.value!.toggle(event),
    },
}

const menuItems = computed<EditorMenuItem[]>(() => {
    if (!editor.value) {
        return []
    }

    const items: EditorMenuItem[] = []
    const headingItems = ['heading-1', 'heading-2', 'heading-3'].map<EditorMenuItem>((key) => ({
        displayName: nodes[key].displayName,
        icon: nodes[key].icon,
        shortcut: nodes[key].shortcut,
        isVisible: !!(editor.value!.schema.nodes['heading'] && !editor.value?.isActive(nodes[key].name, nodes[key].attributes)),
        callback: nodes[key].callback,
    }))

    if (isAtEmptyRootParagraph()) {
        if (editor.value.schema.nodes['heading']) {
            items.push(...headingItems)
        }

        ['blockquote', 'bulletList', 'orderedList', 'image', 'codeBlock', 'horizontalRule'].forEach((key) => {
            if (editor.value!.schema.nodes[key]) {
                items.push({
                    displayName: nodes[key].displayName,
                    icon: nodes[key].icon,
                    shortcut: nodes[key].shortcut,
                    callback: nodes[key].callback,
                })
            }
        })
    } else if (isAtTextBlock()) {
        if (editor.value.isActive('paragraph')) {
            ['bold', 'italic', 'underline', 'strike', 'code', 'link'].forEach((key) => {
                if (editor.value!.schema.marks[key]
                    && (key === 'code' || !editor.value!.isActive(marks['code'].name))
                ) {
                    items.push({
                        displayName: marks[key].displayName,
                        icon: marks[key].icon,
                        shortcut: marks[key].shortcut,
                        isActive: editor.value!.isActive(marks[key].name),
                        callback: marks[key].callback,
                    })
                }
            })

            items.push({isSeparator: true})
        }

        const currentNodeInfo = getCurrentNodeInfo()

        items.push({
            displayName: `Преобразовать «${currentNodeInfo?.displayName ?? '?'}» в`,
            icon: 'icon-text-height',
            children: [
                ...headingItems,
                ...['paragraph', 'codeBlock', 'bulletList', 'orderedList'].map((key) => ({
                    displayName: nodes[key].displayName,
                    icon: nodes[key].icon,
                    shortcut: nodes[key].shortcut,
                    isVisible: !!(editor.value!.schema.nodes[key] && currentNodeInfo?.name !== key),
                    callback: nodes[key].callback,
                })),
            ],
        })

        if (editor.value!.schema.nodes['blockquote']) {
            items.push({
                displayName: nodes['blockquote'].displayName,
                icon: nodes['blockquote'].icon,
                shortcut: nodes['blockquote'].shortcut,
                isActive: editor.value.isActive(nodes['blockquote'].name),
                isVisible: !editor.value.isActive('bulletList') && !editor.value.isActive('orderedList'),
                callback: nodes['blockquote'].callback,
            })
        }
    }

    return items
})

const selectedText = computed(() => {
    const {view, state} = editor.value!
    const {from, to} = view.state.selection
    return state.doc.textBetween(from, to, '')
})

document.addEventListener('scroll', onScroll)

onUnmounted(() => {
    document.removeEventListener('scroll', onScroll)
})

watch(contentModel, (value) => {
    if (currentEditorContent !== value) {
        currentEditorContent = value ?? ''
        editor.value!.commands.setContent(currentEditorContent)
    }
})

function onScroll() {
    linkOverlayPanel.value?.hide()
}

function isAtTextBlock(): boolean {
    return editor.value ? editor.value.state.selection.$anchor.parent.isTextblock : false
}

function isAtEmptyRootParagraph(editorState: EditorState | undefined = undefined): boolean {
    if (!editorState) {
        if (!editor.value) {
            return false
        }

        editorState = editor.value.state
    }

    const {$anchor, empty} = editorState.selection
    const isRootDepth = $anchor.depth === 1
    const isEmptyParagraph = $anchor.parent.type.name === 'paragraph' && !$anchor.parent.type.spec.code && !$anchor.parent.textContent

    return empty && isRootDepth && isEmptyParagraph
}

function getCurrentNodeInfo(): EditorNodeInfo | null {
    if (!editor.value) {
        return null
    }

    for (const key in nodes) {
        if (editor.value.isActive(nodes[key].name, nodes[key].attributes)) {
            return nodes[key]
        }
    }
    return null
}

function onLinkOverlayPanelShow() {
    currentLink.text = selectedText.value
    currentLink.href = editor.value?.getAttributes('link').href
}

function openImageDialog(callback: ((file: File) => void)) {
    const input = document.createElement('input')
    input.type = 'file'
    input.accept = 'image/jpeg, image/png, image/jpg, image/gif'
    input.style.display = 'none'
    input.onchange = () => {
        callback(input.files![0])
    }
    input.click()
}

function uploadImage(image: File, callback: ((url: string) => void)) {

    if (image.size > 5 * 1024 * 1024) {
        toastStore.error(`Файл слишком большой. Максимальный размер - 5 Мб.`, 'Размер')
        return
    }

    if (props.isCommentEditor && countHTMLTag(htmlContent.value, 'img') > 2) {
        toastStore.error(`Вы не можете отправлять в комментариях более 3-х изображений!`, 'Превышен лимит!')
        return
    }

    const formData = new FormData()
    formData.append('image', image)

    axios.post('/api/upload-image', formData).then((response) => {
        if (response.data.success) {
            callback(response.data.image_url)
        } else {
            if (response.data.errors) {
                toastStore.error(response.data.errors['image'][0])
            }
        }
    }).catch((error: AxiosError) => {
        toastStore.error(getErrorMessageByCode(error.response!.status))
    })
}

function setLink() {
    const selection = editor.value!.view!.state.selection

    if (currentLink.href.trim() !== '') {
        editor.value!
            .chain()
            .focus()
            .setLink({ href: currentLink.href })
            .insertContentAt({ from: selection.from, to: selection.to }, currentLink.text)
            .run()
    }

    linkOverlayPanel.value?.hide()
}

function unsetLink() {
    editor.value!.chain().focus().unsetLink().run()
    linkOverlayPanel.value?.hide()
}
</script>

<template>
    <div class="editor">
        <BubbleMenu
            v-if="editor && editable && !withoutMenus && menuItems.length > 0"
            :tippy-options="{zIndex: 1, maxWidth: 'none'}"
            class="hidden lg:block"
            :editor="editor"
            :update-delay="0"
        >
            <EditorHorizontalMenu
                class="border drop-shadow-[0_0px_2px_rgba(0,0,0,0.3)]"
                :items="menuItems"
            />
        </BubbleMenu>

        <FloatingMenu
            v-if="editor && editable && !withoutMenus"
            :tippy-options="{ placement: 'left', offset: [0, 0], zIndex: 1 }"
            :should-show="({state}) => isAtEmptyRootParagraph(state)"
            class="hidden lg:block"
            :editor="editor"
        >
            <ItemButton
                class="ld-title-font editor-plus-button justify-center md:text-[14px] text-[12px] min-h-[64px] min-w-[64px]"
                style="background-color: transparent; border: none;"
                label-classes="hidden"
                @click="addNodeMenu?.toggle"
                severity="secondary"
                icon="icon-plus focus-highlighted-icon"
            />
            <EditorVerticalMenu ref="addNodeMenu" title="Добавить" :items="menuItems"/>
        </FloatingMenu>

        <!-- @show="onLinkOverlayPanelShow" -->
        <OverlayPanel v-if="editable && !withoutMenus" ref="linkOverlayPanel">
            <form class="content-link-editor ld-primary-background" @submit.prevent="setLink">
                <div class="flex">
                    <Input v-model="currentLink.href" class="editor-link-url w-full" placeholder="https://" autocomplete="off"/>
                    <ItemButton
                        title="Сохранить ссылку"
                        severity="secondary"
                        icon="icon-tick"
                        type="submit"
                    />
                    <ItemButton
                        title="Удалить ссылку"
                        severity="danger"
                        icon="icon-small-cross"
                        @click="unsetLink"
                    />
                </div>
                <div class="separator"></div>
                <Input v-model="currentLink.text" class="editor-link-text w-full h-[3rem]" placeholder="Текст..." autocomplete="off"/>
            </form>
        </OverlayPanel>

        <div>
            <EditorContent :editor="editor"/>
        </div>

        <EditorHorizontalMenu
            v-if="editor && editable && !withoutMenus"
            class="block lg:hidden sticky bottom-0 whitespace-nowrap"
            :items="menuItems"
        />
    </div>
</template>

<style scoped>

</style>
