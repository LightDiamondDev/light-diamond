<script setup lang="ts">
import Dialog from '@/components/elements/Dialog.vue'
import HCaptcha from '@hcaptcha/vue3-hcaptcha'
import {getHCaptchaSiteKey} from '@/helpers'
import {useGlobalModalStore} from '@/stores/global-modal'

const isVisible = defineModel<Boolean>('visible', {required: true})

function onVerify(token: string) {
    isVisible.value = false
    useGlobalModalStore().captchaModalAction(token)
}
</script>

<template>
    <Dialog
        v-model:visible="isVisible"
        form-container-classes="overflow-hidden w-[380px]"
        title="Вы человек?"
    >
        <HCaptcha
            class="ml-9 mb-4"
            :sitekey="getHCaptchaSiteKey()"
            @verify="onVerify"
        />
    </Dialog>
</template>

<style>

</style>
