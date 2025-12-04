/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

<script setup lang="ts">
import { computed } from 'vue';

const patternId = computed(
    () => `pattern-${Math.random().toString(36).substring(2, 9)}`,
);
</script>

<template>
    <svg
        class="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20"
        fill="none"
    >
        <defs>
            <pattern
                :id="patternId"
                x="0"
                y="0"
                width="8"
                height="8"
                patternUnits="userSpaceOnUse"
            >
                <path d="M-1 5L5 -1M3 9L8.5 3.5" stroke-width="0.5"></path>
            </pattern>
        </defs>
        <rect
            stroke="none"
            :fill="`url(#${patternId})`"
            width="100%"
            height="100%"
        ></rect>
    </svg>
</template>
