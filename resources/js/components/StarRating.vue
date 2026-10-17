<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Star } from 'lucide-vue-next';
import { ref } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: number | null;
        readonly?: boolean;
        size?: 'sm' | 'lg';
    }>(),
    { readonly: false, size: 'sm' },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | null): void;
}>();

const hovered = ref<number | null>(null);

// Clicking the current rating again clears it.
const select = (value: number) => {
    if (!props.readonly) {
        emit('update:modelValue', props.modelValue === value ? null : value);
    }
};

const filled = (value: number) => value <= (hovered.value ?? props.modelValue ?? 0);
</script>

<template>
    <div class="flex items-center gap-0.5" :role="readonly ? 'img' : 'radiogroup'" :aria-label="`Rated ${modelValue ?? 0} out of 5`">
        <component
            :is="readonly ? 'span' : 'button'"
            v-for="value in 5"
            :key="value"
            :type="readonly ? undefined : 'button'"
            :aria-label="readonly ? undefined : `${value} star${value > 1 ? 's' : ''}`"
            :class="cn(!readonly && 'rounded transition-transform hover:scale-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring')"
            @click="select(value)"
            @mouseenter="!readonly && (hovered = value)"
            @mouseleave="hovered = null"
        >
            <Star
                :class="
                    cn(
                        size === 'lg' ? 'size-7' : 'size-3.5',
                        filled(value) ? 'fill-brass text-brass' : 'fill-transparent text-muted-foreground/35',
                    )
                "
            />
        </component>
    </div>
</template>
