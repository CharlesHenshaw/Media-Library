<script setup lang="ts" generic="T extends string | null">
import { cn } from '@/lib/utils';

defineProps<{
    items: { value: T; label: string; count?: number }[];
    active: T;
}>();

defineEmits<{ (e: 'select', value: T): void }>();
</script>

<template>
    <nav class="-mb-px flex gap-6 overflow-x-auto">
        <button
            v-for="item in items"
            :key="item.value ?? 'all'"
            type="button"
            :aria-current="active === item.value ? 'page' : undefined"
            :class="
                cn(
                    'whitespace-nowrap border-b-2 pb-2.5 text-sm transition-colors',
                    active === item.value
                        ? 'border-brass font-medium text-foreground'
                        : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground',
                )
            "
            @click="$emit('select', item.value)"
        >
            {{ item.label }}
            <sup v-if="item.count !== undefined" class="ml-0.5 tabular-nums text-muted-foreground">{{ item.count }}</sup>
        </button>
    </nav>
</template>
