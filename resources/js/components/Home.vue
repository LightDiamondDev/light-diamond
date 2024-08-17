<script setup lang="ts">
import {ref} from 'vue'
import Select from '@/components/elements/Select.vue'

const selectedCategory = ref()

const categories = ref([
    { name: 'Новости', code: 'news', icon: 'icon-news' },
    { name: 'Ресурс-Паки', code: 'resource-packs', icon: 'icon-axolotl-bucket' },
    { name: 'Аддоны', code: 'addons', icon: 'icon-brilliant' },
    { name: 'Скины', code: 'skins', icon: 'icon-skin' },
    { name: 'Карты', code: 'maps', icon: 'icon-map' },
    { name: 'Статьи', code: 'articles', icon: 'icon-script' }
])
</script>

<template>
    <img alt="" src="/images/users/content/ld.png">
    <!--
    <img alt="" src="/images/users/content/ld.png">
    <img alt="" src="/images/users/content/ld.png">
    <p class="text-sm">
        sssssssssss fsfjsdkfjsd kfjsdf jsdkf
        sdkfdskfjsdfjsdfd sfds fkdskf sdfjkdsf
        jsdklfjsdkf sdjfsdf jkds f s fdsf jdsl
        fjdsl fjsdlfjsdfjsdfjdofe wrjop  twrif
        ro fgogifogifdogfdo gjopquwieriq0r329ro
        kewfldaskf[pfoewre woipweriferpo[ifgreo
        tfigr[tiw]rt[ew rirtioprwtiorptiropt9i
    </p>
    -->
    <p class="text-sm">
        sssssssssss fsfjsdkfjsd kfjsdf jsdkf
        sdkfdskfjsdfjsdfd sfds fkdskf sdfjkdsf
        jsdklfjsdkf sdjfsdf jkds f s fdsf jdsl
        fjdsl fjsdlfjsdfjsdfjdofe wrjop  twrif
        ro fgogifogifdogfdo gjopquwieriq0r329ro
        kewfldaskf[pfoewre woipweriferpo[ifgreo
        tfigr[tiw]rt[ew rirtioprwtiorptiropt9i
    </p>



    <Select
        class="w-[300px]"
        :options="categories"
        option-label-key="name"
        option-icon-key="icon"
        placeholder="Выберите Категорию"
    >
        <template #option-icon/>
    </Select>



    <p class="text-sm">
        sssssssssss fsfjsdkfjsd kfjsdf jsdkf
        sdkfdskfjsdfjsdfd sfds fkdskf sdfjkdsf
        jsdklfjsdkf sdjfsdf jkds f s fdsf jdsl
        fjdsl fjsdlfjsdfjsdfjdofe wrjop  twrif
        ro fgogifogifdogfdo gjopquwieriq0r329ro
        kewfldaskf[pfoewre woipweriferpo[ifgreo
        tfigr[tiw]rt[ew rirtioprwtiorptiropt9i
    </p>
    <p class="text-sm">
        sssssssssss fsfjsdkfjsd kfjsdf jsdkf
        sdkfdskfjsdfjsdfd sfds fkdskf sdfjkdsf
        jsdklfjsdkf sdjfsdf jkds f s fdsf jdsl
        fjdsl fjsdlfjsdfjsdfjdofe wrjop  twrif
        ro fgogifogifdogfdo gjopquwieriq0r329ro
        kewfldaskf[pfoewre woipweriferpo[ifgreo
        tfigr[tiw]rt[ew rirtioprwtiorptiropt9i
    </p>
</template>

<style scoped>

</style>
