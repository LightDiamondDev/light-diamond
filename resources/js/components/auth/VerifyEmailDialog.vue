<script setup lang="ts">
import Dialog from '@/components/elements/Dialog.vue'
import Button from '@/components/elements/Button.vue'
import {useGlobalModalStore} from '@/stores/global-modal'

const globalModalStore = useGlobalModalStore()

const isVisible = defineModel<Boolean>('visible', {required: true})

</script>

<template>
    <Dialog
        v-model:visible="isVisible"
        class="verify-dialog"
        form-container-classes="p-[4rem] overflow-hidden"
        title="Неподтверждённый E-Mail"
    >
        <form action="" class="login flex flex-col items-center p-2" name="login">
            <fieldset class="flex flex-col items-center">
                <span class="subtitle text-[14px] text-center m-2">
                    Чтобы совершить это действие, пожалуйста, подтвердите Ваш E-Mail.
                </span>
            </fieldset>

            <div class="group flex flex-col items-center w-full">
                <div class="mob parrot flex justify-center items-center mb-4">
                    <div class="animation-dancing-multicoloured-parrot"></div>
                </div>

                <Button
                    as="RouterLink"
                    class="max-w-[300px] w-full"
                    icon="icon-bestiary min-w-[2rem]"
                    label="Подтвердить"
                    @click="globalModalStore.notVerifiedEmailModal = false"
                    :to="{ name: 'settings.security' }"
                />
            </div>

        </form>
    </Dialog>
</template>

<style>
.verify-dialog {
    box-shadow: 0 0 10px 10px rgba(0, 0, 0, .5);
}

.verify-dialog .interface {
    position: relative;
    transition: .5s;
    max-width: 450px;
}

.verify-dialog form fieldset {
    width: 85%;
}

.verify-dialog form fieldset * {
    color: var(--primary-text-color);
}
</style>
