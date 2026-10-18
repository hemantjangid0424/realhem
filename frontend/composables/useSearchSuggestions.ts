export function useSearchSuggestions() {
  const suggestions = ref<any[]>([]);
  const isLoading = ref(false);
  const { fetchWithCache, get } = useFrontendCache();
  const { apiBase } = useApi();
  let debounceTimeout: any = null;
  let abortController: AbortController | null = null;

  const fetchSuggestions = (query: string, city = '') => {
    if (debounceTimeout) {
      clearTimeout(debounceTimeout);
    }

    const trimmed = (query || '').trim();
    if (trimmed.length < 1) {
      suggestions.value = [];
      isLoading.value = false;
      return;
    }

    const params = new URLSearchParams({ q: trimmed });
    if (city) params.append('city', city);
    const cacheUrl = `${apiBase}/api/search/suggestions?${params.toString()}`;

    const cached = get(cacheUrl);
    if (cached && Array.isArray(cached)) {
      suggestions.value = cached;
      isLoading.value = false;
    }

    debounceTimeout = setTimeout(async () => {
      if (abortController) {
        abortController.abort();
      }
      abortController = new AbortController();

      if (!cached) isLoading.value = true;

      try {
        const data: any = await fetchWithCache(cacheUrl, {
          ttlMs: 5 * 60 * 1000,
          swr: true,
          fetchOptions: { signal: abortController.signal },
          onBackgroundUpdate: (freshData: any) => {
            if (Array.isArray(freshData)) {
              suggestions.value = freshData;
            }
          },
        });

        if (Array.isArray(data)) {
          suggestions.value = data;
        }
      } catch (err: any) {
        if (err.name !== 'AbortError') {
          console.error('Failed to fetch search suggestions:', err);
        }
      } finally {
        isLoading.value = false;
      }
    }, cached ? 300 : 150);
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

