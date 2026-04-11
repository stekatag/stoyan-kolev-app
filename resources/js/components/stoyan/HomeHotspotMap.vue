<script setup lang="ts">
import type { StoyanCategoriesByHotspot, StoyanHotspot } from '@/types';

type Props = {
    imageUrl: string;
    hotspots: StoyanHotspot[];
    categoriesByHotspot: StoyanCategoriesByHotspot;
};

defineProps<Props>();

defineEmits<{
    hotspotClick: [hotspotKey: string];
}>();
</script>

<template>
    <div
        class="relative overflow-hidden rounded-[1.6rem] border border-stone-200 bg-white shadow-[0_14px_40px_rgba(58,43,16,0.14)]"
    >
        <img
            :src="imageUrl"
            alt="Stoyan Kolev categories"
            class="block w-full"
        />

        <button
            v-for="hotspot in hotspots"
            :key="hotspot.key"
            type="button"
            class="absolute cursor-pointer bg-transparent transition hover:scale-[1.02] hover:bg-stone-950/12 focus:ring-2 focus:ring-amber-300 focus:outline-none"
            :style="{
                left: `${hotspot.x}%`,
                top: `${hotspot.y}%`,
                width: `${hotspot.width}%`,
                height: `${hotspot.height}%`,
            }"
            :title="categoriesByHotspot[hotspot.key]?.name ?? hotspot.label"
            :aria-label="`Open ${categoriesByHotspot[hotspot.key]?.name ?? hotspot.label}`"
            @click="$emit('hotspotClick', hotspot.key)"
        />
    </div>
</template>
