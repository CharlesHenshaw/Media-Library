<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <!-- Three books and one leaning on a shelf. -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" :class="className" v-bind="$attrs">
        <g fill="currentColor">
            <rect x="5" y="9" width="6" height="24" rx="1" />
            <rect x="12.5" y="13" width="5" height="20" rx="1" />
            <rect x="19" y="6" width="6.5" height="27" rx="1" />
            <rect x="27" y="10" width="5.5" height="23" rx="1" transform="rotate(14 29.75 33)" />
            <rect x="3" y="34" width="34" height="3" rx="1" />
        </g>
    </svg>
</template>
