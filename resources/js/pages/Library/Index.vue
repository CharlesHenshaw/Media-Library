<script setup lang="ts">
import FlashMessage from '@/components/FlashMessage.vue';
import LibraryEntryDialog from '@/components/LibraryEntryDialog.vue';
import MediaCard from '@/components/MediaCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { mediaTypeLabels, statusLabels, type EntryStatus, type LibraryEntry, type MediaType } from '@/types/media';
import { Head, Link, router } from '@inertiajs/vue3';
import { Library, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Sort = 'recent' | 'rating' | 'title';

const props = defineProps<{
    entries: {
        data: LibraryEntry[];
        links: { first: string | null; last: string | null; prev: string | null; next: string | null };
        meta: { current_page: number; last_page: number; total: number };
    };
    counts: Partial<Record<MediaType, number>>;
    filters: { type: MediaType | null; status: EntryStatus | null; sort: Sort };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Library', href: '/library' }];

const totalCount = computed(() => Object.values(props.counts).reduce((sum, n) => sum + (n ?? 0), 0));

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

const chipClass = (active: boolean) =>
    cn(
        'rounded-full border px-3 py-1 text-sm transition-colors',
        active ? 'border-foreground bg-foreground text-background' : 'border-border text-muted-foreground hover:border-foreground/40 hover:text-foreground',
    );
</script>

<template>
    <Head title="Library" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap gap-2">
                    <button type="button" :class="chipClass(!filters.type)" @click="applyFilters({ type: null })">All · {{ totalCount }}</button>
                    <button
                        v-for="(label, value) in mediaTypeLabels"
                        :key="value"
                        type="button"
                        :class="chipClass(filters.type === value)"
                        @click="applyFilters({ type: value })"
                    >
                        {{ label }} · {{ counts[value] ?? 0 }}
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select
                        :value="filters.status ?? ''"
                        aria-label="Filter by status"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="applyFilters({ status: (($event.target as HTMLSelectElement).value || null) as EntryStatus | null })"
                    >
                        <option value="">Any status</option>
                        <option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <select
                        :value="filters.sort"
                        aria-label="Sort by"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm"
                        @change="applyFilters({ sort: ($event.target as HTMLSelectElement).value as Sort })"
                    >
                        <option value="recent">Recently updated</option>
                        <option value="rating">Highest rated</option>
                        <option value="title">Title A–Z</option>
                    </select>
                    <Button as-child>
                        <Link :href="route('search')"><Plus /> Add</Link>
                    </Button>
                </div>
            </div>

            <div v-if="entries.data.length" class="grid grid-cols-2 gap-x-4 gap-y-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                <MediaCard v-for="entry in entries.data" :key="entry.id" :media="entry.media" :entry="entry" @select="select(entry)" />
            </div>

            <div v-else class="flex flex-col items-center gap-3 py-24 text-center text-muted-foreground">
                <Library class="size-10" />
                <p v-if="totalCount === 0" class="text-sm">Your library is empty. Search for something you've finished (or given up on) to get started.</p>
                <p v-else class="text-sm">Nothing matches these filters.</p>
                <Button v-if="totalCount === 0" as-child variant="outline">
                    <Link :href="route('search')">Search media</Link>
                </Button>
            </div>

            <nav v-if="entries.meta.last_page > 1" class="flex items-center justify-center gap-4 text-sm">
                <Button variant="outline" size="sm" :disabled="!entries.links.prev" as-child>
                    <Link v-if="entries.links.prev" :href="entries.links.prev" preserve-scroll>Previous</Link>
                    <span v-else>Previous</span>
                </Button>
                <span class="text-muted-foreground">Page {{ entries.meta.current_page }} of {{ entries.meta.last_page }}</span>
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
