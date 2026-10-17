/**
 * Frontend Cache System for RealHem
 *
 * Implements high-performance multi-tier client-side caching:
 * - Level 1: Fast In-Memory Map (0ms instant access)
 * - Level 2: SessionStorage (persists across tab navigations & back/forward history)
 * - Stale-While-Revalidate (SWR): Returns cached data instantly, background-refreshes seamlessly
 * - Request Deduplication: In-flight duplicate requests share the same promise
 * - Cache Invalidation: Automatic on mutation events (property created/updated/deleted)
 */

import { reactive } from 'vue';

const memoryCache = new Map();
const inFlightRequests = new Map();

const DEFAULT_TTL_MS = 3 * 60 * 1000; // 3 minutes default for property listings
const STORAGE_PREFIX = 'rh_cache_';

/**
 * Generate a consistent storage key from a URL or key string
 */
function getStorageKey(key) {
    return `${STORAGE_PREFIX}${key}`;
}

/**
 * Read from SessionStorage safely
 */
function readStorage(key) {
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

/**
 * Write to SessionStorage safely
 */
function writeStorage(key, data, expiresAt, etag = null) {
    if (typeof window === 'undefined' || !window.sessionStorage) return;
    try {
        window.sessionStorage.setItem(
            getStorageKey(key),
            JSON.stringify({ data, expiresAt, etag, savedAt: Date.now() })
        );
    } catch (e) {
        // Quota exceeded: clean older items
        cleanStorage();
    }
}

/**
 * Evict expired items from SessionStorage if storage gets full
 */
function cleanStorage() {
    if (typeof window === 'undefined' || !window.sessionStorage) return;
    try {
        const now = Date.now();
        const keysToRemove = [];
        for (let i = 0; i < window.sessionStorage.length; i++) {
            const k = window.sessionStorage.key(i);
            if (k && k.startsWith(STORAGE_PREFIX)) {
                try {
                    const item = JSON.parse(window.sessionStorage.getItem(k));
                    if (now > item.expiresAt) {
                        keysToRemove.push(k);
                    }
                } catch {
                    keysToRemove.push(k);
                }
            }
        }
        keysToRemove.forEach(k => window.sessionStorage.removeItem(k));
    } catch {
        // ignore
    }
}

export function useFrontendCache() {
    /**
     * Get item from memory or sessionStorage
     */
    const get = (key) => {
        const now = Date.now();
        // 1. Check memory
        if (memoryCache.has(key)) {
            const entry = memoryCache.get(key);
            if (now < entry.expiresAt) {
                return entry.data;
            }
            memoryCache.delete(key);
        }

        // 2. Check sessionStorage
        const stored = readStorage(key);
        if (stored) {
            // Restore into memory cache for faster repeated access
            memoryCache.set(key, stored);
            return stored.data;
        }

        return null;
    };

    /**
     * Set item into memory & sessionStorage
     */
    const set = (key, data, ttlMs = DEFAULT_TTL_MS, etag = null) => {
        const expiresAt = Date.now() + ttlMs;
        const entry = { data, expiresAt, etag, savedAt: Date.now() };
        memoryCache.set(key, entry);
        writeStorage(key, data, expiresAt, etag);
    };

    /**
     * Check if key exists and is valid
     */
    const has = (key) => {
        return get(key) !== null;
    };

    /**
     * Remove a specific key
     */
    const remove = (key) => {
        memoryCache.delete(key);
        if (typeof window !== 'undefined' && window.sessionStorage) {
            window.sessionStorage.removeItem(getStorageKey(key));
        }
    };

    /**
     * Invalidate all cache entries matching prefix (e.g., 'properties', 'suggestions')
     */
    const invalidate = (prefix = '') => {
        // Clear memory
        for (const k of memoryCache.keys()) {
            if (!prefix || k.startsWith(prefix)) {
                memoryCache.delete(k);
            }
        }
        // Clear storage
        if (typeof window !== 'undefined' && window.sessionStorage) {
            const keysToRemove = [];
            for (let i = 0; i < window.sessionStorage.length; i++) {
                const k = window.sessionStorage.key(i);
                if (k && k.startsWith(STORAGE_PREFIX)) {
                    const actualKey = k.slice(STORAGE_PREFIX.length);
                    if (!prefix || actualKey.startsWith(prefix)) {
                        keysToRemove.push(k);
                    }
                }
            }
            keysToRemove.forEach(k => window.sessionStorage.removeItem(k));
        }
    };

    /**
     * Fetch with caching + Stale-While-Revalidate + Request Deduplication
     *
     * @param {string} url - Target API URL
     * @param {Object} options
     * @param {number} [options.ttlMs] - Cache TTL in ms (default 3 mins)
     * @param {boolean} [options.swr] - Stale-While-Revalidate (default true)
     * @param {Function} [options.onBackgroundUpdate] - Callback if background revalidation brings new data
     * @param {RequestInit} [options.fetchOptions] - Custom fetch options
     */
    const fetchWithCache = async (url, options = {}) => {
        const ttlMs = options.ttlMs || DEFAULT_TTL_MS;
        const swr = options.swr !== false;
        const onBackgroundUpdate = options.onBackgroundUpdate || null;
        const cacheKey = url;

        // 1. Check existing cache
        const cached = get(cacheKey);
        if (cached !== null) {
            if (swr) {
                // Background revalidation
                revalidateInBackground(url, cacheKey, ttlMs, cached, onBackgroundUpdate, options.fetchOptions);
            }
            return cached;
        }

        // 2. Request deduplication (if identical request is already in-flight, await it)
        if (inFlightRequests.has(cacheKey)) {
            return inFlightRequests.get(cacheKey);
        }

        // 3. Perform network fetch
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

    /**
     * Silent background revalidation for Stale-While-Revalidate
     */
    const revalidateInBackground = async (url, cacheKey, ttlMs, currentData, callback, fetchOptions) => {
        if (inFlightRequests.has(cacheKey)) return;

        const entry = memoryCache.get(cacheKey) || readStorage(cacheKey);
        const etag = entry?.etag;

        const promise = (async () => {
            try {
                const headers = {
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
                    // Not modified: refresh expiration
                    set(cacheKey, currentData, ttlMs, etag);
                    return;
                }

                if (res.ok) {
                    const freshData = await res.json();
                    const newEtag = res.headers.get('ETag');
                    set(cacheKey, freshData, ttlMs, newEtag);

                    // If data changed and callback provided, notify component
                    if (callback && JSON.stringify(freshData) !== JSON.stringify(currentData)) {
                        callback(freshData);
                    }
                }
            } catch {
                // Background revalidation failure can be silently swallowed since cached data was already rendered
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

