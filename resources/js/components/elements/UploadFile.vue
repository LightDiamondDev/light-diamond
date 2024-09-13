<script setup lang="ts">
import {useToastStore} from '@/stores/toast'
import {ref} from 'vue'

const props = defineProps({
    id: String,
    editable: {
        type: Boolean,
        default: true
    },
    icon: String,
    title: String,
    fileSrc: String,
    maxSizeInMegabytes: {
        type: Number,
        required: true
    }
})

const emit = defineEmits<{
    (e: 'upload', file: File): void
}>()

const model = defineModel<string>({default: ''})
const fileSrc = ref(props.fileSrc)
const toastStore = useToastStore()

const allowedFileFormats = ['MCPACK', 'MCADDON', 'MCWORLD', 'MCSKIN', 'ZIP', 'PNG'];
const maxSizeInMegabytes = props.maxSizeInMegabytes

const isDraggingOver = ref(false)

function onChange(event: any) {
    if (props.editable && event.target && event.target.files[0]) {
        uploadFile(event.target.files![0])
    }
}

function onDrop(event: DragEvent) {
    if (props.editable) {
        if (event.dataTransfer?.files) {
            uploadFile(event.dataTransfer.files[0])
        }
        isDraggingOver.value = false
    }
}

function onDragEnter() {
    if (props.editable) {
        isDraggingOver.value = true
    }
}

function onDragLeave() {
    if (props.editable) {
        isDraggingOver.value = false
    }
}

function uploadFile(file: File) {
    const fileExtension = file.name.split('.').pop().toUpperCase()

    if (!allowedFileFormats.includes(fileExtension)) {
        toastStore.warning(
            `Недопустимый формат Файла, разрешены только ${allowedFileFormats.join(', ')}.`,
            'Неверный формат'
        )
        return
    }

    if (file.size > maxSizeInMegabytes * 1024 * 1024) {
        toastStore.warning(
            `Файл слишком большой. Максимальный размер - ${maxSizeInMegabytes} Мб.`,
            'Размер'
        )
        return
    }

    emit('upload', file)
}
</script>

<template>
<label
    :class="{'editable': editable, 'cursor-pointer': editable, 'dragover': isDraggingOver, 'loaded': fileSrc }"
    class="upload-file-container flex justify-center items-center locked"
    :style="'background-size: 100% 100%;background-image: url(' + fileSrc + ');'"
    @dragenter.prevent="onDragEnter"
    @dragleave="onDragLeave"
    @dragover.prevent
    @drop.prevent="onDrop"
    :for="id"
>
    <em
        :class="{ 'loaded': fileSrc }"
        class="upload-file-info flex flex-col items-center gap-2 p-4"
    >
        <span class="upload-file-heading flex gap-2">
            <span :class="icon" class="icon relative"></span>
            <span class="head-font md:text-[1.2rem] text-center duration-200">{{ title }}</span>
        </span>
        <span class="text-center">Максимальный размер — {{ maxSizeInMegabytes }} Мб</span>
        <span class="text-center">{{ allowedFileFormats.join(" / ") }}</span>
        <input
            @change="onChange"
            accept="file/*"
            type="file"
            :id="id"
        />
    </em>
</label>
</template>

<style scoped>
.upload-file-container:active .icon,
.upload-file-container:hover .icon,
.upload-file-container.dragover .icon {
    animation: levitation-icon-animation infinite 1s ease;
}
.upload-file-info {
    background-size: 100% 100%;
    pointer-events: none;
}
.loaded {
    background: radial-gradient(rgba(0, 0, 0, .6), rgba(0, 0, 0, .6), rgba(0, 0, 0, .1));
    box-shadow: 0 0 2em rgba(0, 0, 0, .6);
}
em {
    transition: .5s;
}
em span {
    text-shadow: none;
}
em.loaded span {
    text-shadow: 0 0 8px black;
}
em.loaded {
    opacity: 0;
}
em.loaded .upload-file-heading {
    color: var(--hover-text-color);
}
.upload-file-container:hover em.loaded:not(:hover) {
    opacity: 1;
}
.upload-file-container.loaded:not(.dragover) {
    outline: 0;
}
input {
    height: 0;
    width: 0;
}

/* =============== [ Медиа-Запрос { ?px < 768px } ] =============== */

@media screen and (max-width: 767px) {
    em span {
        font-size: .9rem;
    }
}

/* =============== [ Медиа-Запрос { ?px < 425px } ] =============== */

@media screen and (max-width: 425px) {
    em span {
        font-size: .7rem;
    }
}
</style>
