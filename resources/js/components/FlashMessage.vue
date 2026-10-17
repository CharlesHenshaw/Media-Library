<script setup lang="ts">
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2 } from 'lucide-vue-next';
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
            class="fixed bottom-4 right-4 z-50 flex items-center gap-2 rounded-lg border bg-background px-4 py-3 text-sm shadow-lg"
        >
            <CheckCircle2 class="size-4 text-emerald-600" />
            {{ message }}
        </div>
    </Transition>
</template>
