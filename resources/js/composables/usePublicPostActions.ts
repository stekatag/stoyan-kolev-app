import { onMounted, ref } from 'vue';

const likeStorageKey = 'stoyan-kolev:post-liked';

export function usePublicPostActions() {
    const postMenu = ref<HTMLElement | null>(null);
    const isPostLiked = ref(false);
    const isMenuOpen = ref(false);

    onMounted(() => {
        isPostLiked.value = window.localStorage.getItem(likeStorageKey) === '1';
        window.addEventListener('pointerdown', handlePointerDown);
    });

    const togglePostMenu = (): void => {
        isMenuOpen.value = !isMenuOpen.value;
    };

    const closeMenu = (): void => {
        isMenuOpen.value = false;
    };

    const toggleLike = (): void => {
        isPostLiked.value = !isPostLiked.value;
        window.localStorage.setItem(
            likeStorageKey,
            isPostLiked.value ? '1' : '0',
        );
        closeMenu();
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
            } else if (
                navigator.clipboard &&
                typeof navigator.clipboard.writeText === 'function'
            ) {
                await navigator.clipboard.writeText(shareUrl);
            }
        } catch {}

        closeMenu();
    };

    function handlePointerDown(event: PointerEvent): void {
        if (!(event.target instanceof Node)) {
            return;
        }

        if (postMenu.value?.contains(event.target)) {
            return;
        }

        closeMenu();
    }

    return {
        postMenu,
        isPostLiked,
        isMenuOpen,
        toggleLike,
        togglePostMenu,
        closeMenu,
        sharePost,
    };
}
