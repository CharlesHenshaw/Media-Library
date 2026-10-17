<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import StarRating from '@/components/StarRating.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { statusLabels, type EntryStatus, type LibraryEntry, type Media } from '@/types/media';
import { router, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

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
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent v-if="media" class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ entry ? 'Edit' : 'Add to library' }}</DialogTitle>
                <DialogDescription>
                    {{ media.title }}<span v-if="media.year"> ({{ media.year }})</span>
                </DialogDescription>
            </DialogHeader>

            <form id="library-entry-form" class="flex gap-4" @submit.prevent="submit">
                <img v-if="media.cover_url" :src="media.cover_url" alt="" class="hidden aspect-[2/3] w-24 shrink-0 rounded-md object-cover sm:block" />

                <div class="flex flex-1 flex-col gap-4">
                    <div class="grid gap-2">
                        <Label>Status</Label>
                        <div class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
                            <button
                                v-for="status in ['finished', 'dnf'] as const"
                                :key="status"
                                type="button"
                                :aria-pressed="form.status === status"
                                :class="
                                    cn(
                                        'flex-1 rounded-md px-3 py-1.5 text-sm transition-colors',
                                        form.status === status
                                            ? 'bg-white shadow-sm dark:bg-neutral-700 dark:text-neutral-100'
                                            : 'text-neutral-500 hover:bg-neutral-200/60 dark:text-neutral-400 dark:hover:bg-neutral-700/60',
                                    )
                                "
                                @click="form.status = status"
                            >
                                {{ statusLabels[status] }}
                            </button>
                        </div>
                        <InputError :message="form.errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Rating <span class="font-normal text-muted-foreground">(optional)</span></Label>
                        <StarRating v-model="form.rating" size="lg" />
                        <InputError :message="form.errors.rating" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="finished_on">Date <span class="font-normal text-muted-foreground">(optional)</span></Label>
                        <Input id="finished_on" v-model="form.finished_on" type="date" :max="today" />
                        <InputError :message="form.errors.finished_on" />
                    </div>

                    <InputError :message="(form.errors as Record<string, string>).external_id" />
                </div>
            </form>

            <DialogFooter class="gap-2 sm:justify-between">
                <Button v-if="entry" type="button" variant="ghost" class="text-destructive hover:text-destructive" @click="remove">Remove</Button>
                <span v-else />
                <Button type="submit" form="library-entry-form" :disabled="form.processing">
                    {{ entry ? 'Save' : 'Add' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
