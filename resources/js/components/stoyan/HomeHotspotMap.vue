<script setup lang="ts">
import { nextTick, onMounted, ref, watch } from 'vue';
import { Skeleton } from '@/components/ui/skeleton';
import type { StoyanCategoriesByHotspot, StoyanHotspot } from '@/types';

type Props = {
    imageUrl: string;
    hotspots: StoyanHotspot[];
    categoriesByHotspot: StoyanCategoriesByHotspot;
};

const props = defineProps<Props>();

const imageElement = ref<HTMLImageElement | null>(null);

defineEmits<{
    hotspotClick: [hotspotKey: string];
}>();

const isImageReady = ref(false);

const markImageReady = (): void => {
    isImageReady.value = true;
};

const syncImageReadyFromElement = (): void => {
    if (imageElement.value?.complete) {
        isImageReady.value = true;
    }
};

onMounted(() => {
    syncImageReadyFromElement();
});

watch(
    () => props.imageUrl,
    async () => {
        isImageReady.value = false;

        await nextTick();
        syncImageReadyFromElement();
    },
    { immediate: true },
);
</script>

<template>
    <div
        class="relative aspect-square overflow-hidden rounded-[1.6rem] border border-stone-200 bg-white"
    >
        <Skeleton
            v-if="!isImageReady"
            class="absolute inset-0 rounded-none bg-stone-200/80"
        />

        <img
            ref="imageElement"
            :src="imageUrl"
            alt="Stoyan Kolev categories"
            class="absolute inset-0 block h-full w-full object-cover transition duration-300"
            :class="isImageReady ? 'opacity-100' : 'opacity-0'"
            @load="markImageReady"
            @error="markImageReady"
        />

        <button
            v-for="hotspot in hotspots"
            :key="hotspot.key"
            v-show="isImageReady"
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
