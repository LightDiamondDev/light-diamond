<script setup lang="ts">
    import GlobalModals from '@/components/GlobalModals.vue'
    import Header from '@/components/Header.vue'
    import Footer from '@/components/Footer.vue'
    import Toaster from '@/components/elements/Toaster.vue'
    import {usePostCategoryStore} from '@/stores/postCategory'

    usePostCategoryStore().load()
</script>

<template>
    <GlobalModals/>
    <Toaster/>
    <div class="surface-ground">
        <Header/>
        <div class='page-container'>
            <RouterView></RouterView>
        </div>
        <Footer/>
    </div>
</template>

<style/>
