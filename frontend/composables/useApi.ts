// Shared in-flight controllers registry for client-side AJAX requests
const inFlightControllers = new Set<{ controller: AbortController; url: string; method: string }>();

export function abortInFlightApiRequests(reason = 'navigation') {
  if (!import.meta.client) return;
  inFlightControllers.forEach(({ controller }) => {
    try {
      controller.abort(reason);
    } catch {
      // ignore
    }
  });
  inFlightControllers.clear();
}

export function registerInFlightController(controller: AbortController, url = '', method = 'GET') {
  if (!import.meta.client) return () => {};
  const item = { controller, url, method };
  inFlightControllers.add(item);
  return () => {
    inFlightControllers.delete(item);
  };
}

export function useApi() {
  const config = useRuntimeConfig();
  const apiBase = config.public.apiBase || 'http://localhost:8000';

  /**
   * Helper to execute API requests with optional Bearer token and automatic cancellation tracking
   */
  const request = async (endpoint: string, options: any = {}) => {
    let token = null;
    if (import.meta.client) {
      token = localStorage.getItem('realhem_user_token') || localStorage.getItem('realhem_admin_token');
    }

    const headers = {
      'Accept': 'application/json',
      ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
      ...(options.headers || {}),
    };

    const url = endpoint.startsWith('http') ? endpoint : `${apiBase}${endpoint.startsWith('/') ? '' : '/'}${endpoint}`;
    const method = (options.method || 'GET').toUpperCase();

    let unregister: (() => void) | null = null;
    let signal = options.signal;

    // Client-side requests are registered as cancellable on route changes unless persist: true is passed
    if (import.meta.client && !signal && options.persist !== true) {
      const controller = new AbortController();
      signal = controller.signal;
      unregister = registerInFlightController(controller, url, method);
    }

    try {
      return await $fetch(url, {
        ...options,
        signal,
        headers,
      });
    } catch (err: any) {
      if (err?.name === 'AbortError' || err?.message?.includes('aborted') || signal?.aborted) {
        const abortErr = new Error('Request aborted');
        abortErr.name = 'AbortError';
        (abortErr as any).isAbort = true;
        throw abortErr;
      }
      throw err;
    } finally {
      if (unregister) {
        unregister();
      }
    }
  };

  const get = (endpoint: string, options: any = {}) => request(endpoint, { ...options, method: 'GET' });
  const post = (endpoint: string, body?: any, options: any = {}) => request(endpoint, { ...options, method: 'POST', body });
  const put = (endpoint: string, body?: any, options: any = {}) => request(endpoint, { ...options, method: 'PUT', body });
  const del = (endpoint: string, options: any = {}) => request(endpoint, { ...options, method: 'DELETE' });

  return {
    apiBase,
    request,
    get,
    post,
    put,
    del,
    abortInFlightApiRequests,
  };
}
