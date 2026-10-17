<script setup lang="ts">
import FlashMessage from '@/components/FlashMessage.vue';
import LibraryEntryDialog from '@/components/LibraryEntryDialog.vue';
import MediaCard from '@/components/MediaCard.vue';
import PageHeading from '@/components/PageHeading.vue';
import ShelfGrid from '@/components/ShelfGrid.vue';
import TabNav from '@/components/TabNav.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { mediaTypeLabels, type EntryStatus, type LibraryEntry, type MediaType } from '@/types/media';
import { Head, Link, router } from '@inertiajs/vue3';
import { LibraryBig, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Sort = 'recent' | 'rating' | 'title';

const props = defineProps<{
    entries: {
        data: LibraryEntry[];
        links: { first: string | null; last: string | null; prev: string | null; next: string | null };
        meta: { current_page: number; last_page: number; total: number };
    };
    counts: Partial<Record<MediaType, number>>;
    statusCounts: Partial<Record<EntryStatus, number>>;
    filters: { type: MediaType | null; status: EntryStatus | null; sort: Sort };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Library', href: '/library' }];

const totalCount = computed(() => Object.values(props.counts).reduce((sum, n) => sum + (n ?? 0), 0));

const typeTabs = computed(() => [
    { value: null, label: 'All shelves', count: totalCount.value },
    ...(Object.entries(mediaTypeLabels) as [MediaType, string][]).map(([value, label]) => ({ value, label, count: props.counts[value] ?? 0 })),
]);

const applyFilters = (changes: Partial<typeof props.filters>) => {
    const filters = { ...props.filters, ...changes };
    router.get(
        route('library.index'),
        {
            type: filters.type ?? undefined,
            status: filters.status ?? undefined,
            sort: filters.sort === 'recent' ? undefined : filters.sort,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const selected = ref<LibraryEntry | null>(null);
const dialogOpen = ref(false);

const select = (entry: LibraryEntry) => {
    selected.value = entry;
    dialogOpen.value = true;
};

const selectClass = 'h-9 rounded-md border border-input bg-card px-3 text-sm text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-ring';
</script>

<template>
    <Head title="Library" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-8 px-4 py-8 sm:px-8">
            <PageHeading eyebrow="Your collection" title="The Library">
                <template v-if="totalCount">
                    {{ totalCount }} {{ totalCount === 1 ? 'work' : 'works' }} catalogued · {{ statusCounts.finished ?? 0 }} finished ·
                    {{ statusCounts.dnf ?? 0 }} set aside
                </template>
                <template v-else>Nothing catalogued yet.</template>

                <template #actions>
                    <Button as-child>
                        <Link :href="route('search')"><Plus /> Add to library</Link>
                    </Button>
                </template>
            </PageHeading>

            <div class="flex flex-col gap-4 border-b lg:flex-row lg:items-end lg:justify-between">
                <TabNav :items="typeTabs" :active="filters.type" @select="applyFilters({ type: $event })" />

                <div class="flex flex-wrap items-center gap-2 pb-3">
                    <select
                        :value="filters.status ?? ''"
                        aria-label="Filter by status"
                        :class="selectClass"
                        @change="applyFilters({ status: (($event.target as HTMLSelectElement).value || null) as EntryStatus | null })"
                    >
                        <option value="">Any status</option>
                        <option value="finished">Finished</option>
                        <option value="dnf">Did not finish</option>
                    </select>
                    <select
                        :value="filters.sort"
                        aria-label="Sort by"
                        :class="selectClass"
                        @change="applyFilters({ sort: ($event.target as HTMLSelectElement).value as Sort })"
                    >
                        <option value="recent">Recently shelved</option>
                        <option value="rating">Highest rated</option>
                        <option value="title">Title, A–Z</option>
                    </select>
                </div>
            </div>

            <ShelfGrid v-if="entries.data.length">
                <MediaCard v-for="entry in entries.data" :key="entry.id" :media="entry.media" :entry="entry" @select="select(entry)" />
            </ShelfGrid>

            <div v-else class="flex flex-col items-center gap-4 py-24 text-center">
                <LibraryBig class="size-10 text-brass" stroke-width="1.5" />
                <template v-if="totalCount === 0">
                    <p class="font-serif text-2xl">The shelves are bare.</p>
                    <p class="max-w-sm text-sm text-muted-foreground">
                        Search the catalogue for something you've finished, or something you gave up on, and it'll be shelved here.
                    </p>
                    <Button as-child variant="outline" class="mt-2">
                        <Link :href="route('search')">Search the catalogue</Link>
                    </Button>
                </template>
                <p v-else class="font-serif text-xl text-muted-foreground">Nothing on this shelf matches.</p>
            </div>

            <nav v-if="entries.meta.last_page > 1" class="flex items-center justify-center gap-4 text-sm">
                <Button variant="outline" size="sm" :disabled="!entries.links.prev" as-child>
                    <Link v-if="entries.links.prev" :href="entries.links.prev" preserve-scroll>Previous</Link>
                    <span v-else>Previous</span>
                </Button>
                <span class="font-serif italic text-muted-foreground">Page {{ entries.meta.current_page }} of {{ entries.meta.last_page }}</span>
                <Button variant="outline" size="sm" :disabled="!entries.links.next" as-child>
                    <Link v-if="entries.links.next" :href="entries.links.next" preserve-scroll>Next</Link>
                    <span v-else>Next</span>
                </Button>
            </nav>
        </div>

        <LibraryEntryDialog v-model:open="dialogOpen" :media="selected?.media ?? null" :entry="selected" />
        <FlashMessage />
    </AppLayout>
</template>
