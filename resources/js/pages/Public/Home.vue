<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Ellipsis, Heart, Share2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import CategoryVideoModal from '@/components/stoyan/CategoryVideoModal.vue';
import HomeHotspotMap from '@/components/stoyan/HomeHotspotMap.vue';
import IntroVideoGate from '@/components/stoyan/IntroVideoGate.vue';
import VideoPlayerModal from '@/components/stoyan/VideoPlayerModal.vue';
import { useIntroVideoPreference } from '@/composables/useIntroVideoPreference';
import type {
    StoyanCategoriesByHotspot,
    StoyanCategory,
    StoyanHotspot,
    StoyanVideo,
} from '@/types';

type Props = {
    introVideoUrl: string;
    profileImageUrl: string;
    homepageImageUrl: string;
    hotspots: StoyanHotspot[];
    categoriesByHotspot: StoyanCategoriesByHotspot;
};

const props = defineProps<Props>();
const postMenu = ref<HTMLElement | null>(null);

const selectedCategory = ref<StoyanCategory | null>(null);
const selectedVideo = ref<StoyanVideo | null>(null);
const isPostLiked = ref(false);
const shareFeedback = ref<string | null>(null);
const isMenuOpen = ref(false);
let shareFeedbackTimer: ReturnType<typeof window.setTimeout> | null = null;

const { introPreferenceReady, shouldShowIntro, dismissIntro } =
    useIntroVideoPreference();
const likeStorageKey = 'stoyan-kolev:post-liked';

const introOpen = computed(
    () =>
        introPreferenceReady.value &&
        shouldShowIntro.value &&
        props.introVideoUrl !== '',
);

onMounted(() => {
    isPostLiked.value = window.localStorage.getItem(likeStorageKey) === '1';
    window.addEventListener('pointerdown', handlePointerDown);
});

onBeforeUnmount(() => {
    window.removeEventListener('pointerdown', handlePointerDown);

    if (shareFeedbackTimer !== null) {
        window.clearTimeout(shareFeedbackTimer);
    }
});

const openHotspot = (hotspotKey: string): void => {
    const category = props.categoriesByHotspot[hotspotKey] ?? null;

    if (category === null) {
        return;
    }

    if (category.opensDirectly && category.directVideo !== null) {
        selectedVideo.value = category.directVideo;
        selectedCategory.value = null;

        return;
    }

    selectedCategory.value = category;
};

const openVideo = (video: StoyanVideo): void => {
    selectedVideo.value = video;
};

const closeVideo = (): void => {
    selectedVideo.value = null;
};

const closeCategory = (): void => {
    selectedCategory.value = null;
};

const togglePostMenu = (): void => {
    isMenuOpen.value = !isMenuOpen.value;
};

const toggleLike = (): void => {
    isPostLiked.value = !isPostLiked.value;
    window.localStorage.setItem(likeStorageKey, isPostLiked.value ? '1' : '0');
    isMenuOpen.value = false;
};

const sharePost = async (): Promise<void> => {
    const shareUrl = window.location.href;
    const sharePayload = {
        title: 'Stoyan Kolev',
        text: 'Koi stoyan si dnes?',
        url: shareUrl,
    };

    try {
        if (
            typeof navigator.share === 'function' &&
            (typeof navigator.canShare !== 'function' ||
                navigator.canShare(sharePayload))
        ) {
            await navigator.share(sharePayload);
            shareFeedback.value = 'Share sheet opened.';
        } else if (
            navigator.clipboard &&
            typeof navigator.clipboard.writeText === 'function'
        ) {
            await navigator.clipboard.writeText(shareUrl);
            shareFeedback.value = 'Link copied to clipboard.';
        } else {
            shareFeedback.value = 'Sharing is not available on this device.';
        }
    } catch {
        shareFeedback.value = 'Sharing was cancelled.';
    }

    isMenuOpen.value = false;
    queueShareFeedbackReset();
};

function queueShareFeedbackReset(): void {
    if (shareFeedbackTimer !== null) {
        window.clearTimeout(shareFeedbackTimer);
    }

    shareFeedbackTimer = window.setTimeout(() => {
        shareFeedback.value = null;
    }, 2400);
}

function handlePointerDown(event: PointerEvent): void {
    if (!(event.target instanceof Node)) {
        return;
    }

    if (postMenu.value?.contains(event.target)) {
        return;
    }

    isMenuOpen.value = false;
}
</script>

<template>
    <Head title="Stoyan Kolev" />

    <div class="min-h-screen bg-[#efede8] text-stone-900">
        <IntroVideoGate
            :open="introOpen"
            :video-url="introVideoUrl"
            @dismissed="dismissIntro"
        />

        <div
            class="mx-auto flex min-h-screen w-full justify-center px-3 py-5 sm:px-6 sm:py-8"
        >
            <main class="w-full max-w-[760px]">
                <article
                    class="overflow-hidden rounded-[2rem] border border-stone-200 bg-white shadow-[0_24px_80px_rgba(41,33,23,0.12)]"
                >
                    <header
                        class="flex items-start justify-between gap-4 px-4 pt-4 pb-3 sm:px-5 sm:pt-5"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <img
                                :src="profileImageUrl"
                                alt="Stoyan Kolev"
                                class="h-12 w-12 rounded-full object-cover ring-2 ring-stone-200"
                            />

                            <div class="min-w-0">
                                <p
                                    class="truncate text-[15px] font-semibold text-stone-900"
                                >
                                    Stoyan Kolev
                                </p>
                                <p class="text-xs text-stone-500">
                                    Meme post · Public
                                </p>
                            </div>
                        </div>

                        <div ref="postMenu" class="relative">
                            <button
                                type="button"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full text-stone-500 transition hover:bg-stone-100 hover:text-stone-900"
                                aria-label="Post options"
                                :aria-expanded="isMenuOpen"
                                @click.stop="togglePostMenu"
                            >
                                <Ellipsis class="h-5 w-5" />
                            </button>

                            <div
                                v-if="isMenuOpen"
                                class="absolute top-12 right-0 z-10 w-52 overflow-hidden rounded-2xl border border-stone-200 bg-white p-2 shadow-[0_18px_40px_rgba(41,33,23,0.14)]"
                            >
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm font-medium text-stone-700 transition hover:bg-stone-100"
                                    @click="toggleLike"
                                >
                                    <Heart
                                        :class="[
                                            'h-4 w-4',
                                            isPostLiked
                                                ? 'fill-current text-rose-500'
                                                : 'text-stone-500',
                                        ]"
                                    />
                                    <span>{{
                                        isPostLiked
                                            ? 'Unlike post'
                                            : 'Like post'
                                    }}</span>
                                </button>
                                <button
                                    type="button"
                                    class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm font-medium text-stone-700 transition hover:bg-stone-100"
                                    @click="sharePost"
                                >
                                    <Share2 class="h-4 w-4 text-stone-500" />
                                    <span>Share post</span>
                                </button>
                            </div>
                        </div>
                    </header>

                    <div class="px-4 pb-4 sm:px-5">
                        <p class="mb-4 text-sm leading-6 text-stone-700">
                            Koi stoyan si dnes? Tap a mood from the collage to
                            open the clips.
                        </p>

                        <HomeHotspotMap
                            :image-url="homepageImageUrl"
                            :hotspots="hotspots"
                            :categories-by-hotspot="categoriesByHotspot"
                            @hotspot-click="openHotspot"
                        />

                        <div
                            class="mt-4 flex items-center justify-between border-b border-stone-200 pb-3 text-sm text-stone-500"
                        >
                            <span>6 moods</span>
                            <span>Video meme archive</span>
                        </div>

                        <div
                            class="mt-2 grid grid-cols-2 gap-2 text-sm font-medium text-stone-600"
                        >
                            <button
                                type="button"
                                class="flex items-center justify-center gap-2 rounded-2xl px-3 py-2 transition hover:bg-stone-100"
                                @click="toggleLike"
                            >
                                <Heart
                                    :class="[
                                        'h-4 w-4',
                                        isPostLiked
                                            ? 'fill-current text-rose-500'
                                            : 'text-stone-500',
                                    ]"
                                />
                                <span>{{
                                    isPostLiked ? 'Liked' : 'Like'
                                }}</span>
                            </button>
                            <button
                                type="button"
                                class="flex items-center justify-center gap-2 rounded-2xl px-3 py-2 transition hover:bg-stone-100"
                                @click="sharePost"
                            >
                                <Share2 class="h-4 w-4 text-stone-500" />
                                <span>Share</span>
                            </button>
                        </div>

                        <p
                            v-if="shareFeedback"
                            class="mt-3 text-sm text-stone-500"
                        >
                            {{ shareFeedback }}
                        </p>
                    </div>
                </article>
            </main>
        </div>

        <CategoryVideoModal
            :open="selectedCategory !== null"
            :category="selectedCategory"
            @close="closeCategory"
            @select-video="openVideo"
        />

        <VideoPlayerModal
            :open="selectedVideo !== null"
            :video="selectedVideo"
            @close="closeVideo"
        />
    </div>
</template>
