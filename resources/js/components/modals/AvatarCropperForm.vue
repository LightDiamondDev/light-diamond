<script setup lang="ts">
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css';
import Button from '@/components/elements/Button.vue'

defineProps({
    imageSrc: {
        type: String,
        required: true
    }
})

function change({ coordinates, canvas }) {
    console.log(coordinates, canvas);
}
</script>

<template>
    <form action="" class="avatar-cropper-form flex flex-col items-center" name="avatar-cropper">
        <p class="subtitle md:text-[14px] text-[12px] text-center mb-4 px-4">
            Выбранная область изображения будет отображаться в качестве Вашего Аватара.
        </p>
        <Cropper
            :src="imageSrc"
            :stencil-props="{ aspectRatio: 1 }"
            @change="change"
        />
        <div class="flex justify-center items-end w-[85%] gap-2 my-2">
            <Button
                button-type="submit"
                label="Подтвердить"
                label-classes="px-6"
                class="success xs:w-[40%]"
            />
            <Button
                button-type="button"
                label="Отмена"
                label-classes="px-6"
                class="secondary xs:w-[40%]"
            />
        </div>
    </form>
</template>
