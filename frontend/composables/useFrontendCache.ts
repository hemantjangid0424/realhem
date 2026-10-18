const memoryCache = new Map<string, any>();
const inFlightRequests = new Map<string, Promise<any>>();

const DEFAULT_TTL_MS = 3 * 60 * 1000;
const STORAGE_PREFIX = 'rh_cache_';

function getStorageKey(key: string) {
  return `${STORAGE_PREFIX}${key}`;
}

function readStorage(key: string) {
  if (typeof window === 'undefined' || !window.sessionStorage) return null;
  try {
    const raw = window.sessionStorage.getItem(getStorageKey(key));
    if (!raw) return null;
    const item = JSON.parse(raw);
    if (Date.now() > item.expiresAt) {
      window.sessionStorage.removeItem(getStorageKey(key));
      return null;
    }
    return item;
  } catch {
    return null;
  }
}

function writeStorage(key: string, data: any, expiresAt: number, etag: string | null = null) {
  if (typeof window === 'undefined' || !window.sessionStorage) return;
  try {
    window.sessionStorage.setItem(
      getStorageKey(key),
      JSON.stringify({ data, expiresAt, etag, savedAt: Date.now() })
    );
  } catch {
    cleanStorage();
  }
}

function cleanStorage() {
  if (typeof window === 'undefined' || !window.sessionStorage) return;
  try {
    const now = Date.now();
    const keysToRemove: string[] = [];
    for (let i = 0; i < window.sessionStorage.length; i++) {
      const k = window.sessionStorage.key(i);
      if (k && k.startsWith(STORAGE_PREFIX)) {
        try {
          const item = JSON.parse(window.sessionStorage.getItem(k) || '{}');
          if (now > item.expiresAt) {
            keysToRemove.push(k);
          }
        } catch {
          keysToRemove.push(k);
        }
      }
    }
    keysToRemove.forEach((k) => window.sessionStorage.removeItem(k));
  } catch {
    // ignore
  }
}

export function useFrontendCache() {
  const get = (key: string) => {
    const now = Date.now();
    if (memoryCache.has(key)) {
      const entry = memoryCache.get(key);
      if (now < entry.expiresAt) {
        return entry.data;
      }
      memoryCache.delete(key);
    }

    const stored = readStorage(key);
    if (stored) {
      memoryCache.set(key, stored);
      return stored.data;
    }

    return null;
  };

  const set = (key: string, data: any, ttlMs = DEFAULT_TTL_MS, etag: string | null = null) => {
    const expiresAt = Date.now() + ttlMs;
    const entry = { data, expiresAt, etag, savedAt: Date.now() };
    memoryCache.set(key, entry);
    writeStorage(key, data, expiresAt, etag);
  };

  const has = (key: string) => {
    return get(key) !== null;
  };

  const remove = (key: string) => {
    memoryCache.delete(key);
    if (typeof window !== 'undefined' && window.sessionStorage) {
      window.sessionStorage.removeItem(getStorageKey(key));
    }
  };

  const invalidate = (prefix = '') => {
    for (const k of memoryCache.keys()) {
      if (!prefix || k.startsWith(prefix)) {
        memoryCache.delete(k);
      }
    }
    if (typeof window !== 'undefined' && window.sessionStorage) {
      const keysToRemove: string[] = [];
      for (let i = 0; i < window.sessionStorage.length; i++) {
        const k = window.sessionStorage.key(i);
        if (k && k.startsWith(STORAGE_PREFIX)) {
          const actualKey = k.slice(STORAGE_PREFIX.length);
          if (!prefix || actualKey.startsWith(prefix)) {
            keysToRemove.push(k);
          }
        }
      }
      keysToRemove.forEach((k) => window.sessionStorage.removeItem(k));
    }
  };

  const fetchWithCache = async (url: string, options: any = {}) => {
    const ttlMs = options.ttlMs || DEFAULT_TTL_MS;
    const swr = options.swr !== false;
    const onBackgroundUpdate = options.onBackgroundUpdate || null;
    const cacheKey = url;

    const cached = get(cacheKey);
    if (cached !== null) {
      if (swr) {
        revalidateInBackground(url, cacheKey, ttlMs, cached, onBackgroundUpdate, options.fetchOptions);
      }
      return cached;
    }

    if (inFlightRequests.has(cacheKey)) {
      return inFlightRequests.get(cacheKey);
    }

    const promise = (async () => {
      try {
        const headers = {
          'Accept': 'application/json',
          ...(options.fetchOptions?.headers || {}),
        };

        const res = await fetch(url, {
          ...options.fetchOptions,
          headers,
        });

        if (!res.ok) {
          throw new Error(`HTTP error ${res.status}`);
        }

        const etag = res.headers.get('ETag');
        const data = await res.json();
        set(cacheKey, data, ttlMs, etag);
        return data;
      } finally {
        inFlightRequests.delete(cacheKey);
      }
    })();

    inFlightRequests.set(cacheKey, promise);
    return promise;
  };

  const revalidateInBackground = async (
    url: string,
    cacheKey: string,
    ttlMs: number,
    currentData: any,
    callback: ((freshData: any) => void) | null,
    fetchOptions: any
  ) => {
    if (inFlightRequests.has(cacheKey)) return;

    const entry = memoryCache.get(cacheKey) || readStorage(cacheKey);
    const etag = entry?.etag;

    const promise = (async () => {
      try {
        const headers: Record<string, string> = {
          'Accept': 'application/json',
          ...(fetchOptions?.headers || {}),
        };
        if (etag) {
          headers['If-None-Match'] = etag;
        }

        const res = await fetch(url, {
          ...fetchOptions,
          headers,
        });

        if (res.status === 304) {
          set(cacheKey, currentData, ttlMs, etag);
          return;
        }

        if (res.ok) {
          const freshData = await res.json();
          const newEtag = res.headers.get('ETag');
          set(cacheKey, freshData, ttlMs, newEtag);

          if (callback && JSON.stringify(freshData) !== JSON.stringify(currentData)) {
            callback(freshData);
          }
        }
      } catch {
        // silent fail for background SWR
      } finally {
        inFlightRequests.delete(cacheKey);
      }
    })();

    inFlightRequests.set(cacheKey, promise);
  };

  return {
    get,
    set,
    has,
    remove,
    invalidate,
    fetchWithCache,
  };
}

