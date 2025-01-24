<script setup lang="ts">
import {StarterKit} from '@tiptap/starter-kit'
import {Placeholder} from '@tiptap/extension-placeholder'
import {Underline} from '@tiptap/extension-underline'
import {Link} from '@tiptap/extension-link'
import {Blockquote} from '@tiptap/extension-blockquote'
// import {Image} from '@tiptap/extension-image'

import Editor from '@/components/elements/editor/Editor.vue'

const content = defineModel<string>()

const editorExtensions = [
    StarterKit.configure({
        heading: false,
        blockquote: false,
        horizontalRule: false,
    }),
    Blockquote.extend({
        priority: 101,
    }),
    Placeholder.configure({
        placeholder: ({node}) => {
            switch (node.type.name) {
                case 'codeBlock':
                    return '// Код'
                default:
                    return 'Текст комментария...'
            }
        },
    }),
    Underline,
    Link.configure({
        openOnClick: 'whenNotEditable',
    }),
    // Image
]
</script>

<template>
    <Editor
        v-model="content"
        :extensions="editorExtensions"
        editor-class="material-comment-content min-h-[4rem]"
        class="lg:px-12 lg:pb-4 xs:px-4 px-2 pt-4"
        :max-image-count="3"
    />
</template>

<style>
    .editor-horizontal-menu .item-button {
        min-height: 48px;
    }
    .material-comment-editor .editor-plus-button {
        position: relative;
        left: 8px;
    }
    .wide .material-comment-editor .editor-plus-button {
        left: 16px;
    }
    /* =============== [ Медиа-Запрос { ?px < 769px } ] =============== */

    @media screen and (max-width: 768px) {
        .material-comment-editor .tiptap {
            padding: .5rem;
        }
    }
</style>
