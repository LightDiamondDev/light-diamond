<script setup lang="ts">
    import GlobalModals from '@/components/GlobalModals.vue'
    import Header from '@/components/Header.vue'
    import {usePostCategoryStore} from '@/stores/postCategory'
    import {ref} from 'vue'
    import Footer from '@/components/Footer.vue'

    usePostCategoryStore().load()
</script>

<template>
    <GlobalModals/>
    <div class="surface-ground">
        <Header/>
        <div class='page-container'>
            <RouterView></RouterView>
        </div>
        <Footer/>
    </div>
</template>

<style/>
