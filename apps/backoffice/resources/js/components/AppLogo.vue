<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const name = usePage().props.name;
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-lg bg-[#163f35] text-white shadow-sm"
    >
        <AppLogoIcon class="size-5 fill-current" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="truncate leading-tight font-semibold tracking-[-0.01em]">{{
            name
        }}</span>
        <span
            class="truncate text-[10px] tracking-[0.16em] text-muted-foreground uppercase"
        >
            Backoffice
        </span>
    </div>
</template>
