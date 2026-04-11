import { computed, onMounted, ref } from 'vue';

const storageKey = 'stoyan-kolev:intro-dismissed';

export function useIntroVideoPreference() {
    const introDismissed = ref(true);
    const introPreferenceReady = ref(false);

    onMounted(() => {
        introDismissed.value = window.localStorage.getItem(storageKey) === '1';
        introPreferenceReady.value = true;
    });

    const shouldShowIntro = computed(
        () => introPreferenceReady.value && !introDismissed.value,
    );

    const dismissIntro = (): void => {
        introDismissed.value = true;
        introPreferenceReady.value = true;

        if (typeof window !== 'undefined') {
            window.localStorage.setItem(storageKey, '1');
        }
    };

    return {
        introPreferenceReady,
        shouldShowIntro,
        dismissIntro,
    };
}
