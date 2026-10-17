<script setup lang="ts">
import FlashMessage from '@/components/FlashMessage.vue';
import LibraryEntryDialog from '@/components/LibraryEntryDialog.vue';
import MediaCard from '@/components/MediaCard.vue';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { mediaTypeLabels, type LibraryEntry, type Media, type MediaType } from '@/types/media';
import { Head, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Loader2, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    type: MediaType;
    q: string;
    error: string | null;
    results: { media: Media; entry: LibraryEntry | null }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Search', href: '/search' }];

const query = ref(props.q);
const loading = ref(false);

const runSearch = (type: MediaType = props.type) => {
    router.get(
        route('search'),
        { type, q: query.value || undefined },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['type', 'q', 'error', 'results'],
            onStart: () => (loading.value = true),
            onFinish: () => (loading.value = false),
        },
    );
};

const debouncedSearch = useDebounceFn(() => runSearch(), 400);

const selected = ref<{ media: Media; entry: LibraryEntry | null } | null>(null);
const dialogOpen = ref(false);

const select = (result: { media: Media; entry: LibraryEntry | null }) => {
    selected.value = result;
    dialogOpen.value = true;
};

const placeholder = computed(() => `Search ${mediaTypeLabels[props.type].toLowerCase()}…`);
</script>

<template>
    <Head title="Search" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
                    <button
                        v-for="(label, value) in mediaTypeLabels"
                        :key="value"
                        type="button"
                        :class="
                            cn(
                                'rounded-md px-3.5 py-1.5 text-sm transition-colors',
                                type === value
                                    ? 'bg-white shadow-sm dark:bg-neutral-700 dark:text-neutral-100'
                                    : 'text-neutral-500 hover:bg-neutral-200/60 dark:text-neutral-400 dark:hover:bg-neutral-700/60',
                            )
                        "
                        @click="runSearch(value)"
                    >
                        {{ label }}
                    </button>
                </div>

                <form class="relative flex-1" @submit.prevent="runSearch()">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="query" type="search" :placeholder="placeholder" class="pl-9" autofocus @input="debouncedSearch" />
                    <Loader2 v-if="loading" class="absolute right-3 top-1/2 size-4 -translate-y-1/2 animate-spin text-muted-foreground" />
                </form>
            </div>

            <p v-if="error" class="rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-destructive">
                {{ error }}
            </p>

            <div v-if="results.length" class="grid grid-cols-2 gap-x-4 gap-y-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                <MediaCard
                    v-for="result in results"
                    :key="result.media.external_id"
                    :media="result.media"
                    :entry="result.entry"
                    @select="select(result)"
                />
            </div>

            <p v-else-if="q.length >= 2 && !error && !loading" class="py-16 text-center text-sm text-muted-foreground">
                No {{ mediaTypeLabels[type].toLowerCase() }} found for “{{ q }}”.
            </p>

            <p v-else-if="!q" class="py-16 text-center text-sm text-muted-foreground">
                Search for something you've watched, played or read, then click it to add it to your library.
            </p>
        </div>

        <LibraryEntryDialog v-model:open="dialogOpen" :media="selected?.media ?? null" :entry="selected?.entry" />
        <FlashMessage />
    </AppLayout>
</template>
