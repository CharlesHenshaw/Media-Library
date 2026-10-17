<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground shadow-inner">
        <AppLogoIcon class="size-5" />
    </div>
    <div class="ml-1 grid flex-1 text-left">
        <span class="truncate font-serif text-base font-medium leading-none">Media Library</span>
    </div>
</template>
