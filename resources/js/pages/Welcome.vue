<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { BookMarked, Search, Star } from 'lucide-vue-next';

// Decorative spines for the hero bookshelf: height (rem), width (rem), cloth colour, optional spine title.
const spines: { h: number; w: number; color: string; title?: string; lean?: boolean }[] = [
    { h: 11, w: 2.2, color: 'hsl(158 38% 24%)' },
    { h: 12.5, w: 2.8, color: 'hsl(356 50% 32%)', title: 'Films' },
    { h: 10, w: 1.8, color: 'hsl(38 55% 42%)' },
    { h: 13, w: 3, color: 'hsl(215 35% 26%)', title: 'Books' },
    { h: 11.5, w: 2, color: 'hsl(24 35% 28%)' },
    { h: 12, w: 2.6, color: 'hsl(158 30% 32%)', title: 'Games' },
    { h: 9.5, w: 1.6, color: 'hsl(356 40% 42%)' },
    { h: 12.8, w: 2.9, color: 'hsl(30 25% 18%)', title: 'Television' },
    { h: 10.5, w: 2.2, color: 'hsl(38 50% 50%)' },
    { h: 11, w: 2.4, color: 'hsl(215 25% 36%)', lean: true },
];

const features = [
    { icon: Search, title: 'Search four catalogues', body: 'Films and TV from TMDB, games from RAWG, and books from Open Library, with their covers.' },
    { icon: BookMarked, title: 'Finished, or set aside', body: 'Mark each one Finished or Did not finish. Abandoned books count too.' },
    { icon: Star, title: 'Rate out of five', body: 'Give a star rating, note when you finished, and browse it all on your shelves.' },
];
</script>

<template>
    <Head title="Welcome" />

    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <header class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-6">
            <Link :href="route('home')" class="flex items-center gap-2.5">
                <span class="flex size-9 items-center justify-center rounded-md bg-primary text-primary-foreground">
                    <AppLogoIcon class="size-5" />
                </span>
                <span class="font-serif text-lg font-medium">Media Library</span>
            </Link>
            <nav class="flex items-center gap-2">
                <Button v-if="$page.props.auth.user" as-child>
                    <Link :href="route('library.index')">Go to your library</Link>
                </Button>
                <template v-else>
                    <Button variant="ghost" as-child>
                        <Link :href="route('login')">Log in</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="route('register')">Get a library card</Link>
                    </Button>
                </template>
            </nav>
        </header>

        <main class="mx-auto flex w-full max-w-6xl flex-1 flex-col items-center px-6 pb-20 pt-10 text-center sm:pt-16">
            <p class="catalogue-label">A catalogue of everything you've watched, played and read</p>
            <h1 class="mt-5 max-w-3xl font-serif text-4xl font-medium leading-[1.1] tracking-tight sm:text-6xl">
                Every story you've finished, <em class="font-normal text-primary">and the ones you didn't.</em>
            </h1>
            <p class="mt-6 max-w-xl text-base text-muted-foreground sm:text-lg">
                Keep one library for your films, TV, games and books. Shelve what you finish, admit what you abandon, and rate the lot.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <Button size="lg" as-child>
                    <Link :href="$page.props.auth.user ? route('library.index') : route('register')">
                        {{ $page.props.auth.user ? 'Open your library' : 'Start your library' }}
                    </Link>
                </Button>
                <Button v-if="!$page.props.auth.user" size="lg" variant="outline" as-child>
                    <Link :href="route('login')">I already have a card</Link>
                </Button>
            </div>

            <!-- Decorative bookshelf -->
            <div class="mt-16 w-full max-w-2xl" aria-hidden="true">
                <div class="flex items-end justify-center gap-1 px-6">
                    <div
                        v-for="(spine, i) in spines"
                        :key="i"
                        class="relative flex shrink-0 justify-center overflow-hidden rounded-t-[3px] shadow-[inset_-3px_0_6px_hsl(0_0%_0%/0.25),inset_2px_0_3px_hsl(0_0%_100%/0.12)]"
                        :class="spine.lean && 'ml-3 origin-bottom-left rotate-[9deg]'"
                        :style="{ height: `${spine.h}rem`, width: `${spine.w}rem`, backgroundColor: spine.color }"
                    >
                        <span class="absolute inset-x-0 top-3 h-px bg-[hsl(40_60%_70%/0.6)]" />
                        <span class="absolute inset-x-0 top-4 h-px bg-[hsl(40_60%_70%/0.6)]" />
                        <span
                            v-if="spine.title"
                            class="mt-8 font-serif text-[0.7rem] uppercase tracking-[0.2em] text-[hsl(40_60%_80%)] [writing-mode:vertical-rl]"
                        >
                            {{ spine.title }}
                        </span>
                        <span class="absolute inset-x-0 bottom-3 h-px bg-[hsl(40_60%_70%/0.6)]" />
                    </div>
                </div>
                <div class="shelf-plank" />
            </div>

            <section class="mt-20 grid w-full gap-10 text-left sm:grid-cols-3">
                <div v-for="feature in features" :key="feature.title" class="space-y-2 border-t pt-6">
                    <component :is="feature.icon" class="size-5 text-brass" />
                    <h2 class="font-serif text-xl font-medium">{{ feature.title }}</h2>
                    <p class="text-sm leading-relaxed text-muted-foreground">{{ feature.body }}</p>
                </div>
            </section>
        </main>
    </div>
</template>
