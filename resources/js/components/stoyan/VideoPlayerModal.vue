<script setup lang="ts">
import { Expand, X } from 'lucide-vue-next';
import { ref } from 'vue';
import type { StoyanVideo } from '@/types';

type Props = {
    open: boolean;
    video: StoyanVideo | null;
};

defineProps<Props>();

defineEmits<{
    close: [];
}>();

const playerSurface = ref<HTMLElement | null>(null);
const requestFullscreen = async (): Promise<void> => {
    const surface = playerSurface.value;

    if (surface === null || document.fullscreenElement === surface) {
        return;
    }

    await surface.requestFullscreen?.();
};
</script>

<template>
    <div
        v-if="open && video"
        class="fixed inset-0 z-40 bg-black"
        @click.self="$emit('close')"
    >
        <div
            ref="playerSurface"
            class="relative flex h-dvh w-screen items-center justify-center overflow-hidden bg-black text-white"
        >
            <div class="absolute top-4 right-4 z-10 flex items-center gap-2">
                <button
                    type="button"
                    class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-white/15 bg-black/55 backdrop-blur transition hover:bg-black/70"
                    aria-label="Open fullscreen"
                    @click="requestFullscreen"
                >
                    <Expand class="h-5 w-5" />
                </button>
                <button
                    type="button"
                    class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-white/15 bg-black/55 backdrop-blur transition hover:bg-black/70"
                    aria-label="Close video"
                    @click="$emit('close')"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="flex h-full w-full items-center justify-center">
                <video
                    v-if="video.videoUrl"
                    class="h-full w-full bg-black object-contain"
                    :src="video.videoUrl"
                    :poster="video.thumbnailUrl ?? undefined"
                    controls
                    autoplay
                    playsinline
                    preload="metadata"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center border border-dashed border-white/15 text-sm text-white/60"
                >
                    The video file is currently unavailable.
                </div>
            </div>
        </div>
    </div>
</template>
