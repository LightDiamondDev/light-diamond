<script setup lang="ts">
import {ref} from 'vue'

import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'

import Button from '@/components/elements/Button.vue'

defineProps({
    imageSrc: {
        type: String,
        required: true
    }
})

const emit = defineEmits<{
    (e: 'cancel'): void
    (e: 'submit', blob: Blob): void
}>()

const avatarCropper = ref<any>()

function cancelChangeAvatar() { emit('cancel') }

function submitChangeAvatar() {
    avatarCropper.value?.getResult().canvas.toBlob((blob) => {
        emit('submit', blob)
    })
}

</script>

<template>
    <form action="" class="avatar-cropper-form flex flex-col items-center" name="avatar-cropper">
        <p class="ld-primary-border-bottom subtitle md:text-[14px] text-[12px] text-center py-2 px-4">
            Выбранная область изображения будет отображаться в качестве Вашего Аватара.
        </p>
        <Cropper
            :src="imageSrc"
            :stencil-props="{ aspectRatio: 1 }"
            ref="avatarCropper"
        />
        <div class="ld-primary-border-top flex justify-center items-end w-full gap-2 py-2">
            <Button
                button-type="button"
                label="Подтвердить"
                label-classes="px-6"
                class="success xs:w-[40%]"
                @click="submitChangeAvatar"
            />
            <Button
                button-type="button"
                label="Отмена"
                label-classes="px-6"
                class="secondary xs:w-[40%]"
                @click="cancelChangeAvatar"
            />
        </div>
    </form>
</template>
