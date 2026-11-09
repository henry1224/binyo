<script setup lang="ts">
import { Monitor, Moon, Sun } from '@lucide/vue';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Terang' },
    { value: 'dark', Icon: Moon, label: 'Gelap' },
    { value: 'system', Icon: Monitor, label: 'Sistem' },
] as const;
</script>

<template>
    <div
        class="grid w-full grid-cols-3 gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800"
        role="group"
        aria-label="Tema tampilan"
    >
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            type="button"
            @click="updateAppearance(value)"
            :aria-pressed="appearance === value"
            :class="[
                'flex items-center justify-center rounded-md px-2 py-1.5 transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                appearance === value
                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
        >
            <component :is="Icon" class="h-4 w-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>
