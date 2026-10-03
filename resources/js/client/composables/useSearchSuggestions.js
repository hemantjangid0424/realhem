import { ref } from 'vue';

export function useSearchSuggestions() {
    const suggestions = ref([]);
    const isLoading = ref(false);
    let debounceTimeout = null;
    let abortController = null;

    const fetchSuggestions = (query, city = '') => {
        if (debounceTimeout) {
            clearTimeout(debounceTimeout);
        }

        const trimmed = (query || '').trim();
        if (trimmed.length < 1) {
            suggestions.value = [];
            isLoading.value = false;
            return;
        }

        debounceTimeout = setTimeout(async () => {
            if (abortController) {
                abortController.abort();
            }
            abortController = new AbortController();

            isLoading.value = true;
            try {
                const params = new URLSearchParams({
                    q: trimmed,
                });
                if (city) {
                    params.append('city', city);
                }

                const res = await fetch(`/api/search/suggestions?${params.toString()}`, {
                    signal: abortController.signal,
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                if (res.ok) {
                    const data = await res.json();
                    suggestions.value = Array.isArray(data) ? data : [];
                } else {
                    suggestions.value = [];
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.error('Failed to fetch search suggestions:', err);
                    suggestions.value = [];
                }
            } finally {
                isLoading.value = false;
            }
        }, 150);
    };

    const clearSuggestions = () => {
        if (debounceTimeout) clearTimeout(debounceTimeout);
        if (abortController) abortController.abort();
        suggestions.value = [];
        isLoading.value = false;
    };

    return {
        suggestions,
        isLoading,
        fetchSuggestions,
        clearSuggestions,
    };
}
