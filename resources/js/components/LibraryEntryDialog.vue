<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import StarRating from '@/components/StarRating.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { mediaTypeLabels, statusLabels, type EntryStatus, type LibraryEntry, type Media } from '@/types/media';
import { router, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps<{
    open: boolean;
    media: Media | null;
    entry?: LibraryEntry | null;
}>();

const emit = defineEmits<{ (e: 'update:open', value: boolean): void }>();

const form = useForm({
    status: 'finished' as EntryStatus,
    rating: null as number | null,
    finished_on: '' as string,
});

// Reset the form to the selected item's current values each time the dialog opens.
watch(
    () => props.open,
    (open) => {
        if (!open) return;
        form.clearErrors();
        form.status = props.entry?.status ?? 'finished';
        form.rating = props.entry?.rating ?? null;
        form.finished_on = props.entry?.finished_on ?? '';
    },
);

const close = () => emit('update:open', false);

const submit = () => {
    const options = { preserveScroll: true, preserveState: true, onSuccess: close };
    const fields = { status: form.status, rating: form.rating, finished_on: form.finished_on || null };

    if (props.entry) {
        form.transform(() => fields).patch(route('library.update', props.entry.id), options);
    } else if (props.media) {
        const { type, external_id } = props.media;
        form.transform(() => ({ ...fields, type, external_id })).post(route('library.store'), options);
    }
};

const remove = () => {
    if (!props.entry) return;
    router.delete(route('library.destroy', props.entry.id), { preserveScroll: true, onSuccess: close });
};

const today = new Date().toISOString().slice(0, 10);

// Singular form of the type label for the card's classification line, e.g. "Films" -> "Film".
const classification = computed(() => (props.media ? mediaTypeLabels[props.media.type].replace(/s$/, '') : ''));

const dateLabel = computed(() => (form.status === 'dnf' ? 'Set aside on' : 'Finished on'));

const stampClass = (status: EntryStatus) =>
    cn(
        'flex-1 rounded-sm border-2 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-card',
        form.status !== status && 'border-dashed border-border text-muted-foreground hover:border-muted-foreground hover:text-foreground',
        form.status === status && status === 'finished' && '-rotate-2 border-[hsl(158_45%_32%)] bg-[hsl(158_45%_32%/0.08)] text-[hsl(158_45%_32%)] dark:border-[hsl(158_40%_50%)] dark:text-[hsl(158_40%_60%)]',
        form.status === status && status === 'dnf' && 'rotate-2 border-oxblood bg-oxblood/5 text-oxblood',
    );
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent v-if="media" class="catalogue-card gap-0 overflow-hidden rounded-sm bg-card p-0 shadow-[0_24px_60px_-12px_hsl(24_40%_8%/0.55)] sm:max-w-xl sm:rounded-sm">
            <DialogHeader class="h-[4.25rem] justify-center space-y-0.5 px-6 pr-12 text-left">
                <p class="catalogue-label">
                    {{ classification }}<template v-if="media.year"> · {{ media.year }}</template>
                    <template v-if="entry"> · In your library</template>
                </p>
                <DialogTitle class="truncate font-serif text-xl font-medium leading-tight">{{ media.title }}</DialogTitle>
                <DialogDescription v-if="media.creator" class="sr-only">by {{ media.creator }}</DialogDescription>
            </DialogHeader>

            <form id="library-entry-form" class="flex gap-6 px-6 pb-2 pt-6" @submit.prevent="submit">
                <div class="hidden w-28 shrink-0 sm:block">
                    <div class="relative aspect-[2/3] overflow-hidden rounded-[3px] bg-muted shadow-[0_8px_16px_-6px_hsl(24_40%_10%/0.5)]">
                        <img v-if="media.cover_url" :src="media.cover_url" alt="" class="h-full w-full object-cover" />
                        <div class="cover-sheen absolute inset-0" />
                    </div>
                    <p v-if="media.creator" class="mt-2 font-serif text-xs italic text-muted-foreground">{{ media.creator }}</p>
                </div>

                <div class="flex flex-1 flex-col gap-5">
                    <fieldset class="grid gap-2">
                        <legend class="catalogue-label mb-2">Status</legend>
                        <div class="flex gap-3">
                            <button
                                v-for="status in ['finished', 'dnf'] as const"
                                :key="status"
                                type="button"
                                :aria-pressed="form.status === status"
                                :class="stampClass(status)"
                                @click="form.status = status"
                            >
                                {{ status === 'dnf' ? 'Did not finish' : statusLabels[status] }}
                            </button>
                        </div>
                        <InputError :message="form.errors.status" />
                    </fieldset>

                    <fieldset class="grid gap-2">
                        <legend class="catalogue-label mb-2">Your rating</legend>
                        <StarRating v-model="form.rating" size="lg" />
                        <InputError :message="form.errors.rating" />
                    </fieldset>

                    <div class="grid gap-2">
                        <label for="finished_on" class="catalogue-label">{{ dateLabel }} <span class="normal-case tracking-normal">(optional)</span></label>
                        <Input id="finished_on" v-model="form.finished_on" type="date" :max="today" class="w-auto bg-card/80" />
                        <InputError :message="form.errors.finished_on" />
                    </div>

                    <InputError :message="(form.errors as Record<string, string>).external_id" />
                </div>
            </form>

            <DialogFooter class="gap-2 px-6 pb-5 pt-4 sm:justify-between">
                <Button v-if="entry" type="button" variant="ghost" class="text-destructive hover:bg-destructive/10 hover:text-destructive" @click="remove">
                    Remove from library
                </Button>
                <span v-else />
                <Button type="submit" form="library-entry-form" :disabled="form.processing">
                    {{ entry ? 'Save changes' : 'Add to shelves' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
