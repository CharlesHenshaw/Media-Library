<script setup lang="ts">
import StarRating from '@/components/StarRating.vue';
import { cn } from '@/lib/utils';
import { statusLabels, type LibraryEntry, type Media } from '@/types/media';
import { BookOpen, Check, Film, Gamepad2, Tv, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    media: Media;
    entry?: LibraryEntry | null;
}>();

defineEmits<{ (e: 'select'): void }>();

const imageFailed = ref(false);
const imageLoaded = ref(false);

const placeholderIcon = computed(() => ({ film: Film, tv: Tv, game: Gamepad2, book: BookOpen })[props.media.type]);
</script>

<template>
    <button type="button" class="group flex flex-col text-left focus-visible:outline-none" @click="$emit('select')">
        <!-- The cover, standing on the shelf -->
        <div class="px-1.5 sm:px-2">
            <div
                class="relative aspect-[2/3] w-full overflow-hidden rounded-[3px] bg-muted shadow-[0_2px_4px_hsl(24_40%_10%/0.25),0_10px_18px_-6px_hsl(24_40%_10%/0.4)] transition duration-300 ease-out group-hover:-translate-y-1.5 group-hover:shadow-[0_4px_8px_hsl(24_40%_10%/0.25),0_18px_26px_-8px_hsl(24_40%_10%/0.5)] group-focus-visible:-translate-y-1.5 group-focus-visible:ring-2 group-focus-visible:ring-ring group-focus-visible:ring-offset-2 group-focus-visible:ring-offset-background"
            >
                <!-- Plain cloth binding, shown while the cover loads or when there's no cover art -->
                <div
                    class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-gradient-to-br from-primary to-primary/80 p-4 text-center text-primary-foreground"
                >
                    <component :is="placeholderIcon" class="size-6 opacity-70" />
                    <span class="line-clamp-4 font-serif text-sm leading-snug">{{ media.title }}</span>
                    <span class="h-px w-8 bg-current opacity-40" />
                </div>
                <img
                    v-if="media.cover_url && !imageFailed"
                    :src="media.cover_url"
                    :alt="`Cover of ${media.title}`"
                    loading="lazy"
                    :class="cn('relative h-full w-full object-cover transition-opacity duration-500', imageLoaded ? 'opacity-100' : 'opacity-0')"
                    @load="imageLoaded = true"
                    @error="imageFailed = true"
                />
                <div class="cover-sheen pointer-events-none absolute inset-0" />

                <!-- Bookmark ribbon -->
                <span
                    v-if="entry"
                    :title="statusLabels[entry.status]"
                    :class="
                        cn(
                            'absolute right-2.5 top-0 flex h-9 w-6 items-start justify-center pt-1.5 text-white shadow-md [clip-path:polygon(0_0,100%_0,100%_100%,50%_78%,0_100%)]',
                            entry.status === 'finished' ? 'bg-[hsl(158_45%_28%)]' : 'bg-oxblood',
                        )
                    "
                >
                    <Check v-if="entry.status === 'finished'" class="size-3.5" stroke-width="3" />
                    <X v-else class="size-3.5" stroke-width="3" />
                    <span class="sr-only">{{ statusLabels[entry.status] }}</span>
                </span>
            </div>
        </div>

        <div class="shelf-plank relative z-10" />

        <!-- Shelf label -->
        <div class="min-w-0 space-y-1 px-1.5 pt-3 sm:px-2">
            <p class="line-clamp-2 font-serif text-[0.95rem] font-medium leading-snug" :title="media.title">{{ media.title }}</p>
            <p class="truncate text-xs text-muted-foreground">
                <span v-if="media.year" class="tabular-nums">{{ media.year }}</span>
                <span v-if="media.year && media.creator"> · </span>
                <span v-if="media.creator" class="italic">{{ media.creator }}</span>
            </p>
            <StarRating v-if="entry?.rating" :model-value="entry.rating" readonly />
            <p v-else-if="entry?.status === 'dnf'" class="catalogue-label !text-oxblood">Did not finish</p>
        </div>
    </button>
</template>
