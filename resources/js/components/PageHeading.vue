<script setup lang="ts">
defineProps<{
    eyebrow?: string;
    title: string;
}>();
</script>

<template>
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="space-y-1.5">
            <p v-if="eyebrow" class="catalogue-label">{{ eyebrow }}</p>
            <h1 class="font-serif text-3xl font-medium tracking-tight sm:text-4xl">{{ title }}</h1>
            <p v-if="$slots.default" class="text-sm text-muted-foreground"><slot /></p>
        </div>
        <slot name="actions" />
    </header>
</template>
