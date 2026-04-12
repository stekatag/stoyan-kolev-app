<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import VideoThumbnailCard from '@/components/stoyan/VideoThumbnailCard.vue';
import { Skeleton } from '@/components/ui/skeleton';
import type { StoyanCategory, StoyanVideo } from '@/types';

type Props = {
    open: boolean;
    category: StoyanCategory | null;
};

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
    selectVideo: [video: StoyanVideo];
}>();

const categoryEmojiBySlug: Record<string, string> = {
    qdosan: '😠',
    uchuden: '😮',
    kaish: '🤪',
    vesel: '😆',
    izmoren: '😢',
    izpederastql: '👍',
};

const isOpen = computed(() => props.open && props.category !== null);

const categoryEmoji = computed(() => {
    if (props.category === null) {
        return '';
    }

    return categoryEmojiBySlug[props.category.slug] ?? '';
});

const skeletonCardCount = computed(() =>
    Math.max(props.category?.videos.length ?? 0, 3),
);

const areThumbnailImagesReady = ref(false);

let preloadRunId = 0;

const preloadImage = (url: string): Promise<void> => {
    if (typeof Image === 'undefined') {
        return Promise.resolve();
    }

    return new Promise((resolve) => {
        const image = new Image();

        const finish = (): void => {
            resolve();
        };

        image.onload = finish;
        image.onerror = finish;
        image.src = url;

        if (image.complete) {
            resolve();
        }
    });
};

watch(
    () => [
        props.open,
        props.category?.id ?? null,
        props.category?.videos
            .map((video) => video.thumbnailUrl ?? '')
            .join('|') ?? '',
    ],
    async () => {
        if (!isOpen.value || props.category === null) {
            areThumbnailImagesReady.value = false;

            return;
        }

        const thumbnailUrls = [
            ...new Set(
                props.category.videos
                    .map((video) => video.thumbnailUrl)
                    .filter((url): url is string => Boolean(url)),
            ),
        ];

        if (thumbnailUrls.length === 0 || typeof window === 'undefined') {
            areThumbnailImagesReady.value = true;

            return;
        }

        areThumbnailImagesReady.value = false;

        const currentRunId = ++preloadRunId;

        await Promise.all(thumbnailUrls.map((url) => preloadImage(url)));

        if (currentRunId === preloadRunId) {
            areThumbnailImagesReady.value = true;
        }
    },
    { immediate: true },
);
</script>

<template>
    <Transition
        appear
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isOpen && category"
            class="fixed inset-0 z-30 flex items-center justify-center overflow-hidden bg-stone-950/78 px-3 py-3 backdrop-blur-sm sm:px-4 sm:py-4"
            @click.self="emit('close')"
        >
            <Transition
                appear
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="translate-y-2 scale-[0.985] opacity-0"
                enter-to-class="translate-y-0 scale-100 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="translate-y-0 scale-100 opacity-100"
                leave-to-class="translate-y-2 scale-[0.985] opacity-0"
            >
                <div
                    v-if="isOpen && category"
                    class="flex max-h-[94dvh] w-full max-w-6xl flex-col overflow-hidden rounded-4xl border border-white/10 bg-[#121212] text-white shadow-[0_30px_90px_rgba(0,0,0,0.45)]"
                    @click.stop
                >
                    <div
                        class="flex items-center justify-between gap-4 border-b border-white/10 px-4 py-4 sm:px-6"
                    >
                        <div class="min-w-0">
                            <h3
                                class="flex items-center gap-3 truncate text-2xl font-semibold tracking-tight text-white sm:text-3xl"
                            >
                                <span class="truncate">{{
                                    category.name
                                }}</span>
                                <span
                                    v-if="categoryEmoji"
                                    class="text-3xl leading-none"
                                    aria-hidden="true"
                                >
                                    {{ categoryEmoji }}
                                </span>
                            </h3>
                        </div>

                        <button
                            type="button"
                            class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-white/15 bg-black/55 backdrop-blur transition hover:bg-black/70"
                            aria-label="Close category"
                            @click="emit('close')"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div
                        class="max-h-[calc(94dvh-88px)] overflow-y-auto overscroll-contain p-3 sm:p-5"
                    >
                        <div
                            v-if="!areThumbnailImagesReady"
                            class="mx-auto grid max-w-5xl grid-cols-1 gap-3 min-[520px]:grid-cols-2 lg:grid-cols-3"
                        >
                            <Skeleton
                                v-for="index in skeletonCardCount"
                                :key="index"
                                class="aspect-[4/5] rounded-[1.7rem] bg-white/8"
                            />
                        </div>

                        <div
                            v-else
                            class="mx-auto grid max-w-5xl grid-cols-1 gap-3 min-[520px]:grid-cols-2 lg:grid-cols-3"
                        >
                            <VideoThumbnailCard
                                v-for="video in category.videos"
                                :key="video.id"
                                :video="video"
                                @select="emit('selectVideo', $event)"
                            />
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>
