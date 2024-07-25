<script setup lang="ts">
import Button from '@/components/elements/Button.vue'
import useDesignManager from '@/design-manager'
const designManager = useDesignManager()
</script>

<template>
    <div class="search-form inner flex justify-center">
        <form action="" class="flex flex-col items-center">
            <label class="flex items-center" for="search-input">
                <button class="flex justify-center items-center" id="search-button" type="button">
                    <span class="icon icon-magnifier"></span>
                </button>
                <input class="flex text-[0.9rem]" id="search-input" placeholder="Поиск..." type="text">
                <button class="flex justify-center items-center" @click="isVisible = false" type="button">
                    <span class="icon icon-cross"></span>
                </button>
            </label>
            <fieldset class="flex flex-col"></fieldset>
        </form>
    </div>
</template>

<style>
.search-dialog .dialog-form-container {
    max-width: 1232px;
    transition: .5s;
    width: 100%;
}
.search-dialog .dialog-form-container .interface {
    width: 100%;
}
.search-form {
    width: 100%;
}
.search-form form {
    width: 100%;
}
.search-form form label {
    width: calc(100% - 20px);
    pointer-events: visible;
    max-width: 768px;
    margin: 10px;
}
.header-wrap .search-button,
.search-form form label button {
    height: 48px;
    width: 48px;
}
.search-form form label button { user-select: none; }
.search-form form label button .icon {
    height: 32px;
    width: 32px;
}
.search-form form label input {
    color: var(--primary-text-color);
    height: 100%;
    width: 100%;
    padding: 0;
}
.search-form form fieldset {
    min-height: 72px;
    width: 100%;
}
</style>
