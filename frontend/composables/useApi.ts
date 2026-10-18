export function useApi() {
  const config = useRuntimeConfig();
  const apiBase = config.public.apiBase || 'http://localhost:8000';

  /**
   * Helper to execute API requests with optional Bearer token
   */
  const request = async (endpoint, options = {}) => {
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

    return $fetch(url, {
      ...options,
      headers,
    });
  };

  const get = (endpoint, options = {}) => request(endpoint, { ...options, method: 'GET' });
  const post = (endpoint, body, options = {}) => request(endpoint, { ...options, method: 'POST', body });
  const put = (endpoint, body, options = {}) => request(endpoint, { ...options, method: 'PUT', body });
  const del = (endpoint, options = {}) => request(endpoint, { ...options, method: 'DELETE' });

  return {
    apiBase,
    request,
    get,
    post,
    put,
    del,
  };
}

