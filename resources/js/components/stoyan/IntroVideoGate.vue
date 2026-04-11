<script setup lang="ts">
type Props = {
    open: boolean;
    videoUrl: string;
};

defineProps<Props>();

defineEmits<{
    dismissed: [];
}>();
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/95 px-4 py-6"
    >
        <div class="relative w-full max-w-5xl overflow-hidden rounded-3xl bg-neutral-950 shadow-2xl">
            <button
                type="button"
                class="absolute right-4 top-4 z-10 rounded-full border border-white/20 bg-black/40 px-4 py-2 text-sm font-medium text-white transition hover:bg-black/60"
                @click="$emit('dismissed')"
            >
                Skip intro
            </button>

            <video
                class="aspect-video w-full bg-black"
                :src="videoUrl"
                controls
                autoplay
                playsinline
                @ended="$emit('dismissed')"
            />
        </div>
    </div>
</template>