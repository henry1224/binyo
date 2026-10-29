<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 40"
        :class="className"
        v-bind="$attrs"
    >
        <path
            d="M8 5h12.5C28.6 5 34 9.2 34 15.3c0 3.4-1.8 6-4.7 7.4 2.1 1.2 3.7 3.1 4.4 5.6C30.8 32.6 25.9 35 19 35H8V5Zm8 7v7h4.2c3.5 0 5.4-1.2 5.4-3.6 0-2.3-1.9-3.4-5.4-3.4H16Zm0 13v3h3.9c2.2 0 3.9-.3 5.1-1.1-.8-1.3-2.3-1.9-4.8-1.9H16Z"
        />
    </svg>
</template>
