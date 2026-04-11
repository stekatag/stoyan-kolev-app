import { ref } from 'vue';
import type { StoyanCategoriesByHotspot, StoyanCategory, StoyanVideo } from '@/types';

export function usePublicHomeModals() {
    const selectedCategory = ref<StoyanCategory | null>(null);
    const selectedVideo = ref<StoyanVideo | null>(null);

    const openHotspot = (
        categoriesByHotspot: StoyanCategoriesByHotspot,
        hotspotKey: string,
    ): void => {
        const category = categoriesByHotspot[hotspotKey] ?? null;

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

    return {
        selectedCategory,
        selectedVideo,
        openHotspot,
        openVideo,
        closeVideo,
        closeCategory,
    };
}