<script setup lang="ts">
import { ref, watch } from 'vue';
import type { StoyanVideo } from '@/types';

const props = defineProps<{
    video: StoyanVideo;
}>();

defineEmits<{
    select: [video: StoyanVideo];
}>();

const isImageReady = ref(false);

watch(
    () => props.video.thumbnailUrl,
    () => {
        isImageReady.value = false;
    },
    { immediate: true },
);
</script>

<template>
    <button
        type="button"
        class="group relative overflow-hidden rounded-[1.7rem] bg-[#1d1d1d] text-left shadow-[0_16px_40px_rgba(0,0,0,0.28)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_22px_52px_rgba(0,0,0,0.34)] focus:ring-2 focus:ring-amber-300 focus:outline-none"
        :title="video.title"
        :aria-label="`Play ${video.title}`"
        @click="$emit('select', props.video)"
    >
        <div class="aspect-[4/5] overflow-hidden bg-stone-900">
            <div
                v-if="video.thumbnailUrl && !isImageReady"
                class="absolute inset-0 animate-pulse bg-stone-800"
            />

            <img
                v-if="video.thumbnailUrl"
                :src="video.thumbnailUrl"
                :alt="video.title"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.06] group-hover:brightness-110"
                :class="isImageReady ? 'opacity-100' : 'opacity-0'"
                @load="isImageReady = true"
                @error="isImageReady = true"
            />
            <div
                v-else
                class="flex h-full items-center justify-center bg-stone-800 px-5 text-center text-base leading-6 font-semibold text-white"
            >
                {{ video.title }}
            </div>
        </div>

        <div
            class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-linear-to-t from-black/70 to-transparent opacity-0 transition duration-300 group-hover:opacity-100"
        />
    </button>
</template>
