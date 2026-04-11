<script setup lang="ts">
import VideoThumbnailCard from '@/components/stoyan/VideoThumbnailCard.vue';
import type { StoyanCategory, StoyanVideo } from '@/types';

type Props = {
    open: boolean;
    category: StoyanCategory | null;
};

defineProps<Props>();

defineEmits<{
    close: [];
    selectVideo: [video: StoyanVideo];
}>();
</script>

<template>
    <div
        v-if="open && category"
        class="fixed inset-0 z-30 flex items-center justify-center bg-stone-950/78 px-3 py-3 backdrop-blur-sm sm:px-4 sm:py-4"
        @click.self="$emit('close')"
    >
        <div
            class="flex max-h-[94dvh] w-full max-w-6xl flex-col overflow-hidden rounded-[2rem] border border-white/10 bg-[#121212] text-white shadow-[0_30px_90px_rgba(0,0,0,0.45)]"
        >
            <div
                class="flex items-center justify-between gap-4 border-b border-white/10 px-4 py-4 sm:px-6"
            >
                <div class="min-w-0">
                    <h3
                        class="truncate text-2xl font-semibold tracking-tight text-white sm:text-3xl"
                    >
                        {{ category.name }}
                    </h3>
                    <p class="mt-1 text-sm text-white/60">
                        {{ category.videoCount }} videos
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/10"
                    @click="$emit('close')"
                >
                    Close
                </button>
            </div>

            <div class="max-h-[calc(94dvh-88px)] overflow-y-auto p-3 sm:p-5">
                <div
                    class="mx-auto grid max-w-5xl grid-cols-1 gap-3 min-[520px]:grid-cols-2 lg:grid-cols-3"
                >
                    <VideoThumbnailCard
                        v-for="video in category.videos"
                        :key="video.id"
                        :video="video"
                        @select="$emit('selectVideo', $event)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
