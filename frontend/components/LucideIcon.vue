<template>
    <component
        :is="iconComponent"
        v-if="iconComponent"
        :size="size"
        :stroke-width="strokeWidth"
        :class="props.class"
    />
</template>

<script setup>
import { computed } from 'vue';
import * as icons from 'lucide-vue-next';

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    size: {
        type: [Number, String],
        default: 20,
    },
    strokeWidth: {
        type: [Number, String],
        default: 2,
    },
    class: {
        type: [String, Object, Array],
        default: '',
    },
});

const iconComponent = computed(() => {
    if (!props.name) return null;

    // Direct match
    if (icons[props.name]) return icons[props.name];
    if (icons[`${props.name}Icon`]) return icons[`${props.name}Icon`];

    // Convert kebab-case or snake_case to PascalCase (e.g. 'bed-double' -> 'BedDouble')
    const formatted = props.name
        .replace(/[-_]([a-z])/g, (_, letter) => letter.toUpperCase())
        .replace(/^[a-z]/, (letter) => letter.toUpperCase());

    return icons[formatted] || icons[`${formatted}Icon`] || null;
});
</script>

