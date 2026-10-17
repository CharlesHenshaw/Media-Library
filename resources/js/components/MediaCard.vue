<script setup lang="ts">
import StarRating from '@/components/StarRating.vue';
import { cn } from '@/lib/utils';
import { statusLabels, type LibraryEntry, type Media } from '@/types/media';
import { BookOpen, Film, Gamepad2, Tv } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    media: Media;
    entry?: LibraryEntry | null;
}>();

defineEmits<{ (e: 'select'): void }>();

const imageFailed = ref(false);

const placeholderIcon = computed(() => ({ film: Film, tv: Tv, game: Gamepad2, book: BookOpen })[props.media.type]);
</script>

<template>
    <button
        type="button"
        class="group flex flex-col gap-2 rounded-lg text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        @click="$emit('select')"
    >
        <div class="relative aspect-[2/3] w-full overflow-hidden rounded-lg border border-sidebar-border/70 bg-muted dark:border-sidebar-border">
            <img
                v-if="media.cover_url && !imageFailed"
                :src="media.cover_url"
                :alt="`Cover of ${media.title}`"
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                @error="imageFailed = true"
            />
            <div v-else class="flex h-full w-full flex-col items-center justify-center gap-2 p-3 text-center text-muted-foreground">
                <component :is="placeholderIcon" class="size-8" />
                <span class="line-clamp-3 text-xs">{{ media.title }}</span>
            </div>

            <span
                v-if="entry"
                :class="
                    cn(
                        'absolute left-2 top-2 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide shadow',
                        entry.status === 'finished' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white',
                    )
                "
            >
                {{ statusLabels[entry.status] }}
            </span>
        </div>

        <div class="min-w-0 space-y-0.5 px-0.5">
            <p class="truncate text-sm font-medium leading-tight" :title="media.title">{{ media.title }}</p>
            <p class="truncate text-xs text-muted-foreground">
                {{ [media.year, media.creator].filter(Boolean).join(' · ') || ' ' }}
            </p>
            <StarRating v-if="entry?.rating" :model-value="entry.rating" readonly />
        </div>
    </button>
</template>
