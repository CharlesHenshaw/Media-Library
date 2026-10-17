<script setup lang="ts">
import FlashMessage from '@/components/FlashMessage.vue';
import LibraryEntryDialog from '@/components/LibraryEntryDialog.vue';
import MediaCard from '@/components/MediaCard.vue';
import PageHeading from '@/components/PageHeading.vue';
import ShelfGrid from '@/components/ShelfGrid.vue';
import TabNav from '@/components/TabNav.vue';
import AppLayout from '@/layouts/AppLayout.vue';
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

const typeTabs = (Object.entries(mediaTypeLabels) as [MediaType, string][]).map(([value, label]) => ({ value, label }));

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

const placeholder = computed(
    () =>
        ({
            film: 'A film title, e.g. Spirited Away',
            tv: 'A show, e.g. Severance',
            game: 'A game, e.g. Outer Wilds',
            book: 'A title or author, e.g. Piranesi',
        })[props.type],
);
</script>

<template>
    <Head title="Search" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-8 px-4 py-8 sm:px-8">
            <PageHeading eyebrow="The catalogue" title="Find something to shelve">
                Search films, TV, games and books, then choose one to add it to your library.
            </PageHeading>

            <div class="flex flex-col gap-5">
                <div class="border-b">
                    <TabNav :items="typeTabs" :active="type" @select="runSearch($event)" />
                </div>

                <form class="relative max-w-2xl" role="search" @submit.prevent="runSearch()">
                    <Search class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-muted-foreground" />
                    <input
                        v-model="query"
                        type="search"
                        :placeholder="placeholder"
                        :aria-label="`Search ${mediaTypeLabels[type].toLowerCase()}`"
                        autofocus
                        class="h-14 w-full rounded-md border border-input bg-card pl-12 pr-12 font-serif text-lg shadow-inner placeholder:text-muted-foreground/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        @input="debouncedSearch"
                    />
                    <Loader2 v-if="loading" class="absolute right-4 top-1/2 size-5 -translate-y-1/2 animate-spin text-muted-foreground" />
                </form>
            </div>

            <p v-if="error" class="rounded-sm border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-destructive">
                {{ error }}
            </p>

            <ShelfGrid v-if="results.length">
                <MediaCard
                    v-for="result in results"
                    :key="result.media.external_id"
                    :media="result.media"
                    :entry="result.entry"
                    @select="select(result)"
                />
            </ShelfGrid>

            <p v-else-if="q.length >= 2 && !error && !loading" class="py-16 text-center font-serif text-xl text-muted-foreground">
                Nothing in the catalogue for “{{ q }}”.
            </p>
        </div>

        <LibraryEntryDialog v-model:open="dialogOpen" :media="selected?.media ?? null" :entry="selected?.entry" />
        <FlashMessage />
    </AppLayout>
</template>
