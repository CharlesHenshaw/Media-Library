<script setup lang="ts">
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { Stamp } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const page = usePage<SharedData>();
const message = ref<string | null>(null);
let timer: ReturnType<typeof setTimeout> | undefined;

watch(
    () => page.props.flash?.success,
    (value) => {
        if (!value) return;
        message.value = value;
        clearTimeout(timer);
        timer = setTimeout(() => (message.value = null), 3000);
    },
    { immediate: true },
);
</script>

<template>
    <Transition
        enter-from-class="translate-y-2 opacity-0"
        enter-active-class="transition duration-200"
        leave-to-class="translate-y-2 opacity-0"
        leave-active-class="transition duration-200"
    >
        <div
            v-if="message"
            role="status"
            class="fixed bottom-5 right-5 z-50 flex max-w-sm items-center gap-3 rounded-sm border bg-card px-4 py-3 font-serif text-sm shadow-[0_10px_30px_-8px_hsl(24_40%_10%/0.35)]"
        >
            <Stamp class="size-4 shrink-0 text-brass" />
            {{ message }}
        </div>
    </Transition>
</template>
